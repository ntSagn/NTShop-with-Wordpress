<?php 
namespace EmailKit\Admin\Emails\Woocommerce;

use WP_Query;
use EmailKit\Admin\Emails\EmailLists;

defined("ABSPATH") || exit ;

class CancelledOrder
{

	private $db_query_class = null;

	public function __construct()
	{
		$args = array(
			'post_type'  => 'emailkit',
			'meta_query' => array(
				array(
					'key'   => 'emailkit_template_type',
					'value' => EmailLists::CANCELLED_ORDER,
				),
				array(
					'key'   => 'emailkit_template_status',
					'value' => 'Active',
				),
			),
		);

		$this->db_query_class = new WP_Query($args);

		if (isset($this->db_query_class->posts[0])) {
			add_action('woocommerce_email', [$this, 'remove_woocommerce_emails']);
		}

		add_filter('woocommerce_order_status_processing_to_cancelled_notification', [$this, 'cancelOrder'], 10, 2);
		add_filter('woocommerce_order_status_on-hold_to_cancelled_notification', [$this, 'cancelOrder'], 10, 2);
	}

	public function remove_woocommerce_emails($email_class)
	{

		remove_action('woocommerce_order_status_processing_to_cancelled_notification', array($email_class->emails['WC_Email_Cancelled_Order'], 'trigger'));
		remove_action('woocommerce_order_status_on-hold_to_cancelled_notification',  array($email_class->emails['WC_Email_Cancelled_Order'], 'trigger'));
	}

	public function cancelOrder($order_id, $order)
	{


		$query = $this->db_query_class;
		$email = get_option('admin_email');
		if (isset($query->posts[0])) {
			$html  = get_post_meta($query->posts[0]->ID, 'emailkit_template_content_html', true);

			$woocommerce_currency_settings = get_woocommerce_currency_symbol();
			$replacements = [];
			foreach ($order->get_items() as $item_id => $item) {
				$product = $item->get_product();
				$id = $item['product_id'];
				$product_name = $item['name'];
				$item_qty = $item['quantity'];
				$item_total = $item['total'];
				$product_price = $product->get_price() * $item_qty;

				// Use the currency symbol from the WooCommerce settings
				$currency_symbol = $woocommerce_currency_settings;

				// Format the product price with the currency symbol
				$formatted_product_price = $currency_symbol . number_format($product_price, 2);

				// Format the item total with the currency symbol
				$formatted_item_total = $currency_symbol . number_format($item_total, 2);

				$replacements[] = [$product_name, $item_qty, $formatted_item_total, $formatted_product_price];

			}

			$html = \EmailKit\Admin\Emails\Helpers\Utils::order_items_replace($html, $replacements);

			$order = wc_get_order($order_id);

			$shipping_first_name = $order->get_shipping_first_name();
			$shipping_last_name = $order->get_shipping_last_name();
			$shipping_full_name = $shipping_first_name . ' ' . $shipping_last_name;

			$details = [
				"{{order_id}}" =>  $order->get_id(),
				"{{order_number}}" => $order->get_order_number(),
				"{{order_status}}" => $order->get_status(),
				"{{order_currency}}" => $order->get_currency(),
				"{{billing_name}}"   => $order->get_formatted_billing_full_name(),
				"{{billing_phone}}" => $order->get_billing_phone(),
				"{{shipping_total}}" => wc_price( $order->get_shipping_total() ),
				"{{order_subtotal}}" => wc_price( $order->get_subtotal()),
				"{{shipping_tax_total}}" => wc_format_decimal($order->get_shipping_tax(), 2),
				"{{order_date}}" => gmdate('Y-m-d H:i:s', strtotime(get_post($order->get_id())->post_date)),
				"{{shipping_method}}" => $order->get_shipping_method(),
				"{{payment_method}}" => $order->get_payment_method_title(),
				"{{total}}" => wc_price($order->get_total(), 2),
				"{{billing_first_name}}" => $order->get_billing_first_name(),
				"{{billing_last_name}}" =>  $order->get_billing_last_name(),
				"{{billing_company}}" => $order->get_billing_company(),
				"{{billing_address_1}}" => $order->get_billing_address_1(),
				"{{billing_address_2}}" => $order->get_billing_address_2(),
				"{{billing_city}}" => $order->get_billing_city(),
				"{{billing_state}}" => $order->get_billing_state(),
				"{{billing_postcode}}" => $order->get_billing_postcode(),
				"{{billing_country}}" => $order->get_billing_country(),
				"{{billing_email}}"    => $order->get_billing_email(),
				"{{shipping_name}}" => $shipping_full_name,
				"{{shipping_company}}" => $order->get_shipping_company(),
				"{{shipping_address_1}}" => $order->get_shipping_address_1(),
				"{{shipping_address_2}}" => $order->get_shipping_address_2(),
				"{{shipping_city}}" => $order->get_shipping_city(),
				"{{shipping_state}}" => $order->get_shipping_state(),
				"{{shipping_postcode}}" => $order->get_shipping_postcode(),
				"{{shipping_country}}" => $order->get_shipping_country(),
				"{{shipping_phone}}" => $order->get_shipping_phone(),
				"{{customer_note}}" => $order->get_customer_note(),
				"{{download_permissions}}" => $order->is_download_permitted() ? $order->is_download_permitted() : 0,
				"{{shipping_first_name}}" => $shipping_first_name,
				"{{shipping_last_name}}" => $shipping_last_name,
			];

			$message  = str_replace(array_keys($details), array_values($details), apply_filters('emailkit_shortcode_filter', $html));
			$to       = get_option('admin_email');
			

			$pre_header = get_post_meta($query->posts[0]->ID, 'emailkit_email_preheader', true);
			$pre_header = !empty($pre_header) ? $pre_header : esc_html__("Cancelled Order", 'emailkit');
			$subject = str_replace(array_keys($details), array_values($details), get_post_meta($query->posts[0]->ID, 'emailkit_email_subject', true));
			$subject = !empty($subject) ? $subject . ' - ' . $pre_header : esc_attr($order->get_billing_first_name() . " " . $order->get_billing_last_name()) . ", " . esc_attr("Your Order has been cancelled order.#$order_id - " . "on"." " . get_bloginfo('name')) . ' - '. $pre_header;
			


			$headers = [
				'From: ' . $email . "\r\n",
				'Reply-To: ' . $email . "\r\n",
				'Content-Type: text/html; charset=UTF-8',
			];

			wp_mail($to, $subject, $message, $headers);
		}
	}
}