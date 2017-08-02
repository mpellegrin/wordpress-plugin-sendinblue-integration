<?php
if (get_the_ID()) {
	$post_id = get_the_ID();
} else {
	$post_id = '';
}
$posts = new WP_Query(array(
	'post_type' => 'sib_newsletter',
	'post__in' => array($post_id),
	'post_status' => 'publish',
	'order' => 'ASC',
	'ignore_sticky_posts' => true,
));
?>
<?php if ($posts->have_posts()): ?>
<?php while ($posts->have_posts()): ?>
<?php $posts->the_post(); ?>
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
									<tr><td class="img-block-center" align="center" style="max-width: 100%;" valign="top"><a href="<?php echo get_site_url(); ?>"><img style="max-width: 100%;" src="<?php echo get_the_post_thumbnail_url($post->ID, 'large'); ?>" /></a></td></tr>
									<tr>
										<td class="col_td_gap" style="font-size:1px; line-height:1px;" height="10">&nbsp;</td>
									</tr>
									<?php endif; ?>
									<?php /*
									<tr><td style="font-size:18px; font-family:Arial,Helvetica,sans-serif; color:#999; text-align:left;">
										<a href="<?php echo get_permalink($post->ID); ?>">
											<span style="color:#999; "><strong><span style="font-size:18px;"><?php echo $post->post_title; ?></span></strong></span>
										</a>
									</td></tr>
									<tr>
										<td class="col_td_gap" style="font-size:1px; line-height:1px;" height="10">&nbsp;</td>
									</tr>
									*/ ?>
									<tr><td style="font-size:14px; font-family:Arial,Helvetica,sans-serif, sans-serif; color:#555;">
										<?php the_content(''); ?>
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

<?php endwhile; ?>
<?php endif; ?>
<?php if (isset($e)) { error_reporting($e); } ?>
