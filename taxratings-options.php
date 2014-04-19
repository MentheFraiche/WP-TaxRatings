<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress Plugin: WP-TaxRatings 1.00								|
|	Copyright (c) 2012 Aurélien Capdecomme									|
|																							|
|	File Written By:																	|
|	- Aurélien Capdecomme															|
|	- http://menthefraiche.com															|
|																							|
|	File Information:																	|
|	- Configure Taxonomy Ratings Options												|
|	- wp-content/plugins/wp-taxratings/taxratings-options.php		|
|																							|
+----------------------------------------------------------------+
*/


### Check Whether User Can Manage Ratings
if(!current_user_can('manage_ratings')) {
	die('Access Denied');
}


### Ratings Variables
$base_name = plugin_basename('wp-taxratings/taxratings-manager.php');
$base_page = 'admin.php?page='.$base_name;


### If Form Is Submitted
if($_POST['Submit']) {
	$taxratings_customrating = intval($_POST['taxratings_customrating']);
	$taxratings_template_vote = trim($_POST['taxratings_template_vote']);
	$taxratings_template_text = trim($_POST['taxratings_template_text']);
	$taxratings_template_permission = trim($_POST['taxratings_template_permission']);
	$taxratings_template_none = trim($_POST['taxratings_template_none']);
	$taxratings_image = strip_tags(trim($_POST['taxratings_image']));
	$taxratings_max = intval($_POST['taxratings_max']);
	$taxratings_ratingstext_array = $_POST['taxratings_ratingstext'];
	$taxratings_ratingstext = array();
	foreach($taxratings_ratingstext_array as $ratingstext) {
		$taxratings_ratingstext[] = trim($ratingstext);
	}
	$taxratings_ratingsvalue_array = $_POST['taxratings_ratingsvalue'];
	$taxratings_ratingsvalue = array();
	foreach($taxratings_ratingsvalue_array as $ratingsvalue) {
		$taxratings_ratingsvalue[] =intval($ratingsvalue);
	}
	$taxratings_ajax_style = array('loading' => intval($_POST['taxratings_ajax_style_loading']), 'fading' => intval($_POST['taxratings_ajax_style_fading']));
	$taxratings_logging_method = intval($_POST['taxratings_logging_method']);
	$taxratings_allowtorate = intval($_POST['taxratings_allowtorate']);
	$update_ratings_queries = array();
	$update_ratings_text = array();
	$update_ratings_queries[] = update_option('taxratings_customrating', $taxratings_customrating);
	$update_ratings_queries[] = update_option('taxratings_template_vote', $taxratings_template_vote);
	$update_ratings_queries[] = update_option('taxratings_template_text', $taxratings_template_text);
	$update_ratings_queries[] = update_option('taxratings_template_permission', $taxratings_template_permission);
	$update_ratings_queries[] = update_option('taxratings_template_none', $taxratings_template_none);
	$update_ratings_queries[] = update_option('taxratings_image', $taxratings_image);
	$update_ratings_queries[] = update_option('taxratings_max', $taxratings_max);
	$update_ratings_queries[] = update_option('taxratings_ratingstext', $taxratings_ratingstext);
	$update_ratings_queries[] = update_option('taxratings_ratingsvalue', $taxratings_ratingsvalue);
	$update_ratings_queries[] = update_option('taxratings_ajax_style', $taxratings_ajax_style);
	$update_ratings_queries[] = update_option('taxratings_logging_method', $taxratings_logging_method);
	$update_ratings_queries[] = update_option('taxratings_allowtorate', $taxratings_allowtorate);
	$update_ratings_text[] = __('Custom Rating', 'wp-taxratings');
	$update_ratings_text[] = __('Ratings Template Vote', 'wp-taxratings');
	$update_ratings_text[] = __('Ratings Template Voted', 'wp-taxratings');
	$update_ratings_text[] = __('Ratings Template No Permission', 'wp-taxratings');
	$update_ratings_text[] = __('Ratings Template For No Ratings', 'wp-taxratings');
	$update_ratings_text[] = __('Ratings Image', 'wp-taxratings');
	$update_ratings_text[] = __('Max Ratings', 'wp-taxratings');
	$update_ratings_text[] = __('Individual Rating Text', 'wp-taxratings');
	$update_ratings_text[] = __('Individual Rating Value', 'wp-taxratings');
	$update_ratings_text[] = __('Ratings AJAX Style', 'wp-taxratings');
	$update_ratings_text[] = __('Logging Method', 'wp-taxratings');
	$update_ratings_text[] = __('Allow To Vote Option', 'wp-taxratings');
	$i = 0;
	$text = '';
	foreach($update_ratings_queries as $update_ratings_query) {
		if($update_ratings_query) {
			$text .= '<font color="green">'.$update_ratings_text[$i].' '.__('Updated', 'wp-taxratings').'</font><br />';
		}
		$i++;
	}
	if(empty($text)) {
		$text = '<font color="red">'.__('No Ratings Option Updated', 'wp-taxratings').'</font>';
	}
}


### Needed Variables
$taxratings_max = intval(get_option('taxratings_max'));
$taxratings_customrating = intval(get_option('taxratings_customrating'));
$taxratings_url = plugins_url('wp-taxratings/images');
$taxratings_path = WP_PLUGIN_DIR.'/wp-taxratings/images';
$taxratings_ratingstext = get_option('taxratings_ratingstext');
$taxratings_ratingsvalue = get_option('taxratings_ratingsvalue');
$taxratings_image = get_option('taxratings_image');
?>
<script type="text/javascript">
/* <![CDATA[*/
	function ratings_updown_templates(template, print) {
		var default_template;
		switch(template) {
			case "vote":
				default_template = "%RATINGS_IMAGES_VOTE% (<strong>%RATINGS_SCORE%</strong> <?php _e('rating', 'wp-taxratings'); ?><?php _e(',', 'wp-taxratings'); ?> <strong>%RATINGS_USERS%</strong> <?php _e('votes', 'wp-taxratings'); ?>)<br />%RATINGS_TEXT%";
				break;
			case "text":
				default_template = "%RATINGS_IMAGES% (<em><strong>%RATINGS_SCORE%</strong> <?php _e('rating', 'wp-taxratings'); ?><?php _e(',', 'wp-taxratings'); ?> <strong>%RATINGS_USERS%</strong> <?php _e('votes', 'wp-taxratings'); ?><?php _e(',', 'wp-taxratings'); ?> <strong><?php _e('rated', 'wp-taxratings'); ?></strong></em>)";
				break;
			case "permission":
				default_template = "%RATINGS_IMAGES% (<em><strong>%RATINGS_SCORE%</strong> <?php _e('rating', 'wp-taxratings'); ?><?php _e(',', 'wp-taxratings'); ?> <strong>%RATINGS_USERS%</strong> <?php _e('votes', 'wp-taxratings'); ?><?php _e(',', 'wp-taxratings'); ?> <strong><?php _e('rated', 'wp-taxratings'); ?></strong></em>)<br /><em><?php _e('You need to be a registered member to rate this tax.', 'wp-taxratings'); ?></em>";
				break;
			case "none":
				default_template = "%RATINGS_IMAGES_VOTE% (<?php _e('No Ratings Yet', 'wp-taxratings'); ?>)<br />%RATINGS_TEXT%";
				break;
		}
		if(print) {
			jQuery("#taxratings_template_" + template).val(default_template);
		} else {
			return default_template;
		}
	}
	function ratings_default_templates(template, print) {
		var default_template;
		switch(template) {
			case "vote":
				default_template = "%RATINGS_IMAGES_VOTE% (<strong>%RATINGS_USERS%</strong> <?php _e('votes', 'wp-taxratings'); ?><?php _e(',', 'wp-taxratings'); ?> <?php _e('average', 'wp-taxratings'); ?>: <strong>%RATINGS_AVERAGE%</strong> <?php _e('out of', 'wp-taxratings'); ?> %RATINGS_MAX%)<br />%RATINGS_TEXT%";
				break;
			case "text":
				default_template = "%RATINGS_IMAGES% (<em><strong>%RATINGS_USERS%</strong> <?php _e('votes', 'wp-taxratings'); ?><?php _e(',', 'wp-taxratings'); ?> <?php _e('average', 'wp-taxratings'); ?>: <strong>%RATINGS_AVERAGE%</strong> <?php _e('out of', 'wp-taxratings'); ?> %RATINGS_MAX%<?php _e(',', 'wp-taxratings'); ?> <strong><?php _e('rated', 'wp-taxratings'); ?></strong></em>)";
				break;
			case "permission":
				default_template = "%RATINGS_IMAGES% (<em><strong>%RATINGS_USERS%</strong> <?php _e('votes', 'wp-taxratings'); ?><?php _e(',', 'wp-taxratings'); ?> <?php _e('average', 'wp-taxratings'); ?>: <strong>%RATINGS_AVERAGE%</strong> <?php _e('out of', 'wp-taxratings'); ?> %RATINGS_MAX%</em>)<br /><em><?php _e('You need to be a registered member to rate this tax.', 'wp-taxratings'); ?></em>";
				break;
			case "none":
				default_template = "%RATINGS_IMAGES_VOTE% (<?php _e('No Ratings Yet', 'wp-taxratings'); ?>)<br />%RATINGS_TEXT%";
				break;
		}
		if(print) {
			jQuery("#taxratings_template_" + template).val(default_template);
		} else {
			return default_template;
		}
	}
	function set_custom(custom, max) {
		if(custom == 1) {
			jQuery("#taxratings_max").val(max);
			jQuery("#taxratings_max").attr("readonly", true);
			if(max == 2) {
				jQuery("#taxratings_template_vote").val(ratings_updown_templates("vote", false));
				jQuery("#taxratings_template_text").val(ratings_updown_templates("text", false));
				jQuery("#taxratings_template_permission").val(ratings_updown_templates("permission", false));
				jQuery("#taxratings_template_none").val(ratings_updown_templates("none", false));
			} else {
				jQuery("#taxratings_template_vote").val(ratings_default_templates("vote", false));
				jQuery("#taxratings_template_text").val(ratings_default_templates("text", false));
				jQuery("#taxratings_template_none").val(ratings_default_templates("none", false));
			}
		} else {
			jQuery("#taxratings_max").val(<?php echo $taxratings_max; ?>);
			jQuery("#taxratings_max").attr("readonly", false);
			jQuery("#taxratings_template_vote").val(ratings_default_templates("vote", false));
			jQuery("#taxratings_template_text").val(ratings_default_templates("text", false));
			jQuery("#taxratings_template_permission").val(ratings_default_templates("permission", false));
			jQuery("#taxratings_template_none").val(ratings_default_templates("none", false));
		}
		jQuery("#taxratings_customrating").val(custom);
	}
/* ]]> */
</script>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<div class="wrap">
	<div id="icon-wp-taxratings" class="icon32"><br /></div>
	<h2><?php _e('Post Ratings Options', 'wp-taxratings'); ?></h2> 
	<form method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>"> 
		<input type="hidden" id="taxratings_customrating" name="taxratings_customrating" value="<?php echo $taxratings_customrating; ?>" />
		<input type="hidden" id="taxratings_template_vote" name="taxratings_template_vote" value="<?php echo htmlspecialchars(stripslashes(get_option('taxratings_template_vote'))); ?>" />
		<input type="hidden" id="taxratings_template_text" name="taxratings_template_text" value="<?php echo htmlspecialchars(stripslashes(get_option('taxratings_template_text'))); ?>" />
		<input type="hidden" id="taxratings_template_permission" name="taxratings_template_permission" value="<?php echo htmlspecialchars(stripslashes(get_option('taxratings_template_permission'))); ?>" />
		<input type="hidden" id="taxratings_template_none" name="taxratings_template_none" value="<?php echo htmlspecialchars(stripslashes(get_option('taxratings_template_none'))); ?>" />
		<h3><?php _e('Ratings Settings', 'wp-taxratings'); ?></h3>
		<table class="form-table">
			 <tr>
				<th scope="row" valign="top"><?php _e('Ratings Image:', 'wp-taxratings'); ?></th>
				<td>
					<?php
						$taxratings_images_array = array();
						if($handle = @opendir($taxratings_path)) {     
							while (false !== ($filename = readdir($handle))) {  
								if ($filename != '.' && $filename != '..' && strpos($filename, '.') !== 0) {
									if(is_dir($taxratings_path.'/'.$filename)) {
										$taxratings_images_array[$filename] = ratings_images_folder($filename);
									}
								} 
							} 
							closedir($handle);
						}
						foreach($taxratings_images_array as $key => $value) {
							if(strpos($value['images'][0], '.'.RATINGS_IMG_EXT) === false) {
								continue;
							}
							echo '<p>';
							if($value['custom'] == 0) {
								if($taxratings_image == $key) {
									echo '<input type="radio" name="taxratings_image" onclick="set_custom('.$value['custom'].', '.$value['max'].');" value="'.$key.'" checked="checked" />';
								} else {
									echo '<input type="radio" name="taxratings_image" onclick="set_custom('.$value['custom'].', '.$value['max'].');" value="'.$key.'" />';
								}
								echo '&nbsp;&nbsp;&nbsp;';
								if('rtl' == $text_direction && file_exists($taxratings_path.'/'.$key.'/rating_start-rtl.'.RATINGS_IMG_EXT)) {
									echo '<img src="'.$taxratings_url.'/'.$key.'/rating_start-rtl.'.RATINGS_IMG_EXT.'" alt="rating_start-rtl.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
								} else if(file_exists($taxratings_path.'/'.$key.'/rating_start.'.RATINGS_IMG_EXT)) {
									echo '<img src="'.$taxratings_url.'/'.$key.'/rating_start.'.RATINGS_IMG_EXT.'" alt="rating_start.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
								}
								echo '<img src="'.$taxratings_url.'/'.$key.'/rating_over.'.RATINGS_IMG_EXT.'" alt="rating_over.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
								echo '<img src="'.$taxratings_url.'/'.$key.'/rating_on.'.RATINGS_IMG_EXT.'" alt="rating_on.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
								echo '<img src="'.$taxratings_url.'/'.$key.'/rating_on.'.RATINGS_IMG_EXT.'" alt="rating_on.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
								if('rtl' == $text_direction && file_exists($taxratings_path.'/'.$key.'/rating_half-rtl.'.RATINGS_IMG_EXT)) {
									echo '<img src="'.$taxratings_url.'/'.$key.'/rating_half-rtl.'.RATINGS_IMG_EXT.'" alt="rating_half-rtl.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
								} else {
									echo '<img src="'.$taxratings_url.'/'.$key.'/rating_half.'.RATINGS_IMG_EXT.'" alt="rating_half.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
								}
								echo '<img src="'.$taxratings_url.'/'.$key.'/rating_off.'.RATINGS_IMG_EXT.'" alt="rating_off.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
							} else {
								if($taxratings_image == $key) {
									echo '<input type="radio" name="taxratings_image" onclick="set_custom('.$value['custom'].', '.$value['max'].');" value="'.$key.'" checked="checked" />';
								} else {
									echo '<input type="radio" name="taxratings_image" onclick="set_custom('.$value['custom'].', '.$value['max'].');" value="'.$key.'" />';
								}
								echo '&nbsp;&nbsp;&nbsp;';
								if('rtl' == $text_direction && file_exists($taxratings_path.'/'.$key.'/rating_start-rtl.'.RATINGS_IMG_EXT)) {
									echo '<img src="'.$taxratings_url.'/'.$key.'/rating_start-rtl.'.RATINGS_IMG_EXT.'" alt="rating_start-rtl.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
								} elseif(file_exists($taxratings_path.'/'.$key.'/rating_start.'.RATINGS_IMG_EXT)) {
									echo '<img src="'.$taxratings_url.'/'.$key.'/rating_start.'.RATINGS_IMG_EXT.'" alt="rating_start.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
								}
								for($i = 1; $i <= $value['max']; $i++) {
										if(file_exists($taxratings_path.'/'.$key.'/rating_'.$i.'_off.'.RATINGS_IMG_EXT)) {
											echo '<img src="'.$taxratings_url.'/'.$key.'/rating_'.$i.'_off.'.RATINGS_IMG_EXT.'" alt="rating_'.$i.'_off.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
										}
								}
							}
							if('rtl' == $text_direction && file_exists($taxratings_path.'/'.$key.'/rating_end-rtl.'.RATINGS_IMG_EXT)) {
								echo '<img src="'.$taxratings_url.'/'.$key.'/rating_end-rtl.'.RATINGS_IMG_EXT.'" alt="rating_end-rtl.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
							} elseif(file_exists($taxratings_path.'/'.$key.'/rating_end.'.RATINGS_IMG_EXT)) {
								echo '<img src="'.$taxratings_url.'/'.$key.'/rating_end.'.RATINGS_IMG_EXT.'" alt="rating_end.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
							}
							echo '&nbsp;&nbsp;&nbsp;('.$key.')';
							echo '</p>'."\n";
						}
					?>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><?php _e('Max Ratings:', 'wp-taxratings'); ?></th>
				<td><input type="text" id="taxratings_max" name="taxratings_max" value="<?php echo $taxratings_max; ?>" size="3" <?php if($taxratings_customrating) { echo 'readonly="readonly"'; } ?> /></td>
			</tr>
			<tr>
				<td colspan="2" align="center"><input type="button" name="update" value="<?php _e('Update \'Individual Rating Text/Value\' Display', 'wp-taxratings'); ?>" onclick="update_rating_text_value();" class="button" /><br /><img id="taxratings_loading" src="<?php echo $taxratings_url; ?>/loading.gif" alt="" style="display: none;" /></td>
			</tr>
		</table>
		<h3><?php _e('Individual Rating Text/Value', 'wp-taxratings'); ?></h3>
		<div id="rating_text_value">
			<table class="form-table">
				<thead>
					<tr>
						<th><?php _e('Rating Image', 'wp-taxratings'); ?></th>
						<th><?php _e('Rating Text', 'wp-taxratings'); ?></th>
						<th><?php _e('Rating Value', 'wp-taxratings'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
						for($i = 1; $i <= $taxratings_max; $i++) {
							echo '<tr>'."\n";
							echo '<td>'."\n";
							if('rtl' == $text_direction && file_exists($taxratings_path.'/'.$taxratings_image.'/rating_start-rtl.'.RATINGS_IMG_EXT)) {
								echo '<img src="'.$taxratings_url.'/'.$taxratings_image.'/rating_start-rtl.'.RATINGS_IMG_EXT.'" alt="rating_start-rtl.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
							} elseif(file_exists($taxratings_path.'/'.$taxratings_image.'/rating_start.'.RATINGS_IMG_EXT)) {
								echo '<img src="'.$taxratings_url.'/'.$taxratings_image.'/rating_start.'.RATINGS_IMG_EXT.'" alt="rating_start.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
							}
							if($taxratings_customrating) {
								if($taxratings_max == 2) {
									echo '<img src="'.$taxratings_url.'/'.$taxratings_image.'/rating_'.$i.'_on.'.RATINGS_IMG_EXT.'" alt="rating_'.$i.'_on.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
								} else {
									for($j = 1; $j < ($i+1); $j++) {
										echo '<img src="'.$taxratings_url.'/'.$taxratings_image.'/rating_'.$j.'_on.'.RATINGS_IMG_EXT.'" alt="rating_on.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
									}
								}
							} else {
								for($j = 1; $j < ($i+1); $j++) {
									echo '<img src="'.$taxratings_url.'/'.$taxratings_image.'/rating_on.'.RATINGS_IMG_EXT.'" alt="rating_on.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
								}
							}
							if('rtl' == $text_direction && file_exists($taxratings_path.'/'.$taxratings_image.'/rating_end-rtl.'.RATINGS_IMG_EXT)) {
								echo '<img src="'.$taxratings_url.'/'.$taxratings_image.'/rating_end-rtl.'.RATINGS_IMG_EXT.'" alt="rating_end-rtl.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
							} elseif(file_exists($taxratings_path.'/'.$taxratings_image.'/rating_end.'.RATINGS_IMG_EXT)) {
								echo '<img src="'.$taxratings_url.'/'.$taxratings_image.'/rating_end.'.RATINGS_IMG_EXT.'" alt="rating_end.'.RATINGS_IMG_EXT.'" class="tax-ratings-image" />';
							}
							echo '</td>'."\n";
							echo '<td>'."\n";
							echo '<input type="text" id="taxratings_ratingstext_'.$i.'" name="taxratings_ratingstext[]" value="'.stripslashes($taxratings_ratingstext[$i-1]).'" size="20" maxlength="50" />'."\n";
							echo '</td>'."\n";
							echo '<td>'."\n";
							echo '<input type="text" id="taxratings_ratingsvalue_'.$i.'" name="taxratings_ratingsvalue[]" value="';
							if($taxratings_ratingsvalue[$i-1] > 0 && $taxratings_customrating) {
								echo '+';
							}
							echo $taxratings_ratingsvalue[$i-1].'" size="3" maxlength="5" />'."\n";
							echo '</td>'."\n";
							echo '</tr>'."\n";
						}								
					?>
				</tbody>
			</table>
		</div>
		<?php $taxratings_ajax_style = get_option('taxratings_ajax_style'); ?>
		<h3><?php _e('Ratings AJAX Style', 'wp-taxratings'); ?></h3>
		<table class="form-table">
			 <tr>
				<th scope="row" valign="top"><?php _e('Show Loading Image With Text', 'wp-taxratings'); ?></th>
				<td>
					<select name="taxratings_ajax_style_loading" size="1">
						<option value="0"<?php selected('0', $taxratings_ajax_style['loading']); ?>><?php _e('No', 'wp-taxratings'); ?></option>
						<option value="1"<?php selected('1', $taxratings_ajax_style['loading']); ?>><?php _e('Yes', 'wp-taxratings'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><?php _e('Show Fading In And Fading Out Of Ratings', 'wp-taxratings'); ?></th>
				<td>
					<select name="taxratings_ajax_style_fading" size="1">
						<option value="0"<?php selected('0', $taxratings_ajax_style['fading']); ?>><?php _e('No', 'wp-taxratings'); ?></option>
						<option value="1"<?php selected('1', $taxratings_ajax_style['fading']); ?>><?php _e('Yes', 'wp-taxratings'); ?></option>
					</select>
				</td> 
			</tr>
		</table>
		<h3><?php _e('Allow To Rate', 'wp-taxratings'); ?></h3>
		<table class="form-table">
			 <tr>
				<th scope="row" valign="top"><?php _e('Who Is Allowed To Rate?', 'wp-taxratings'); ?></th>
				<td>
					<select name="taxratings_allowtorate" size="1">
						<option value="0"<?php selected('0', get_option('taxratings_allowtorate')); ?>><?php _e('Guests Only', 'wp-taxratings'); ?></option>
						<option value="1"<?php selected('1', get_option('taxratings_allowtorate')); ?>><?php _e('Registered Users Only', 'wp-taxratings'); ?></option>
						<option value="2"<?php selected('2', get_option('taxratings_allowtorate')); ?>><?php _e('Registered Users And Guests', 'wp-taxratings'); ?></option>
					</select>
				</td>
			</tr>
		</table>
		<h3><?php _e('Logging Method', 'wp-taxratings'); ?></h3>
		<table class="form-table">
			 <tr>
				<th scope="row" valign="top"><?php _e('Ratings Logging Method:', 'wp-taxratings'); ?></th>
				<td>
					<select name="taxratings_logging_method" size="1">
						<option value="0"<?php selected('0', get_option('taxratings_logging_method')); ?>><?php _e('Do Not Log', 'wp-taxratings'); ?></option>
						<option value="1"<?php selected('1', get_option('taxratings_logging_method')); ?>><?php _e('Logged By Cookie', 'wp-taxratings'); ?></option>
						<option value="2"<?php selected('2', get_option('taxratings_logging_method')); ?>><?php _e('Logged By IP', 'wp-taxratings'); ?></option>
						<option value="3"<?php selected('3', get_option('taxratings_logging_method')); ?>><?php _e('Logged By Cookie And IP', 'wp-taxratings'); ?></option>
						<option value="4"<?php selected('4', get_option('taxratings_logging_method')); ?>><?php _e('Logged By Username', 'wp-taxratings'); ?></option>
					</select>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes', 'wp-taxratings'); ?>" />
		</p>
	</form>
</div>