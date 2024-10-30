<?php
/**
 * Template
 *
 * @package    Contact_Form_7_Redirect
 * @subpackage Contact_Form_7
 */

// phpcs:disable Generic.Formatting.MultipleStatementAlignment.NotSameWarning
$cf7_id        = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$selected_page = get_post_meta( $cf7_id, 'redirect_page', true );

$selected_post_type = get_post_meta( $cf7_id, 'redirect_post_type', true );
$selected_post_type = ! empty( $selected_post_type ) ? $selected_post_type : 'page';
$get_posts          = $this->cf7_redirect_get_posts_by_cpt( $selected_post_type );

$checked = get_post_meta( $cf7_id, 'use_external_url', true );
$external_url = get_post_meta( $cf7_id, 'external_url', true );
$enable_redirect = get_post_meta( $cf7_id, 'enable_redirect', true );
$get_post_types = get_post_types(
	array(
		'public' => true,
	)
);
unset( $get_post_types['attachment'] );
$get_post_types = array_filter( $get_post_types );
?>
<div class="cf7-redirect-wrap">
	<div class="enable-redirection">
		<input type="checkbox" name="enable_redirect" <?php checked( 1, $enable_redirect ); ?>>
		<label><?php esc_html_e( 'Enable redirection.', 'cf7-thank-you-page' ); ?></label>
	</div>
	<div class="cf7-redirect-url cf7-redirect-hidden">
		<div class="external-url">
			<input type="checkbox" name="custom_link"<?php checked( $checked, 1 ); ?>><label> <?php esc_html_e( 'Use external URL?', 'cf7-thank-you-page' ); ?></label>
			<input type="text" name="external-url" class="cf7-redirect-hidden url-textbox" placeholder="<?php esc_attr_e( 'Enter url', 'cf7-thank-you-page' ); ?>" value="<?php echo ! empty( $external_url ) ? esc_attr( $external_url ) : ''; ?>" required>
		</div>
		<div class="dynamic-url">
			<label> <?php esc_html_e( 'Select post type : ', 'cf7-thank-you-page' ); ?>
			<select name="all_posts_type" class="all_post_types">
				<?php foreach ( $get_post_types as $slug => $name ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>"<?php selected( $selected_post_type, $slug ); ?>><?php echo esc_html( ucfirst( $name ) ); ?></option>
				<?php endforeach; ?>
			</select></label>
			<div class="divider"><span class="dashicons dashicons-arrow-right-alt"></span></div>
			<select name="all_pages">
				<option value=""><?php esc_html_e( '--None--', 'cf7-thank-you-page' ); ?></option>
				<?php
				if ( ! empty( $get_posts ) ) {
					foreach ( $get_posts as $p ) :
						?>
						<option value="<?php echo esc_attr( $p->ID ); ?>"<?php selected( $selected_page, $p->ID ); ?>> <?php echo esc_html( ucfirst( $p->post_title ) ); ?> </option>
						<?php
					endforeach;
				}
				?>
			</select>
		</div>
	</div>
</div>
