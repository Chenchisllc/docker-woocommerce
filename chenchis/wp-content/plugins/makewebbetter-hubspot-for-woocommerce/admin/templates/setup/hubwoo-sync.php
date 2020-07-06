<?php
/**
 * The admin-facing file for contacts sync.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/admin/templates/setup
 */

if ( isset( $_GET['action'] ) && 'hubwoo-osc-schedule-sync' == $_GET['action'] ) {
	update_option( 'hubwoo_greeting_displayed_setup', 'yes' );
	Hubwoo_Admin::hubwoo_schedule_sync_listener();
	wp_safe_redirect( admin_url( 'admin.php?page=hubwoo&hubwoo_tab=hubwoo-overview&hubwoo_key=sync' ) );
}
	$total_registered_users        = Hubwoo_Admin::hubwoo_get_all_users_count();
	$sync_process['display_sync']  = 'block';
	$sync_process['display_greet'] = 'none';

if ( 'yes' == get_option( 'hubwoo_greeting_displayed_setup', 'no' ) ) {
	$sync_process['display_sync']  = 'none';
	$sync_process['display_greet'] = 'block';
}
?>

<div class="mwb-heb-welcome sync-page" style="display: <?php echo esc_html( $sync_process['display_sync'] ); ?>">
	<div class="hubwoo-box">
		<div class="mwb-heb-wlcm__title">			
			<h2>
				<?php esc_html_e( 'Sync WooCommerce data with HubSpot', 'makewebbetter-hubspot-for-woocommerce' ); ?>
			</h2>
		</div>
		<div class="mwb-heb-wlcm__content">
			<div class="hubwoo-content__para">
				<p>
					<?php esc_html_e( "You're almost done! The last step is to sync your WooCommerce data with HubSpot.", 'makewebbetter-hubspot-for-woocommerce' ); ?>
				</p>
				<p>
					<?php esc_html_e( "Once you sync your data, you'll be able to see all your WooCommerce information in HubSpot, so you can start engaging with your contacts and customers right away.", 'makewebbetter-hubspot-for-woocommerce' ); ?>
				</p>			
			</div>				
			<div class="mwb-heb-wlcm__btn-wrap">
				<?php
				if ( $total_registered_users < 500 ) {
					?>
							<a href="javascript:void(0);" id = "hubwoo-osc-instant-sync" class="hubwoo-osc-instant-sync hubwoo-btn--primary" data-total_users= "<?php echo esc_attr( $total_registered_users ); ?>"><?php esc_html_e( 'Sync Now', 'makewebbetter-hubspot-for-woocommerce' ); ?></a>		
						<?php
				} else {
					?>
							
							<a href="?page=hubwoo&hubwoo_tab=hubwoo-sync-contacts&action=hubwoo-osc-schedule-sync" id = "hubwoo-osc-schedule-sync" id="hubwoo-osc-schedule-sync" class="hubwoo-osc-schedule-sync hubwoo__btn"><?php esc_html_e( 'Schedule Sync', 'makewebbetter-hubspot-for-woocommerce' ); ?></a>
						<?php
				}
				?>
			</div>
		</div>
		<div>
			<div class="hubwoo-progress-wrap" style="display: none;">
				<p>
					<strong><?php esc_html_e( 'Contact sync is in progress. This should only take a few moments. Thanks for your patience!', 'makewebbetter-hubspot-for-woocommerce' ); ?></strong>
				</p>					
				<div class="hubwoo-progress">
					<div class="hubwoo-progress-bar" role="progressbar" style="width:0"></div>
				</div>
			</div>					
		</div>
	</div>
</div>

<div id="hubwoo-visit-dashboard" class="acc-connected mwb-heb-welcome" style="display: <?php echo esc_attr( $sync_process['display_greet'] ); ?>">
	<div class="hubwoo-box">
		<div class="mwb-heb-wlcm__title">			
			<h2>
				<?php esc_html_e( 'Congrats! You’ve successfully set up the HubSpot for WooCommerce plugin', 'makewebbetter-hubspot-for-woocommerce' ); ?>
			</h2>
		</div>
		<div class="mwb-heb-wlcm__content">
			<div class="hubwoo-content__para hubwoo-content__para--greeting">
				<div class="hubwoo-content__para--greeting-img" >
					<p>
						<?php esc_html_e( "What's next? Go to your dashboard to learn more about the integration." ); ?>
					</p>
					<div class="mwb-heb-wlcm__btn-wrap">
						<a href="javascript:void(0);" class="hubwoo__btn hubwoo_manage_screen" data-process="greet-to-dashboard" data-tab="hubwoo_tab" ><?php esc_html_e( 'Visit DashBoard', 'makewebbetter-hubspot-for-woocommerce' ); ?></a>
					</div>														
				</div>
				<div class="hubwoo-content__para--greeting-content" >
					<img height="150px" width="150px" src="<?php echo esc_url( HUBWOO_URL . 'admin/images/congo.jpg' ); ?>">
				</div>
			</div>
		</div>
	</div>
</div>
