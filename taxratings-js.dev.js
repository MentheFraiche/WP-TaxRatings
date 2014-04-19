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
|	- Taxonomy Ratings Javascript File													|
|	- wp-content/plugins/wp-taxratings/taxratings-js.php				|
|																							|
+----------------------------------------------------------------+
*/


// Variables
var tax_id = 0;
var tax_rating = 0;
var is_being_rated = false;
ratingsL10n.custom = parseInt(ratingsL10n.custom);
ratingsL10n.max = parseInt(ratingsL10n.max);
ratingsL10n.show_loading = parseInt(ratingsL10n.show_loading);
ratingsL10n.show_fading = parseInt(ratingsL10n.show_fading);

// When User Mouse Over Ratings
function current_rating(id, rating, rating_text) {
	if(!is_being_rated) {
		tax_id = id;
		tax_rating = rating;
		if(ratingsL10n.custom && ratingsL10n.max == 2) {
			jQuery('#rating_' + tax_id + '_' + rating).attr('src', eval('ratings_' + rating + '_mouseover_image.src'));
		} else {
			for(i = 1; i <= rating; i++) {
				if(ratingsL10n.custom) {
					jQuery('#rating_' + tax_id + '_' + i).attr('src', eval('ratings_' + i + '_mouseover_image.src'));
				} else {
					jQuery('#rating_' + tax_id + '_' + i).attr('src', ratings_mouseover_image.src);
				}
			}
		}
		if(jQuery('#ratings_' + tax_id + '_text').length) {
			jQuery('#ratings_' + tax_id + '_text').show();
			jQuery('#ratings_' + tax_id + '_text').html(rating_text);
		}
	}
}


// When User Mouse Out Ratings
function ratings_off(rating_score, insert_half) {
	if(!is_being_rated) {
		for(i = 1; i <= ratingsL10n.max; i++) {
			if(i <= rating_score) {
				if(ratingsL10n.custom) {
					jQuery('#rating_' + tax_id + '_' + i).attr('src', ratingsL10n.template_url + '/images/rating_' + i + '_on.' + ratingsL10n.image_ext);
				} else {
					jQuery('#rating_' + tax_id + '_' + i).attr('src', ratingsL10n.template_url + '/images/rating_on.' + ratingsL10n.image_ext);
				}
			} else if(i == insert_half) {
				if(ratingsL10n.custom) {
					jQuery('#rating_' + tax_id + '_' + i).attr('src',  ratingsL10n.template_url + '/images/rating_' + i + '_half' + '.' + ratingsL10n.image_ext);
				} else {
					jQuery('#rating_' + tax_id + '_' + i).attr('src', ratingsL10n.template_url + '/images/rating_half' + '.' + ratingsL10n.image_ext);
				}
			} else {
				if(ratingsL10n.custom) {
					jQuery('#rating_' + tax_id + '_' + i).attr('src', ratingsL10n.template_url + '/images/rating_' + i + '_off.' + ratingsL10n.image_ext);
				} else {
					jQuery('#rating_' + tax_id + '_' + i).attr('src', ratingsL10n.template_url + '/images/rating_off.' + ratingsL10n.image_ext);
				}
			}
		}
		if(jQuery('#ratings_' + tax_id + '_text').length) {
			jQuery('#ratings_' + tax_id + '_text').hide();
			jQuery('#ratings_' + tax_id + '_text').empty();
		}
	}
}

// Set is_being_rated Status
function set_is_being_rated(rated_status) {
	is_being_rated = rated_status;
}

// Process Post Ratings Success
function rate_tax_success(data) {
	jQuery('#tax-ratings-' + tax_id).html(data);
	if(ratingsL10n.show_loading) {
		jQuery('#tax-ratings-' + tax_id + '-loading').hide();
		jQuery('#tax-ratings-' + tax_id).show();
	}
	if(ratingsL10n.show_fading) {
		jQuery('#tax-ratings-' + tax_id).fadeTo('def', 1, function () {
			jQuery('#tax-ratings-' + tax_id).show();
			set_is_being_rated(false);	
		});
	} else {
		set_is_being_rated(false);	
	}
}

// Process Post Ratings
function rate_tax() {
	if(!is_being_rated) {
		set_is_being_rated(true);
		if(ratingsL10n.show_fading) {
			jQuery('#tax-ratings-' + tax_id).fadeTo('def', 0, function () {
				if(ratingsL10n.show_loading) {
					jQuery('#tax-ratings-' + tax_id).hide();
					jQuery('#tax-ratings-' + tax_id + '-loading').show();
				}
				jQuery.ajax({type: 'GET', url: ratingsL10n.ajax_url, data: 'pid=' + tax_id + '&rate=' + tax_rating, cache: false, success: rate_tax_success});
			});
		} else {
			if(ratingsL10n.show_loading) {
				jQuery('#tax-ratings-' + tax_id).hide();
				jQuery('#tax-ratings-' + tax_id + '-loading').show();
			}
			jQuery.ajax({type: 'GET', url: ratingsL10n.ajax_url, data: 'pid=' + tax_id + '&rate=' + tax_rating, cache: false, success: rate_tax_success});
		}
	} else {
		alert(ratingsL10n.text_wait);
	}
}