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
	$taxratings_template_vote = trim($_POST['taxratings_template_vote']);
	$taxratings_template_text = trim($_POST['taxratings_template_text']);
	$taxratings_template_permission = trim($_POST['taxratings_template_permission']);
	$taxratings_template_none = trim($_POST['taxratings_template_none']);
	$update_ratings_queries = array();
	$update_ratings_text = array();
	$update_ratings_queries[] = update_option('taxratings_template_vote', $taxratings_template_vote);
	$update_ratings_queries[] = update_option('taxratings_template_text', $taxratings_template_text);
	$update_ratings_queries[] = update_option('taxratings_template_permission', $taxratings_template_permission);
	$update_ratings_queries[] = update_option('taxratings_template_none', $taxratings_template_none);
	$update_ratings_text[] = __('Ratings Template Vote', 'wp-taxratings');
	$update_ratings_text[] = __('Ratings Template Voted', 'wp-taxratings');
	$update_ratings_text[] = __('Ratings Template No Permission', 'wp-taxratings');
	$update_ratings_text[] = __('Ratings Template For No Ratings', 'wp-taxratings');
	$i = 0;
	$text = '';
	foreach($update_ratings_queries as $update_ratings_query) {
		if($update_ratings_query) {
			$text .= '<font color="green">'.$update_ratings_text[$i].' '.__('Updated', 'wp-taxratings').'</font><br />';
		}
		$i++;
	}
	if(empty($text)) {
		$text = '<font color="red">'.__('No Ratings Templates Updated', 'wp-taxratings').'</font>';
	}
}
?>
<script language="JavaScript" type="text/javascript">
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
/* ]]> */
</script>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<div class="wrap">
	<div id="icon-wp-taxratings" class="icon32"><br /></div>
	<h2><?php _e('Post Ratings Templates', 'wp-taxratings'); ?></h2> 
	<form method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>">
		<h3><?php _e('Template Variables', 'wp-taxratings'); ?></h3>
		<table class="form-table">
			<tr>
				<td><strong>%RATINGS_IMAGES%</strong> - <?php _e('Display the ratings images', 'wp-taxratings'); ?></td>
				<td><strong>%RATINGS_IMAGES_VOTE%</strong> - <?php _e('Display the ratings voting image', 'wp-taxratings'); ?></td>
			</tr>
			<tr>
				<td><strong>%RATINGS_AVERAGE%</strong> - <?php _e('Display the average ratings', 'wp-taxratings'); ?></td>
				<td><strong>%RATINGS_USERS%</strong> - <?php _e('Display the total number of users rated for the tax', 'wp-taxratings'); ?></td>						
			</tr>
			<tr>
				<td><strong>%RATINGS_MAX%</strong> - <?php _e('Display the max number of ratings', 'wp-taxratings'); ?></td>
				<td><strong>%RATINGS_PERCENTAGE%</strong> - <?php _e('Display the ratings percentage', 'wp-taxratings'); ?></td>
			</tr>
			<tr>
				<td><strong>%RATINGS_SCORE%</strong> - <?php _e('Display the total score of the ratings', 'wp-taxratings'); ?></td>
				<td><strong>%RATINGS_TEXT%</strong> - <?php _e('Display the individual rating text. Eg: 1 Star, 2 Stars, etc', 'wp-taxratings'); ?></td>
			</tr>
		</table>
		<h3><?php _e('Ratings Templates', 'wp-taxratings'); ?></h3>
		<table class="form-table">
			 <tr>
				<td width="30%">
					<strong><?php _e('Ratings Vote Text:', 'wp-taxratings'); ?></strong><br /><br />
					<?php _e('Allowed Variables:', 'wp-taxratings'); ?>
					<p style="margin: 2px 0">- %RATINGS_IMAGES_VOTE%</p>
					<p style="margin: 2px 0">- %RATINGS_MAX%</p>
					<p style="margin: 2px 0">- %RATINGS_SCORE%</p>
					<p style="margin: 2px 0">- %RATINGS_TEXT%</p>
					<p style="margin: 2px 0">- %RATINGS_USERS%</p>
					<p style="margin: 2px 0">- %RATINGS_AVERAGE%</p>
					<p style="margin: 2px 0">- %RATINGS_PERCENTAGE%</p>
					<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template (Normal Rating)', 'wp-taxratings'); ?>" onclick="ratings_default_templates('vote', true);" class="button" />
					<br />
					<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template (Up/Down Rating)', 'wp-taxratings'); ?>" onclick="ratings_updown_templates('vote', true);" class="button" />
				</td>
				<td><textarea cols="80" rows="15" id="taxratings_template_vote" name="taxratings_template_vote"><?php echo htmlspecialchars(stripslashes(get_option('taxratings_template_vote'))); ?></textarea></td>
			</tr>
			<tr>
				<td width="30%">
					<strong><?php _e('Ratings Voted Text:', 'wp-taxratings'); ?></strong><br /><br />
					<?php _e('Allowed Variables:', 'wp-taxratings'); ?>
          <p style="margin: 2px 0">- %RATINGS_IMAGES%</p>
          <p style="margin: 2px 0">- %RATINGS_MAX%</p>
          <p style="margin: 2px 0">- %RATINGS_SCORE%</p>
          <p style="margin: 2px 0">- %RATINGS_USERS%</p>
          <p style="margin: 2px 0">- %RATINGS_AVERAGE%</p>
          <p style="margin: 2px 0">- %RATINGS_PERCENTAGE%</p>
					<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template (Normal Rating)', 'wp-taxratings'); ?>" onclick="ratings_default_templates('text', true);" class="button" /><br />
					<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template (Up/Down Rating)', 'wp-taxratings'); ?>" onclick="ratings_updown_templates('text', true);" class="button" />
				</td>
				<td><textarea cols="80" rows="15" id="taxratings_template_text" name="taxratings_template_text"><?php echo htmlspecialchars(stripslashes(get_option('taxratings_template_text'))); ?></textarea></td>
			</tr>
			<tr>
				<td width="30%">
					<strong><?php _e('Ratings No Permission Text:', 'wp-taxratings'); ?></strong><br /><br />
					<?php _e('Allowed Variables:', 'wp-taxratings'); ?>
          <p style="margin: 2px 0">- %RATINGS_IMAGES%</p>
          <p style="margin: 2px 0">- %RATINGS_MAX%</p>
          <p style="margin: 2px 0">- %RATINGS_SCORE%</p>
          <p style="margin: 2px 0">- %RATINGS_USERS%</p>
          <p style="margin: 2px 0">- %RATINGS_AVERAGE%</p>
          <p style="margin: 2px 0">- %RATINGS_PERCENTAGE%</p>
					<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template (Normal Rating)', 'wp-taxratings'); ?>" onclick="ratings_default_templates('permission', true);" class="button" /><br />
					<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template (Up/Down Rating)', 'wp-taxratings'); ?>" onclick="ratings_updown_templates('permission', true);" class="button" />
				</td>
				<td><textarea cols="80" rows="15" id="taxratings_template_permission" name="taxratings_template_permission"><?php echo htmlspecialchars(stripslashes(get_option('taxratings_template_permission'))); ?></textarea></td>
			</tr>
			<tr>
				<td width="30%">
					<strong><?php _e('Ratings None:', 'wp-taxratings'); ?></strong><br /><br />
					<?php _e('Allowed Variables:', 'wp-taxratings'); ?><br />
          <p style="margin: 2px 0">- %RATINGS_IMAGES_VOTE%</p>
          <p style="margin: 2px 0">- %RATINGS_MAX%</p>
          <p style="margin: 2px 0">- %RATINGS_SCORE%</p>
          <p style="margin: 2px 0">- %RATINGS_TEXT%</p>
          <p style="margin: 2px 0">- %RATINGS_USERS%</p>
          <p style="margin: 2px 0">- %RATINGS_AVERAGE%</p>
          <p style="margin: 2px 0">- %RATINGS_PERCENTAGE%</p>
					<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template (Normal Rating)', 'wp-taxratings'); ?>" onclick="ratings_default_templates('none', true);" class="button" /><br />
					<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template (Up/Down Rating)', 'wp-taxratings'); ?>" onclick="ratings_updown_templates('none', true);" class="button" />
				</td>
				<td><textarea cols="80" rows="15" id="taxratings_template_none" name="taxratings_template_none"><?php echo htmlspecialchars(stripslashes(get_option('taxratings_template_none'))); ?></textarea></td>
			</tr>
		</table>	
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes', 'wp-taxratings'); ?>" />
		</p>
	</form>
</div>