<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple Sales Tax.
 *
 * Main plugin class.
 *
 * @author  Simple Sales Tax
 * @package SST
 * @since   6.0.0
 */
final class SimpleSalesTax {

	/**
	 * @var string Plugin version.
	 */
	public $version = '6.0.11';

	/**
	 * The singleton plugin instance.
	 *
	 * @var SimpleSalesTax
	 */
	protected static $instance = null;

	/**
	 * Singleton instance accessor.
	 *
	 * @return SimpleSalesTax
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Plugin constructor.
	 */
	private function __construct() {
		$this->define_constants();
		$this->load_text_domain();

		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	/**
	 * Initializes the plugin on `plugins_loaded` if all requirements are met.
	 */
	public function init() {
		if ( $this->check_environment() ) {
			$this->includes();
			$this->add_hooks();
		}
	}

	/**
	 * Defines plugin constants.
	 */
	protected function define_constants() {
		define( 'SST_DEFAULT_SHIPPING_TIC', 11010 );
		define( 'SST_SHIPPING_ITEM', 'SHIPPING' );
		define( 'SST_DEFAULT_FEE_TIC', 10010 );
		define( 'SST_RATE_ID', get_option( 'wootax_rate_id' ) );
		define( 'SST_FILE', dirname( dirname( __FILE__ ) ) . '/simple-sales-tax.php' );
		define( 'SST_PLUGIN_BASENAME', plugin_basename( SST_FILE ) );
	}

	/**
	 * Runs on plugin activation.
	 *
	 * Adjusts WooCommerce settings for optimal plugin performance.
	 *
	 * @since 6.0.0
	 */
	public function activate() {
		update_option( 'woocommerce_calc_taxes', 'yes' );
		update_option( 'woocommerce_prices_include_tax', 'no' );
		update_option( 'woocommerce_tax_based_on', 'shipping' );
		update_option( 'woocommerce_default_customer_address', 'base' );
		update_option( 'woocommerce_shipping_tax_class', '' );
		update_option( 'woocommerce_tax_round_at_subtotal', false );
		update_option( 'woocommerce_tax_display_shop', 'excl' );
		update_option( 'woocommerce_tax_display_cart', 'excl' );
		update_option( 'woocommerce_tax_total_display', 'itemized' );
	}

	/**
	 * Runs on plugin deactivation.
	 *
	 * @since 6.0.0
	 */
	public function deactivate() {
		SST_Install::deactivate();
	}

	/**
	 * Include required plugin files.
	 *
	 * @since 4.4
	 */
	private function includes() {
		/**
		 * Abstract classes.
		 */
		include_once __DIR__ . '/abstracts/class-sst-abstract-cart.php';

		/**
		 * Core classes.
		 */
		include_once __DIR__ . '/sst-functions.php';
		include_once __DIR__ . '/sst-compatibility-functions.php';
		include_once __DIR__ . '/class-sst-install.php';
		include_once __DIR__ . '/class-sst-settings.php';
		include_once __DIR__ . '/class-sst-logger.php';
		include_once __DIR__ . '/class-sst-ajax.php';
		include_once __DIR__ . '/class-sst-tic.php';
		include_once __DIR__ . '/class-sst-product.php';
		include_once __DIR__ . '/class-sst-shipping.php';
		include_once __DIR__ . '/class-sst-addresses.php';
		include_once __DIR__ . '/class-sst-origin-address.php';
		include_once __DIR__ . '/class-sst-certificates.php';
		include_once __DIR__ . '/class-sst-order.php';
		include_once __DIR__ . '/class-sst-order-controller.php';
		include_once __DIR__ . '/class-sst-assets.php';

		/**
		 * Third party integrations.
		 */
		$this->load_integrations();

		/**
		 * Admin only.
		 */
		if ( $this->is_request( 'admin' ) ) {
			include_once __DIR__ . '/admin/class-sst-admin.php';
		}

		/**
		 * Frontend only.
		 */
		if ( $this->is_request( 'frontend' ) ) {
			include_once __DIR__ . '/frontend/class-sst-cart-proxy.php';
			include_once __DIR__ . '/frontend/class-sst-checkout.php';
		}
	}

	/**
	 * Registers the plugin activation and deactivation hooks.
	 */
	private function add_hooks() {
		register_activation_hook( SST_FILE, [ $this, 'activate' ] );
		register_deactivation_hook( SST_FILE, [ $this, 'deactivate' ] );
	}
	
	/**
	 * Loads integrations with third party extensions as needed.
	 */
	private function load_integrations() {
		$integrations_dir = __DIR__ . '/integrations';

		// WooCommerce Subscriptions by Prospress
		if ( sst_subs_active() ) {
			include_once $integrations_dir . '/class-sst-subscriptions.php';
		}

		// WooCommerce Composite Products
		if ( is_plugin_active( 'woocommerce-composite-products/woocommerce-composite-products.php' ) ) {
			include_once $integrations_dir . '/class-sst-composite-products.php';
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @param string $type ajax, frontend or admin
	 *
	 * @return bool
	 * @since 4.4
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}

		return false;
	}

	/**
	 * Loads the plugin text domain.
	 */
	public function load_text_domain() {
		load_plugin_textdomain( 'simple-sales-tax', false, basename( dirname( SST_FILE ) ) . '/languages' );
	}

	/**
	 * Checks the environment for compatible versions of PHP and WooCommerce.
	 *
	 * @return bool True if the installed PHP and WooCommerce are compatible, false otherwise.
	 */
	private function check_environment() {
		// Make sure is_plugin_active() is defined
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		// Check PHP version
		if ( version_compare( phpversion(), '5.5', '<' ) ) {
			add_action( 'admin_notices', [ $this, 'php_version_notice' ] );

			return false;
		}

		// Check WooCommerce version
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			add_action( 'admin_notices', [ $this, 'woocommerce_required_notice' ] );

			return false;
		} elseif ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, '3.0', '<' ) ) {
			add_action( 'admin_notices', [ $this, 'woocommerce_version_notice' ] );

			return false;
		}

		return true;
	}

	/**
	 * Notice displayed when the installed version of PHP is not compatible.
	 */
	public function php_version_notice() {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			__( '<strong>PHP needs to be updated.</strong> Simple Sales Tax requires PHP 5.5+.', 'simple-sales-tax' )
		);
	}

	/**
	 * Notice displayed if WooCommerce is not installed or inactive.
	 */
	public function woocommerce_required_notice() {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			__(
				'<strong>WooCommerce not detected.</strong> Please install or activate WooCommerce to use Simple Sales Tax.',
				'simple-sales-tax'
			)
		);
	}

	/**
	 * Notice displayed if the installed version of WooCommerce is not compatible.
	 */
	public function woocommerce_version_notice() {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			__(
				'<strong>WooCommerce needs to be updated.</strong> Simple Sales Tax requires WooCommerce 3.0.0+.',
				'simple-sales-tax'
			)
		);
	}

	/**
	 * Gets the full path to a file or directory in the plugin directory.
	 *
	 * @param string $path Relative path to file or directory
	 *
	 * @return string
	 */
	public function path( $path = '' ) {
		return plugin_dir_path( SST_FILE ) . $path;
	}

	/**
	 * Gets the URL of a file or directory in the plugin directory.
	 *
	 * @param string $path Relative path to file or directory
	 *
	 * @return string
	 */
	public function url( $path ) {
		return plugin_dir_url( SST_FILE ) . $path;
	}

}
