<?php
/*
Plugin Name: iSimpleDesign Clicktell Text Message
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: The Plugin's Version Number, e.g.: 1.0
Author: Samuel R W East
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/


// Put our defaults in the "wp-options" table
add_option("isd-user", $user);
add_option("isd-password", $password);
add_option("isd-apiid", $apiid);


// Start the plugin
if ( ! class_exists( 'ISD_Text_Admin' ) ) {
	
	class ISD_Text_Admin {


// prep options page insertion
		function add_config_page() {
			if ( function_exists('add_submenu_page') ) {
				add_options_page('ISD Options', 'ISD SMS Options', 10, basename(__FILE__), array('ISD_Text_Admin','config_page'));
			}	
	}
	
// Options/Settings page in WP-Admin
		function config_page() {
			if ( isset($_POST['submit']) ) {
				$nonce = $_REQUEST['_wpnonce'];
				if (! wp_verify_nonce($nonce, 'isd-updatesettings') ) die('Security check failed'); 
				if (!current_user_can('manage_options')) die(__('You cannot edit the search-by-category options.'));
				check_admin_referer('isd-updatesettings');
				
				
			// Get our new option values
			$user	= $_POST['user'];
			$password	= $_POST['password'];
			$apiid	= $_POST['apiid'];

				
				
				
				
				// Update the DB with the new option values
				update_option("isd-user", mysql_real_escape_string($user));
				update_option("isd-password", mysql_real_escape_string($password));
				update_option("isd-apiid", mysql_real_escape_string($apiid));

			}

			$user	= get_option("isd-user");
			$password	= get_option("isd-password");
			$apiid	= get_option("isd-apiid");

			
?>

<div class="wrap">
  <h2>Clickatell Options</h2>
  <form action="" method="post" id="isd-config">
    <table class="form-table">
      <?php if (function_exists('wp_nonce_field')) { wp_nonce_field('isd-updatesettings'); } ?>
      <tr>
        <th scope="row" valign="top"><label for="user">User:</label></th>
        <td><input type="text" name="user" id="user" class="regular-text" value="<?php echo $user; ?>"/></td>
      </tr>
      
      <tr>
        <th scope="row" valign="top"><label for="password">Password:</label></th>
        <td><input type="text" name="password" id="password" class="regular-text" value="<?php echo $password; ?>"/></td>
      </tr>
      
      <tr>
        <th scope="row" valign="top"><label for="apiid">Api ID:</label></th>
        <td><input type="text" name="apiid" id="apiid" class="regular-text" value="<?php echo $apiid; ?>"/></td>
      </tr>

    </table>
    <br/>
    <span class="submit" style="border: 0;">
    <input type="submit" name="submit" value="Save Settings" />
    </span>
  </form>
  
  <small>You can view my article here for more info on setting up a clicktell account <a href="http://www.isimpledesign.co.uk/blog/seo/send-test">Setting up Clicktell Account</a></small>
  
 </div>
<?php		}
	}
}



// Base function 
function isd_text() {	

if(isset($_POST['send'])) {
	
$isduser	    = get_option("isd-user");
$isdpassword	= get_option("isd-password");
$isdapiid	    = get_option("isd-apiid");	

$text = $_POST['text'];
$number = $_POST['number'];

$user = $isduser;
$password = $isdpassword;
$api_id = $isdapiid;
$baseurl ="http://api.clickatell.com";
$text = urlencode($text);
$to = $number;
// auth call
$url = "$baseurl/http/auth?user=$user&password=$password&api_id=$api_id";


// do auth call
$ret = file($url);
// split our response. return string is on first line of the data returned
$sess = split(":",$ret[0]);
if ($sess[0] == "OK") {
$sess_id = trim($sess[1]); // remove any whitespace
$url = "$baseurl/http/sendmsg?session_id=$sess_id&to=$to&text=$text";


// do sendmsg call
$ret = file($url);
$send = split(":",$ret[0]);
if ($send[0] == "ID")
echo "success you message was sent";
else
echo "send message failed";
} else {
echo "Authentication failure: ". $ret[0];
exit();
}
}


echo "<form id='isd-clicktell-form' method='post' action=''>

<label for='number'>Enter your Number <small>Please use the following format 447702220339</small></label>
<input name='number' id='number' class='number' />

<label for='text'>Your Message:</label>
<textarea name='text' id='text' class='word_count'></textarea>

<input type='submit' name='send' class='send' id='send' value='send' />
<div class='message'></div>
<span class='counter'></span>
</form>"; 



 
}

// insert custom js
function sms_js_insert() {
		$current_path = get_option('siteurl').'/wp-content/plugins/'.basename(dirname(__FILE__));
		
		echo '<script type="text/javascript" src="'.$current_path.'/js/isd-clicktell.js"></script>';		
} 
add_action('wp_footer','sms_js_insert');

// insert custom stylesheet
function sms_style_insert() {
		$current_path = get_option('siteurl').'/wp-content/plugins/'.basename(dirname(__FILE__));
		
		echo '<link href="'.$current_path.'/css/isd-clicktell.css" type="text/css" rel="stylesheet" />';
		
} 
add_action('wp_head','sms_style_insert');

// insert into admin panel
add_action('admin_menu', array('ISD_Text_Admin','add_config_page'));
add_shortcode('sms', 'isd_text');
?>