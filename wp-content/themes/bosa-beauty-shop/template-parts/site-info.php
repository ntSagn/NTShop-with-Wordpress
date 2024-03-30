<?php
/**
 * Template part for displaying site info
 *
 * @package Bosa Beauty Shop 1.0.0
 */

?>

<div class="site-info">
	<?php echo wp_kses_post( html_entity_decode( esc_html__( 'Copyright &copy; ' , 'bosa-beauty-shop' ) ) );
		echo esc_html( date( 'Y' ) );
		printf( esc_html__( ' Bosa Beauty Shop. Powered by', 'bosa-beauty-shop' ) );
	?>
	<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'bosa-beauty-shop' ) ); ?>" target="_blank">
		<?php
			printf( esc_html__( 'WordPress', 'bosa-beauty-shop' ) );
		?>
	</a>
</div><!-- .site-info -->