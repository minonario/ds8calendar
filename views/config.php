<?php

//phpcs:disable VariableAnalysis
// There are "undefined" variables here because they're defined in the code that includes this file as a template.

?>
<div id="fdestadisticas-plugin-container">
	<div class="fdestadisticas-masthead">
		<div class="fdestadisticas-masthead__inside-container">
			<div class="fdestadisticas-masthead__logo-container">
				<img class="fdestadisticas-masthead__logo" src="<?php echo esc_url( plugins_url( '../_inc/img/logo-full-2x.png', __FILE__ ) ); ?>" alt="FDEstadisticas" />
			</div>
		</div>
	</div>
	<div class="fdestadisticas-lower">
		<?php if ( ! empty( $notices ) ) { ?>
			<?php foreach ( $notices as $notice ) { ?>
				<?php FDEstadisticas::view( 'notice', $notice ); ?>
			<?php } ?>
		<?php } ?>
		<?php if ( $stat_totals && isset( $stat_totals['all'] ) && (int) $stat_totals['all']->spam > 0 ) : ?>
			<div class="fdestadisticas-card">
				<div class="fdestadisticas-section-header">
					<div class="fdestadisticas-section-header__label">
						<span><?php esc_html_e( 'Statistics' , 'fdestadisticas'); ?></span>
					</div>
					<div class="fdestadisticas-section-header__actions">
						<a href="<?php echo esc_url( FDEstadisticas_Admin::get_page_url( 'stats' ) ); ?>">
							<?php esc_html_e( 'Detailed Stats' , 'fdestadisticas');?>
						</a>
					</div>
				</div>
				
			</div>
		<?php endif;?>

		<?php if ( $fdestadisticas_user ) : ?>
			<div class="fdestadisticas-card">
				<div class="fdestadisticas-section-header">
					<div class="fdestadisticas-section-header__label">
						<span><?php esc_html_e( 'Settings' , 'fdestadisticas'); ?></span>
					</div>
				</div>

				<div class="inside">
					<form action="<?php echo esc_url( FDEstadisticas_Admin::get_page_url() ); ?>" method="POST">
						<table cellspacing="0" class="fdestadisticas-settings">
							<tbody>
								<?php if ( ! FDEstadisticas::predefined_api_key() ) { ?>
								<tr>
									<th class="fdestadisticas-api-key" width="10%" align="left" scope="row">
										<label for="key"><?php esc_html_e( 'API Key', 'fdestadisticas' ); ?></label>
									</th>
									<td width="5%"/>
									<td align="left">
										<span class="api-key"><input id="key" name="key" type="text" size="15" value="<?php echo esc_attr( get_option('wordpress_api_key') ); ?>" class="<?php echo esc_attr( 'regular-text code ' . $fdestadisticas_user->status ); ?>"></span>
									</td>
								</tr>
								<?php } ?>
								<?php if ( isset( $_GET['ssl_status'] ) ) { ?>
									<tr>
										<th align="left" scope="row"><?php esc_html_e( 'SSL Status', 'fdestadisticas' ); ?></th>
										<td></td>
										<td align="left">
											<p>
												<?php

												if ( ! wp_http_supports( array( 'ssl' ) ) ) {
													?><b><?php esc_html_e( 'Disabled.', 'fdestadisticas' ); ?></b> <?php esc_html_e( 'Your Web server cannot make SSL requests; contact your Web host and ask them to add support for SSL requests.', 'fdestadisticas' ); ?><?php
												}
												else {
													$ssl_disabled = get_option( 'fdestadisticas_ssl_disabled' );

													if ( $ssl_disabled ) {
														?><b><?php esc_html_e( 'Temporarily disabled.', 'fdestadisticas' ); ?></b> <?php esc_html_e( 'FDEstadisticas encountered a problem with a previous SSL request and disabled it temporarily. It will begin using SSL for requests again shortly.', 'fdestadisticas' ); ?><?php
													}
													else {
														?><b><?php esc_html_e( 'Enabled.', 'fdestadisticas' ); ?></b> <?php esc_html_e( 'All systems functional.', 'fdestadisticas' ); ?><?php
													}
												}

												?>
											</p>
										</td>
									</tr>
								<?php } ?>
								<tr>
									<th align="left" scope="row"><?php esc_html_e('Comments', 'fdestadisticas');?></th>
									<td></td>
									<td align="left">
										<p>
											<label for="fdestadisticas_show_user_comments_approved" title="<?php esc_attr_e( 'Show approved comments' , 'fdestadisticas'); ?>">
												<input
													name="fdestadisticas_show_user_comments_approved"
													id="fdestadisticas_show_user_comments_approved"
													value="1"
													type="checkbox"
													<?php
													
													// If the option isn't set, or if it's enabled ('1'), or if it was enabled a long time ago ('true'), check the checkbox.
													checked( true, ( in_array( get_option( 'fdestadisticas_show_user_comments_approved' ), array( false, '1', 'true' ), true ) ) );
													
													?>
													/>
												<?php esc_html_e( 'Show the number of approved comments beside each comment author', 'fdestadisticas' ); ?>
											</label>
										</p>
									</td>
								</tr>
								<tr>
									<th class="strictness" align="left" scope="row"><?php esc_html_e('Strictness', 'fdestadisticas'); ?></th>
									<td></td>
									<td align="left">
										<fieldset><legend class="screen-reader-text"><span><?php esc_html_e('FDEstadisticas anti-spam strictness', 'fdestadisticas'); ?></span></legend>
										<p><label for="fdestadisticas_strictness_1"><input type="radio" name="fdestadisticas_strictness" id="fdestadisticas_strictness_1" value="1" <?php checked('1', get_option('fdestadisticas_strictness')); ?> /> <?php esc_html_e('Silently discard the worst and most pervasive spam so I never see it.', 'fdestadisticas'); ?></label></p>
										<p><label for="fdestadisticas_strictness_0"><input type="radio" name="fdestadisticas_strictness" id="fdestadisticas_strictness_0" value="0" <?php checked('0', get_option('fdestadisticas_strictness')); ?> /> <?php esc_html_e('Always put spam in the Spam folder for review.', 'fdestadisticas'); ?></label></p>
										</fieldset>
										<span class="fdestadisticas-note"><strong><?php esc_html_e('Note:', 'fdestadisticas');?></strong>
										<?php
									
										$delete_interval = max( 1, intval( apply_filters( 'fdestadisticas_delete_comment_interval', 15 ) ) );
									
										printf(
											_n(
												'Spam in the <a href="%1$s">spam folder</a> older than 1 day is deleted automatically.',
												'Spam in the <a href="%1$s">spam folder</a> older than %2$d days is deleted automatically.',
												$delete_interval,
												'fdestadisticas'
											),
											admin_url( 'edit-comments.php?comment_status=spam' ),
											$delete_interval
										);
									
										?>
									</td>
								</tr>
								<tr>
									<th class="comment-form-privacy-notice" align="left" scope="row"><?php esc_html_e('Privacy', 'fdestadisticas'); ?></th>
									<td></td>
									<td align="left">
										<fieldset><legend class="screen-reader-text"><span><?php esc_html_e('FDEstadisticas privacy notice', 'fdestadisticas'); ?></span></legend>
										<p><label for="fdestadisticas_comment_form_privacy_notice_display"><input type="radio" name="fdestadisticas_comment_form_privacy_notice" id="fdestadisticas_comment_form_privacy_notice_display" value="display" <?php checked('display', get_option('fdestadisticas_comment_form_privacy_notice')); ?> /> <?php esc_html_e('Display a privacy notice under your comment forms.', 'fdestadisticas'); ?></label></p>
										<p><label for="fdestadisticas_comment_form_privacy_notice_hide"><input type="radio" name="fdestadisticas_comment_form_privacy_notice" id="fdestadisticas_comment_form_privacy_notice_hide" value="hide" <?php echo in_array( get_option('fdestadisticas_comment_form_privacy_notice'), array('display', 'hide') ) ? checked('hide', get_option('fdestadisticas_comment_form_privacy_notice'), false) : 'checked="checked"'; ?> /> <?php esc_html_e('Do not display privacy notice.', 'fdestadisticas'); ?></label></p>
										</fieldset>
										<span class="fdestadisticas-note"><?php esc_html_e( 'To help your site with transparency under privacy laws like the GDPR, FDEstadisticas can display a notice to your users under your comment forms. This feature is disabled by default, however, you can turn it on above.', 'fdestadisticas' );?></span>
									</td>
								</tr>
							</tbody>
						</table>
						<div class="fdestadisticas-card-actions">
							<?php if ( ! FDEstadisticas::predefined_api_key() ) { ?>
							<div id="delete-action">
								<a class="submitdelete deletion" href="<?php echo esc_url( FDEstadisticas_Admin::get_page_url( 'delete_key' ) ); ?>"><?php esc_html_e('Disconnect this account', 'fdestadisticas'); ?></a>
							</div>
							<?php } ?>
							<?php wp_nonce_field(FDEstadisticas_Admin::NONCE) ?>
							<div id="publishing-action">
								<input type="hidden" name="action" value="enter-key">
								<input type="submit" name="submit" id="submit" class="fdestadisticas-button fdestadisticas-could-be-primary" value="<?php esc_attr_e('Save Changes', 'fdestadisticas');?>">
							</div>
							<div class="clear"></div>
						</div>
					</form>
				</div>
			</div>
			
			<?php if ( ! FDEstadisticas::predefined_api_key() ) { ?>
				<div class="fdestadisticas-card">
					<div class="fdestadisticas-section-header">
						<div class="fdestadisticas-section-header__label">
							<span><?php esc_html_e( 'Account' , 'fdestadisticas'); ?></span>
						</div>
					</div>
				
					<div class="inside">
						<table cellspacing="0" border="0" class="fdestadisticas-settings">
							<tbody>
								<tr>
									<th scope="row" align="left"><?php esc_html_e( 'Subscription Type' , 'fdestadisticas');?></th>
									<td width="5%"/>
									<td align="left">
										<p><?php echo esc_html( $fdestadisticas_user->account_name ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row" align="left"><?php esc_html_e( 'Status' , 'fdestadisticas');?></th>
									<td width="5%"/>
									<td align="left">
										<p><?php 
											if ( 'cancelled' == $fdestadisticas_user->status ) :
												esc_html_e( 'Cancelled', 'fdestadisticas' ); 
											elseif ( 'suspended' == $fdestadisticas_user->status ) :
												esc_html_e( 'Suspended', 'fdestadisticas' );
											elseif ( 'missing' == $fdestadisticas_user->status ) :
												esc_html_e( 'Missing', 'fdestadisticas' ); 
											elseif ( 'no-sub' == $fdestadisticas_user->status ) :
												esc_html_e( 'No Subscription Found', 'fdestadisticas' );
											else :
												esc_html_e( 'Active', 'fdestadisticas' );  
											endif; ?></p>
									</td>
								</tr>
								<?php if ( $fdestadisticas_user->next_billing_date ) : ?>
								<tr>
									<th scope="row" align="left"><?php esc_html_e( 'Next Billing Date' , 'fdestadisticas');?></th>
									<td width="5%"/>
									<td align="left">
										<p><?php echo date( 'F j, Y', $fdestadisticas_user->next_billing_date ); ?></p>
									</td>
								</tr>
								<?php endif; ?>
							</tbody>
						</table>
						<div class="fdestadisticas-card-actions">
							<div id="publishing-action">
								<?php FDEstadisticas::view( 'get', array( 'text' => ( $fdestadisticas_user->account_type == 'free-api-key' && $fdestadisticas_user->status == 'active' ? __( 'Upgrade' , 'fdestadisticas') : __( 'Change' , 'fdestadisticas') ), 'redirect' => 'upgrade' ) ); ?>
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			<?php } ?>
		<?php endif;?>
	</div>
</div>
