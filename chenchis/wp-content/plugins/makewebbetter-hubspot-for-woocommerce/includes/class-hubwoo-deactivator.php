<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */

if ( ! class_exists( 'Hubwoo_Deactivator' ) ) {
	/**
	 * Fired during plugin de activation.
	 *
	 * This class defines all code necessary to run during the plugin's de activation.
	 *
	 * @since      1.0.0
	 * @package    makewebbetter-hubspot-for-woocommerce
	 * @subpackage makewebbetter-hubspot-for-woocommerce/includes
	 * @author     makewebbetter <webmaster@makewebbetter.com>
	 */
	class Hubwoo_Deactivator {

		/**
		 * Clear log file saved for HubSpot API call logging. (use period)
		 *
		 * @since    1.0.0
		 */
		public static function deactivate() {

			wp_clear_scheduled_hook( 'hubwoo_cron_schedule' );
		}
	}
}
