<?php
/**
 * Fired during plugin activation
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/includes
 */

if ( ! class_exists( 'Hubwoo_Activator' ) ) {

	/**
	 * Fired during plugin activation.
	 *
	 * This class defines all code necessary to run during the plugin's activation.
	 *
	 * @since      1.0.0
	 * @package    makewebbetter-hubspot-for-woocommerce
	 * @subpackage makewebbetter-hubspot-for-woocommerce/includes
	 * @author     makewebbetter <webmaster@makewebbetter.com>
	 */
	class Hubwoo_Activator {

		/**
		 * Schedule the realtime sync for HubSpot WooCommerce Integration
		 *
		 * Create a log file in the WooCommerce defined log directory
		 * and use the same for the logging purpose of our plugin.
		 *
		 * @since    1.0.0
		 */
		public static function activate() {

			update_option( 'hubwoo_plugin_activated_time', time() );

			fopen( WC_LOG_DIR . 'hubspot-for-woocommerce-logs.log', 'a' );

			if ( ! wp_next_scheduled( 'hubwoo_cron_schedule' ) ) {

				wp_schedule_event( time(), 'mwb-hubwoo-contacts-5min', 'hubwoo_cron_schedule' );
			}

			if ( ! wp_next_scheduled( 'hubwoo_deals_sync_check' ) ) {

				wp_schedule_event( time(), 'mwb-hubwoo-check-deals-5min', 'hubwoo_deals_sync_check' );
			}
		}
	}
}
