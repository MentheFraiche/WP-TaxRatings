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
|	- Manage Taxonomy Ratings Logs													|
|	- wp-content/plugins/wp-taxratings/taxratings-manager.php		|
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
$taxratings_page = intval($_GET['ratingpage']);
$taxratings_filterid = trim(addslashes($_GET['id']));
$taxratings_filteruser = trim(addslashes($_GET['user']));
$taxratings_filterrating = trim(addslashes($_GET['rating']));
$taxratings_sortby = trim($_GET['by']);
$taxratings_sortby_text = '';
$taxratings_sortorder = trim($_GET['order']);
$taxratings_sortorder_text = '';
$taxratings_log_perpage = intval($_GET['perpage']);
$taxratings_sort_url = '';
$ratings_image = 'stars(png)';
$ratings_max = intval(MAX_RATING);


### Form Processing 
if(!empty($_POST['do'])) {
	// Decide What To Do
	switch($_POST['do']) {
		case 'Supprimer':
			$tax_ids = trim($_POST['delete_taxid']);
			$delete_datalog = intval($_POST['delete_datalog']);
			if(!empty($tax_ids)) {
				switch($delete_datalog) {
						case 3:
							if($tax_ids == 'all') {
								$delete_logs = $wpdb->query("DELETE FROM $wpdb->ratings");
								if($delete_logs) {
									$text = '<font color="green">Toutes les notes ont &eacute;t&eacute; supprim&eacute;es.</font><br />';
								} else {
									$text = '<font color="red">Une erreur est survenue.</font><br />';
								}
							} else {
								$delete_logs = $wpdb->query("DELETE FROM $wpdb->ratings WHERE rating_taxid IN($tax_ids)");
								if($delete_logs) {
									$text = '<font color="green">'.sprintf('Toutes les notes de l\'objet %s ont &eacute;t&eacute; supprim&eacute;es.', $tax_ids).'</font><br />';
								} else {
									$text = '<font color="red">'.sprintf('Une erreur est survenue lors de la suppression des notes de l\'objet %s.', $tax_ids).'</font><br />';
								}
							}
							break;
				}
			}
			break;
	}
}


### Form Sorting URL
if(!empty($taxratings_filterid)) {
	$taxratings_filterid = intval($taxratings_filterid);
	$taxratings_sort_url .= '&amp;id='.$taxratings_filterid;
}
if(!empty($taxratings_filteruser)) {
	$taxratings_sort_url .= '&amp;user='.$taxratings_filteruser;
}
if($_GET['rating'] != '') {
	$taxratings_filterrating = intval($taxratings_filterrating);
	$taxratings_sort_url .= '&amp;rating='.$taxratings_filterrating;
}
if(!empty($taxratings_sortby)) {
	$taxratings_sort_url .= '&amp;by='.$taxratings_sortby;
}
if(!empty($taxratings_sortorder)) {
	$taxratings_sort_url .= '&amp;order='.$taxratings_sortorder;
}
if(!empty($taxratings_log_perpage)) {
	$taxratings_log_perpage = intval($taxratings_log_perpage);
	$taxratings_sort_url .= '&amp;perpage='.$taxratings_log_perpage;
}


### Get Order By
switch($taxratings_sortby) {
	case 'id':
		$taxratings_sortby = 'rating_id';
		$taxratings_sortby_text = 'ID';
		break;
	case 'username':
		$taxratings_sortby = 'rating_username';
		$taxratings_sortby_text = 'Utilisateur';
		break;
	case 'rating':
		$taxratings_sortby = 'rating_rating';
		$taxratings_sortby_text = 'Note';
		break;
	case 'taxid':
		$taxratings_sortby = 'rating_taxid';
		$taxratings_sortby_text = 'ID Objet';
		break;
	case 'taxtitle':
		$taxratings_sortby = 'rating_taxtitle';
		$taxratings_sortby_text = 'Titre';
		break;
	case 'ip':
		$taxratings_sortby = 'rating_ip';
		$taxratings_sortby_text = 'IP';
		break;
	case 'host':
		$taxratings_sortby = 'rating_host';
		$taxratings_sortby_text = 'Host';
		break;
	case 'date':
	default:
		$taxratings_sortby = 'rating_timestamp';
		$taxratings_sortby_text = 'Date';
}


### Get Sort Order
switch($taxratings_sortorder) {
	case 'asc':
		$taxratings_sortorder = 'ASC';
		$taxratings_sortorder_text = 'Ascending';
		break;
	case 'desc':
	default:
		$taxratings_sortorder = 'DESC';
		$taxratings_sortorder_text = 'Descending';
}


// Where
$taxratings_where = '';
if(!empty($taxratings_filterid)) {
	$taxratings_where = "AND rating_taxid =$taxratings_filterid";
}
if(!empty($taxratings_filteruser)) {
	$taxratings_where .= " AND rating_username = '$taxratings_filteruser'";
}
if($_GET['rating'] != '') {
	$taxratings_where .= " AND rating_rating = '$taxratings_filterrating'";
}
// Get Taxonomy Ratings Logs Data
$total_ratings = $wpdb->get_var("SELECT COUNT(rating_id) FROM $wpdb->ratings WHERE 1=1 $taxratings_where");
$total_users = $wpdb->get_var("SELECT COUNT(rating_id) AS ratings_users FROM $wpdb->ratings WHERE 1=1 GROUP BY rating_userid");
$total_score = $wpdb->get_var("SELECT SUM((rating_rating+0.00)) AS ratings_score FROM $wpdb->ratings");
$ratings_custom = intval(CUSTOM_RATING);
if($total_users == 0) { 
	$total_average = 0;
} else {
	$total_average = $total_score/$total_ratings;
}
// Checking $taxratings_page and $offset
if(empty($taxratings_page) || $taxratings_page == 0) { $taxratings_page = 1; }
if(empty($offset)) { $offset = 0; }
if(empty($taxratings_log_perpage) || $taxratings_log_perpage == 0) { $taxratings_log_perpage = 20; }
// Determin $offset
$offset = ($taxratings_page-1) * $taxratings_log_perpage;
// Determine Max Number Of Ratings To Display On Page
if(($offset + $taxratings_log_perpage) > $total_ratings) { 
	$max_on_page = $total_ratings; 
} else { 
	$max_on_page = ($offset + $taxratings_log_perpage); 
}
// Determine Number Of Ratings To Display On Page
if (($offset + 1) > ($total_ratings)) { 
	$display_on_page = $total_ratings; 
} else { 
	$display_on_page = ($offset + 1); 
}
// Determing Total Amount Of Pages
$total_pages = ceil($total_ratings / $taxratings_log_perpage);

// Get The Logs
$taxratings_logs = $wpdb->get_results("SELECT * FROM $wpdb->ratings WHERE 1=1 $taxratings_where ORDER BY $taxratings_sortby $taxratings_sortorder LIMIT $offset, $taxratings_log_perpage");
?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<!-- Manage Taxonomy Ratings -->
<div class="wrap">
	<div id="icon-wp-taxratings" class="icon32"><br /></div>
	<h2>Gestion des notes</h2>
	<h3>Liste des notes</h3>
	<p><?php printf('Affichage de <strong>%s</strong> &agrave; <strong>%s</strong> sur <strong>%s</strong>', number_format_i18n($display_on_page), number_format_i18n($max_on_page), number_format_i18n($total_ratings)); ?></p>
	<p><?php printf('Tri&eacute;es par <strong>%s</strong> (<strong>%s</strong>)', $taxratings_sortby_text, $taxratings_sortorder_text); ?></p>
	<table class="widefat">
		<thead>
			<tr>
				<th width="2%">ID</th>
				<th width="10%">Utilisateur</th>
				<th width="10%">Note</th>
				<th width="8%">ID Objet</th>
				<th width="25%">Titre</th>	
				<th width="20%">Date / Heure</th>
				<th width="25%">IP / Host</th>			
			</tr>
		</thead>
		<tbody>
	<?php
		if($taxratings_logs) {
			$i = 0;
			foreach($taxratings_logs as $taxratings_log) {
				if($i%2 == 0) {
					$style = 'class="alternate"';
				}  else {
					$style = '';
				}
				$taxratings_id = intval($taxratings_log->rating_id);
				$taxratings_username = stripslashes($taxratings_log->rating_username);
				$taxratings_rating = intval($taxratings_log->rating_rating);
				$taxratings_taxid = intval($taxratings_log->rating_taxid);
				$taxratings_taxtitle = str_replace('$$', ',', stripslashes($taxratings_log->rating_taxtitle));
				$taxratings_date = mysql2date(sprintf('%s @ %s', get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', $taxratings_log->rating_timestamp));
				$taxratings_ip = $taxratings_log->rating_ip;
				$taxratings_host = $taxratings_log->rating_host;				
				echo "<tr $style>\n";
				echo '<td>'.$taxratings_id.'</td>'."\n";
				echo "<td>$taxratings_username</td>\n";
				echo '<td nowrap="nowrap">';
				if($ratings_custom && $ratings_max == 2) {
					if($taxratings_rating > 0) {
						$taxratings_rating = '+'.$taxratings_rating;
					}
					echo $taxratings_rating;
				} else {
					if($ratings_custom) {
						for($j=1; $j <= $ratings_max; $j++) {
							if($j <= $taxratings_rating) {
								echo '<img src="'.plugins_url('wp-taxratings/images/'.$ratings_image.'/rating_'.$j.'_on.'.RATINGS_IMG_EXT).'" alt="'.$taxratings_rating.' sur '.$ratings_max.'" class="tax-ratings-image" />';
							} else {
								echo '<img src="'.plugins_url('wp-taxratings/images/'.$ratings_image.'/rating_'.$j.'_off.'.RATINGS_IMG_EXT).'" alt="'.$taxratings_rating.' sur '.$ratings_max.'" class="tax-ratings-image" />';
							}
						}
					} else {
						for($j=1; $j <= $ratings_max; $j++) {
							if($j <= $taxratings_rating) {
								echo '<img src="'.plugins_url('wp-taxratings/images/'.$ratings_image.'/rating_on.'.RATINGS_IMG_EXT).'" alt="'. $taxratings_rating.' sur '.$ratings_max.'" class="tax-ratings-image" />';
							} else {
								echo '<img src="'.plugins_url('wp-taxratings/images/'.$ratings_image.'/rating_off.'.RATINGS_IMG_EXT).'" alt="'.$taxratings_rating.' sur '.$ratings_max.'" class="tax-ratings-image" />';
							}
						}
					}
				}
				echo '</td>'."\n";
				echo '<td>'.number_format_i18n($taxratings_taxid).'</td>'."\n";
				echo "<td>$taxratings_taxtitle</td>\n";
				echo "<td>$taxratings_date</td>\n";
				echo "<td>$taxratings_ip / $taxratings_host</td>\n";
				echo '</tr>';
				$i++;
			}
		} else {
			echo '<tr><td colspan="7" align="center"><strong>Pas de notes</strong></td></tr>';
		}
	?>
		</tbody>
	</table>
		<!-- <Paging> -->
		<?php
			if($total_pages > 1) {
		?>
		<br />
		<table class="widefat">
			<tr>
				<td align="left" width="50%">
					<?php
						if($taxratings_page > 1 && ((($taxratings_page*$taxratings_log_perpage)-($taxratings_log_perpage-1)) <= $total_ratings)) {
							echo '<strong>&laquo;</strong> <a href="'.$base_page.'&amp;ratingpage='.($taxratings_page-1).$taxratings_sort_url.'" title="&laquo; Page pr&eacute;c&eacute;dente">Page pr&eacute;c&eacute;dente</a>';
						} else {
							echo '&nbsp;';
						}
					?>
				</td>
				<td align="right" width="50%">
					<?php
						if($taxratings_page >= 1 && ((($taxratings_page*$taxratings_log_perpage)+1) <=  $total_ratings)) {
							echo '<a href="'.$base_page.'&amp;ratingpage='.($taxratings_page+1).$taxratings_sort_url.'" title="Page suivante &raquo;">Page suivante</a> <strong>&raquo;</strong>';
						} else {
							echo '&nbsp;';
						}
					?>
				</td>
			</tr>
			<tr class="alternate">
				<td colspan="2" align="center">
					<?php printf('Pages (%s) : ', number_format_i18n($total_pages)); ?>
					<?php
						if ($taxratings_page >= 4) {
							echo '<strong><a href="'.$base_page.'&amp;ratingpage=1'.$taxratings_sort_url.$taxratings_sort_url.'" title="Premi&egrave;re page">&laquo; Premi&egrave;re page</a></strong> ... ';
						}
						if($taxratings_page > 1) {
							echo ' <strong><a href="'.$base_page.'&amp;ratingpage='.($taxratings_page-1).$taxratings_sort_url.'" title="&laquo; Aller &agrave; la page '.number_format_i18n($taxratings_page-1).'">&laquo;</a></strong> ';
						}
						for($i = $taxratings_page - 2 ; $i  <= $taxratings_page +2; $i++) {
							if ($i >= 1 && $i <= $total_pages) {
								if($i == $taxratings_page) {
									echo '<strong>['.number_format_i18n($i).']</strong> ';
								} else {
									echo '<a href="'.$base_page.'&amp;ratingpage='.($i).$taxratings_sort_url.'" title="Page '.number_format_i18n($i).'">'.number_format_i18n($i).'</a> ';
								}
							}
						}
						if($taxratings_page < $total_pages) {
							echo ' <strong><a href="'.$base_page.'&amp;ratingpage='.($taxratings_page+1).$taxratings_sort_url.'" title="Aller &agrave; la page '.number_format_i18n($taxratings_page+1).' &raquo;">&raquo;</a></strong> ';
						}
						if (($taxratings_page+2) < $total_pages) {
							echo ' ... <strong><a href="'.$base_page.'&amp;ratingpage='.($total_pages).$taxratings_sort_url.'" title="Derni&egrave;re page">Derni&egrave;re page &raquo;</a></strong>';
						}
					?>
				</td>
			</tr>
		</table>	
		<!-- </Paging> -->
		<?php
			}
		?>
	<br />
	<form action="<?php echo admin_url('admin.php'); ?>" method="get">
		<input type="hidden" name="page" value="<?php echo $base_name; ?>" />
		<table class="widefat">
			<tr>
				<th>Filtres :</th>
				<td>
					ID Objet :&nbsp;<input type="text" name="id" value="<?php echo $taxratings_filterid; ?>" size="5" maxlength="5" />
					&nbsp;&nbsp;&nbsp;
					<select name="user" size="1">
						<option value=""></option>
						<?php
							$filter_users = $wpdb->get_results("SELECT DISTINCT rating_username, rating_userid FROM $wpdb->ratings WHERE rating_username != '".__('Guest', 'wp-taxratings')."' ORDER BY rating_userid ASC, rating_username ASC");
							if($filter_users) {
								foreach($filter_users as $filter_user) {
									$rating_username = stripslashes($filter_user->rating_username);
									$rating_userid = intval($filter_user->rating_userid);
									if($rating_userid > 0) {
										$prefix = 'Utilisateur : ';
									} else {
										$prefix = 'Commentateur : ';
									}
									if($rating_username == $taxratings_filteruser) {
										echo '<option value="'.htmlspecialchars($rating_username).'" selected="selected">'.$prefix.' '.$rating_username.'</option>'."\n";
									} else {
										echo '<option value="'.htmlspecialchars($rating_username).'">'.$prefix.' '.$rating_username.'</option>'."\n";
									}
								}
							}
						?>
					</select>
					&nbsp;&nbsp;&nbsp;
					<select name="rating" size="1">
						<option value=""></option>
						<?php
							$filter_ratings = $wpdb->get_results("SELECT DISTINCT rating_rating FROM $wpdb->ratings ORDER BY rating_rating ASC");
							if($filter_ratings) {
								foreach($filter_ratings as $filter_rating) {
									$rating_rating = $filter_rating->rating_rating;
									$prefix = 'Note : ';
									if($rating_rating == $taxratings_filterrating) {
										echo '<option value="'.$rating_rating.'" selected="selected">'.$prefix.' '.number_format_i18n($rating_rating).'</option>'."\n";
									} else {
										echo '<option value="'.$rating_rating.'">'.$prefix.' '.number_format_i18n($rating_rating).'</option>'."\n";
									}
								}
							}
						?>
					</select>
				</td>
			</tr>
			<tr class="alternate">
				<th>Tri :</th>
				<td>
					<select name="by" size="1">
						<option value="id"<?php if($taxratings_sortby == 'rating_id') { echo ' selected="selected"'; }?>><?php _e('ID', 'wp-taxratings'); ?></option>
						<option value="username"<?php if($taxratings_sortby == 'rating_username') { echo ' selected="selected"'; }?>>Utilisateur</option>
						<option value="rating"<?php if($taxratings_sortby == 'rating_rating') { echo ' selected="selected"'; }?>>Note</option>
						<option value="taxid"<?php if($taxratings_sortby == 'rating_taxid') { echo ' selected="selected"'; }?>>ID Objet</option>
						<option value="taxtitle"<?php if($taxratings_sortby == 'rating_taxtitle') { echo ' selected="selected"'; }?>>Titre</option>
						<option value="date"<?php if($taxratings_sortby == 'rating_timestamp') { echo ' selected="selected"'; }?>>Date</option>
						<option value="ip"<?php if($taxratings_sortby == 'rating_ip') { echo ' selected="selected"'; }?>>IP</option>
						<option value="host"<?php if($taxratings_sortby == 'rating_host') { echo ' selected="selected"'; }?>>Host</option>
					</select>
					&nbsp;&nbsp;&nbsp;
					<select name="order" size="1">
						<option value="asc"<?php if($taxratings_sortorder == 'ASC') { echo ' selected="selected"'; }?>>Croissant</option>
						<option value="desc"<?php if($taxratings_sortorder == 'DESC') { echo ' selected="selected"'; } ?>>D&eacute;croissant</option>
					</select>
					&nbsp;&nbsp;&nbsp;
					<select name="perpage" size="1">
					<?php
						for($i=10; $i <= 100; $i+=10) {
							if($taxratings_log_perpage == $i) {
								echo "<option value=\"$i\" selected=\"selected\">Par page : ".number_format_i18n($i)."</option>\n";
							} else {
								echo "<option value=\"$i\">Par page : ".number_format_i18n($i)."</option>\n";
							}
						}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center"><input type="submit" value="<?php _e('Go', 'wp-taxratings'); ?>" class="button" /></td>
			</tr>
		</table>
	</form>
</div>
<p>&nbsp;</p>

<!-- Taxonomy Ratings Stats -->
<div class="wrap">
	<h3>Statistiques</h3>
	<br style="clear" />
	<table class="widefat">
		<tr>
			<th>Nombre de votes :</th>
			<td><?php echo number_format_i18n($total_ratings); ?></td>
		</tr>
		<tr class="alternate">
			<th>Notes globale :</th>
			<td><?php echo number_format_i18n($total_score); ?></td>
		</tr>
		<tr>
			<th>Moyenne globale :</th>
			<td><?php echo number_format_i18n($total_average, 2); ?></td>
		</tr>
	</table>
</div>
<p>&nbsp;</p>

<!-- Delete Taxonomy Ratings Logs -->
<div class="wrap">
	<h3>Suppression</h3>
	<br style="clear" />
	<div align="center">
		<form method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>">
		<table class="widefat">
			<tr>
				<td valign="top"><b>Type :</b></td>
				<td valign="top">
					<select size="1" name="delete_datalog">
						<option value="3">Donn&eacute;es</option>
					</select>				
				</td>
			</tr>
			<tr>
				<td valign="top"><b>ID Objet :</b></td>
				<td valign="top">
					<input type="text" name="delete_taxid" size="20" dir="ltr" />
					<p>S&eacute;parer les objets par des virgules.</p>
					<p>Pour supprimer les ID 2, 3 et 4, saisissez <b>2,3,4</b>.</p>
					<p>Pour tout supprimer, saisissez <b>all</b>.</p>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input type="submit" name="do" value="Supprimer" class="button" onclick="return confirm('Confirmez-vous la suppression ?')" />
				</td>
			</tr>
		</table>
		</form>	
	</div>
	<h3>NB :</h3>
	<ul>
		<li>Si vous bloquez les votes par cookies, les utilisateurs ne pourront pas revoter tant que le cookie n'aura pas expir&eacute;.</li>
	</ul>
</div>