<?php

/**
 * Wpstorm Url Shortener core.
 *
 * @since 1.0.0
 */

/**
 * Class to create short URLs and decode shortened URLs
 *
 * @author CodexWorld.com <contact@codexworld.com>
 * @copyright Copyright (c) 2018, CodexWorld.com
 * @url https://www.codexworld.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wpstorm_Shortener_Core.
 */
class Wpstorm_Shortener_Core {
	/**
	 * Instance
	 *
	 * @access public
	 * @var object Class object.
	 * @since 2.0.0
	 */
	public static object $instance;

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

	protected static string $chars = "abcdfghjkmnpqrstvwxyz|ABCDFGHJKLMNPQRSTVWXYZ|0123456789";
	protected static string $table;
	public static bool $checkUrlExists = false;
	protected static int $codeLength = 7;

	protected static string $timestamp;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		self::$table = $wpdb->prefix . 'wpstorm_short_urls';

		self::$timestamp = date( "Y-m-d H:i:s" );
	}



	public static function validateUrlFormat( $url ) {
		return filter_var( $url, FILTER_VALIDATE_URL );
	}

	public static function verifyUrlExists( $url ) {
		$handler = curl_init();
		curl_setopt( $handler, CURLOPT_URL, $url );
		curl_setopt( $handler, CURLOPT_NOBODY, true );
		curl_setopt( $handler, CURLOPT_RETURNTRANSFER, true );
		curl_exec( $handler );
		$response = curl_getinfo( $handler, CURLINFO_HTTP_CODE );
		curl_close( $handler );

		return ( ! empty( $response ) && $response != 404 );
	}

	public static function urlExistsInDB( $url ) {
		global $wpdb;

		$result = $wpdb->get_row( "SELECT short_code FROM " . self::$table . " WHERE long_url = %s LIMIT 1", $url );

		return ( empty( $result ) ) ? false : $result["short_code"];
	}

	public static function createShortCode( $url ) {
		$shortCode = self::generateRandomString( self::$codeLength );
		$id        = self::insertUrlInDB( $url, $shortCode );

		return $shortCode;
	}

	public static function generateRandomString( int $codeLength = 6 ) {
		$sets       = explode( '|', self::$chars );
		$all        = '';
		$randString = '';
		foreach ( $sets as $set ) {
			$randString .= $set[ array_rand( str_split( $set ) ) ];
			$all        .= $set;
		}
		$all = str_split( $all );
		for ( $i = 0; $i < $codeLength - count( $sets ); $i ++ ) {
			$randString .= $all[ array_rand( $all ) ];
		}

		return str_shuffle( $randString );
	}

	public static function insertUrlInDB( $url, $shortCode ) {
		global $wpdb;
		$wpdb->insert( self::$table, array(
			"long_url"   => $url,
			"short_code" => $shortCode,
			"timestamp"  => self::$timestamp
		) );

		return $wpdb->insert_id;
	}

	/**
	 * @throws Exception
	 */
	public static function shortCodeToUrl( $code, $increment = true ) {
		if ( empty( $code ) ) {
			throw new Exception( __( 'No short code was supplied.', 'wpstorm-shortener' ) );
		}

		if ( ! self::validateShortCode( $code ) ) {
			throw new Exception( __( 'Short code does not have a valid format.', 'wpstorm-shortener' ) );
		}

		$urlRow = self::getUrlFromDB( $code );
		if ( empty( $urlRow ) ) {
			throw new Exception( __( 'Short code does not appear to exist.', 'wpstorm-shortener' ) );
		}

		if ( $increment ) {
			self::incrementCounter( $urlRow["id"] );
		}

		return $urlRow["long_url"];
	}

	public static function validateShortCode( $code ) {
		$rawChars = str_replace( '|', '', self::$chars );

		return preg_match( "|[" . $rawChars . "]+|", $code );
	}

	public static function getUrlFromDB( $code ) {
		global $wpdb;

		$result = $wpdb->get_row( "SELECT id, long_url FROM " . self::$table . " WHERE short_code = %s LIMIT 1", $code );

		return ( empty( $result ) ) ? false : $result;
	}

	public static function incrementCounter( $id ) {
		global $wpdb;

		$query = "UPDATE " . self::$table . " SET hits = hits + 1 WHERE id = %d";
		$wpdb->query( $wpdb->prepare( $query, $id ) );
	}


}

Wpstorm_Shortener_Core::get_instance();
