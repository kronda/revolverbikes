<?php
/**
 * @package Make
 */

$footer_text = ttfmake_sanitize_text( get_theme_mod( 'footer-text', ttfmake_get_default( 'footer-text' ) ) );
?>

<?php if ( $footer_text ) : ?>
<div class="footer-text">
	<?php echo ttfmake_sanitize_text( $footer_text ); ?>
</div>
<?php endif; ?>

<div class="site-info">
	<span class="theme-name">Make</span>
	<span class="theme-by"><?php _ex( 'by', 'attribution', 'make' ); ?></span>
	<span class="theme-author">
		<a title="The Theme Foundry <?php esc_attr_e( 'homepage', 'make' ); ?>" href="https://thethemefoundry.com/">
			The Theme Foundry
		</a>
	</span>
</div>