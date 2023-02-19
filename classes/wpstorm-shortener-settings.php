<?php

/**
 * Wpstorm Url Shortener Settings.
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wpstorm_Shortener_Settings.
 */
class Wpstorm_Shortener_Settings {
	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 2.0.0
	 */
	private static object $instance;

	/**
	 * Initiator
	 *
	 * @return object Initialized object of class.
	 * @since 2.0.0
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_styles' ] );
		add_action( 'admin_menu', [ $this, 'init_menu' ] );
		add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 60 );
		add_filter( 'plugin_action_links_' . WPSTORM_SHORTENER_BASE, [ $this, 'settings_link' ] );
		add_action( 'wp_dashboard_setup', [ $this, 'rss_meta_box' ] );
	}




	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_styles() {
		wp_enqueue_style( 'wpstorm-shortener-style', WPSTORM_SHORTENER_URL . 'build/index.css' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts( $hook ) {
		wp_enqueue_script(
			'wpstorm-shortener-script',
			WPSTORM_SHORTENER_URL . 'build/index.js',
			[
				'wp-element',
				'wp-i18n',
			],
			'1.0.0',
			true
		);

		/*
		 * Add a localization object ,The base rest api url and a security nonce
		 * @see https://since1979.dev/snippet-014-setup-axios-for-the-wordpress-rest-api/
		 * */
		wp_localize_script(
			'wpstorm-shortener-script',
			'wpstormShortenerJsObject',
			[
				'rootapiurl'    => esc_url_raw( rest_url() ),
				'nonce'         => wp_create_nonce( 'wp_rest' ),
			]
		);

		// Load Wpstorm_Shortener languages for JavaScript files.
		wp_set_script_translations( 'wpstorm-shortener-script', 'wpstorm-shortener', WPSTORM_SHORTENER_PATH . '/languages' );
	}

	/**
	 * Add Admin Menu.
	 *
	 * @return void
	 */
	public function init_menu() {
		add_menu_page(
			__( 'Wpstorm Url Shortener', 'wpstorm-shortener' ),
			__( 'Url Shortener', 'wpstorm-shortener' ),
			'manage_options',
			WPSTORM_SHORTENER_SLUG,
			[
				$this,
				'admin_page',
			],
			'dashicons-admin-links',
			1
		);
	}

	/**
	 * Init Admin Page.
	 *
	 * @return void
	 */
	public function admin_page() {
		include_once WPSTORM_SHORTENER_CLASSES_PATH . 'wpstorm-shortener-admin-page.php';
	}

	/**
	 * Add bar menu. Show some links for Wpstorm Url Shortener plugin on the admin bar.
	 *
	 * @since 1.0.0
	 */
	public function admin_bar_menu() {
		global $wp_admin_bar;
		if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
			return;
		}

		$wp_admin_bar->add_menu(
			[
				'id'     => 'wpstorm-shortener',
				'parent' => null,
				'group'  => null,
				'title'  => __( 'Wpstorm Url Shortener', 'wpstorm-shortener' ),
				'meta'   => [
					'title' => __( 'Wpstorm Url Shortener', 'wpstorm-shortener' ),
					// This title will show on hover
				],
			]
		);

		$wp_admin_bar->add_menu(
			[
				'parent' => 'farazsms',
				'title'  => __( 'Generate Short Link', 'wpstorm-shortener' ),
				'id'     => 'wpstorm-generate-short-link',
				'href'   => get_bloginfo( 'url' ) . '/wp-admin/' . WPSTORM_SHORTENER_SETTINGS_LINK,
			]
		);


	}

	/**
	 * Plugin settings link on all plugins page.
	 *
	 * @since 2.0.0
	 */
	public function settings_link( $links ) {
		// Add settings link
		$settings_link = '<a href="' . WPSTORM_SHORTENER_SETTINGS_LINK . '">Settings</a>';

		// Add document link
		$doc_link = '<a href="' . WPSTORM_SHORTENER_WEB_MAIN_DOC . '" target="_blank" rel="noopener noreferrer">Docs</a>';
		array_push( $links, $settings_link, $doc_link );

		return $links;

	}

	public function rss_meta_box() {
		if ( get_option( 'fsms_rss_meta_box', '1' ) == '1' ) {
			add_meta_box(
				'Wpstorm_Shortener_Rss',
				__( 'Wpstorm latest news', 'wpstorm-shortener' ),
				[
					$this,
					'rss_postbox_container',
				],
				'dashboard',
				'side',
				'low'
			);
		}

	}

	public function rss_postbox_container() {
		?>
		<div class="wpstorm-shortener-rss-widget">
			<?php
			wp_widget_rss_output(
				'https://wpstorm.ir/feed/',
				[
					'items'        => 3,
					'show_summary' => 1,
					'show_author'  => 1,
					'show_date'    => 1,
				]
			);
			?>
		</div>
		<?php

	}

}

Wpstorm_Shortener_Settings::get_instance();
