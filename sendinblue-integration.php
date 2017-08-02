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
Text Domain: sib-integration
Domain Path: /languages
License: GPL v3 - http://www.gnu.org/licenses/gpl-3.0.html
*/


if (!class_exists('Mailin')) {
	require_once(__DIR__ . '/api/mailin.php');
}

require_once(__DIR__ . '/includes/newsletter.type.php');
require_once(__DIR__ . '/includes/newsletter_category.type.php');

//add_action('admin_menu', 'sib_integration_menu');
//add_action('admin_post_sib_integration_create_campaign', '_sib_integration_create_campaign');
//add_action('admin_head', 'sib_integration_cssjs');

// Load Text Domain
function sib_integration_load_plugin_textdomain() {
	load_plugin_textdomain( 'sib-integration', FALSE, __DIR__ . '/languages/' );
}
add_action( 'plugins_loaded', 'sib_integration_load_plugin_textdomain' );

/*
 * Dead code
 */
function sib_integration_cssjs() {
	wp_enqueue_style( 'sib_integration-admin_css', plugins_url('sendinblue-integration/css/admin.css', 'sendinblue-integration') );
	wp_enqueue_style( 'sib_integration-jquery_ui', plugins_url('sendinblue-integration/js/jquery-ui-1.12.1.custom/jquery-ui.min.css', 'sendinblue-integration') );
	wp_enqueue_script( 'sib_integration-jquery_ui', plugins_url('sendinblue-integration/js/jquery-ui-1.12.1.custom/jquery-ui.min.js', 'sendinblue-integration'), array(), false, true );
}

/*
 * Dead code
 */
function sib_integration_menu() {
	$hook_suffix = add_menu_page( 'SendInBlue Integration', 'SendInBlue Integration', 'publish_posts', 'sendinblue-integration', 'sib_integration_view');
}

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
			return $result['data']['id'];
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
	<p><?php echo _e('This interface creates the campaigns in SendInBlue interface', 'sib-integration'); ?></p>
    <a href="<?php echo admin_url( 'admin-post.php?action=sib_integration_preview_campaign' ) ?>" target="_blank">Aperçu</a>
    <button id="sendinblue_create_campaign" data-href="<?php echo htmlspecialchars(admin_url( 'admin-post.php?action=_sib_integration_create_campaign' )) ?>"><?php echo htmlentities('Créer une campagne') ;?></button>
    <div class="messages"></div>
	<div id="sib_integration--tabs">
		<ul>
			<li><a href="#sib_integration--tab_wordpress"><?php echo _e('Wordpress Campaigns', 'sib-integration'); ?></a></li>
			<li><a href="#sib_integration--tab_sendinblue"><?php echo _e('SendInBlue Campaigns', 'sib-integration'); ?></a></li>
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
    if ( get_post_type() == 'sib_newsletter' ) {
		if (isset($_GET['preview_nonce'])) {
			echo 'Preview does not work (yet) with this plugin, please save your content and then click on the permalink.';
			exit();
		}
		ob_start();
		require(__DIR__ . '/templates/html.php');
		$html_content = ob_get_clean();
		ob_end_clean();
		echo $html_content;
		exit();
    }
}
add_filter( 'template_redirect', 'sib_integration_override_template' );

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
		_sib_integration_update_campaign($post_id);
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
