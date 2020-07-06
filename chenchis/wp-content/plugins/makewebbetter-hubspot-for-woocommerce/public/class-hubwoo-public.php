<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/public
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Hubwoo_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Update key as soon as user data is updated.
	 *
	 * @since    1.0.0
	 * @param      string $user_id       User Id.
	 */
	public function hubwoo_woocommerce_save_account_details( $user_id ) {

		update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'yes' );
	}

	/**
	 * Enqueue public facing javascript files
	 *
	 * @since    1.0.0
	 */
	public function load_input_scripts() {

		wp_enqueue_script( 'hubwoo-public', HUBWOO_URL . '/admin/js/hubwoo-public.min.js', array( 'jquery' ), WC_VERSION, true );
	}


	/**
	 * Enqueuing the HubSpot Tracking Script
	 * in the footer file
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_add_hs_script() {

		$portal_id = get_option( 'hubwoo_pro_hubspot_id', '' );

		if ( ! empty( $portal_id ) ) {
			//phpcs:disable
			?>
			<script type="text/javascript" id="hs-script-loader" async defer src="//js.hs-scripts.com/<?php echo $portal_id; ?>.js"></script>
			<?php
			//phpcs:enable
		}
	}

	/**
	 * Update key as soon as guest order is done
	 *
	 * @since    1.0.0
	 * @param    string $order_id       Order Id.
	 */
	public function hubwoo_pro_woocommerce_guest_orders( $order_id ) {

		if ( ! empty( $order_id ) ) {

			$customer_id = get_post_meta( $order_id, '_customer_user', true );

			if ( empty( $customer_id ) || 0 == $customer_id ) {

				update_post_meta( $order_id, 'hubwoo_pro_guest_order', 'yes' );
			}
		}
	}

	/**
	 * Update key as soon as order is renewed
	 *
	 * @since    1.0.0
	 * @param      string $order_id       Order Id.
	 */
	public function hubwoo_pro_save_renewal_orders( $order_id ) {

		if ( ! empty( $order_id ) ) {

			$user_id = (int) get_post_meta( $order_id, '_customer_user', true );

			if ( 0 !== $user_id && 0 < $user_id ) {

				update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'yes' );
			}
		}
	}

	/**
	 * Update key as soon as customer make changes in his/her subscription orders
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_save_changes_in_subs() {

		$user_id = get_current_user_id();

		if ( $user_id ) {

			update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'yes' );
		}
	}

	/**
	 * Update key as soon as customer make changes in his/her subscription orders
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_subscription_switch() {

		if ( isset( $_GET['switch-subscription'] ) && isset( $_GET['item'] ) ) {

			$user_id = get_current_user_id();

			if ( $user_id ) {

				update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'yes' );
			}
		}
	}

	/**
	 * Update key as soon as subscriptions order status changes.
	 *
	 * @since 1.0.0
	 * @param object $subs subscription order object.
	 */
	public function hubwoo_pro_update_subs_changes( $subs ) {

		if ( ! empty( $subs ) && ( $subs instanceof WC_Subscription ) ) {

			$order_id = $subs->get_id();

			if ( ! empty( $order_id ) ) {

				$user_id = (int) get_post_meta( $order_id, '_customer_user', true );

				if ( 0 !== $user_id && 0 < $user_id ) {

					update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'yes' );
				}
			}
		}
	}


	/**
	 * Add checkout optin checkbox at woocommerce checkout
	 *
	 * @since    1.0.0
	 * @param object $checkout woocommerce checkut object.
	 */
	public function hubwoo_pro_checkout_field( $checkout ) {

		if ( is_user_logged_in() ) {
			$subscribe_status    = get_user_meta( get_current_user_id(), 'hubwoo_checkout_marketing_optin', true );
			$registeration_optin = get_user_meta( get_current_user_id(), 'hubwoo_registeration_marketing_optin', true );
		}
		if ( ! empty( $subscribe_status ) && 'yes' === $subscribe_status ) {
			return;
		} elseif ( ! empty( $registeration_optin ) && 'yes' === $registeration_optin ) {
			return;
		}
		$label = get_option( 'hubwoo_checkout_optin_label', __( 'Subscribe', 'makewebbetter-hubspot-for-woocommerce' ) );
		echo '<div class="form-row form-row-wide hubwoo_checkout_marketing_optin">';
		woocommerce_form_field(
			'hubwoo_checkout_marketing_optin',
			array(
				'type'  => 'checkbox',
				'class' => array( 'hubwoo-input-checkbox', 'woocommerce-form__input', 'woocommerce-form__input-checkbox' ),
				'label' => $label,
			),
			WC()->checkout->get_value( 'hubwoo_checkout_marketing_optin' )
		);
		echo '</div>';
	}

	/**
	 * Optin checkbox on My Account page.
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_pro_register_field() {

		$label = get_option( 'hubwoo_registeration_optin_label', __( 'Subscribe', 'makewebbetter-hubspot-for-woocommerce' ) );
		echo '<div class="form-row form-row-wide hubwoo_registeration_marketing_optin">';
		woocommerce_form_field(
			'hubwoo_registeration_marketing_optin',
			array(
				'type'    => 'checkbox',
				'class'   => array( 'hubwoo-input-checkbox', 'woocommerce-form__input', 'woocommerce-form__input-checkbox' ),
				'label'   => $label,
				'default' => 'yes',
			),
			'yes'
		);
		echo '</div>';
	}

	/**
	 * Save order meta when any user optin through checkout.
	 *
	 * @since 1.0.0
	 * @param int $order_id order ID.
	 */
	public function hubwoo_pro_process_checkout_optin( $order_id ) {

		if ( ! empty( $_REQUEST['woocommerce-process-checkout-nonce'] ) && ! empty( $_REQUEST['_wpnonce'] ) ) {
			$nonce_value = wc_get_var( sanitize_text_field( $_REQUEST['woocommerce-process-checkout-nonce'] ), wc_get_var( sanitize_text_field( $_REQUEST['_wpnonce'] ), '' ) ); // @codingStandardsIgnoreLine.
		} else {
			$nonce_value = '';
		}

		if ( ! empty( $nonce_value ) && wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
			if ( ! empty( $_POST['hubwoo_checkout_marketing_optin'] ) ) {

				if ( ! empty( $order_id ) ) {

					if ( is_user_logged_in() ) {

						update_user_meta( get_current_user_id(), 'hubwoo_checkout_marketing_optin', 'yes' );
					} else {

						update_post_meta( $order_id, 'hubwoo_checkout_marketing_optin', 'yes' );
					}
				}
			}
		}
	}

	/**
	 * Save user meta when they optin via woocommerce registeration form.
	 *
	 * @since 1.0.0
	 * @param int $user_id user ID.
	 */
	public function hubwoo_save_register_optin( $user_id ) {

		if ( ! empty( $user_id ) ) {
			$nonce_value = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.NoNonceVerification
			$nonce_value = isset( $_POST['woocommerce-register-nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce-register-nonce'] ) ) : $nonce_value; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.NoNonceVerification
			if ( isset( $_POST['register'], $_POST['email'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-register' ) ) {
				if ( isset( $_POST['hubwoo_registeration_marketing_optin'] ) ) {

					update_user_meta( $user_id, 'hubwoo_registeration_marketing_optin', 'yes' );
				}
			}
		}
	}

	/**
	 * Create a new ecomm deal.
	 *
	 * @since 1.0.0
	 * @param int $order_id order id to be updated.
	 */
	public function hubwoo_ecomm_deal_on_new_order( $order_id ) {

		if ( ! empty( $order_id ) ) {

			$post_type = get_post_type( $order_id );

			if ( 'shop_subscription' == $post_type ) {
				return;
			}

			wp_schedule_single_event( time() + 10, 'hubwoo_ecomm_deal_upsert', array( $order_id ) );
		}
	}

	/**
	 * Start session for abandonec carts.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_abncart_start_session() {

		if ( ! session_id() || session_id() === '' ) {

			session_start();
			self::hubwoo_abncart_set_locale();
		}
	}

	/**
	 * Save cart of the user if captured by HubSpot.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_track_cart_for_formuser() {

		if ( ! empty( $_SESSION['mwb_guest_user_email'] ) && empty( $_SESSION['hs_form_user_tracked'] ) ) {

			$guest_user_cart = array();

			if ( function_exists( 'WC' ) ) {

				$guest_user_cart['cart'] = WC()->session->cart;
			} else {

				$guest_user_cart['cart'] = $woocommerce->session->cart;
			}

			if ( empty( $guest_user_cart['cart'] ) ) {
				return;
			}

			$session_id = session_id();

			$locale = ! empty( $_SESSION['locale'] ) ? $_SESSION['locale'] : '';

			$user_data = array(
				'email'     => $_SESSION['mwb_guest_user_email'],
				'cartData'  => $guest_user_cart,
				'timeStamp' => time(),
				'sessionID' => $session_id,
				'locale'    => $locale,
				'sent'      => 'no',
			);

			self::hubwoo_abncart_update_new_data( $_SESSION['mwb_guest_user_email'], $user_data, $session_id );

			$_SESSION['hs_form_user_tracked'] = true;
		}
	}

	/**
	 * Save cart when billing email is entered on checkout.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_save_guest_user_cart() {

		check_ajax_referer( 'hubwoo_cart_security', 'nonce' );

		if ( ! empty( $_POST['email'] ) ) {

			$posted_email = wp_unslash( $_POST['email'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$session_id   = session_id();

			$guest_user_cart = array();

			if ( function_exists( 'WC' ) ) {

				$guest_user_cart['cart'] = WC()->session->cart;
			} else {

				$guest_user_cart['cart'] = $woocommerce->session->cart;
			}

			if ( ! empty( $session_id ) ) {

				$locale = ! empty( $_POST['locale'] ) ? sanitize_text_field( wp_unslash( $_POST['locale'] ) ) : '';

				if ( empty( $_SESSION['mwb_guest_user_email'] ) ) {

					$_SESSION['mwb_guest_user_email'] = ! empty( $posted_email ) ? $posted_email : '';
					$user_data                        = array(
						'email'     => $_SESSION['mwb_guest_user_email'],
						'cartData'  => $guest_user_cart,
						'timeStamp' => time(),
						'sessionID' => $session_id,
						'locale'    => $locale,
						'sent'      => 'no',
					);

					self::hubwoo_abncart_update_new_data( $_SESSION['mwb_guest_user_email'], $user_data, $session_id );
				} else {

					$new_email_entered = ! empty( $posted_email ) ? $posted_email : '';

					$before_entered_email = $_SESSION['mwb_guest_user_email'];

					$_SESSION['mwb_guest_user_email'] = $new_email_entered;

					$existing_cart_data = get_option( 'mwb_hubwoo_guest_user_cart', array() );

					if ( ! empty( $existing_cart_data ) ) {

						if ( $new_email_entered === $before_entered_email ) {

							foreach ( $existing_cart_data as $key => &$single_cart_data ) {

								if ( array_key_exists( 'sessionID', $single_cart_data ) && $single_cart_data['sessionID'] == $session_id ) {

									$single_cart_data['cartData']  = $guest_user_cart;
									$single_cart_data['timeStamp'] = time();
									$single_cart_data['locale']    = $locale;
									$single_cart_data['sent']      = 'no';
									break;
								} elseif ( array_key_exists( 'email', $single_cart_data ) && $single_cart_data['email'] == $before_entered_email ) {

									$single_cart_data['cartData']  = $guest_user_cart;
									$single_cart_data['timeStamp'] = time();
									$single_cart_data['locale']    = $locale;
									$single_cart_data['sent']      = 'no';
									break;
								}
							}
						} else {

							foreach ( $existing_cart_data as $key => &$single_cart_data ) {

								if ( array_key_exists( 'sessionID', $single_cart_data ) && $single_cart_data['sessionID'] == $session_id ) {

									$single_cart_data['cartData']  = $guest_user_cart;
									$single_cart_data['timeStamp'] = time();
									$single_cart_data['email']     = $new_email_entered;
									$single_cart_data['locale']    = $locale;
									$single_cart_data['sent']      = 'no';

									$user_data            = array(
										'email'     => $before_entered_email,
										'cartData'  => '',
										'timeStamp' => time(),
										'sessionID' => $session_id,
										'locale'    => $locale,
										'sent'      => 'no',
									);
									$existing_cart_data[] = $user_data;
									break;
								}
							}
						}
					} else {

						$_SESSION['mwb_guest_user_email'] = ! empty( $posted_email ) ? $posted_email : '';
						$user_data                        = array(
							'email'     => $_SESSION['mwb_guest_user_email'],
							'cartData'  => $guest_user_cart,
							'timeStamp' => time(),
							'sessionID' => $session_id,
							'locale'    => $locale,
							'sent'      => 'no',
						);
						self::hubwoo_abncart_update_new_data( $_SESSION['mwb_guest_user_email'], $user_data, $session_id );
					}

					update_option( 'mwb_hubwoo_guest_user_cart', $existing_cart_data );
				}
			}

			wp_die();
		}
	}

	/**
	 * Set site local in session.
	 *
	 * @since 1.0.0
	 */
	public static function hubwoo_abncart_set_locale() {

		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {

			$locale = ICL_LANGUAGE_CODE;
		} else {

			$locale = get_locale();
		}

		if ( empty( $_SESSION['locale'] ) ) {

			$_SESSION['locale'] = $locale;
		} else {

			$_SESSION['locale'] = $locale;
		}
	}

	/**
	 * Track billing form for email on checkout.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_track_email_for_guest_users() {

		if ( ! is_user_logged_in() ) {

			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {

				$locale = ICL_LANGUAGE_CODE;
			} else {

				$locale = get_locale();
			}
			?>
			<script type="text/javascript">
				jQuery( 'input#billing_email' ).on( 'change', function() {
					var guest_user_email = jQuery( 'input#billing_email' ).val();
					var ajaxUrl = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
					var locale = "<?php echo esc_html( $locale ); ?>";
					var nonce = "<?php echo esc_html( wp_create_nonce( 'hubwoo_cart_security' ) ); ?>";
					jQuery.post( ajaxUrl, { 'action' : 'hubwoo_save_guest_user_cart', 'email' : guest_user_email, 'locale' : locale, 'nonce' : nonce }, function( status ) {});
				});
			</script>
			<?php
		}
	}

	/**
	 * Clear saved cart on new order.
	 *
	 * @since 1.0.0
	 * @param int $order_id id of new order.
	 */
	public function hubwoo_abncart_woocommerce_new_orders( $order_id ) {

		if ( ! session_id() || session_id() === '' ) {
			session_start();
		}

		$session_id = session_id();

		if ( ! empty( $order_id ) ) {

			$order = new WC_Order( $order_id );

			$order_status = $order->get_status();

			$order_email = get_post_meta( $order_id, '_billing_email', true );

			$existing_cart_data = get_option( 'mwb_hubwoo_guest_user_cart', array() );

			if ( 'failed' !== $order_status ) {

				if ( ! empty( $existing_cart_data ) ) {

					foreach ( $existing_cart_data as $key => &$single_cart_data ) {

						if ( array_key_exists( 'sessionID', $single_cart_data ) && $single_cart_data['sessionID'] == $session_id ) {

							if ( isset( $single_cart_data['cartData']['cart'] ) ) {

								$single_cart_data['cartData']['cart'] = '';
								$single_cart_data['sent']             = 'no';
							}
						}

						if ( array_key_exists( 'email', $single_cart_data ) && $single_cart_data['email'] == $order_email ) {

							if ( isset( $single_cart_data['cartData']['cart'] ) ) {

								$single_cart_data['cartData']['cart'] = '';
								$single_cart_data['sent']             = 'no';
							}
						}

						if ( array_key_exists( 'email', $single_cart_data ) && isset( $_SESSION['mwb_guest_user_email'] ) && $single_cart_data['email'] == $_SESSION['mwb_guest_user_email'] ) {

							if ( isset( $single_cart_data['cartData']['cart'] ) ) {

								$single_cart_data['cartData']['cart'] = '';
								$single_cart_data['sent']             = 'no';
							}
						}
					}

					update_option( 'mwb_hubwoo_guest_user_cart', $existing_cart_data );
				}
			}

			if ( is_user_logged_in() ) {

				update_user_meta( get_current_user_id(), 'hubwoo_pro_user_cart_sent', 'no' );
			}
		}
	}

	/**
	 * Track changes on cart being updated.
	 *
	 * @since 1.0.0
	 */
	public static function hubwoo_abncart_track_guest_cart() {

		if ( ! is_user_logged_in() ) {

			$session_id = session_id();

			if ( ! empty( $session_id ) ) {

				if ( isset( $_SESSION['mwb_guest_user_email'] ) && ! empty( $_SESSION['mwb_guest_user_email'] ) ) {

					$guest_user_email = $_SESSION['mwb_guest_user_email'];

					if ( ! empty( $guest_user_email ) ) {

						$guest_user_cart = array();

						$locale = ! empty( $_SESSION['locale'] ) ? $_SESSION['locale'] : '';

						if ( function_exists( 'WC' ) ) {

							$guest_user_cart['cart'] = WC()->session->cart;
						} else {

							$guest_user_cart['cart'] = $woocommerce->session->cart;
						}

						$existing_cart_data = get_option( 'mwb_hubwoo_guest_user_cart', array() );

						$saved_cart = array();

						if ( ! empty( $existing_cart_data ) ) {

							foreach ( $existing_cart_data as $single_cart_data ) {

								if ( array_key_exists( 'email', $single_cart_data ) && $_SESSION['mwb_guest_user_email'] == $single_cart_data['email'] ) {

									if ( array_key_exists( 'cartData', $single_cart_data ) ) {

										if ( ! empty( $single_cart_data['cartData']['cart'] ) ) {
											$saved_cart = $single_cart_data['cartData']['cart'];
										}
									}
									break;
								}
							}
						}

						if ( $saved_cart === $guest_user_cart['cart'] ) {

							return;
						}

						$user_data = array(
							'email'     => $_SESSION['mwb_guest_user_email'],
							'cartData'  => $guest_user_cart,
							'timeStamp' => time(),
							'sessionID' => $session_id,
							'locale'    => $locale,
							'sent'      => 'no',
						);

						self::hubwoo_abncart_update_new_data( $_SESSION['mwb_guest_user_email'], $user_data, $session_id );
					}
				}
			}
		}
	}

	/**
	 * Callback to update cart data in DB.
	 *
	 * @since 1.0.0
	 * @param string $email email of the contact.
	 * @param array  $user_data formatted data for cart.
	 * @param string $session session id for the cart activity.
	 */
	public static function hubwoo_abncart_update_new_data( $email, $user_data, $session ) {

		$existing_cart_data = get_option( 'mwb_hubwoo_guest_user_cart', array() );
		$update_flag        = false;

		if ( ! empty( $existing_cart_data ) ) {

			foreach ( $existing_cart_data as $key => &$single_cart_data ) {

				if ( ! empty( $single_cart_data['email'] ) && $single_cart_data['email'] == $email ) {

					$single_cart_data = $user_data;
					$update_flag      = true;
					break;
				} elseif ( ! empty( $single_cart_data['sessionID'] ) && $single_cart_data['sessionID'] == $session ) {

					$single_cart_data = $user_data;
					$update_flag      = true;
					break;
				}
			}
		}

		if ( ! $update_flag ) {

			$existing_cart_data[] = $user_data;
		}

		update_option( 'mwb_hubwoo_guest_user_cart', $existing_cart_data );
	}

	/**
	 * Transfer guest cart to user on account registeration.
	 *
	 * @since 1.0.0
	 * @param int $user_id user ID.
	 */
	public function hubwoo_abncart_user_registeration( $user_id ) {

		$user  = get_user_by( 'id', $user_id );
		$email = ! empty( $user->data->user_email ) ? $user->data->user_email : '';
		if ( empty( $email ) ) {
			return;
		}
		$session_id         = session_id();
		$existing_cart_data = get_option( 'mwb_hubwoo_guest_user_cart', array() );
		foreach ( $existing_cart_data as $key => &$single_cart_data ) {
			if ( array_key_exists( 'sessionID', $single_cart_data ) && $single_cart_data['sessionID'] === $session_id ) {
				if ( ! empty( $single_cart_data['sent'] ) && 'no' === $single_cart_data['sent'] ) {
					unset( $existing_cart_data[ $key ] );
				} else {
					$single_cart_data['cartData'] = '';
					$single_cart_data['sent']     = 'no';
				}
			} elseif ( array_key_exists( 'email', $single_cart_data ) && $single_cart_data['email'] === $email ) {
				if ( ! empty( $single_cart_data['sent'] ) && 'no' === $single_cart_data['sent'] ) {
					unset( $existing_cart_data[ $key ] );
				} else {
					$single_cart_data['cartData'] = '';
					$single_cart_data['sent']     = 'no';
				}
			}
		}

		update_option( 'mwb_hubwoo_guest_user_cart', $existing_cart_data );

		$locale = ! empty( $_SESSION['locale'] ) ? $_SESSION['locale'] : '';

		update_user_meta( $user_id, 'hubwoo_pro_user_left_cart', 'yes' );
		update_user_meta( $user_id, 'hubwoo_pro_last_addtocart', time() );
		update_user_meta( $user_id, 'hubwoo_pro_user_cart_sent', 'no' );
		update_user_meta( $user_id, 'hubwoo_pro_cart_locale', $locale );
	}

	/**
	 * Clear session on logout.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_clear_session() {

		if ( isset( $_SESSION['locale'] ) ) {
			unset( $_SESSION['locale'] );
		}
		if ( isset( $_SESSION['mwb_guest_user_email'] ) ) {
			unset( $_SESSION['mwb_guest_user_email'] );
		}
	}

	/**
	 * Handles the cart data when the guest user updates the cart.
	 *
	 * @since 1.0.0
	 * @param bool $cart_updated true/false.
	 * @return bool $cart_updated true/false.
	 */
	public function hubwoo_guest_cart_updated( $cart_updated ) {

		if ( is_user_logged_in() ) {

			$user_id = get_current_user_id();
			//phpcs:disable
			$locale  = ! empty( $_SESSION['locale'] ) ? $_SESSION['locale'] : '';
			//phpcs:enable
			if ( ! empty( $user_id ) && $user_id ) {

				update_user_meta( $user_id, 'hubwoo_pro_user_left_cart', 'yes' );
				update_user_meta( $user_id, 'hubwoo_pro_last_addtocart', time() );
				update_user_meta( $user_id, 'hubwoo_pro_cart_locale', $locale );
				update_user_meta( $user_id, 'hubwoo_pro_user_cart_sent', 'no' );
			}
		} else {

			self::hubwoo_abncart_track_guest_cart();
		}
		return $cart_updated;
	}

	/**
	 * Update key as soon as user makes a addtocart action
	 *
	 * @since       1.0.0
	 */
	public function hubwoo_abncart_woocommerce_add_to_cart() {

		if ( is_user_logged_in() ) {

			$user_id = get_current_user_id();
			//phpcs:disable
			$locale = ! empty( $_SESSION['locale'] ) ? $_SESSION['locale'] : '';
			//phpcs:enable
			if ( ! empty( $user_id ) && $user_id ) {

				update_user_meta( $user_id, 'hubwoo_pro_user_left_cart', 'yes' );

				update_user_meta( $user_id, 'hubwoo_pro_last_addtocart', time() );

				update_user_meta( $user_id, 'hubwoo_pro_cart_locale', $locale );

				update_user_meta( $user_id, 'hubwoo_pro_user_cart_sent', 'no' );
			}
		} else {

			self::hubwoo_abncart_track_guest_cart();
		}
	}
}
