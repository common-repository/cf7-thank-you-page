<?php
/**
 * Create class `Contact_Form_7_Redirect`
 *
 * @package Contact_Form_7_Redirect
 * @subpackage Contact_Form_7
 */

// If check class exists or not.
if ( ! class_exists( 'Contact_Form_7_Redirect' ) ) {

	/**
	 * Declare class `Contact_Form_7_Redirect`
	 */
	class Contact_Form_7_Redirect {

		/**
		 * Calling class construct.
		 */
		public function __construct() {
			// If check contact form 7 active or not.
			if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
				if ( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'install-plugin', 'activate' ) ) ) {  // phpcs:ignore
					return;
				}
				add_action( 'admin_notices', array( $this, 'cf7_redirect_admin_notices' ) );
				return;
			}

			// Contact form 7 tab panel.
			add_filter( 'wpcf7_editor_panels', array( $this, 'cf7_redirect_panels' ) );

			// Save contact form.
			add_action( 'wpcf7_save_contact_form', array( $this, 'cf7_redirect_save_form' ) );

			// Redirect process.
			add_action( 'wp_footer', array( $this, 'cf7_redirect_process' ), PHP_INT_MAX );

			// Enqueue admin style.
			add_action( 'admin_enqueue_scripts', array( $this, 'cf7_redirect_admin_scripts' ) );

			// Ajax.
			add_action( 'wp_ajax_cf7_redirect_action', array( $this, 'cf7_redirect_select_posts' ) );
			add_action( 'wp_ajax_nopriv_cf7_redirect_action', array( $this, 'cf7_redirect_select_posts' ) );
		}

		/**
		 * Get all pages.
		 *
		 * @param string $cpt post types.
		 * @return array
		 */
		private function cf7_redirect_get_posts_by_cpt( $cpt = 'page' ) {
			$args  = array(
				'post_type'      => $cpt,
				'posts_per_page' => -1,
			);
			$args  = apply_filters( 'cf7_redirect_pages_dropdown_query', $args );
			$pages = get_posts( $args );
			return apply_filters( 'cf7_redirect_pages_list', $pages );
		}

		/**
		 * Contact form 7 redirect panel.
		 *
		 * @param array $panels panels.
		 * @return array.
		 */
		public function cf7_redirect_panels( $panels ) {
			$panels['redirect-panels'] = array(
				'title'    => __( 'Redirect to page', 'cf7-thank-you-page' ),
				'callback' => array( $this, 'cf7_redirect_panels_callback' ),
			);
			return $panels;
		}

		/**
		 * Template
		 */
		public function cf7_redirect_panels_callback() {
			require plugin_dir_path( __FILE__ ) . '../templates/redirect-setting-page.php';
		}

		/**
		 * Save contact form.
		 */
		public function cf7_redirect_save_form() {
			$cf7_id = (int) filter_input( INPUT_POST, 'post_ID', FILTER_SANITIZE_NUMBER_INT );
			if ( isset( $_POST['all_pages'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$page_id = (int) filter_input( INPUT_POST, 'all_pages', FILTER_SANITIZE_NUMBER_INT );
				update_post_meta( $cf7_id, 'redirect_page', $page_id );
			}
			if ( isset( $_POST['all_posts_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$post_name = isset( $_POST['all_posts_type'] ) ? sanitize_text_field( wp_unslash( $_POST['all_posts_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				update_post_meta( $cf7_id, 'redirect_post_type', $post_name );
			}

			$custom_link = isset( $_POST['custom_link'] ) ? 1 : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_post_meta( $cf7_id, 'use_external_url', $custom_link );

			if ( $custom_link && isset( $_POST['external-url'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$external_url = isset( $_POST['external-url'] ) ? sanitize_text_field( wp_unslash( $_POST['external-url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				update_post_meta( $cf7_id, 'external_url', $external_url );
			} else {
				delete_post_meta( $cf7_id, 'external_url' );
			}

			$enable_redirect = isset( $_POST['enable_redirect'] ) ? 1 : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_post_meta( $cf7_id, 'enable_redirect', $enable_redirect );
		}

		/**
		 * Redirect process.
		 */
		public function cf7_redirect_process() {
			// If check contact form 7 script exists or not.
			if ( wp_script_is( 'contact-form-7' ) ) {
				?>
				<script type="text/javascript">
					document.addEventListener( 'wpcf7mailsent', function( e ) {
						var redirectUrls = '<?php echo wp_json_encode( $this->cf7_redirect_get_all_contact_form() ); ?>';
						redirectUrls = JSON.parse( redirectUrls );
						if ( redirectUrls.length > 0 ) {
							var currentFormId = e.detail.contactFormId;
							var result = redirectUrls.filter( function( v,i ) { // Find out array in current form id.
								return v['id'] == currentFormId;
							} );
							if ( result.length > 0 ) {
								window.location.href = result[0].url;
							}
						}
					}, false );
				</script>
				<?php
			}
		}

		/**
		 * Get all contact form.
		 *
		 * @return array
		 */
		public function cf7_redirect_get_all_contact_form() {
			$redirect_url = array();
			$contact_ids  = get_posts(
				array(
					'post_type'   => 'wpcf7_contact_form',
					'numberposts' => -1,
					'fields'      => 'ids',
				)
			);
			foreach ( $contact_ids as $contact_id ) {
				$use_external_url = get_post_meta( $contact_id, 'use_external_url', true );
				$enable_redirect  = get_post_meta( $contact_id, 'enable_redirect', true );
				if ( ! empty( $enable_redirect ) ) {
					if ( ! empty( $use_external_url ) ) {
						$url = get_post_meta( $contact_id, 'external_url', true );
					} else {
						$page_id = get_post_meta( $contact_id, 'redirect_page', true );
						$url     = get_the_permalink( $page_id );
					}
					if ( $url ) {
						$redirect_url[] = array(
							'id'  => $contact_id,
							'url' => $url,
						);
					}
				}
			}
			return $redirect_url;
		}

		/**
		 * Admin notices callback function.
		 */
		public function cf7_redirect_admin_notices() {
			if ( ! file_exists( WP_PLUGIN_DIR . '/contact-form-7/wp-contact-form-7.php' ) ) {
				$action = 'install-plugin';
				$plugin = 'contact-form-7';

				$install_plugin = wp_nonce_url(
					add_query_arg(
						array(
							'action' => $action,
							'plugin' => $plugin,
						),
						admin_url( 'update.php' )
					),
					$action . '_' . $plugin
				);

				$link = sprintf( '<a href="%1$s">%2$s</a>', $install_plugin, __( 'Click to install', 'cf7-thank-you-page' ) );
			} else {
				$action = 'activate';
				$plugin = 'contact-form-7/wp-contact-form-7.php';

				$activate_plugin = wp_nonce_url(
					add_query_arg(
						array(
							'action' => $action,
							'plugin' => $plugin,
						),
						admin_url( 'plugins.php' )
					),
					'activate-plugin_' . $plugin
				);

				$link = sprintf( '<a href="%1$s">%2$s</a>', $activate_plugin, __( 'Click to active', 'cf7-thank-you-page' ) );
			}
			?>
			<div class="notice notice-error">
				<p>
					<?php
					esc_html_e( 'Please install and activate Contact Form 7 plugin before activating Contact form 7 Redirect. ', 'cf7-thank-you-page' );
					echo wp_kses( $link, array( 'a' => array( 'href' => array() ) ) );
					?>
				</p>
			</div>
			<?php
		}

		/**
		 * Enqueue admin style.
		 */
		public function cf7_redirect_admin_scripts() {
			// Enqueue css.
			wp_enqueue_style( 'cf7-redirect-style', plugin_dir_url( __FILE__ ) . '../assets/css/style.css', array(), CF7_REDIRECT_VERSION );

			// Enqueue js.
			wp_enqueue_script( 'cf7-redirect-script', plugin_dir_url( __FILE__ ) . '../assets/js/script.js', array(), CF7_REDIRECT_VERSION, false );

			// Ajax.
			wp_localize_script(
				'cf7-redirect-script',
				'cf7_redirect',
				array(
					'ajaxurl'        => admin_url( 'admin-ajax.php' ),
					'security_nonce' => wp_create_nonce( 'cf7_redirect_get_posts' ),
				)
			);
		}

		/**
		 * Select post type.
		 */
		public function cf7_redirect_select_posts() {
			check_ajax_referer( 'cf7_redirect_get_posts', 'nonce' );

			$option = '<option value="">' . __( '--None--', 'cf7-thank-you-page' ) . '</option>';
			if ( isset( $_POST['selected_post_type'] ) && ! empty( $_POST['selected_post_type'] ) ) {
				$cpts = $this->cf7_redirect_get_posts_by_cpt( sanitize_text_field( wp_unslash( $_POST['selected_post_type'] ) ) );
				if ( ! empty( $cpts ) ) {
					foreach ( $cpts as $cpt ) :
						$option .= '<option value="' . $cpt->ID . '">' . esc_html( ucfirst( $cpt->post_title ) ) . '</option>';
					endforeach;
				}
			}
			wp_send_json(
				array(
					'status' => 1,
					'html'   => $option,
				)
			);
			exit;
		}
	}
}
