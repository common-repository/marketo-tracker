<?php
/**
 Plugin Name: Marketo Tracker
 Plugin URI: http://www.get10up.com/plugins/marketo-tracker-wordpress/
 Description: Easily add the <strong>Marketo "Munchkin" tracking script</strong> to your website footer and create lead associations from comments. Option to disable tracking for contributor and higher roles. 
 Version: 2.5
 Author: Jake Goldman (10up)
 Author URI: http://www.get10up.com

    Plugin: Copyright 2011 10up  (email : jake@get10up.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * plugin upgrade
 */
 
register_activation_hook( __FILE__, 'marketo_tracker_install' );

function marketo_tracker_install()
{
	if ( get_option('marketo-tracker-code') ) :		// upgrade from pre 2.2 - convert options
	
		$marketo_options = array(
			'code' => get_option('marketo-tracker-code'),
			'hide' => get_option('marketo-tracker-hide'),
			'commenters' => get_option('marketo-tracker-commenters'),
			'secretkey' => get_option('marketo-tracker-secretkey'),
			'leadsource' => get_option('marketo-tracker-leadsource')
		);
		
		add_option( 'marketo_tracker_options', $marketo_options );
		
		delete_option('marketo-tracker-code');
		delete_option('marketo-tracker-hide');
		delete_option('marketo-tracker-commenters');
		delete_option('marketo-tracker-secretkey');
		delete_option('marketo-tracker-leadsource');
	
	endif;	
}

/**
 * plug-in initialization
 */

add_action( 'admin_init', 'marketo_tracker_admin_init' );

function marketo_tracker_admin_init() {
	register_setting( 'marketo-tracker-options', 'marketo_tracker_options', 'marketo_tracker_sanitize' );
}

/**
 * add plug-in action link to settings page
 */
 
add_filter( 'plugin_action_links', 'marketo_tracker_plugin_actlinks', 10, 2 );

function marketo_tracker_plugin_actlinks( $links, $file ) 
{ 
	if ( $file == plugin_basename(__FILE__) )
		$links[] = '<a href="' . admin_url( 'options-general.php?page=marketo_tracker_config' ) . '">Settings</a>';

    return $links;
}

/**
 * admin control panel
 */

add_action('admin_menu', 'marketo_tracker_admin_menu');

function marketo_tracker_admin_menu() 
{
	$marketo_page = add_options_page( 'Marketo Tracker Configuration', 'Marketo Tracker', 'manage_options', 'marketo_tracker_config', 'marketo_tracker_config' );
	add_action( "load-$marketo_page", 'marketo_tracker_check_settings' );
}

function marketo_tracker_config() 
{
?>
<div class="wrap">
	<div class="icon32" style="background: transparent url(<?php echo plugin_dir_url( __FILE__ ); ?>marketo_icon.png) 3px 3px no-repeat;"><br /></div>
	<h2>Marketo Tracker Configuration</h2>
	
	<form method="post" action="options.php">
		<?php 
			settings_fields('marketo-tracker-options'); 
			$marketo_options = get_option('marketo_tracker_options'); 
		?>
		
		<p>The Marketo Tracker plug-in allows you to track visitors using Marketo's Munchkin tracker and assign fields from comments to the lead being tracked. For additional help and instructions, open the help tab. This plug-in was developed by <a href="http://www.get10up.com" target="_blank">Jake Goldman (10up)</a> independent of Marketo Inc. and is provided at no charge. If you're looking for a professional Marketo integration with WordPress, consider <a href="http://www.get10up.com/contact/" target="_blank">getting in touch with us</a>!</p>
		
		<h3 class="title">General Options</h3>
		
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="marketo-tracker-code">Tracking code</label></th>
				<td>
					<input type="text" name="marketo_tracker_options[code]" id="marketo-tracker-code" value="<?php echo @esc_attr( $marketo_options['code'] ); ?>" maxlength="12" class="regular-text" />
					<span class="description">Provided by Marketo.</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Track contributors</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Track contributors</span></legend>
						<label for="marketo-tracker-hide">
							<input type="checkbox" name="marketo_tracker_options[hide]" value="1" id="marketo-tracker-hide" <?php @checked( $marketo_options['hide'] ); ?> />
							Track users with Contributor or higher roles.
						</label>
					</fieldset>
				</td>
			</tr>
		</table>
		
		<h3 class="title">Associate Comment Fields with Lead</h3>
		
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Comment Field Tracking</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span>Comment Field Tracking</span></legend>
						<label for="marketo-tracker-commenters">
							<input type="checkbox" name="marketo_tracker_options[commenters]" value="1" id="marketo-tracker-commenters" <?php @checked( $marketo_options['commenters'] ); ?> />
							Enable comment field tracking.
						</label>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="marketo-tracker-secretkey">Secret API Key</label></th>
				<td>
					<input type="text" name="marketo_tracker_options[secretkey]" id="marketo-tracker-secretkey" value="<?php echo @esc_attr( $marketo_options['secretkey'] ); ?>" class="regular-text" />
					<span class="description">Required for comment association.</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="marketo-tracker-leadsource">Assign Lead Source</label></th>
				<td>
					<?php $leadsource = intval( $marketo_options['leadsource'] ); ?>
					<select name="marketo_tracker_options[leadsource]" id="marketo-tracker-leadsource">
						<option value="0">Don't set</option>
						<option value="1" <?php selected( $leadsource, 1 ); ?>>Permalink for the comment</option>
						<option value="2" <?php selected( $leadsource, 2 ); ?>>Site name: &ldquo;<?php bloginfo('name'); ?>&rdquo;</option>
						<option value="3" <?php selected( $leadsource, 3 ); ?>>Site URL: &ldquo;<?php echo home_url(); ?>&rdquo;</option>
						<option value="4" <?php selected( $leadsource, 4 ); ?>>Text: &ldquo;Website Comment&rdquo;</option>
					</select>
				</td>
			</tr>
		</table>
		
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
			
	</form>	
</div>	
<?php 
}

function marketo_tracker_check_settings() {
	// notification message if comment tracking is enabled but no API key provided
	add_action( 'admin_notices', 'marketo_tracker_no_key_warning' );
	
	// context help
	$help = '
		<p>This plug-in requires an account with <a href="http://www.marketo.com" target="_blank">Marketo</a>.</p>
		<p><strong>Tracking code</strong> - A unique, alpha-numeric tracking ID, provided by Marketo.</p>
		<p><strong>Track contributors</strong> - In most cases, tracking users above the subscriber level (blog contributors, authors, administrators, etc) is undesirable. Tracking these users typically provides little meaningful data while incurring tracking overhead on these heavy users. If you would like to track users with contributor and above roles anyhow, check this option.</p>
		<p><strong>Comment field tracking</strong> - When a visitor posts a comment, Marketo will associate the name, email, and website fields with the visitor (or "lead") in the Marketo tracking database. This does not apply to logged in users; only commenters who fill out the comment form fields. Assumes the existence of FirstName, LastName, and Website fields in Marketo. First and last name are determined by seeking a space character in the name field. Requires a Marketo secret API key. Comments trapped as spam will not be associated.</p>
		<p><strong>Secret API Key</strong> - In order to make a call back to Marketo to associate lead information, you must provide your secret API key. This can be found on the Munchkin Web Tracking Setup screen under Admin.</p>
		<p><strong>Assign Lead Source</strong> - Customize the lead source associated with the lead when a comment is left. Assumes the existence of a LeadSource field in Marketo.</p>
		<p><strong>For more information:</strong></p>
		<p>Visit 10up\'s <a href="http://www.get10up.com/plugins/marketo-tracker-wordpress/" target="_blank">plug-in page</a>.</p>
	';
	
	global $current_screen;
	add_contextual_help( $current_screen, $help );
}

function marketo_tracker_no_key_warning()
{
	$marketo_options = get_option('marketo_tracker_options');
	
	if ( isset($marketo_options['commenters']) && $marketo_options['commenters'] && ( !isset($marketo_options['secretkey']) || empty($marketo_options['secretkey']) ) )	
		echo '<div id="message" class="error"><p><strong>Marketo comment tracking is enabled, but no API key was provided.</strong></p></div>';
}

function marketo_tracker_sanitize( $input )
{
	$new_input = array();
	
	$new_input['code'] = wp_kses( $input['code'], array() ); // might research consistent structure of keys later
	$new_input['hide'] = ( $input['hide'] ) ? 1 : 0;
	$new_input['commenters'] = ( $input['commenters'] ) ? 1 : 0;
	$new_input['secretkey'] = $input['secretkey']; // maybe sanitize some day
	
	$leadsource = intval( $input['leadsource'] );
	$new_input['leadsource'] = ( $leadsource >= 0 && $leadsource < 5 ) ? $leadsource : 0;
	
	return $new_input;	
}

/**
 * actual tracker functionality
 */
 
add_action( 'wp_footer', 'marketo_tracker' );

function marketo_tracker() 
{
	$marketo_options = get_option('marketo_tracker_options');
	
	if ( !isset($marketo_options['code']) || empty($marketo_options['code']) )
		return;
		
	// don't track user if contributor or above role and contributor tracking disabled
	if ( !$marketo_options['hide'] && current_user_can('edit_posts') )
		return;
	
	// output tracker - note unclosed script tag until end of function in case we're tracking comment association
			
	echo apply_filters( 'marketo_tracker_credit', "<!-- marketo munchkin tracking plugin by 10up: www.get10up.com -->\n" );
	echo '<script src="http://munchkin.marketo.net/munchkin.js" type="text/javascript"></script>'."\n".'<script type="text/javascript">mktoMunchkin("' . esc_attr( $marketo_options['code'] ) . '");';
	
	// commenter association
	
	if ( $marketo_options['commenters'] && $marketo_options['secretkey'] && !is_user_logged_in() && isset($_COOKIE['comment_author_email_'.COOKIEHASH]) && !empty($_COOKIE['comment_author_email_'.COOKIEHASH]) && !isset($_COOKIE['_mkto_associated_'.COOKIEHASH]) ) {

		$comment_author_email = $_COOKIE['comment_author_email_'.COOKIEHASH];
	
		$comments = get_comments(array( 'author_email' => $comment_author_email, 'number' => 1 ));
				
		//if comment marked as spam don't associate 
		if ( !empty($comments) && $comment[0]->comment_approved != "spam" ) 
		{
			$comment = $comments[0];
			
			echo "\nmktoMunchkinFunction(\n";
	   		echo "\t'associateLead',\n";
	   		echo "\t{\n";
	      	echo "\t\tEmail: '" . $comment_author_email . "'";
	      	
	      	//try to determine first and last name
	      	
	      	$fullname = explode( " ", trim( $comment->comment_author ) );	      	
	      	echo ",\n\t\tFirstName: '" . $fullname[0] . "'";
	      	$last_name = ( count($fullname) > 1 ) ? end($fullname) : false;
	      	if ( $last_name ) echo ",\n\t\tLastName: '$last_name'";
	      	
	      	//lead source tracking
	      	
	      	$leadsource = intval( $marketo_options['leadsource'] );
	      	
	      	switch($leadsource) {
	      		case 1:
	      			$set_source = get_comment_link( $comment ); 
					break;
				case 2:
					$set_source = get_bloginfo('name');
					break;
				case 3:
					$set_source = home_url();
					break;
				case 4:
					$set_source = "Website Comment";
					break; 
	      	}	      	
	      	
	      	if ( isset($set_source) ) 
			  	echo ",\n\t\tLeadSource: '$set_source'";
	      	
	      	//website if provided
	      	
	      	if ( !empty( $comment->comment_author_url ) ) 
			  	echo ",\n\t\tWebsite: '" . $comment->comment_author_url . "'";
	      	
	      	//finish up with key hash
	      	
	   		echo "\n\t},\n\t'" . hash( 'sha1', $marketo_options['secretkey'] . $comment_author_email ) . "'\n);\n";
		}
		
		//store cookie to acknowledge user has already been tracked
		setcookie( '_mkto_associated_' . COOKIEHASH, 'set', time() + apply_filters( 'comment_cookie_lifetime', 30000000 ), COOKIEPATH, COOKIE_DOMAIN );
	}
	
	echo "</script>\n";
}

/**
 * delete options from table upon uninstall
 */
 
register_uninstall_hook( __FILE__, 'marketo_tracker_uninstall' );

function marketo_tracker_uninstall() {
	delete_option( 'marketo_tracker_options' );
}
?>