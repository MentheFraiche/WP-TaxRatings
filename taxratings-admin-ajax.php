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
|	- Taxonomy Ratings AJAX For Admin Backend									|
|	- wp-content/plugins/wp-taxratings/taxratings-admin-ajax.php	|
|																							|
+----------------------------------------------------------------+
*/


### Include wp-config.php
$wp_root = '../../..';
if (file_exists($wp_root.'/wp-load.php')) {
	require_once($wp_root.'/wp-load.php');
} else {
	require_once($wp_root.'/wp-config.php');
}


### Check Whether User Can Manage Ratings
if(!current_user_can('manage_ratings')) {
	die('Access Denied');
}


### Variables
$taxratings_url = plugins_url('wp-taxratings/images');
$taxratings_path = WP_PLUGIN_DIR.'/wp-taxratings/images';
$taxratings_ratingstext = get_option('taxratings_ratingstext');
$taxratings_ratingsvalue = get_option('taxratings_ratingsvalue');


### Form Processing
$taxratings_customrating = intval($_GET['custom']);
$taxratings_image = trim($_GET['image']);
$taxratings_max = intval($_GET['max']);


### If It Is A Up/Down Rating
if($taxratings_customrating && $taxratings_max == 2) {
	$taxratings_ratingsvalue[0] = -1;
	$taxratings_ratingsvalue[1] = 1;
	$taxratings_ratingstext[0] = __('Vote This Taxonomy Down', 'wp-taxratings');
	$taxratings_ratingstext[1] = __('Vote This Taxonomy Up', 'wp-taxratings');
} else {
	for($i = 0; $i < $taxratings_max; $i++) {
		if($i > 0) {
			$taxratings_ratingstext[$i] = sprintf(__('%s Stars', 'wp-taxratings'), number_format_i18n($i+1));
		} else {
			$taxratings_ratingstext[$i] = sprintf(__('%s Star', 'wp-taxratings'), number_format_i18n($i+1));
		}
		$taxratings_ratingsvalue[$i] = $i+1;
	}
}
?>
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
				$taxratings_text = stripslashes($taxratings_ratingstext[$i-1]);
				$taxratings_value = $taxratings_ratingsvalue[$i-1];
				if($taxratings_value > 0) {
					$taxratings_value = '+'.$taxratings_value;
				}
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
				echo '<input type="text" id="taxratings_ratingstext_'.$i.'" name="taxratings_ratingstext[]" value="'.$taxratings_text.'" size="20" maxlength="50" />'."\n";
				echo '</td>'."\n";
				echo '<td>'."\n";
				echo '<input type="text" id="taxratings_ratingsvalue_'.$i.'" name="taxratings_ratingsvalue[]" value="'.$taxratings_value.'" size="2" maxlength="2" />'."\n";
				echo '</td>'."\n";
				echo '</tr>'."\n";
			}								
		?>
	</tbody>
</table>
<?php exit(); ?>