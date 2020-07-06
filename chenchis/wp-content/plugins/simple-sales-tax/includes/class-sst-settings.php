<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SST Settings.
 *
 * Contains methods for getting/setting plugin options.
 *
 * @author  Simple Sales Tax
 * @package SST
 * @since   5.0
 */
class SST_Settings {

	/**
	 * @var string Options key.
	 * @since 5.0
	 */
	private static $options_key = 'woocommerce_wootax_settings';

	/**
	 * @var array Plugin settings.
	 * @since 5.0
	 */
	private static $settings = [];

	/**
	 * Initialize the settings array.
	 *
	 * @since 5.0
	 */
	private static function init_settings() {
		self::$settings = get_option( self::$options_key, [] );
	}

	/**
	 * Get a list of plugin options and their default values.
	 *
	 * @since 5.0
	 */
	public static function get_form_fields() {
		return [
			'taxcloud_settings'           => [
				'title'       => __( 'TaxCloud Settings', 'simple-sales-tax' ),
				'type'        => 'title',
				'description' => __(
					'You must enter a valid TaxCloud API ID and API Key for Simple Sales Tax to work properly. Use the "Verify Settings" button to test your settings.',
					'simple-sales-tax'
				),
			],
			'tc_id'                       => [
				'title'       => __( 'TaxCloud API ID', 'simple-sales-tax' ),
				'type'        => 'text',
				'description' => __(
					'Your TaxCloud API ID. This can be found in your TaxCloud account on the "Websites" page.',
					'simple-sales-tax'
				),
				'desc_tip'    => true,
				'default'     => '',
			],
			'tc_key'                      => [
				'title'       => __( 'TaxCloud API Key', 'simple-sales-tax' ),
				'type'        => 'text',
				'description' => __(
					'Your TaxCloud API Key. This can be found in your TaxCloud account on the "Websites" page.',
					'simple-sales-tax'
				),
				'desc_tip'    => true,
				'default'     => '',
			],
			'verify_settings'             => [
				'title'       => __( 'Verify TaxCloud Settings', 'simple-sales-tax' ),
				'label'       => __( 'Verify Settings', 'simple-sales-tax' ),
				'type'        => 'button',
				'description' => __(
					'Use this button to verify that your site can communicate with TaxCloud.',
					'woocommerce-integration-demo'
				),
				'desc_tip'    => true,
				'id'          => 'verifySettings',
			],
			'business_addresses_settings' => [
				'title'       => __( 'Business Addresses', 'simple-sales-tax' ),
				'type'        => 'title',
				'description' => __(
					'You must enter at least one business address for Simple Sales Tax to work properly. <strong>Important:</strong> Any addresses you enter here should also be registered as <a href="https://simplesalestax.com/taxcloud/locations/" target="_blank">locations</a> in TaxCloud.',
					'simple-sales-tax'
				),
			],
			'addresses'                   => [
				'type'    => 'address_table',
				'default' => [],
			],
			'exemption_settings'          => [
				'title'       => __( 'Exemption Settings', 'simple-sales-tax' ),
				'type'        => 'title',
				'description' => __(
					'If you have tax exempt customers, be sure to enable tax exemptions and enter your company name.',
					'simple-sales-tax'
				),
			],
			'show_exempt'                 => [
				'title'       => __( 'Enable Tax Exemptions?', 'simple-sales-tax' ),
				'type'        => 'select',
				'options'     => [
					'true'  => __( 'Yes', 'simple-sales-tax' ),
					'false' => __( 'No', 'simple-sales-tax' ),
				],
				'default'     => 'false',
				'description' => __( 'Set this to "Yes" if you have tax exempt customers.', 'simple-sales-tax' ),
				'desc_tip'    => true,
			],
			'company_name'                => [
				'title'       => __( 'Company Name', 'simple-sales-tax' ),
				'type'        => 'text',
				'default'     => '',
				'description' => __(
					'Enter your company name as it should be displayed on exemption certificates.',
					'simple-sales-tax'
				),
				'desc_tip'    => true,
			],
			'exempt_roles'                => [
				'title'       => __( 'Exempt User Roles', 'simple-sales-tax' ),
				'type'        => 'multiselect',
				'class'       => 'wc-enhanced-select',
				'options'     => self::get_user_roles(),
				'default'     => [ 'exempt-customer' ],
				'description' => __(
					'When a user with one of these roles shops on your site, WooTax will automatically find and apply the first exemption certificate associated with their account. Convenient if you have repeat exempt customers.',
					'simple-sales-tax'
				),
				'desc_tip'    => true,
			],
			'restrict_exempt'             => [
				'title'       => __( 'Restrict to Exempt Roles', 'simple-sales-tax' ),
				'type'        => 'select',
				'default'     => 'no',
				'description' => __(
					'Set this to "Yes" to restrict users aside from those specified above from seeing the exemption form during checkout.',
					'simple-sales-tax'
				),
				'desc_tip'    => true,
				'options'     => [
					'yes' => __( 'Yes', 'simple-sales-tax' ),
					'no'  => __( 'No', 'simple-sales-tax' ),
				],
			],
			'display_settings'            => [
				'title'       => __( 'Display Settings', 'simple-sales-tax' ),
				'type'        => 'title',
				'description' => __( 'Control how taxes are displayed during checkout.', 'simple-sales-tax' ),
			],
			'show_zero_tax'               => [
				'title'       => __( 'Show Zero Tax?', 'simple-sales-tax' ),
				'type'        => 'select',
				'options'     => [
					'true'  => __( 'Yes', 'simple-sales-tax' ),
					'false' => __( 'No', 'simple-sales-tax' ),
				],
				'default'     => 'false',
				'description' => __(
					'When the sales tax due is zero, should the "Sales Tax" line be shown?',
					'simple-sales-tax'
				),
				'desc_tip'    => true,
			],
			'advanced_settings'           => [
				'title'       => __( 'Advanced Settings', 'simple-sales-tax' ),
				'type'        => 'title',
				'description' => __(
					'For advanced users only. Leave these settings untouched if you are not sure how to use them.',
					'simple-sales-tax'
				),
			],
			'log_requests'                => [
				'title'       => __( 'Log Requests', 'simple-sales-tax' ),
				'type'        => 'checkbox',
				'label'       => ' ',
				'default'     => 'yes',
				'description' => __(
					'When selected, Simple Sales Tax will log all requests sent to TaxCloud for debugging purposes.',
					'simple-sales-tax'
				),
				'desc_tip'    => true,
			],
			'capture_immediately'         => [
				'title'       => __( 'Capture Orders Immediately', 'simple-sales-tax' ),
				'label'       => ' ',
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __(
					'By default, orders are marked as Captured in TaxCloud when they are shipped. Select this option to mark orders as Captured immediately when payment is received. Useful for stores that have items with long lead times.',
					'simple-sales-tax'
				),
				'desc_tip'    => true,
			],
			'tax_based_on'                => [
				'title'       => __( 'Tax Based On', 'simple-sales-tax' ),
				'type'        => 'select',
				'options'     => [
					'item-price'    => __( 'Item Price', 'simple-sales-tax' ),
					'line-subtotal' => __( 'Line Subtotal', 'simple-sales-tax' ),
				],
				'default'     => 'item-price',
				'description' => __(
					'"Item Price": TaxCloud determines the taxable amount for a line item by multiplying the item price by its quantity. "Line Subtotal": the taxable amount is determined by the line subtotal. Useful in instances where rounding becomes an issue.',
					'simple-sales-tax'
				),
				'desc_tip'    => true,
			],
			'remove_all_data'             => [
				'title'       => __( 'Remove All Data', 'simple-sales-tax' ),
				'label'       => ' ',
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __(
					'When this feature is enabled, all Simple Sales Tax options and data will be removed when you click deactivate and delete the plugin.',
					'simple-sales-tax'
				),
				'desc_tip'    => true,
			],
			'download_log_button'         => [
				'title'       => __( 'Download Log File', 'simple-sales-tax' ),
				'label'       => __( 'Download Log', 'simple-sales-tax' ),
				'type'        => 'anchor',
				'url'         => add_query_arg( 'download_log', true ),
				'id'          => 'download_log_button',
				'description' => __(
					'Click this button to download the Simple Sales Tax log file for debugging purposes.',
					'simple-sales-tax'
				),
				'desc_tip'    => true,
			],
		];
	}

	/**
	 * get function.
	 *
	 * Gets an option from the settings API, using defaults if necessary to prevent undefined notices.
	 *
	 * @param string $key
	 * @param mixed  $empty_value
	 *
	 * @return string The value specified for the option or a default value for the option.
	 */
	public static function get( $key, $empty_value = null ) {
		if ( empty( self::$settings ) ) {
			self::init_settings();
		}

		// Get option default if unset.
		if ( ! isset( self::$settings[ $key ] ) ) {
			$form_fields            = self::get_form_fields();
			self::$settings[ $key ] = isset( $form_fields[ $key ] ) ? $form_fields[ $key ]['default'] : '';
		}

		if ( ! is_null( $empty_value ) && '' === self::$settings[ $key ] ) {
			self::$settings[ $key ] = $empty_value;
		}

		return self::$settings[ $key ];
	}

	/**
	 * set function. Sets an option.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function set( $key, $value ) {
		if ( empty( self::$settings ) ) {
			self::init_settings();
		}

		self::$settings[ $key ] = $value;

		update_option( self::$options_key, self::$settings );
	}

	/**
	 * Get a list of user roles.
	 *
	 * @return array
	 * @since 5.0
	 */
	protected static function get_user_roles() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		return $wp_roles->get_names();
	}

}