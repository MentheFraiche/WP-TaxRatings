<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress Plugin: WP-TaxRatings 1.00								|
|	Copyright (c) 2012 Aur�lien Capdecomme									|
|																							|
|	File Written By:																	|
|	- Aur�lien Capdecomme															|
|	- http://menthefraiche.com															|
|																							|
|	File Information:																	|
|	- Containts Taxonomy Rating Stats	 												|
|	- wp-content/plugins/wp-taxratings/taxratings-stats.php			|
|																							|
+----------------------------------------------------------------+
*/


### Function: Display Most Rated Page/Post
if(!function_exists('get_most_rated')) {
	function get_most_rated($mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$ratings_max = intval(get_option('taxratings_max'));
		$ratings_custom = intval(get_option('taxratings_customrating'));
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$temp = stripslashes(get_option('taxratings_template_mostrated'));
		$most_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->taxs.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->taxs LEFT JOIN $wpdb->taxmeta AS t1 ON t1.tax_id = $wpdb->taxs.ID LEFT JOIN $wpdb->taxmeta As t2 ON t1.tax_id = t2.tax_id LEFT JOIN $wpdb->taxmeta AS t3 ON t3.tax_id = $wpdb->taxs.ID WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."' AND $wpdb->taxs.tax_status = 'publish' AND t2.meta_value >= $min_votes AND $where ORDER BY ratings_users DESC, $order_by DESC LIMIT $limit");
		if($most_rated) {
			foreach ($most_rated as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Most Rated Page/Post By Category ID
if(!function_exists('get_most_rated_category')) {
	function get_most_rated_category($category_id = 0, $mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$ratings_max = intval(get_option('taxratings_max'));
		$ratings_custom = intval(get_option('taxratings_customrating'));
		$output = '';
		if(is_array($category_id)) {
			$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
		} else {
			$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$temp = stripslashes(get_option('taxratings_template_mostrated'));
		$most_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->taxs.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->taxs LEFT JOIN $wpdb->taxmeta AS t1 ON t1.tax_id = $wpdb->taxs.ID LEFT JOIN $wpdb->taxmeta As t2 ON t1.tax_id = t2.tax_id LEFT JOIN $wpdb->taxmeta AS t3 ON t3.tax_id = $wpdb->taxs.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->taxs.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."' AND $wpdb->taxs.tax_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND t2.meta_value >= $min_votes AND $where ORDER BY ratings_users DESC, $order_by DESC LIMIT $limit");
		if($most_rated) {
			foreach ($most_rated as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Most Rated Page/Post With Time Range
if(!function_exists('get_most_rated_range')) {
	function get_most_rated_range($time = '1 day', $mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$ratings_max = intval(get_option('taxratings_max'));
		$ratings_custom = intval(get_option('taxratings_customrating'));
		$min_time = strtotime('-'.$time, current_time('timestamp')); 
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$temp = stripslashes(get_option('taxratings_template_mostrated'));
		$most_rated = $wpdb->get_results("SELECT COUNT($wpdb->ratings.rating_taxid) AS ratings_users, SUM($wpdb->ratings.rating_rating) AS ratings_score, ROUND(((SUM($wpdb->ratings.rating_rating)/COUNT($wpdb->ratings.rating_taxid))), 2) AS ratings_average, $wpdb->taxs.* FROM $wpdb->taxs LEFT JOIN $wpdb->ratings ON $wpdb->ratings.rating_taxid = $wpdb->taxs.ID WHERE rating_timestamp >= $min_time AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."'  AND $wpdb->taxs.tax_status = 'publish' AND $where GROUP BY $wpdb->ratings.rating_taxid ORDER BY ratings_users DESC, $order_by DESC LIMIT $limit");
		if($most_rated) {
			foreach ($most_rated as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Most Rated Page/Post With Time Range By Category ID
if(!function_exists('get_most_rated_range_category')) {
	function get_most_rated_range_category($time = '1 day', $category_id = 0, $mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$ratings_max = intval(get_option('taxratings_max'));
		$ratings_custom = intval(get_option('taxratings_customrating'));
		$min_time = strtotime('-'.$time, current_time('timestamp')); 
		$output = '';
		if(is_array($category_id)) {
			// There is a bug with multiple categoies. The number of votes will be multiplied by the number of categories passed in.
			$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
		} else {
			$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$temp = stripslashes(get_option('taxratings_template_mostrated'));
		$most_rated = $wpdb->get_results("SELECT COUNT($wpdb->ratings.rating_taxid) AS ratings_users, SUM($wpdb->ratings.rating_rating) AS ratings_score, ROUND(((SUM($wpdb->ratings.rating_rating)/COUNT($wpdb->ratings.rating_taxid))), 2) AS ratings_average, $wpdb->taxs.* FROM $wpdb->taxs LEFT JOIN $wpdb->ratings ON $wpdb->ratings.rating_taxid = $wpdb->taxs.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->taxs.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE rating_timestamp >= $min_time AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."'  AND $wpdb->taxs.tax_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND $where GROUP BY $wpdb->ratings.rating_taxid ORDER BY ratings_users DESC, $order_by DESC LIMIT $limit");
		if($most_rated) {
			foreach ($most_rated as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Highest Rated Page/Post
if(!function_exists('get_highest_rated')) {
	function get_highest_rated($mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$ratings_max = intval(get_option('taxratings_max'));
		$ratings_custom = intval(get_option('taxratings_customrating'));
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$temp = stripslashes(get_option('taxratings_template_highestrated'));
		$highest_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->taxs.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->taxs LEFT JOIN $wpdb->taxmeta AS t1 ON t1.tax_id = $wpdb->taxs.ID LEFT JOIN $wpdb->taxmeta As t2 ON t1.tax_id = t2.tax_id LEFT JOIN $wpdb->taxmeta AS t3 ON t3.tax_id = $wpdb->taxs.ID WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."' AND $wpdb->taxs.tax_status = 'publish' AND t2.meta_value >= $min_votes AND $where ORDER BY $order_by DESC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Highest Rated Page/Post By Category ID
if(!function_exists('get_highest_rated_category')) {
	function get_highest_rated_category($category_id = 0, $mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$ratings_max = intval(get_option('taxratings_max'));
		$ratings_custom = intval(get_option('taxratings_customrating'));
		$output = '';
		// Code By: Dirceu P. Junior (http://pomoti.com)
		if(is_array($category_id)) {
			$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
		} else {
			$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$temp = stripslashes(get_option('taxratings_template_highestrated'));
		$highest_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->taxs.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->taxs LEFT JOIN $wpdb->taxmeta AS t1 ON t1.tax_id = $wpdb->taxs.ID LEFT JOIN $wpdb->taxmeta AS t2 ON t1.tax_id = t2.tax_id LEFT JOIN $wpdb->taxmeta AS t3 ON t3.tax_id = $wpdb->taxs.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->taxs.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."'  AND $wpdb->taxs.tax_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND t2.meta_value >= $min_votes AND $where ORDER BY $order_by DESC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Highest Rated Page/Post With Time Range
if(!function_exists('get_highest_rated_range')) {
	function get_highest_rated_range($time = '1 day', $mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$ratings_max = intval(get_option('taxratings_max'));
		$ratings_custom = intval(get_option('taxratings_customrating'));
		$min_time = strtotime('-'.$time, current_time('timestamp')); 
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$temp = stripslashes(get_option('taxratings_template_highestrated'));
		$highest_rated = $wpdb->get_results("SELECT COUNT($wpdb->ratings.rating_taxid) AS ratings_users, SUM($wpdb->ratings.rating_rating) AS ratings_score, ROUND(((SUM($wpdb->ratings.rating_rating)/COUNT($wpdb->ratings.rating_taxid))), 2) AS ratings_average, $wpdb->taxs.* FROM $wpdb->taxs LEFT JOIN $wpdb->ratings ON $wpdb->ratings.rating_taxid = $wpdb->taxs.ID WHERE rating_timestamp >= $min_time AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."'  AND $wpdb->taxs.tax_status = 'publish' AND $where GROUP BY $wpdb->ratings.rating_taxid ORDER BY $order_by DESC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Highest Rated Page/Post With Time Range By Category ID
if(!function_exists('get_highest_rated_range_category')) {
	function get_highest_rated_range_category($time = '1 day', $category_id = 0, $mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$ratings_max = intval(get_option('taxratings_max'));
		$ratings_custom = intval(get_option('taxratings_customrating'));
		$min_time = strtotime('-'.$time, current_time('timestamp')); 
		$output = '';
		// Code By: Dirceu P. Junior (http://pomoti.com)
		if(is_array($category_id)) {
			$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
		} else {
			$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$temp = stripslashes(get_option('taxratings_template_highestrated'));
		$highest_rated = $wpdb->get_results("SELECT COUNT($wpdb->ratings.rating_taxid) AS ratings_users, SUM($wpdb->ratings.rating_rating) AS ratings_score, ROUND(((SUM($wpdb->ratings.rating_rating)/COUNT($wpdb->ratings.rating_taxid))), 2) AS ratings_average, $wpdb->taxs.* FROM $wpdb->taxs LEFT JOIN $wpdb->ratings ON $wpdb->ratings.rating_taxid = $wpdb->taxs.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->taxs.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE rating_timestamp >= $min_time AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."'  AND $wpdb->taxs.tax_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND $where GROUP BY $wpdb->ratings.rating_taxid ORDER BY $order_by DESC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Lowest Rated Page/Post
if(!function_exists('get_lowest_rated')) {
	function get_lowest_rated($mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$ratings_max = intval(get_option('taxratings_max'));
		$ratings_custom = intval(get_option('taxratings_customrating'));
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$temp = stripslashes(get_option('taxratings_template_highestrated'));
		$lowest_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->taxs.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->taxs LEFT JOIN $wpdb->taxmeta AS t1 ON t1.tax_id = $wpdb->taxs.ID LEFT JOIN $wpdb->taxmeta As t2 ON t1.tax_id = t2.tax_id LEFT JOIN $wpdb->taxmeta AS t3 ON t3.tax_id = $wpdb->taxs.ID WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."' AND $wpdb->taxs.tax_status = 'publish' AND t2.meta_value >= $min_votes AND $where ORDER BY $order_by ASC, ratings_users DESC LIMIT $limit");
		if($lowest_rated) {
			foreach($lowest_rated as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Lowest Rated Page/Post By Category ID
if(!function_exists('get_lowest_rated_category')) {
	function get_lowest_rated_category($category_id = 0, $mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$ratings_max = intval(get_option('taxratings_max'));
		$ratings_custom = intval(get_option('taxratings_customrating'));
		$output = '';
		// Code By: Dirceu P. Junior (http://pomoti.com)
		if(is_array($category_id)) {
			$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
		} else {
			$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$temp = stripslashes(get_option('taxratings_template_highestrated'));
		$lowest_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->taxs.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->taxs LEFT JOIN $wpdb->taxmeta AS t1 ON t1.tax_id = $wpdb->taxs.ID LEFT JOIN $wpdb->taxmeta AS t2 ON t1.tax_id = t2.tax_id LEFT JOIN $wpdb->taxmeta AS t3 ON t3.tax_id = $wpdb->taxs.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->taxs.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."'  AND $wpdb->taxs.tax_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND t2.meta_value >= $min_votes AND $where ORDER BY $order_by ASC, ratings_users DESC LIMIT $limit");
		if($lowest_rated) {
			foreach($lowest_rated as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Lowest Rated Page/Post With Time Range
if(!function_exists('get_lowest_rated_range')) {
	function get_lowest_rated_range($time = '1 day', $mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$ratings_max = intval(get_option('taxratings_max'));
		$ratings_custom = intval(get_option('taxratings_customrating'));
		$min_time = strtotime('-'.$time, current_time('timestamp')); 
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$temp = stripslashes(get_option('taxratings_template_highestrated'));
		$highest_rated = $wpdb->get_results("SELECT COUNT($wpdb->ratings.rating_taxid) AS ratings_users, SUM($wpdb->ratings.rating_rating) AS ratings_score, ROUND(((SUM($wpdb->ratings.rating_rating)/COUNT($wpdb->ratings.rating_taxid))), 2) AS ratings_average, $wpdb->taxs.* FROM $wpdb->taxs LEFT JOIN $wpdb->ratings ON $wpdb->ratings.rating_taxid = $wpdb->taxs.ID WHERE rating_timestamp >= $min_time AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."'  AND $wpdb->taxs.tax_status = 'publish' AND $where GROUP BY $wpdb->ratings.rating_taxid ORDER BY $order_by ASC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}

### Function: Display Highest Score Page/Post
if(!function_exists('get_highest_score')) {
	function get_highest_score($mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		$temp = stripslashes(get_option('taxratings_template_mostrated'));
		$highest_score = $wpdb->get_results("SELECT DISTINCT $wpdb->taxs.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->taxs LEFT JOIN $wpdb->taxmeta AS t1 ON t1.tax_id = $wpdb->taxs.ID LEFT JOIN $wpdb->taxmeta As t2 ON t1.tax_id = t2.tax_id LEFT JOIN $wpdb->taxmeta AS t3 ON t3.tax_id = $wpdb->taxs.ID WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."' AND $wpdb->taxs.tax_status = 'publish' AND t2.meta_value >= $min_votes AND $where ORDER BY ratings_score DESC, ratings_average DESC LIMIT $limit");
		if($highest_score) {
			foreach ($highest_score as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Highest Score Page/Post By Category ID
if(!function_exists('get_highest_score_category')) {
	function get_highest_score_category($category_id = 0, $mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$output = '';
		if(is_array($category_id)) {
			$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
		} else {
			$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		$temp = stripslashes(get_option('taxratings_template_mostrated'));
		$highest_score = $wpdb->get_results("SELECT DISTINCT $wpdb->taxs.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->taxs LEFT JOIN $wpdb->taxmeta AS t1 ON t1.tax_id = $wpdb->taxs.ID LEFT JOIN $wpdb->taxmeta As t2 ON t1.tax_id = t2.tax_id LEFT JOIN $wpdb->taxmeta AS t3 ON t3.tax_id = $wpdb->taxs.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->taxs.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."' AND $wpdb->taxs.tax_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND t2.meta_value >= $min_votes AND $where ORDER BY ratings_score DESC, ratings_average DESC LIMIT $limit");
		if($highest_score) {
			foreach ($highest_score as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Highest Score Page/Post With Time Range
if(!function_exists('get_highest_score_range')) {
	function get_highest_score_range($time = '1 day', $mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$min_time = strtotime('-'.$time, current_time('timestamp')); 
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		$temp = stripslashes(get_option('taxratings_template_mostrated'));
		$highest_score = $wpdb->get_results("SELECT COUNT($wpdb->ratings.rating_taxid) AS ratings_users, SUM($wpdb->ratings.rating_rating) AS ratings_score, ROUND(((SUM($wpdb->ratings.rating_rating)/COUNT($wpdb->ratings.rating_taxid))), 2) AS ratings_average, $wpdb->taxs.* FROM $wpdb->taxs LEFT JOIN $wpdb->ratings ON $wpdb->ratings.rating_taxid = $wpdb->taxs.ID WHERE rating_timestamp >= $min_time AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."'  AND $wpdb->taxs.tax_status = 'publish' AND $where GROUP BY $wpdb->ratings.rating_taxid ORDER BY ratings_score DESC, ratings_average DESC LIMIT $limit");
		if($highest_score) {
			foreach ($highest_score as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Highest Score Page/Post With Time Range By Category ID
if(!function_exists('get_highest_score_range_category')) {
	function get_highest_score_range_category($time = '1 day', $category_id = 0, $mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$min_time = strtotime('-'.$time, current_time('timestamp')); 
		$output = '';
		if(is_array($category_id)) {
			// There is a bug with multiple categoies. The number of votes will be multiplied by the number of categories passed in.
			$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
		} else {
			$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		$temp = stripslashes(get_option('taxratings_template_mostrated'));
		$highest_score = $wpdb->get_results("SELECT COUNT($wpdb->ratings.rating_taxid) AS ratings_users, SUM($wpdb->ratings.rating_rating) AS ratings_score, ROUND(((SUM($wpdb->ratings.rating_rating)/COUNT($wpdb->ratings.rating_taxid))), 2) AS ratings_average, $wpdb->taxs.* FROM $wpdb->taxs LEFT JOIN $wpdb->ratings ON $wpdb->ratings.rating_taxid = $wpdb->taxs.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->taxs.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE rating_timestamp >= $min_time AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."'  AND $wpdb->taxs.tax_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND $where GROUP BY $wpdb->ratings.rating_taxid ORDER BY ratings_score DESC, ratings_average DESC LIMIT $limit");
		if($highest_score) {
			foreach ($highest_score as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Highest Rated Page/Post By Tag ID
if(!function_exists('get_highest_rated_tag')) {
	function get_highest_rated_tag($tag_id = 0, $mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$ratings_max = intval(get_option('taxratings_max'));
		$ratings_custom = intval(get_option('taxratings_customrating'));
		$output = '';
		if(is_array($tag_id)) {
			$tag_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $tag_id).')';
		} else {
			$tag_sql = "$wpdb->term_taxonomy.term_id = $tag_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$temp = stripslashes(get_option('taxratings_template_highestrated'));
		$highest_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->taxs.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->taxs LEFT JOIN $wpdb->taxmeta AS t1 ON t1.tax_id = $wpdb->taxs.ID LEFT JOIN $wpdb->taxmeta AS t2 ON t1.tax_id = t2.tax_id LEFT JOIN $wpdb->taxmeta AS t3 ON t3.tax_id = $wpdb->taxs.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->taxs.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."'  AND $wpdb->taxs.tax_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'tax_tag' AND $tag_sql AND t2.meta_value >= $min_votes AND $where ORDER BY $order_by DESC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Lowest Rated Page/Post By Tag ID
if(!function_exists('get_lowest_rated_tag')) {
	function get_lowest_rated_tag($tag_id = 0, $mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$ratings_max = intval(get_option('taxratings_max'));
		$ratings_custom = intval(get_option('taxratings_customrating'));
		$output = '';
		if(is_array($tag_id)) {
			$tag_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $tag_id).')';
		} else {
			$tag_sql = "$wpdb->term_taxonomy.term_id = $tag_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->taxs.tax_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$temp = stripslashes(get_option('taxratings_template_highestrated'));
		$lowest_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->taxs.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->taxs LEFT JOIN $wpdb->taxmeta AS t1 ON t1.tax_id = $wpdb->taxs.ID LEFT JOIN $wpdb->taxmeta AS t2 ON t1.tax_id = t2.tax_id LEFT JOIN $wpdb->taxmeta AS t3 ON t3.tax_id = $wpdb->taxs.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->taxs.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->taxs.tax_password = '' AND $wpdb->taxs.tax_date < '".current_time('mysql')."'  AND $wpdb->taxs.tax_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'tax_tag' AND $tag_sql AND t2.meta_value >= $min_votes AND $where ORDER BY $order_by ASC, ratings_users DESC LIMIT $limit");
		if($lowest_rated) {
			foreach($lowest_rated as $tax) {
				$output .= expand_ratings_template($temp, $tax->ID, $tax, $chars)."\n";
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-taxratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Total Rating Users
if(!function_exists('get_ratings_users')) {
	function get_ratings_users($display = true) {
		global $wpdb;
		$ratings_users = $wpdb->get_var("SELECT SUM((meta_value+0.00)) FROM $wpdb->taxmeta WHERE meta_key = 'ratings_users'");
		if($display) {
			echo $ratings_users;
		} else {
			return $ratings_users;
		}
	}
}
?>