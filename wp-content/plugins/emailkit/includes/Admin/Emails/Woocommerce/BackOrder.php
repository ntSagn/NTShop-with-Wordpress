<?php 

namespace EmailKit\Admin\Emails\Woocommerce;

use WP_Query;
use EmailKit\Admin\Emails\EmailLists;
defined( 'ABSPATH' ) || exit;

class BackOrder {

	private $db_query_class = null;
	public function __construct() {
       
		$args = [
			'post_type'  => 'emailkit',
			'meta_query' => [
				[
					'key'   => 'emailkit_template_type',
					'value' => EmailLists::BACK_ORDER,
				],
				[
					'key'   => 'emailkit_template_status',
					'value' => 'Active',
				],
			],
		];


		$this->db_query_class = new WP_Query($args);

		if (isset($this->db_query_class->posts[0])) {
			add_action('woocommerce_email', [$this, 'remove_woocommerce_emails']);
		}
		
		add_filter('woocommerce_product_on_backorder_notification', [$this, 'stockNotification'], 10, 1);
	}


	public function remove_woocommerce_emails($email_class)
	{

		remove_action('woocommerce_product_on_backorder_notification', [$email_class, 'backorder']);
	}

	public function stockNotification($product)
	{


		$query = $this->db_query_class;
		$email = get_option('admin_email');
		if (isset($query->posts[0])) {
			$html  = get_post_meta($query->posts[0]->ID, 'emailkit_template_content_html', true);
			$tbody = substr($html, strpos($html, '<tbody'));
			$row   = strpos($tbody, '</tbody>');
			$rows = '';
			$html = str_replace($row, $rows, $html);

			$details = [

				"{{stock_status}}"   	=>  $product['product']->get_stock_status(),
				"{{product_name}}"      =>  $product['product']->get_name(),
				"{{stock_quantity}}" 	=>  $product['product']->get_stock_quantity(),
				"{{status}}"         	=>  $product['product']->get_status(),
				"{{product_id}}"     	=>  $product['product']->get_id(),
				"{{short_description}}" => 	$product['product']->get_short_description(),
				"{{product_price}}"     => 	$product['product']->get_price(),
				"{{manage_stock}}"      => 	$product['product']->get_manage_stock(),
				"{{sku}}"               => 	$product['product']->get_sku(),
				"{{backorders}}"        => 	$product['product']->get_backorders(),
				"{{site_name}}"         => 	get_bloginfo('name'),
				"{{display_name}}"      =>  wp_get_current_user()->display_name,

			];
			$message  	= str_replace(array_keys($details), array_values($details), apply_filters('emailkit_shortcode_filter', $html));
			$to      	= get_option('admin_email');

			$subject 	= str_replace(array_keys($details), array_values($details), get_post_meta($query->posts[0]->ID, 'emailkit_email_subject', true));
			$pre_header = get_post_meta($query->posts[0]->ID, 'emailkit_email_preheader', true);
			$pre_header = !empty($pre_header) ? $pre_header : esc_html__(' Product is on backorder', 'emailkit');
			$subject 	= !empty($subject) ? $subject . ' - ' . $pre_header : $product['product']->get_name() . " - " .esc_html__(' is on backorder', 'emailkit').' - ' . $pre_header;
		


			$headers = [
				'From: ' . $email . "\r\n",
				'Reply-To: ' . $email . "\r\n",
				'Content-Type: text/html; charset=UTF-8',
			];

			wp_mail($to, $subject, $message, $headers);
		}
	}
}