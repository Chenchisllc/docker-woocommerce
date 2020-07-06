<?php
/**
 * Queue Contact Sync with HubSpot.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/includes
 */

defined( 'ABSPATH' ) || exit;


require_once plugin_dir_path( __FILE__ ) . 'classes/class-wp-async-request.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/class-wp-background-process.php';

/**
 * All functionalities to sync bulk users from WooCommerce to HubSpot.
 *
 * @since      1.0.0
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class HubWoo_Background_Process extends WP_Background_Process {

	/**
	 * Initiate new background process.
	 */
	public function __construct() {

		// Uses unique prefix per blog so each blog has separate queue.
		$this->prefix = 'wp_' . get_current_blog_id();
		$this->action = 'hubwoo_background_process';

		@putenv( 'MAGICK_THREAD_LIMIT=1' ); // @codingStandardsIgnoreLine.
		parent::__construct();
	}

	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 */
	public function handle_cron_healthcheck() {

		if ( $this->is_process_running() ) {
			// Background process already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();
			return;
		}

		$this->handle();
	}

	/**
	 * CheckHandle
	 *
	 * Pass each queue item to the task handler, while remaining
	 * within server memory and time limit constraints.
	 */
	public function checkHandle() {

		$this->handle();
	}

	/**
	 * Handle
	 *
	 * Pass each queue item to the task handler, while remaining
	 * within server memory and time limit constraints.
	 */
	protected function handle() {

		$this->lock_process();
		$hubwoo_datasync = new HubwooDataSync();
		do {

			$users_need_syncing = $hubwoo_datasync->hubwoo_get_all_unique_user();

			if ( ! count( $users_need_syncing ) ) {
				$roles = get_option( 'hubwoo_customers_role_settings', array() );
				if ( in_array( 'guest_user', $roles, true ) ) {
					$order_need_syncing = $hubwoo_datasync->hubwoo_get_all_unique_user( false, 'guestOrder' );

					$order_chunk = array_chunk( $order_need_syncing, 20 );

					if ( is_array( $order_chunk ) && count( $order_chunk ) ) {
						foreach ( $order_chunk as $order_ids ) {

							$task = $this->task( $order_ids, 'orderSync' );
							if ( $this->time_exceeded() || $this->memory_exceeded() ) {
								// Batch limits reached.
								break;
							}
						}
					}
				}
			} else {
				$user_chunks = array_chunk( $users_need_syncing, 20 );

				if ( is_array( $user_chunks ) && count( $user_chunks ) ) {

					foreach ( $user_chunks as $user_group ) {

						$task = $this->task( $user_group );

						if ( $this->time_exceeded() || $this->memory_exceeded() ) {
							// Batch limits reached.
							break;
						}
					}
				}
			}
		} while ( ! $this->time_exceeded() && ! $this->memory_exceeded() && ! $this->is_queue_empty() );

		$this->unlock_process();

		// Start next batch or complete process.
		if ( ! $this->is_queue_empty() ) {
			$this->dispatch();
		} else {
			$this->complete();
		}

		wp_die();
	}

	/**
	 * Check queue.
	 *
	 * Check if the sync queue is empty or not.
	 */
	public function is_queue_empty() {

		// check here how many contacts are left without updating..
		$hubwoo_data_sync = new hubwooDataSync();
		$contactqueue     = $hubwoo_data_sync->hubwoo_get_all_unique_user( true );

		if ( $contactqueue ) {
			return false;
		}

		return true;
	}


	/**
	 * Code to execute for each contact groups in the queue.
	 *
	 * @param array  $item list of contacts to sync.
	 * @param string $sync_type type of item to be synced.
	 * @return bool
	 */
	protected function task( $item, $sync_type = 'customer' ) {
		if ( ! is_array( $item ) ) {
			return false;
		}

		$contact_sync = true;

		if ( 'orderSync' === $sync_type ) {
			$contact_sync = false;
			$user_data    = HubwooDataSync::get_guest_sync_data( $item );
		} else {
			$user_data = HubwooDataSync::get_sync_data( $item );
		}

		if ( is_array( $user_data ) ) {

			if ( Hubwoo::is_valid_client_ids_stored() ) {

				$flag = true;

				if ( Hubwoo::is_access_token_expired() ) {

					$hapikey = HUBWOO_CLIENT_ID;
					$hseckey = HUBWOO_SECRET_ID;
					$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

					if ( ! $status ) {

						$flag = false;
					}
				}

				if ( $flag ) {

					$response = HubWooConnectionMananager::get_instance()->create_or_update_contacts( $user_data );
					if ( ( count( $user_data ) > 1 ) && isset( $response['status_code'] ) && 400 === $response['status_code'] ) {
						$response = Hubwoo_Admin::hubwoo_split_contact_batch( $user_data );
					}

					$hsocssynced  = get_option( 'hubwoo_ocs_contacts_synced', 0 );
					$hsocssynced += count( $item );

					if ( $contact_sync ) {
						foreach ( $item as $user_id ) {
							update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'synced' );
						}
					} else {
						foreach ( $item as $order_id ) {
							update_post_meta( $order_id, 'hubwoo_pro_guest_order', 'synced' );
						}
					}

					update_option( 'hubwoo_ocs_contacts_synced', $hsocssynced );
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * This runs once the job has completed all items on the queue.
	 *
	 * @return void
	 */
	protected function complete() {
		// all contacts are synced.
		Hubwoo::hubwoo_stop_sync( 'stop-contact' );
	}
}
