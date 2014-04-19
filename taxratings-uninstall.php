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
|	- Uninstall WP-TaxRatings														|
|	- wp-content/plugins/wp-taxratings/taxratings-uninstall.php		|
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
$mode = trim($_GET['mode']);
$ratings_tables = array($wpdb->ratings);
$ratings_settings = array('taxratings_image', 'taxratings_max', 'taxratings_template_vote', 'taxratings_template_text', 'taxratings_template_none', 'taxratings_logging_method', 'taxratings_allowtorate', 'taxratings_ratingstext', 'taxratings_template_highestrated', 'taxratings_ajax_style', 'widget_ratings_highest_rated', 'widget_ratings_most_rated', 'taxratings_customrating', 'taxratings_ratingsvalue', 'taxratings_template_permission', 'taxratings_template_mostrated', 'widget_ratings', 'widget_ratings-widget');


### Form Processing 
if(!empty($_POST['do'])) {
	// Decide What To Do
	switch($_POST['do']) {
		//  Uninstall WP-PostRatings
		case __('UNINSTALL WP-TaxRatings', 'wp-taxratings') :
			if(trim($_POST['uninstall_rating_yes']) == 'yes') {
				echo '<div id="message" class="updated fade">';
				echo '<p>';
				foreach($ratings_tables as $table) {
					$wpdb->query("DROP TABLE {$table}");
					echo '<font style="color: green;">';
					printf(__('Table \'%s\' has been deleted.', 'wp-taxratings'), "<strong><em>{$table}</em></strong>");
					echo '</font><br />';
				}
				echo '</p>';
				echo '<p>';
				foreach($ratings_settings as $setting) {
					$delete_setting = delete_option($setting);
					if($delete_setting) {
						echo '<font color="green">';
						printf(__('Setting Key \'%s\' has been deleted.', 'wp-taxratings'), "<strong><em>{$setting}</em></strong>");
						echo '</font><br />';
					} else {
						echo '<font color="red">';
						printf(__('Error deleting Setting Key \'%s\' or Setting Key \'%s\' does not exist.', 'wp-taxratings'), "<strong><em>{$setting}</em></strong>", "<strong><em>{$setting}</em></strong>");
						echo '</font><br />';
					}
				}
				echo '</p>';
				echo '</div>'; 
				$mode = 'end-UNINSTALL';
			}
			break;
	}
}


### Determines Which Mode It Is
switch($mode) {
		//  Deactivating WP-PostRatings
		case 'end-UNINSTALL':
			$deactivate_url = 'plugins.php?action=deactivate&amp;plugin=wp-taxratings/wp-taxratings.php';
			if(function_exists('wp_nonce_url')) { 
				$deactivate_url = wp_nonce_url($deactivate_url, 'deactivate-plugin_wp-taxratings/wp-taxratings.php');
			}
			echo '<div class="wrap">';
			echo '<div id="icon-wp-taxratings" class="icon32"><br /></div>';
			echo '<h2>'.__('Uninstall WP-TaxRatings', 'wp-taxratings').'</h2>';
			echo '<p><strong>'.sprintf(__('<a href="%s">Click Here</a> To Finish The Uninstallation And WP-TaxRatings Will Be Deactivated Automatically.', 'wp-taxratings'), $deactivate_url).'</strong></p>';
			echo '</div>';
			break;
	// Main Page
	default:
?>
<!-- Uninstall WP-PostRatings -->
<form method="tax" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>">
<div class="wrap">
	<div id="icon-wp-taxratings" class="icon32"><br /></div>
	<h2><?php _e('Uninstall WP-TaxRatings', 'wp-taxratings'); ?></h2>
	<p>
		<?php _e('Deactivating WP-TaxRatings plugin does not remove any data that may have been created, such as the ratings data and the ratings\'s logs. To completely remove this plugin, you can uninstall it here.', 'wp-taxratings'); ?>
	</p>
	<p style="color: red">
		<strong><?php _e('WARNING:', 'wp-taxratings'); ?></strong><br />
		<?php _e('Once uninstalled, this cannot be undone. You should use a Database Backup plugin of WordPress to back up all the data first.', 'wp-taxratings'); ?>
	</p>
	<p style="color: red">
		<strong><?php _e('The following WordPress Options/Tables will be DELETED:', 'wp-taxratings'); ?></strong><br />
	</p>
	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('WordPress Options', 'wp-taxratings'); ?></th>
				<th><?php _e('WordPress Tables', 'wp-taxratings'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td valign="top">
					<ol>
					<?php
						foreach($ratings_settings as $settings) {
							echo '<li>'.$settings.'</li>'."\n";
						}
					?>
					</ol>
				</td>
				<td valign="top" class="alternate">
					<ol>
					<?php
						foreach($ratings_tables as $tables) {
							echo '<li>'.$tables.'</li>'."\n";
						}
					?>
					</ol>
				</td>
			</tr>
		</tbody>
	</table>
	<p>&nbsp;</p>
	<p style="text-align: center;">
		<input type="checkbox" name="uninstall_rating_yes" value="yes" />&nbsp;<?php _e('Yes', 'wp-taxratings'); ?><br /><br />
		<input type="submit" name="do" value="<?php _e('UNINSTALL WP-TaxRatings', 'wp-taxratings'); ?>" class="button-primary" onclick="return confirm('<?php _e('You Are About To Uninstall WP-TaxRatings From WordPress.\nThis Action Is Not Reversible.\n\n Choose [Cancel] To Stop, [OK] To Uninstall.', 'wp-taxratings'); ?>')" />
	</p>
</div>
</form>
<?php
} // End switch($mode)
?>