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
|	- Taxonomy Ratings Admin Javascript File											|
|	- wp-content/plugins/wp-taxratings/taxratings-admin-js.php		|
|																							|
+----------------------------------------------------------------+
*/


// Function: Update Rating Text, Rating Value
function update_rating_text_value() {
	jQuery('#taxratings_loading').show();
	jQuery.ajax({type: 'GET', url: ratingsAdminL10n.admin_ajax_url, data: 'custom=' + jQuery('#taxratings_customrating').val() + '&image=' + jQuery("input[@name=taxratings_image]:checked").val() + '&max=' + jQuery('#taxratings_max').val(), cache: false, success: function (data) { jQuery('#rating_text_value').html(data); jQuery('#taxratings_loading').hide(); }});
}