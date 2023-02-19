<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class Wpstorm_Shortener_Activator
{

    public static function activate()
    {
        global $wpdb;
        $collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'wpstorm_url_shortener';

        $query = "CREATE TABLE IF NOT EXISTS `$table_name` (
					 `id` int(11) NOT NULL AUTO_INCREMENT,
					 `long_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
					 `short_code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
					 `hits` int(11) NOT NULL,
					 `created` datetime NOT NULL,
					 PRIMARY KEY (`id`)
					) $collate";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($query);

        // This option added for redirect after activation
        add_option('wpstorm_shortener_do_activation_redirect', true);
    }
}
