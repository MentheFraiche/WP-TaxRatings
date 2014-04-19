<?php
/*
Plugin Name: WP-TaxRatings
Plugin URI: 
Description: Adds an AJAX rating system for your WordPress blog's taxonomy.
Version: 1.00
Author: Aurelien Capdecomme
Author URI: http://www.menthefraiche.com
*/


/* 
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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


### Load WP-Config File If This File Is Called Directly
if (!function_exists('add_action')) {
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}

include_once 'taxratings-config.php';

$aRatingTexts = array(RATING_TEXT_1, RATING_TEXT_2, RATING_TEXT_3, RATING_TEXT_4, RATING_TEXT_5);
$aRatingValues = array(RATING_VALUE_1, RATING_VALUE_2, RATING_VALUE_3, RATING_VALUE_4, RATING_VALUE_5);

### Create Text Domain For Translations
add_action('init', 'taxratings_textdomain');
function taxratings_textdomain() {
	load_plugin_textdomain('wp-taxratings', false, 'wp-taxratings');
}

### Rating Logs Table Name
global $wpdb;
$wpdb->ratings = $wpdb->prefix.'ratings';

if ($_GET['wp_ajaxaction'] == 'jquery') {
	$nonce = $_REQUEST['_ajax_check'];
	
	/*if (!wp_verify_nonce($nonce, 'my_rating')) {
		die('Security check'); 
	}*/
	
	if ($nonce != sha1('my_rating' . $_GET['tax_id'])) {
		die('Security check'); 
	}
		
	$id = $wpdb->escape($_GET['tax_id']);
	the_ratings('div', $id);
    exit();
}

### Function: Ratings Administration Menu
add_action('admin_menu', 'ratings_menu');
function ratings_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page(__('Notes', 'wp-taxratings'), __('Notes', 'wp-taxratings'), 'manage_ratings', 'wp-taxratings/taxratings-manager.php', '', plugins_url('wp-taxratings/images/stars(png)/rating_on.png'));
	}
	if (function_exists('add_submenu_page')) {
		add_submenu_page('wp-taxratings/taxratings-manager.php', __('Manage Ratings', 'wp-taxratings'), __('Manage Ratings', 'wp-taxratings'), 'manage_ratings', 'wp-taxratings/taxratings-manager.php');
		//add_submenu_page('wp-taxratings/taxratings-manager.php', __('Ratings Options', 'wp-taxratings'), __('Ratings Options', 'wp-taxratings'),  'manage_ratings', 'wp-taxratings/taxratings-options.php');
		//add_submenu_page('wp-taxratings/taxratings-manager.php', __('Ratings Templates', 'wp-taxratings'), __('Ratings Templates', 'wp-taxratings'),  'manage_ratings', 'wp-taxratings/taxratings-templates.php');
		//add_submenu_page('wp-taxratings/taxratings-manager.php', __('Uninstall WP-TaxRatings', 'wp-taxratings'), __('Uninstall WP-TaxRatings', 'wp-taxratings'), 'manage_ratings', 'wp-taxratings/taxratings-uninstall.php');
	}
}


### Function: Display The Rating For The Post
function the_ratings($start_tag = 'div', $custom_id = 0, $display = true) {
	global $id;
	// Allow Custom ID
	if(intval($custom_id) > 0) {
		$ratings_id = $custom_id;
	} else {
		$ratings_id = $id;
	}
	// Loading Style
	if(AJAX_RATING_STYLE == 1) {
		$loading = "<$start_tag id=\"tax-ratings-$ratings_id-loading\" class=\"tax-ratings-loading\"><img src=\"".TEMPLATE_RATING . '/images/loader_rating.gif'."\" width=\"16\" height=\"16\" alt=\"\" class=\"tax-ratings-image\" /></".$start_tag.">";
	} else {
		$loading = '';
	}
	// Check To See Whether User Has Voted
	$user_voted = check_rated($ratings_id);
	// If User Voted Or Is Not Allowed To Rate
	if($user_voted) {
		if(!$display) {
			return "<$start_tag id=\"tax-ratings-$ratings_id\" class=\"tax-ratings\">".the_ratings_results($ratings_id).'</'.$start_tag.'>'.$loading;
		} else {
			echo "<$start_tag id=\"tax-ratings-$ratings_id\" class=\"tax-ratings\">".the_ratings_results($ratings_id).'</'.$start_tag.'>'.$loading;
			return;
		}
	// If User Is Not Allowed To Rate
	} else if(!check_allowtorate()) {
		if(!$display) {
			return "<$start_tag id=\"tax-ratings-$ratings_id\" class=\"tax-ratings\">".the_ratings_results($ratings_id, 0, 0, 0, 1).'</'.$start_tag.'>'.$loading;
		} else {
			echo "<$start_tag id=\"tax-ratings-$ratings_id\" class=\"tax-ratings\">".the_ratings_results($ratings_id, 0, 0, 0, 1).'</'.$start_tag.'>'.$loading;
			return;
		}
	// If User Has Not Voted
	} else {
		if(!$display) {
			return "<$start_tag id=\"tax-ratings-$ratings_id\" class=\"tax-ratings\">".the_ratings_vote($ratings_id).'</'.$start_tag.'>'.$loading;
		} else {
			echo "<$start_tag id=\"tax-ratings-$ratings_id\" class=\"tax-ratings\">".the_ratings_vote($ratings_id).'</'.$start_tag.'>'.$loading;
			return;
		}
	}
}


### Function: Print Out jQuery Script At The Top
add_action('wp_head', 'ratings_javascripts_header');
function ratings_javascripts_header() {
	wp_print_scripts('jquery');
}


### Function: Enqueue Ratings JavaScripts/CSS
add_action('wp_enqueue_scripts', 'ratings_scripts');
function ratings_scripts() {
	wp_enqueue_style('wp-taxratings', get_stylesheet_directory_uri().'/css/taxratings-css.css', false, '1.50', 'all');	

	$taxratings_max = intval(MAX_RATING);
	$taxratings_custom = intval(CUSTOM_RATING);
	$taxratings_ajax_style = AJAX_RATING_STYLE;
	$taxratings_javascript = '';
	if($taxratings_custom) {
		for($i = 1; $i <= $taxratings_max; $i++) {
			$taxratings_javascript .= 'var ratings_'.$i.'_mouseover_image=new Image();ratings_'.$i.'_mouseover_image.src=ratingsL10n.template_url+"/images/rating_'.$i.'_over."+ratingsL10n.image_ext;';
		}
	} else {
		$taxratings_javascript = 'var ratings_mouseover_image=new Image();ratings_mouseover_image.src=ratingsL10n.template_url+"/images/rating_over."+ratingsL10n.image_ext;';
	}
	wp_enqueue_script('wp-taxratings', plugins_url('wp-taxratings/taxratings-js.js'), array('jquery'), '1.50', true);
	wp_localize_script('wp-taxratings', 'ratingsL10n', array(
		'template_url' => TEMPLATE_RATING,
		'ajax_url' => plugins_url('wp-taxratings/wp-taxratings.php'),
		'text_wait' => 'Merci de n\'&eacute;valuer qu\'un film &agrave; la fois.',
		'image_ext' => RATINGS_IMG_EXT,
		'max' => $taxratings_max,
		'show_loading' => intval($taxratings_ajax_style),
		'show_fading' => intval($taxratings_ajax_style),
		'custom' => $taxratings_custom,
		'l10n_print_after' => $taxratings_javascript
	));
}


### Function: Enqueue Ratings Stylesheets/JavaScripts In WP-Admin
add_action('admin_enqueue_scripts', 'ratings_scripts_admin');
function ratings_scripts_admin($hook_suffix) {
	$taxratings_admin_pages = array('wp-taxratings/taxratings-manager.php', 'wp-taxratings/taxratings-options.php', 'wp-taxratings/taxratings-templates.php', 'wp-taxratings/taxratings-uninstall.php');
	if(in_array($hook_suffix, $taxratings_admin_pages)) {
		wp_enqueue_style('wp-taxratings-admin', plugins_url('wp-taxratings/taxratings-admin-css.css'), false, '1.50', 'all');
		wp_enqueue_script('wp-taxratings-admin', plugins_url('wp-taxratings/taxratings-admin-js.js'), array('jquery'), '1.50', true);
		wp_localize_script('wp-taxratings-admin', 'ratingsAdminL10n', array(
			'admin_ajax_url' => plugins_url('wp-taxratings/taxratings-admin-ajax.php')
		));
	}
}


### Function: Display Ratings Results 
function the_ratings_results($tax_id, $new_user = 0, $new_score = 0, $new_average = 0, $type = 0) {
	if($new_user == 0 && $new_score == 0 && $new_average == 0) {
		$tax_ratings_data = null;
	} else {
		$tax_ratings_data->ratings_users = $new_user;
		$tax_ratings_data->ratings_score = $new_score;
		$tax_ratings_data->ratings_average = $new_average;
	}
	// Display The Contents
	if($type == 1) {
		$template_taxratings_text = stripslashes(MSG_RATING_NO_ACCESS);
	} else {
		$template_taxratings_text = stripslashes(MSG_RATING_DEFAULT);
	}
	// Return Post Ratings Template
	return expand_ratings_template($template_taxratings_text, $tax_id, $tax_ratings_data);
}


### Function: Display Ratings Vote
function the_ratings_vote($tax_id, $new_user = 0, $new_score = 0, $new_average = 0) {
  if($new_user == 0 && $new_score == 0 && $new_average == 0) {
    $tax_ratings_data = null;
  } else {
    $tax_ratings_data->ratings_users = $new_user;
    $tax_ratings_data->ratings_score = $new_score;
    $tax_ratings_data->ratings_average = $new_average;
  }
  
   global $wpdb;
   $ratings_users = $wpdb->get_var("SELECT COUNT(rating_id) AS ratings_users FROM $wpdb->ratings WHERE rating_taxid = " . $tax_id);
  
	// If No Ratings, Return No Ratings templae
	if(intval($ratings_users) == 0) {
		$template_taxratings_none = stripslashes(MSG_RATING_NO_VOTE);
		// Return Post Ratings Template
		return expand_ratings_template($template_taxratings_none, $tax_id, $tax_ratings_data);
	} else {
		// Display The Contents
		$template_taxratings_vote = stripslashes(MSG_RATING_VOTE);
		// Return Post Ratings Voting Template
		return expand_ratings_template($template_taxratings_vote, $tax_id, $tax_ratings_data);
	}
}


### Function: Check Who Is Allow To Rate
function check_allowtorate() {
	global $user_ID;
	$user_ID = intval($user_ID);
	$allow_to_vote = intval(RATING_ALLOWRATE);
	switch($allow_to_vote) {
		// Guests Only
		case 0:
			if($user_ID > 0) {
				return false;
			}
			return true;
			break;
		// Registered Users Only
		case 1:
			if($user_ID == 0) {
				return false;
			}
			return true;
			break;
		// Registered Users And Guests
		case 2:
		default:
			return true;
	}
}


### Function: Check Whether User Have Rated For The Post
function check_rated($tax_id) {
	global $user_ID;
	$taxratings_logging_method = intval(RATING_LOGGING);
	switch($taxratings_logging_method) {
		// Do Not Log
		case 0:
			return false;
			break;
		// Logged By Cookie
		case 1:
			return check_rated_cookie($tax_id);
			break;
		// Logged By IP
		case 2:
			return check_rated_ip($tax_id);
			break;
		// Logged By Cookie And IP
		case 3:
			$rated_cookie = check_rated_cookie($tax_id);
			if($rated_cookie > 0) {
				return true;
			} else {
				return check_rated_ip($tax_id);
			}
			break;
		// Logged By Username
		case 4:
			return check_rated_username($tax_id);
			break;
	}
	return false;	
}


### Function: Check Rated By Cookie
function check_rated_cookie($tax_id) {
	if(isset($_COOKIE["rated_$tax_id"])) {
		return true;
	} else {
		return false;
	}
}


### Function: Check Rated By IP
function check_rated_ip($tax_id) {
	global $wpdb;
	// Check IP From IP Logging Database
	$get_rated = $wpdb->get_var("SELECT rating_ip FROM $wpdb->ratings WHERE rating_taxid = $tax_id AND rating_ip = '".get_ipaddress()."'");
	// 0: False | > 0: True
	return intval($get_rated);
}


### Function: Check Rated By Username
function check_rated_username($tax_id) {
	global $wpdb, $user_ID;
	if(!is_user_logged_in()) {
		return 0;
	}
	$rating_userid = intval($user_ID);
	// Check User ID From IP Logging Database
	$get_rated = $wpdb->get_var("SELECT rating_userid FROM $wpdb->ratings WHERE rating_taxid = $tax_id AND rating_userid = $rating_userid");
	// 0: False | > 0: True
	return intval($get_rated);
}


### Function: Get IP Address
//if(!function_exists('get_ipaddress')) {
	function get_ipaddress() {
		if (empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip_address = $_SERVER["REMOTE_ADDR"];
		} else {
			$ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		if(strpos($ip_address, ',') !== false) {
			$ip_address = explode(',', $ip_address);
			$ip_address = $ip_address[0];
		}
		return esc_attr($ip_address);
	}
//}


### Function: Snippet Text
if(!function_exists('snippet_text')) {
	function snippet_text($text, $length = 0) {
		if (defined('MB_OVERLOAD_STRING')) {
		  $text = @html_entity_decode($text, ENT_QUOTES, get_option('blog_charset'));
		 	if (mb_strlen($text) > $length) {
				return htmlentities(mb_substr($text,0,$length), ENT_COMPAT, get_option('blog_charset')).'...';
		 	} else {
				return htmlentities($text, ENT_COMPAT, get_option('blog_charset'));
		 	}
		} else {
			$text = @html_entity_decode($text, ENT_QUOTES, get_option('blog_charset'));
		 	if (strlen($text) > $length) {
				return htmlentities(substr($text,0,$length), ENT_COMPAT, get_option('blog_charset')).'...';
		 	} else {
				return htmlentities($text, ENT_COMPAT, get_option('blog_charset'));
		 	}
		}
	}
}


### Function: Process Post Excerpt, For Some Reasons, The Default get_tax_excerpt() Does Not Work As Expected
function ratings_tax_excerpt($tax_id, $tax_excerpt, $tax_content) {
	if(empty($tax_excerpt)) {
		return snippet_text(strip_tags($tax_content), 200);
	} else {
		return $tax_excerpt;
	}
}


### Function: Process Ratings
process_ratings();
function process_ratings() {
	global $wpdb, $user_identity, $user_ID, $aRatingValues;
	$rate = intval($_GET['rate']);
	$tax_id = intval($_GET['pid']);
	if($rate > 0 && $tax_id > 0 && check_allowtorate()) {		
		// Check For Bot
		$bots_useragent = array('googlebot', 'google', 'msnbot', 'ia_archiver', 'lycos', 'jeeves', 'scooter', 'fast-webcrawler', 'slurp@inktomi', 'turnitinbot', 'technorati', 'yahoo', 'findexa', 'findlinks', 'gaisbo', 'zyborg', 'surveybot', 'bloglines', 'blogsearch', 'ubsub', 'syndic8', 'userland', 'gigabot', 'become.com');
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		foreach ($bots_useragent as $bot) { 
			if (stristr($useragent, $bot) !== false) {
				return;
			} 
		}
		header('Content-Type: text/html; charset='.get_option('blog_charset').'');
		taxratings_textdomain();
		$rated = check_rated($tax_id);
		// Check Whether Post Has Been Rated By User
		if(!$rated) {
			// Check Whether Is There A Valid Taxonomy
			$tax = get_term($tax_id, TAXO_RATING_TYPE);
			// If Valid Post Then We Rate It
			if($tax) {
				$ratings_max = intval(MAX_RATING);
				$ratings_custom = intval(CUSTOM_RATING);
				$ratings_value = $aRatingValues;
				$tax_title = addslashes($tax->name);
				$tax_ratings_users = intval($wpdb->get_var("SELECT COUNT(rating_id) AS ratings_users FROM $wpdb->ratings WHERE rating_taxid = $tax_id"));
				$tax_ratings_score = intval($wpdb->get_var("SELECT SUM(rating_rating) AS ratings_score FROM $wpdb->ratings WHERE rating_taxid = $tax_id"));	
				// Check For Ratings Lesser Than 1 And Greater Than $ratings_max
				if($rate < 1 || $rate > $ratings_max) {
					$rate = 0;
				}
				$tax_ratings_users = ($tax_ratings_users+1);
				$tax_ratings_score = ($tax_ratings_score+intval($ratings_value[$rate-1]));
				$tax_ratings_average = round($tax_ratings_score/$tax_ratings_users, 2);
				
				// Add Log
				if(!empty($user_identity)) {
					$rate_user = addslashes($user_identity);
				} elseif(!empty($_COOKIE['comment_author_'.COOKIEHASH])) {
					$rate_user = addslashes($_COOKIE['comment_author_'.COOKIEHASH]);
				} else {
					$rate_user = __('Guest', 'wp-taxratings');
				}
				$rate_userid = intval($user_ID);
				// Only Create Cookie If User Choose Logging Method 1 Or 3
				$taxratings_logging_method = intval(RATING_LOGGING);
				if($taxratings_logging_method == 1 || $taxratings_logging_method == 3) {
					$rate_cookie = setcookie("rated_".$tax_id, $ratings_value[$rate-1], time() + 30000000, COOKIEPATH);
				}
				// Log Ratings No Matter What
				$rate_log = $wpdb->query("INSERT INTO $wpdb->ratings VALUES (0, $tax_id, '$tax_title', ".$ratings_value[$rate-1].",'".current_time('timestamp')."', '".get_ipaddress()."', '".esc_attr(@gethostbyaddr(get_ipaddress()))."' ,'$rate_user', $rate_userid)");
				// Output AJAX Result
				echo the_ratings_results($tax_id, $tax_ratings_users, $tax_ratings_score, $tax_ratings_average);
				exit();
			} else {
				print('Film inconnu.');
				exit();
			} // End if($tax)
		} else {
			print('Vous avez d&eacute;j&agrave; not&eacute; ce film.');
			exit();	
		}// End if(!$rated)
	} // End if($rate && $tax_id && check_allowtorate())
}

### Function: Gets HTML of rating images
function get_ratings_images($ratings_custom, $ratings_max, $tax_rating, $image_alt, $insert_half) {
	$ratings_images = '';
	
	if($ratings_custom) { 
		for($i=1; $i <= $ratings_max; $i++) {
			if($i <= $tax_rating) {
				$ratings_images .= '<img src="'.TEMPLATE_RATING.'/images/rating_'.$i.'_on.'.RATINGS_IMG_EXT.'" alt="'.$image_alt.'" title="'.$image_alt.'" class="tax-ratings-image" />';
			} elseif($i == $insert_half) {            
				$ratings_images .= '<img src="'.TEMPLATE_RATING.'/images/rating_'.$i.'_half.'.RATINGS_IMG_EXT.'" alt="'.$image_alt.'" title="'.$image_alt.'" class="tax-ratings-image" />';
			} else {
				$ratings_images .= '<img src="'.TEMPLATE_RATING.'/images/rating_'.$i.'_off.'.RATINGS_IMG_EXT.'" alt="'.$image_alt.'" title="'.$image_alt.'" class="tax-ratings-image" />';
			}
		}
	} else {
		for($i=1; $i <= $ratings_max; $i++) {
			if($i <= $tax_rating) {
				$ratings_images .= '<img src="'.TEMPLATE_RATING.'/images/rating_on.'.RATINGS_IMG_EXT.'" alt="'.$image_alt.'" title="'.$image_alt.'" class="tax-ratings-image" />';
			} elseif($i == $insert_half) {
				$ratings_images .= '<img src="'.TEMPLATE_RATING.'/images/rating_half.'.RATINGS_IMG_EXT.'" alt="'.$image_alt.'" title="'.$image_alt.'" class="tax-ratings-image" />';
			} else {
				$ratings_images .= '<img src="'.TEMPLATE_RATING.'/images/rating_off.'.RATINGS_IMG_EXT.'" alt="'.$image_alt.'" title="'.$image_alt.'" class="tax-ratings-image" />';
			}
		}
	}

	return $ratings_images;
}


### Function: Gets HTML of rating images for voting
function get_ratings_images_vote($tax_id, $ratings_custom, $ratings_max, $tax_rating, $image_alt, $insert_half, $ratings_texts) {
	$ratings_images = '';
	
	if($ratings_custom) {
		for($i=1; $i <= $ratings_max; $i++) {
			$ratings_text = stripslashes($ratings_texts[$i-1]);
			if($i <= $tax_rating) {
				$ratings_images .= '<img id="rating_'.$tax_id.'_'.$i.'" src="'.TEMPLATE_RATING.'/images/rating_'.$i.'_on.'.RATINGS_IMG_EXT.'" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$tax_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$tax_rating.', '.$insert_half.');" onclick="rate_tax();" onkeypress="rate_tax();" style="cursor: pointer; border: 0px;" />';    
			} elseif($i == $insert_half) {
				$ratings_images .= '<img id="rating_'.$tax_id.'_'.$i.'" src="'.TEMPLATE_RATING.'/images/rating_'.$i.'_half.'.RATINGS_IMG_EXT.'" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$tax_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$tax_rating.', '.$insert_half.');" onclick="rate_tax();" onkeypress="rate_tax();" style="cursor: pointer; border: 0px;" />';
			} else {
				$ratings_images .= '<img id="rating_'.$tax_id.'_'.$i.'" src="'.TEMPLATE_RATING.'/images/rating_'.$i.'_off.'.RATINGS_IMG_EXT.'" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$tax_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$tax_rating.', '.$insert_half.');" onclick="rate_tax();" onkeypress="rate_tax();" style="cursor: pointer; border: 0px;" />';
			}
		}
	} else {
		for($i=1; $i <= $ratings_max; $i++) {
			$ratings_text = stripslashes($ratings_texts[$i-1]);
			if($i <= $tax_rating) {
				$ratings_images .= '<img id="rating_'.$tax_id.'_'.$i.'" src="'.TEMPLATE_RATING.'/images/rating_on.'.RATINGS_IMG_EXT.'" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$tax_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$tax_rating.', '.$insert_half.');" onclick="rate_tax();" onkeypress="rate_tax();" style="cursor: pointer; border: 0px;" />';    
			} elseif($i == $insert_half) {
				$ratings_images .= '<img id="rating_'.$tax_id.'_'.$i.'" src="'.TEMPLATE_RATING.'/images/rating_half.'.RATINGS_IMG_EXT.'" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$tax_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$tax_rating.', '.$insert_half.');" onclick="rate_tax();" onkeypress="rate_tax();" style="cursor: pointer; border: 0px;" />';
			} else {
				$ratings_images .= '<img id="rating_'.$tax_id.'_'.$i.'" src="'.TEMPLATE_RATING.'/images/rating_off.'.RATINGS_IMG_EXT.'" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$tax_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$tax_rating.', '.$insert_half.');" onclick="rate_tax();" onkeypress="rate_tax();" style="cursor: pointer; border: 0px;" />';
			}
		}
	}

	return $ratings_images;
}

### Function: Replaces the template's variables with appropriate values
function expand_ratings_template($template, $tax_id, $tax_ratings_data = null, $max_tax_title_chars = 0) {
	global $tax, $wpdb, $aRatingTexts;
	$temp_tax = $tax;
	// Get global variables
	$ratings_max = intval(MAX_RATING);
	$ratings_custom = intval(CUSTOM_RATING);
	// Get tax related variables
	if(is_null($tax_ratings_data)) {
		$tax_ratings_users = intval($wpdb->get_var("SELECT COUNT(rating_id) FROM $wpdb->ratings WHERE rating_taxid = $tax_id"));
		$tax_ratings_score = intval($wpdb->get_var("SELECT SUM(rating_rating) AS ratings_score FROM $wpdb->ratings WHERE rating_taxid = $tax_id"));	
		$tax_ratings_average = floatval($wpdb->get_var("SELECT AVG(rating_rating) AS ratings_average FROM $wpdb->ratings WHERE rating_taxid = $tax_id"));
	} else {
		$tax_ratings_users = intval($tax_ratings_data->ratings_users);
		$tax_ratings_score = intval($tax_ratings_data->ratings_score);
		$tax_ratings_average = floatval($tax_ratings_data->ratings_average);
	}
	if($tax_ratings_score == 0 || $tax_ratings_users == 0) {
		$tax_ratings = 0;
		$tax_ratings_average = 0;
		$tax_ratings_percentage = 0;
	} else {
		$tax_ratings = round($tax_ratings_average, 1);
		$tax_ratings_percentage = round((($tax_ratings_score/$tax_ratings_users)/$ratings_max) * 100, 2);    
	}
	$tax_ratings_text = '<span class="tax-ratings-text" id="ratings_'.$tax_id.'_text"></span>';
	// Get the image's alt text
	if($ratings_custom && $ratings_max == 2) {
		if($tax_ratings_score > 0) {
			$tax_ratings_score = '+'.$tax_ratings_score;
		}
		$tax_ratings_alt_text = sprintf(_n('%s vote', '%s votes', $tax_ratings_users, 'wp-taxratings'), number_format_i18n($tax_ratings_users));
	} else {
		$tax_ratings_score = number_format_i18n($tax_ratings_score);
		$tax_ratings_alt_text = sprintf(_n('%s vote', '%s votes', $tax_ratings_users, 'wp-taxratings'), number_format_i18n($tax_ratings_users));
	}
	// Check for half star
	$insert_half = 0;
	$average_diff = abs(floor($tax_ratings_average)-$tax_ratings);
	if($average_diff >= 0.25 && $average_diff <= 0.75) {
		$insert_half = ceil($tax_ratings_average);
	} elseif($average_diff > 0.75) {
		$insert_half = ceil($tax_ratings);
	}  
	// Replace the variables
	$value = $template;
	if (strpos($template, '%RATINGS_IMAGES%') !== false) {
		$tax_ratings_images = get_ratings_images($ratings_custom, $ratings_max, $tax_ratings, $tax_ratings_alt_text, $insert_half);
		$value = str_replace("%RATINGS_IMAGES%", $tax_ratings_images, $value);
	}
	if (strpos($template, '%RATINGS_IMAGES_VOTE%') !== false) {
		$ratings_texts = $aRatingTexts;
		$tax_ratings_images = get_ratings_images_vote($tax_id, $ratings_custom, $ratings_max, $tax_ratings, $tax_ratings_alt_text, $insert_half, $ratings_texts);
		$value = str_replace("%RATINGS_IMAGES_VOTE%", $tax_ratings_images, $value);
	}
	$value = str_replace("%RATINGS_ALT_TEXT%", $tax_ratings_alt_text, $value);
	$value = str_replace("%RATINGS_TEXT%", $tax_ratings_text, $value);
	$value = str_replace("%RATINGS_MAX%", number_format_i18n($ratings_max), $value);
	$value = str_replace("%RATINGS_SCORE%", $tax_ratings_score, $value);
	$value = str_replace("%RATINGS_AVERAGE%", number_format_i18n($tax_ratings_average, 2), $value);
	$value = str_replace("%RATINGS_PERCENTAGE%", number_format_i18n($tax_ratings_percentage, 2), $value);
	$value = str_replace("%RATINGS_USERS%", number_format_i18n($tax_ratings_users), $value);
	
	// Return value
	$tax = $temp_tax;
	return apply_filters('expand_ratings_template', htmlspecialchars_decode($value));
}


### Function: Create Rating Logs Table
add_action('activate_wp-taxratings/wp-taxratings.php', 'create_ratinglogs_table');
function create_ratinglogs_table() {
	global $wpdb;
	taxratings_textdomain();
	if(@is_file(ABSPATH.'/wp-admin/upgrade-functions.php')) {
		include_once(ABSPATH.'/wp-admin/upgrade-functions.php');
	} elseif(@is_file(ABSPATH.'/wp-admin/includes/upgrade.php')) {
		include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
	} else {
		die('We have problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'');
	}
	$charset_collate = '';
	if($wpdb->supports_collation()) {
		if(!empty($wpdb->charset)) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if(!empty($wpdb->collate)) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}
	}
	// Create Post Ratings Table
	$create_ratinglogs_sql = "CREATE TABLE $wpdb->ratings (".
			"rating_id INT(11) NOT NULL auto_increment,".
			"rating_taxid INT(11) NOT NULL ,".
			"rating_taxtitle TEXT NOT NULL,".
			"rating_rating INT(2) NOT NULL ,".
			"rating_timestamp VARCHAR(15) NOT NULL ,".
			"rating_ip VARCHAR(40) NOT NULL ,".
			"rating_host VARCHAR(200) NOT NULL,".
			"rating_username VARCHAR(50) NOT NULL,".
			"rating_userid int(10) NOT NULL default '0',".
			"PRIMARY KEY (rating_id)) $charset_collate;";
	maybe_create_table($wpdb->ratings, $create_ratinglogs_sql);
	
	// Set 'manage_ratings' Capabilities To Administrator	
	$role = get_role('administrator');
	if(!$role->has_cap('manage_ratings')) {
		$role->add_cap('manage_ratings');
	}
}

### Seperate TaxRatings Stats For Readability
//require_once('taxratings-stats.php');
?>
