<?php
/**
 * The admin-facing file for error management.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/admin/templates
 */

global $hubwoo;

$active_tab = ! empty( $_GET['sub_tab'] ) ? sanitize_key( $_GET['sub_tab'] ) : 'viewLogs';

$success_calls = get_option( 'hubwoo-success-api-calls', 0 );
$failed_calls  = get_option( 'hubwoo-error-api-calls', 0 );

?>
<div class="hubwoo-et-wrap">
	<div class="hubwoo-connect-form-header">
		<h2><?php esc_html_e( 'Error Tracking', 'makewebbetter-hubspot-for-woocommerce' ); ?></h2>
	</div>

	<?php

	if ( 'invalidEmails' == $active_tab ) {

		?>
		<div class="hubwoo-invalid-emails">
			<div class="hubwoo-list-emails">
				<h3><?php esc_html_e( 'List of emails that have been invalidated on HubSpot', 'makewebbetter-hubspot-for-woocommerce' ); ?></h3>
				<div class="list-emails-and-orders">
					<?php
						$invalid_emails = get_option( 'hubwoo_pro_invalid_emails', array() );
					if ( ! empty( $invalid_emails ) ) {
						?>
						<table class="hubwoo-emails-table">
							<tr>
								<th><?php esc_html_e( 'Email', 'makewebbetter-hubspot-for-woocommerce' ); ?></th>
							</tr>
							<?php
							foreach ( $invalid_emails as $single_email ) {
								?>
									<tr>
										<td><?php echo esc_html( $single_email ); ?></td>
									</tr>
									<?php
							}
							?>
							</table>
							<?php
					} else {
						?>
							<p><?php esc_html_e( 'No emails yet', 'makewebbetter-hubspot-for-woocommerce' ); ?></p>
							<?php
					}
					?>
					</div>
				</div>
			</div>
			<?php
	} elseif ( 'viewLogs' == $active_tab ) {

		?>
			<div class="hubwoo-log-viewer">
				<div class="hubwoo-notice">
					<p class="hubwoo-log-notice-txt">
						<?php esc_html_e( 'Send us the log file if you are having any trouble.', 'makewebbetter-hubspot-for-woocommerce' ); ?>
						<span style="display: none;" id="hubwoo_email_loader" ><img class="hubwoo_email_loader" src="<?php echo esc_url( HUBWOO_URL . 'admin/images/loading.gif' ); ?>"></span>
						<span style="display: none;" id="hubwoo_email_success"><img class="hubwoo_email_loader" src="<?php echo esc_url( HUBWOO_URL . 'admin/images/success.gif' ); ?>"></span>							
						<a class="button-primary" href="javascript:void(0);" id="hubwoo-pro-email-logs"><?php esc_html_e( 'Send Now', 'makewebbetter-hubspot-for-woocommerce' ); ?></a>
					</p>
				</div>
				<div id="log-viewer">
					<?php if ( file_exists( WC_LOG_DIR . 'hubspot-for-woocommerce-logs.log' ) ) { ?>
							<pre><?php echo esc_html( file_get_contents( WC_LOG_DIR . 'hubspot-for-woocommerce-logs.log' ) ); ?></pre>
						<?php } else { ?>
							<pre><strong><?php echo esc_html( 'Log file:hubspot-for-woocommerce-logs.log not found', 'makewebbetter-hubspot-for-woocommerce' ); ?></strong></pre>
						<?php } ?>
				</div>
			</div>
			<?php
	}
	?>
</div>
