<?php
$categories = get_post_meta($post_id,'sib_newsletter_add_posts_categories', true);
$default_categories = get_option('sib_newsletter_default_add_posts_categories');
$limit = get_post_meta($post_id,'sib_newsletter_add_posts_count', true);
$default_limit = get_option('sib_newsletter_default_add_posts_count');
$args = array(
	'category__in' => ($categories && is_array($categories) ? $categories : $default_categories),
	'post_status' => 'publish',
	'order' => 'DESC',
	'ignore_sticky_posts' => true,
	'posts_per_page' => ($limit ? $limit : $default_limit),
);
$posts = new WP_Query($args);
$newsletter_post = $GLOBALS['post'];
?>
<?php if ($posts->have_posts()): ?>
<?php while ($posts->have_posts()): ?>
<?php $posts->the_post(); ?>
<?php if (is_preview()) { $post_id = (int) $_GET['preview_id']; } else { $post_id = get_the_ID(); } ?>
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
									<tr><td class="img-block-center" align="left" width="100%" valign="top"><a href="<?php echo sib_escape_url(get_permalink($post_id)); ?>"><img width="100%" src="<?php echo sib_escape_url(get_the_post_thumbnail_url($post_id, 'sib_newsletter_thumbnail')); ?>" /></a></td></tr>
									<tr>
										<td class="col_td_gap" style="font-size:1px; line-height:1px;" height="10">&nbsp;</td>
									</tr>
									<?php endif; ?>
									<tr><td style="font-size:18px; font-family:Arial,Helvetica,sans-serif; color:#555; text-align:left;">
										<a href="<?php echo sib_escape_url(get_permalink($post_id)); ?>">
											<span style="color:#555; "><strong><span style="font-size:18px;"><?php echo sib_escape_url($post->post_title); ?></span></strong></span>
										</a>
									</td></tr>
									<tr>
										<td class="col_td_gap" style="font-size:1px; line-height:1px;" height="10">&nbsp;</td>
									</tr>
									<tr><td style="font-size:14px; font-family:Arial,Helvetica,sans-serif, sans-serif; color:#555;">
										<?php the_content(''); ?>
										<a href="<?php echo sib_escape_url(get_permalink($post_id)); ?>">
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

<?php endwhile; ?>
<?php endif; ?>
<?php if (isset($e)) { error_reporting($e); } ?>
<?php $GLOBALS['post'] = $newsletter_post; ?>
