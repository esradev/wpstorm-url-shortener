<?php
/**
 * Plugin Name: Wpstorm Url Shortener
 * Plugin URI: https://wpstorm.ir
 * Description: Complete plugin for generate short links with your own custom domain.
 * Version: 1.0
 * Author: Wpstorm
 * Author URI: https://wpstorm.ir
 * Text Domain: wpstorm-shortener
 * Domain Path: /languages
 * License: GPL v2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Farazsms.
 */
class Wpstorm_Shortener {


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
		$this->define_constants();
		$this->wpstorm_url_shortener_loader();

		register_activation_hook( __FILE__, [ $this, 'activate_farazsms' ] );
		add_action( 'activated_plugin', [ $this, 'farazsms_activation_redirect' ] );
	}

	/**
	 * Defines all constants
	 *
	 * @since 2.0.0
	 */
	public function define_constants() {

		/**
		 * Defines all constants
		 *
		 * @since 2.0.0
		 */
		define( 'WPSTORM_SHORTENER_VER', '1.0.0' );
		define( 'WPSTORM_SHORTENER_FILE', __FILE__ );
		define( 'WPSTORM_SHORTENER_PATH', plugin_dir_path( WPSTORM_SHORTENER_FILE ) );
		define( 'WPSTORM_SHORTENER_BASE', plugin_basename( WPSTORM_SHORTENER_FILE ) );
		define( 'WPSTORM_SHORTENER_SLUG', 'wpstorm_shortener_link_settings' );
		define( 'WPSTORM_SHORTENER_SETTINGS_LINK', admin_url( 'admin.php?page=' . WPSTORM_SHORTENER_SLUG ) );
		define( 'WPSTORM_SHORTENER_URL', plugins_url( '/', WPSTORM_SHORTENER_FILE ) );
		define( 'WPSTORM_SHORTENER_WEB_MAIN', 'https://wpstorm.ir/' );
		define( 'WPSTORM_SHORTENER_WEB_MAIN_DOC', WPSTORM_SHORTENER_WEB_MAIN . 'url-shortener/' );
	}

	/**
	 * Require loader farazsms class.
	 *
	 * @return void
	 */
	public function wpstorm_url_shortener_loader() {
		require WPSTORM_SHORTENER_PATH . 'classes/wpstorm-shortener-loader.php';
	}

	/**
	 * Require farazsms activator class.
	 *
	 * @return void
	 */
	public function activate_farazsms() {
		require_once WPSTORM_SHORTENER_PATH . 'classes/wpstorm-shortener-activator.php';
		Wpstorm_Shortener_Activator::activate();
	}


	/**
	 * Redirect user to plugin settings page after plugin activated.
	 *
	 * @return void
	 */
	public function farazsms_activation_redirect() {
		if ( get_option( 'wpstorm_shortener_do_activation_redirect', false ) ) {
			delete_option( 'wpstorm_shortener_do_activation_redirect' );
			exit( wp_redirect( WPSTORM_SHORTENER_SETTINGS_LINK ) );
		}
	}

}

Wpstorm_Shortener::get_instance();


