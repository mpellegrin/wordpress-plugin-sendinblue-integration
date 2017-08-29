<?php
$days = get_post_meta(get_the_ID(), 'sib_newsletter_add_events_days', true);
if (!$days) {
	$days = get_option('sib_newsletter_default_add_events_days', 30);
}
$start_date = strftime('%Y-%m-%d %H:%M:%S', get_the_date('U'));
$end_date = strftime('%Y-%m-%d %H:%M:%S', strtotime('+' . $days . ' days', get_the_date('U')));
$events = tribe_get_events(array('post_status' => 'publish', 'start_date' => $start_date, 'end_date' => $end_date));
setlocale(LC_ALL, get_locale() . '.utf-8');
?>
<?php foreach ($events as $post): ?>
<?php setup_postdata($post); ?>
<?php $e = error_reporting(error_reporting() & ~E_NOTICE); ?>
<tr>
	<td style="background-color:#d6d6d6;" bgcolor="#d6d6d6" align="center" valign="top">
		<div>
			<table class="rnb-del-min-width" style="min-width:100%; background-color:#d6d6d6;" name="Layout_2" id="Layout_2" cellspacing="0" cellpadding="0" border="0" bgcolor="#d6d6d6" width="100%">
				<tbody>
					<tr>
						<td class="rnb-del-min-width" style="background-color: #d6d6d6;" bgcolor="#d6d6d6" align="center" valign="top">
							<table class="rnb-container" style="max-width: 100%; min-width: 100%; table-layout: fixed; background-color: rgb(255, 255, 255); border-radius: 0px; border-collapse: separate; padding-left: 20px; padding-right: 20px;" cellspacing="0" cellpadding="0" border="0" bgcolor="#ffffff" width="100%">
								<tbody>
									<tr>
										<td style="font-size:1px; line-height:1px;" height="20">&nbsp;</td>
									</tr>
									<?php if (has_post_thumbnail($post->ID)): ?>
									<tr><td class="img-block-center" align="left" width="100%" valign="top"><a href="<?php echo sib_escape_url(get_permalink($post->ID)); ?>"><img width="100%" src="<?php echo sib_escape_url(get_the_post_thumbnail_url($post->ID, 'sib_newsletter_thumbnail')); ?>" /></a></td></tr>
									<tr>
										<td class="col_td_gap" style="font-size:1px; line-height:1px;" height="10">&nbsp;</td>
									</tr>
									<?php endif; ?>
									<tr><td style="font-size:18px; font-family:Arial,Helvetica,sans-serif; color:#555; text-align:left;">
										<a href="<?php echo sib_escape_url(get_permalink($post->ID)); ?>">
											<span style="color:#555; "><strong><span style="font-size:18px;"><?php echo sib_escape_url($post->post_title); ?></span></strong></span>
										</a>
									</td></tr>
									<tr><td style="font-size:18px; font-family:Arial,Helvetica,sans-serif; color:#555; text-align:left;">
										<a href="<?php echo sib_escape_url(get_permalink($post->ID)); ?>">
											<span style="color:#555; "><strong><span style="font-size:18px;"><?php echo strftime('%d', strtotime($post->EventStartDate)) . ' ' . sib_escape_url(ucfirst(strftime('%B', strtotime($post->EventStartDate)))); ?></span></strong></span>
										</a>
									</td></tr>
									<tr>
										<td class="col_td_gap" style="font-size:1px; line-height:1px;" height="10">&nbsp;</td>
									</tr>
									<tr><td style="font-size:14px; font-family:Arial,Helvetica,sans-serif, sans-serif; color:#555;">
										<?php the_content(''); ?>
										<a href="<?php echo sib_escape_url(get_permalink($post->ID)); ?>">
											<span class="read-more"><?php echo __('Read more', 'sendinblue-integration'); ?></span>
										</a>
									</td></tr>
									<tr>
										<td style="font-size:1px; line-height:1px;" height="20">&nbsp;</td>
									</tr>

								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</td>
</tr>
<tr>
	<td style="background-color:#d6d6d6;" bgcolor="#d6d6d6" align="center" valign="top">
		<table class="rnb-del-min-width" style="min-width:590px; background-color:#d6d6d6;" name="Layout_" id="Layout_" cellspacing="0" cellpadding="0" border="0" width="100%">
			<tbody>
				<tr>
					<td class="rnb-del-min-width" style="min-width:590px; background-color:#d6d6d6;" bgcolor="#d6d6d6" align="center" valign="top">
						<table style="background-color:#d6d6d6;" cellspacing="0" cellpadding="0" border="0" bgcolor="#d6d6d6" width="100%" height="30">
							<tbody>
								<tr>
									<td valign="top" height="30"> <img style="display:block; max-height:30px; max-width:20px;" alt="" src="http://img.mailinblue.com/new_images/rnb/rnb_space.gif" width="20" height="30"> </td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</td>
</tr>

<?php endforeach; ?>
<?php if (isset($e)) { error_reporting($e); } ?>
