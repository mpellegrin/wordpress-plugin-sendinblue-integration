<?php
/**
 * @package SendInBlue_Integration
 * @version 1.0
 */
/*
Plugin Name: SendInBlue Integration
Plugin URI: https://github.com/mpellegrin/wordpress-plugin-sendinblue-integration/
Description: Hooks for SendInBlue integration
Author: Mathieu Pellegrin
Version: 1.0
Author URI: http://mathieu-pellegrin.fr/
Text Domain: sendinblue-integration
Domain Path: /languages
License: GPL v3 - http://www.gnu.org/licenses/gpl-3.0.html
*/


if (!class_exists('Mailin')) {
	require_once(__DIR__ . '/api/mailin.php');
}

require_once(__DIR__ . '/includes/newsletter.type.php');
require_once(__DIR__ . '/includes/newsletter_category.type.php');

//add_action('admin_post_sib_integration_create_campaign', '_sib_integration_create_campaign');
//add_action('admin_head', 'sib_integration_cssjs');

// Load Text Domain
function sib_integration_load_plugin_textdomain() {
	load_plugin_textdomain( 'sendinblue-integration', FALSE, basename(__DIR__) . '/languages/' );
}
add_action( 'plugins_loaded', 'sib_integration_load_plugin_textdomain' );

// Register new image size
function sib_integration_register_imagesize() {
	add_image_size( 'sib_newsletter_thumbnail', 550, 400, true );
}
add_action( 'init', 'sib_integration_register_imagesize' );

// Outputs settings section
function sib_integration_settings_section($arg) {
	;
}

// Register settings
function sib_integration_settings_register() {
	add_settings_section( 'sendinblue-integration', __('Newsletter default settings', 'sendinblue-integration'), 'sib_integration_settings_register', 'sendinblue-integration_settings' );

	register_setting( 'sendinblue-integration', 'sib_newsletter_default_add_events', 'boolval' );
	register_setting( 'sendinblue-integration', 'sib_newsletter_default_add_events_days', 'intval' );
	register_setting( 'sendinblue-integration', 'sib_newsletter_default_add_posts', 'boolval' );
	register_setting( 'sendinblue-integration', 'sib_newsletter_default_add_posts_count', 'intval' );
	register_setting( 'sendinblue-integration', 'sib_newsletter_default_add_posts_categories' );

	add_settings_field( 'sib_newsletter_default_add_events', __('Add Events to Newsletter', 'sendinblue-integration'), 'sib_integration_settings_default_add_events', 'sendinblue-integration_settings', 'sendinblue-integration' );
	add_settings_field( 'sib_newsletter_default_add_events_days', __('How many days ahead we should display events', 'sendinblue-integration'), 'sib_integration_settings_default_add_events_days', 'sendinblue-integration_settings', 'sendinblue-integration' );
	add_settings_field( 'sib_newsletter_default_add_posts', __('Add Posts to Newsletter', 'sendinblue-integration'), 'sib_integration_settings_default_add_posts', 'sendinblue-integration_settings', 'sendinblue-integration' );
	add_settings_field( 'sib_newsletter_default_add_posts_count', __('How many posts we should display in Newsletter', 'sendinblue-integration'), 'sib_integration_settings_default_add_posts_count', 'sendinblue-integration_settings', 'sendinblue-integration' );
	add_settings_field( 'sib_newsletter_default_add_posts_categories', __('Categories of posts that will be retrieved', 'sendinblue-integration'), 'sib_integration_settings_default_add_posts_categories', 'sendinblue-integration_settings', 'sendinblue-integration' );
}
add_action( 'admin_init', 'sib_integration_settings_register' );

// Outputs code for each setting field
function sib_integration_settings_default_add_events() {
	$option_value = get_option('sib_newsletter_default_add_events', true);
	echo '<input type="checkbox" id="sib_newsletter_default_add_events" name="sib_newsletter_default_add_events" ' . ($option_value ? 'checked="checked"' : '') . '" value="1" />';
}
function sib_integration_settings_default_add_events_days() {
	$option_value = get_option('sib_newsletter_default_add_events_days', 30);
	echo '<input type="text" id="sib_newsletter_default_add_events_days" name="sib_newsletter_default_add_events_days" value="' . htmlspecialchars($option_value) . '" />';
}
function sib_integration_settings_default_add_posts() {
	$option_value = get_option('sib_newsletter_default_add_posts', false);
	echo '<input type="checkbox" id="sib_newsletter_default_add_posts" name="sib_newsletter_default_add_posts" ' . ($option_value ? 'checked="checked"' : '') . '" value = "1" />';
}
function sib_integration_settings_default_add_posts_count() {
	$option_value = get_option('sib_newsletter_default_add_posts_count', true);
	echo '<input type="text" id="sib_newsletter_default_add_posts_count" name="sib_newsletter_default_add_posts_count" value="' . htmlspecialchars($option_value) . '" />';
}
function sib_integration_settings_default_add_posts_categories() {
	$option_value = get_option('sib_newsletter_default_add_posts_categories');
	echo '<select id="sib_newsletter_default_add_posts_categories" name="sib_newsletter_default_add_posts_categories[]" multiple>';
	$categories = get_categories();
	if ($categories && is_array($categories)) {
		foreach ($categories as $category) {
			echo '<option value="' . $category->term_id . '"' . (is_array($option_value) && in_array($category->term_id, $option_value) ? 'selected="selected"' : '') . '>' . htmlspecialchars($category->name) . '</option>';
		}
	}
	echo '</select>';
}

// Outputs settings form
function sib_integration_settings_form() {
	echo '<form action="options.php" method="post">';
	settings_fields('sendinblue-integration');
	do_settings_sections('sendinblue-integration_settings');
	submit_button();
	echo '</form>';
}

// Add submenu for settings
function sib_integration_menu() {
	$hook_suffix = add_submenu_page( 'edit.php?post_type=sib_newsletter', __('Settings', 'sendinblue-integration'), __('Settings', 'sendinblue-integration'), 'manage_options', 'sendinblue-integration_settings', 'sib_integration_settings_form');
}
add_action('admin_menu', 'sib_integration_menu');

// Create the campaign in SendInBlue, and return the ID if successful
function _sib_integration_create_campaign( $post_id ) {
	$mailin = _sib_integration_get_api();
	$post = get_post($post_id);
	$sib_post = $post;

	if ($post && get_post_type($post) == 'sib_newsletter') {

		// Run template
		ob_start();
		require(__DIR__ . '/templates/html.php');
		$html_content = ob_get_clean();
		ob_end_clean();

		// Get Senders
		$data = array( "option" => "" );
		$result = $mailin->get_senders($data);

		if ($result['code'] == 'success') {
			$senders = $result['data'];
		} else {
			return false;
		}

		$date = strftime('%d/%m/%Y %H:%M:%S');
		$data = array(
			'name' => $sib_post->post_title,
			'subject' => $sib_post->post_title,
			'from_name' => $senders[0]['from_name'],
			'from_email' => $senders[0]['from_email'],
			'html_content' => $html_content,
		);

		$result = $mailin->create_campaign($data);
		//echo htmlspecialchars($result['message']);
		if ($result['code'] == 'success') {
			return $result['data']['id'];
		} else {
			return false;
		}
	}
}


// Check if the campaign is already sent
function _sib_integration_campaign_is_sent( $campaign_id ) {
	$mailin = _sib_integration_get_api();

	if ($campaign_id && $campaign_id != '' && $campaign_id > 0) {
		// Get campaign
		$data = array( 'id' => $campaign_id );
		$result = $mailin->get_campaign_v2($data);
		if ($result['code'] == 'success') {
			return (strtolower($result['data'][0]['status']) == 'sent');
		} else {
			// Pretend the campaign is sent
			return true;
		}
	} else {
		// If something went wrong, pretend the campaign is sent
		return true;
	}
}


// Update the campaign in SendInBlue, and return the ID if successful
function _sib_integration_update_campaign( $post_id ) {
	$mailin = _sib_integration_get_api();
	$post = get_post($post_id);
	$sib_post = $post;
	$campaign_id = get_post_meta($post_id, 'sib_campaign_id', true);

	if ($post && get_post_type($post) == 'sib_newsletter' && $campaign_id) {

		ob_start();
		require(__DIR__ . '/templates/html.php');
		$html_content = ob_get_clean();
		ob_end_clean();

		// Get Senders
		$data = array( "option" => "" );
		$result = $mailin->get_senders($data);

		if ($result['code'] == 'success') {
			$senders = $result['data'];
		} else {
			return false;
		}

		$data = array(
			'id' => $campaign_id,
			'name' => $sib_post->post_title,
			'subject' => $sib_post->post_title,
			'from_name' => $senders[0]['from_name'],
			'from_email' => $senders[0]['from_email'],
			'html_content' => $html_content,
		);

		$result = $mailin->update_campaign($data);
		//echo htmlspecialchars($result['message']);
		if ($result['code'] == 'success') {
			return $campaign_id;
		} else {
			return false;
		}
	}
}

/*
 * Dead code
 */
function _sib_integration_get_sendinblue_table() {
	$mailin = _sib_integration_get_api();
	$data = array(
		'status' => 'draft',
	);
	$result = $mailin->get_campaigns_v2($data);
	if ($result['data'] && $result['data']['campaign_records']) {
		echo '<table><tr><th>ID</th><th>Title</th><th>Subject</th></tr>';
		foreach ($result['data']['campaign_records'] as $item) {
			echo '<tr>';
			echo '<td>' . htmlspecialchars($item['id']) . '</td>';
			echo '<td>' . htmlspecialchars($item['campaign_name']) . '</td>';
			echo '<td>' . htmlspecialchars($item['subject']) . '</td>';
			echo '</tr>';
		}
		echo '</table>';
	} else {
		echo htmlspecialchars($result['message']);
	}
}

/*
 * Dead code
 */
function _sib_integration_get_wordpress_table() {
	$args = array(
		'post_type' => 'sib_newsletter',
		'order' => 'desc',
		'orderby' => 'post_date',
	);
	$query = new WP_Query($args);
	if ( $query->have_posts() ) {
		?>
		<table>
			<tr><th>Title</th><th>Date</th><th colspan="2"></th></tr>
			<?php while ( $query->have_posts() ): ?>
			<?php $query->the_post(); ?>
			<tr>
				<td><?php the_title() ?></td>
				<td><?php the_date() ?></td>
				<td><a href="<?php echo get_site_url() ?>/wp-admin/post.php?post=<?php echo get_the_ID(); ?>&action=edit">Edit</a></td>
			</tr>
			<?php endwhile; ?>
		</table>
		<?php
	}
}

function _sib_integration_get_accesskey() {
	if (class_exists('SIB_Manager')) {
		return SIB_Manager::$access_key;
	} else {
		return false;
	}
}

function _sib_integration_get_api() {
	static $mailin = null;
	if ($mailin === null) {
		$mailin = new Mailin('https://api.sendinblue.com/v2.0', _sib_integration_get_accesskey());
	}
	return $mailin;
}

/*
 * Dead code
 */
function sib_integration_view() {
	?><div id="#sib_integration">
	<h1>SendInBlue Integration</h1>
	<p><?php echo _e('This interface creates the campaigns in SendInBlue interface', 'sendinblue-integration'); ?></p>
	<a href="<?php echo admin_url( 'admin-post.php?action=sib_integration_preview_campaign' ) ?>" target="_blank">Aperçu</a>
	<button id="sendinblue_create_campaign" data-href="<?php echo htmlspecialchars(admin_url( 'admin-post.php?action=_sib_integration_create_campaign' )) ?>"><?php echo htmlentities('Créer une campagne') ;?></button>
	<div class="messages"></div>
	<div id="sib_integration--tabs">
		<ul>
			<li><a href="#sib_integration--tab_wordpress"><?php echo _e('Wordpress Campaigns', 'sendinblue-integration'); ?></a></li>
			<li><a href="#sib_integration--tab_sendinblue"><?php echo _e('SendInBlue Campaigns', 'sendinblue-integration'); ?></a></li>
		</ul>
		<div id="sib_integration--tab_wordpress">
			<?php _sib_integration_get_wordpress_table(); ?>
		</div>
		<div id="sib_integration--tab_sendinblue">
			<?php _sib_integration_get_sendinblue_table(); ?>
		</div>
	</div>
	<script type="text/javascript">
		jQuery(document).ready( function() { jQuery('#sib_integration--tabs').tabs({ active: 'sib_integration--tab_wordpress' }); });
		jQuery('#sendinblue_create_campaign').click(function(e) {
			var url = jQuery(e.target).attr('data-href');
			jQuery.get(url, function(result) {
				jQuery('.messages').text(result);
			});
		});
	</script>
	</div><?php
}

// Display Newsletter preview instead of Wordpress templates
function sib_integration_override_template( $template )
{
	if ( is_single() && get_post_type() == 'sib_newsletter' ) {
		$template = locate_template( array( 'sendinblue-integration/html.php' ) );
		if ($template == '') {
			return __DIR__ . '/templates/html.php';
		} else {
			return $template;
		}
	} else {
		return $template;
	}
}
add_filter( 'template_include', 'sib_integration_override_template' );

// Execute campaign creation when post is saved
function sib_save_newsletter($post_id) {
	// If this is a revision, get real post ID
	if ( $parent_id = wp_is_post_revision( $post_id ) )
		$post_id = $parent_id;

	// Check post type
	if (get_post_type($post_id) != 'sib_newsletter') {
		return;
	}

	$campaign_id = get_post_meta($post_id, 'sib_campaign_id', true);
	if ($campaign_id) {
		// Check if campaign is sent, if true, create a new campaign
		if (_sib_integration_campaign_is_sent($campaign_id)) {
			// unhook this function so it doesn't loop infinitely
			remove_action( 'save_post', 'sib_save_newsletter' );

			// update the post, which calls save_post again
			$campaign_id = _sib_integration_create_campaign($post_id);
			if ($campaign_id) {
				update_post_meta($post_id, 'sib_campaign_id', $campaign_id);
			} else {
				// Something gone wrong
				;
			}
		} else {
			_sib_integration_update_campaign($post_id);
		}
	} else {
		// unhook this function so it doesn't loop infinitely
		remove_action( 'save_post', 'sib_save_newsletter' );

		// update the post, which calls save_post again
		$campaign_id = _sib_integration_create_campaign($post_id);
		if ($campaign_id) {
			update_post_meta($post_id, 'sib_campaign_id', $campaign_id);
		} else {
			// Something gone wrong
			;
		}

		// re-hook this function
		add_action( 'save_post', 'sib_save_newsletter' );
	}
}
add_action( 'save_post', 'sib_save_newsletter' );

// Meta box to configure Newsletter
function sib_integration_add_metabox() {
	add_meta_box('sib_integration_newsletter_configure', __('Configure Newsletter', 'sendinblue-integration'), 'sib_integration_newsletter_metabox_content', 'sib_newsletter', 'normal');
}
add_action('add_meta_boxes','sib_integration_add_metabox');

// Newsletter metabox content
function sib_integration_newsletter_metabox_content($post) {

	// Events
	if (sib_is_edit_page('edit')) {
		$add_events = get_post_meta($post->ID, 'sib_newsletter_add_events', true);
		$add_events_days = get_post_meta($post->ID, 'sib_newsletter_add_events_days', true);
	} else {
		$add_events = get_option('sib_newsletter_default_add_events');
		$add_events_days = get_option('sib_newsletter_default_add_events_days');
	}
	echo '<div>';
	echo '<label><input type="checkbox" name="sib_newsletter_add_events" ' . ($add_events ? 'checked="checked"' : '') . ' value="1" /> ' . __('Add Events to Newsletter', 'sendinblue-integration') . '</label>';
	echo '&nbsp;';
	echo '<label>' . sprintf(__('Show events %s days ahead', 'sendinblue-integration'), '<input type="text" name="sib_newsletter_add_events_days" size="2" value="' . ($add_events_days != '' ? htmlspecialchars($add_events_days) : '0') . '" />') . '</label>';
	echo '</div>';
	echo '<div>';

	// Articles
	if (sib_is_edit_page('edit')) {
		$add_posts = get_post_meta($post->ID, 'sib_newsletter_add_posts', true);
		$add_posts_count = get_post_meta($post->ID, 'sib_newsletter_add_posts', true);
		$add_posts_categories = get_post_meta($post->ID, 'sib_newsletter_add_posts_categories', true);
	} else {
		$add_posts = get_option('sib_newsletter_default_add_posts');
		$add_posts_count = get_option('sib_newsletter_default_add_posts_count');
		$add_posts_categories = get_option('sib_newsletter_default_add_posts_categories');
	}
	$categories = get_categories();
	$categories_options = '';
	foreach ($categories as $category) {
		$categories_options .= '<option value="' . $category->term_id . '"' . (isset($add_posts_categories) && is_array($add_posts_categories) && in_array($category->term_id, $add_posts_categories) ? 'selected="selected"' : '') . '>' . htmlspecialchars($category->name) . '</option>';
	}
	echo '<label><input type="checkbox" name="sib_newsletter_add_posts" ' . ($add_posts ? 'checked="checked"' : '') . ' value="1" /> ' . __('Add Posts to Newsletter', 'sendinblue-integration') . '</label>';
	echo '&nbsp;';
	echo '<label>' . sprintf(__('Show %s posts from categories %s', 'sendinblue-integration'), '<input type="text" name="sib_newsletter_add_posts_count" size="2" value="'. (isset($add_posts_count) ? htmlspecialchars($add_posts_count) : '0') . '" />', '<select name="sib_newsletter_add_posts_categories[]" multiple size="' . count($categories) . '">' . $categories_options . '</select>') . '</label>';
	echo '</div>';
}

// Callback on post save to save Newsletter metabox
function sib_integration_newsletter_metabox_save($post_id) {
	update_post_meta($post_id, 'sib_newsletter_add_events', (bool) @$_POST['sib_newsletter_add_events']);
	update_post_meta($post_id, 'sib_newsletter_add_events_days', (int) @$_POST['sib_newsletter_add_events_days']);
	update_post_meta($post_id, 'sib_newsletter_add_posts', (bool) @$_POST['sib_newsletter_add_posts']);
	update_post_meta($post_id, 'sib_newsletter_add_posts_count', (int) @$_POST['sib_newsletter_add_posts_count']);
	if (isset($_POST['sib_newsletter_add_posts_categories']) && is_array($_POST['sib_newsletter_add_posts_categories'])) {
		/*
		delete_post_meta($post_id, 'sib_newsletter_add_posts_categories');
		foreach ($_POST['sib_newsletter_add_posts_categories'] as $category) {
			add_post_meta($post_id, 'sib_newsletter_add_posts_categories', (int) $category);
		}
		*/
		update_post_meta($post_id, 'sib_newsletter_add_posts_categories', $_POST['sib_newsletter_add_posts_categories']);
	}
}
add_action('save_post','sib_integration_newsletter_metabox_save');

function sib_reverse_parseurl($array) {
	$scheme   = isset($array['scheme']) ? $array['scheme'] . '://' : '';
	$host     = isset($array['host']) ? $array['host'] : '';
	$port     = isset($array['port']) ? ':' . $array['port'] : '';
	$user     = isset($array['user']) ? $array['user'] : '';
	$pass     = isset($array['pass']) ? ':' . $array['pass']  : '';
	$pass     = ($user || $pass) ? "$pass@" : '';
	$path     = isset($array['path']) ? $array['path'] : '';
	$query    = isset($array['query']) ? '?' . $array['query'] : '';
	$fragment = isset($array['fragment']) ? '#' . $array['fragment'] : '';
	return $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
}

function sib_escape_url($url) {
	$url = parse_url($url);
	$path = explode('/', $url['path']);
	array_walk($path, function(&$value, &$key) { $value = urlencode($value); });
	$path = implode('/', $path);
	$url['path'] = $path;
	return sib_reverse_parseurl($url);
}

function sib_is_edit_page($new_edit = null){
    global $pagenow;
    //make sure we are on the backend
    if (!is_admin()) return false;

    if ($new_edit == "edit")
        return in_array( $pagenow, array( 'post.php',  ) );
    elseif ($new_edit == "new") //check for new post page
        return in_array( $pagenow, array( 'post-new.php' ) );
    else //check for either new or edit
        return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
}
