<?php
/**
 * Define the routes for this plugin for enable REST Routs for API.
 *
 * @since    1.0.0
 * @access   private
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wpstorm_Shortener_Routes.
 */
class Wpstorm_Shortener_Routes {
	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static object $instance;


	/**
	 * Initiator
	 *
	 * @return object Initialized object of class.
	 * @since 1.0.0
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
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$version   = '1';
		$namespace = 'wpstorm-shortener/v' . $version;

		//Register settings_options rest route
		register_rest_route( $namespace, '/' . 'settings_options', [
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'url_to_short_code' ],
				'permission_callback' => [ $this, 'permissions_check' ],
			],
		] );

	}

	/**
	 * @throws Exception
	 */
	public function url_to_short_code( $url ) {
		if ( empty( $url ) ) {
			throw new Exception( __( 'No URL was supplied.', 'wpstorm-shortener' ) );
		}

		if ( ! Wpstorm_Shortener_Core::validateUrlFormat( $url ) ) {
			throw new Exception( __( 'URL does not have a valid format.', 'wpstorm-shortener' ) );
		}

		if ( Wpstorm_Shortener_Core::$checkUrlExists ) {
			if ( ! Wpstorm_Shortener_Core::verifyUrlExists( $url ) ) {
				throw new Exception( __( 'URL does not appear to exist.', 'wpstorm-shortener' ) );
			}
		}

		$shortCode = Wpstorm_Shortener_Core::urlExistsInDB( $url );
		if ( ! $shortCode ) {
			$shortCode = Wpstorm_Shortener_Core::createShortCode( $url );
		}

		return $shortCode;
	}

	/**
	 * Check if a given request has permissions
	 *
	 * @return bool
	 */
	public function permissions_check( $request ) {
		//return true; <--use to make readable by all
		return true;
	}

}

Wpstorm_Shortener_Routes::get_instance();
