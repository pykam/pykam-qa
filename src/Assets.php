<?php
namespace PykamQA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders all admin/public assets required by the plugin.
 */
class Assets {

	/**
	 * Wires WordPress hooks responsible for asset loading.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_styles' ) );
	}

	/**
	 * Loads admin styles/scripts on Q&A edit screens.
	 *
	 * @param string $hook
	 *
	 * @return void
	 */
	public function enqueue_admin_styles( $hook ) {
		global $post_type;

		if ( ( $hook === 'post.php' || $hook === 'post-new.php' ) && $post_type === 'pykam-qa' ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-dialog' );

			// Admin CSS
			wp_enqueue_style( 'pykam-qa-admin', constant( 'PYKAM_QA_URL' ) . '/assets/admin/styles.css' );

			// Enqueue admin script (moved from inline) and localize strings/data
			wp_enqueue_script(
				'pykam-qa-admin',
				constant( 'PYKAM_QA_URL' ) . '/assets/admin/scripts.js',
				array( 'jquery', 'jquery-ui-dialog' ),
				defined( 'PYKAM_QA_VERSION' ) ? PYKAM_QA_VERSION : false,
				true
			);

			wp_localize_script(
				'pykam-qa-admin',
				'pykamQaAdmin',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'pykam_qa_get_posts_nonce' ),
					'i18n' => array(
						'loadingPosts' => __( 'Loading posts...', 'pykam-qa' ),
						'view' => __( 'View', 'pykam-qa' ),
						'postSelected' => __( 'Post selected successfully!', 'pykam-qa' ),
						'loadingError' => __( 'Error loading posts', 'pykam-qa' ),
						'noPostsFound' => __( 'No posts found', 'pykam-qa' ),
						'previous' => __( 'Previous', 'pykam-qa' ),
						'next' => __( 'Next', 'pykam-qa' ),
					),
				)
			);
		}
	}

	/**
	 * Enqueues front-end styles for the Q&A block.
	 *
	 * @return void
	 */
	public function enqueue_public_styles() {
		wp_enqueue_style( 'pykam-qa-styles', constant( 'PYKAM_QA_URL' ) . '/assets/public/pykam-qa.css' );
	}
}
