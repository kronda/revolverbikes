jQuery(document).ready(function($){

    wpv_initialize_filter_select('popup_add_filter');
    wpv_initialize_filter_select('popup_add_filter_taxonomy');
    wpv_initialize_filter_select('popup_add_custom_field');
    wpv_initialize_filter_select('popup_add_category_field');

});

function wpv_initialize_filter_select(filter) {
    var type = jQuery('#' + filter + '_select').val();
    if (type) {
        type = type.replace('[', '\\[');
        type = type.replace(']', '\\]');
        jQuery('#' + filter + '_con_' + type).show();
    
        jQuery('#' + filter + '_select').change(function() {
            jQuery('.wpv_add_filter_con_'+filter).each(function() {
                jQuery(this).hide();
            });
            
            var type = jQuery('#' + filter + '_select').val();
			if (type == -1) {
				// Hide the "Condition:" text and the "Add field" button
				jQuery('#' + filter + '_select').next().next().hide();
				jQuery('#' + filter + '_select').parent().find('#wpv_add_filters_button').hide();
				jQuery('#' + filter + '_select').parent().find('#wpv_add_filters_taxonomy_button').hide();
			} else {
				type = type.replace('[', '\\[');
				type = type.replace(']', '\\]');
				jQuery('#' + filter + '_con_' + type).show();
				// Show the "Condition:" text and the "Add field" button
				jQuery('#' + filter + '_select').next().next().show();
				jQuery('#' + filter + '_select').parent().find('#wpv_add_filters_button').show();
				jQuery('#' + filter + '_select').parent().find('#wpv_add_filters_taxonomy_button').show();
			}			
        });
    }
    
    var loop = true;
    jQuery('#' + filter + '_select option').each(function(index) {
        if (loop && jQuery(this).css('display') != 'none') {
            jQuery(this).attr('selected', 'selected');
            jQuery('#' + filter + '_select').trigger('change');
            loop = false;
        }
    });

    
}

var wpv_add_filter_callbacks = Array();
function wpv_register_add_filter_callback(function_name) {
    wpv_add_filter_callbacks.push(function_name);    
}

function wpv_add_filter_submit(div_id) {

    var query_type = jQuery('input[name="_wpv_settings\\[query_type\\]\\[\\]"]:checked').val();

    var type = jQuery('#' + div_id + '_select').val();
    
    if (jQuery('input[name=author_mode\\[\\]]:checked').val() == 'by_url') {
	if (jQuery('input[name=author_url]').val() == '') {
		jQuery('.wpv_author_url_param_missing').show();
		jQuery('.wpv_author_shortcode_param_missing').hide();
		return;
	} else {
		var pat = /^[a-z0-9\-\_]+$/;
		var t = jQuery('input[name=author_url]').val();
		if(pat.test(t) == false)
		{
		  jQuery('.wpv_author_helper').html('');
		  jQuery('.wpv_author_url_param_ilegal').show();
		  return;
		}
	}
    }
    
    if (jQuery('input[name=author_mode\\[\\]]:checked').val() == 'shortcode') {
	if (jQuery('input[name=author_shortcode]').val() == '') {
		jQuery('.wpv_author_url_param_missing').hide();
		jQuery('.wpv_author_shortcode_param_missing').show();
		return;
	} else {
		var pat = /^[a-z0-9]+$/;
		var t = jQuery('input[name=author_shortcode]').val();
		if(pat.test(t) == false)
		{
		  jQuery('.wpv_author_helper').html('');
		  jQuery('.wpv_author_shortcode_param_ilegal').show();
		  return;
		}
	}
    }
    
	if (jQuery('input[name=id_mode\\[\\]]:checked').val() == 'by_url') {
		if (jQuery('input[name=post_ids_url]').val() == '') {
			jQuery('.wpv_id_url_param_missing').show();
			jQuery('.wpv_id_shortcode_param_missing').hide();
			return;
		} else {
			var pat = /^[a-z0-9\-\_]+$/;
			var t = jQuery('input[name=post_ids_url]').val();
			if(pat.test(t) == false)
			{
			  jQuery('.wpv_id_helper').html('');
			  jQuery('.wpv_id_url_param_ilegal').show();
			  return;
			}
		}
    }
    
    if (jQuery('input[name=id_mode\\[\\]]:checked').val() == 'shortcode') {
		if (jQuery('input[name=post_ids_shortcode]').val() == '') {
			jQuery('.wpv_id_url_param_missing').hide();
			jQuery('.wpv_id_shortcode_param_missing').show();
			return;
		} else {
			var pat = /^[a-z0-9]+$/;
			var t = jQuery('input[name=post_ids_shortcode]').val();
			if(pat.test(t) == false)
			{
			  jQuery('.wpv_id_helper').html('');
			  jQuery('.wpv_id_shortcode_param_ilegal').show();
			  return;
			}
		}
    }
	
    if (type.substr(0, 13) == 'custom-field-') {
        if (wpv_add_edit_custom_field(div_id, type, 'add')) {
			jQuery('option[value="'+type+'"]').hide();
			jQuery('#popup_add_filter_select option:first').attr('selected', 'selected');
			jQuery('#popup_add_filter_select').trigger('change');
			wpv_initialize_filter_select('popup_add_custom_field');
		}
        return;
    }
    
    if (type == 'post_category' || type.substr(0, 9) == 'tax_input') {
        if (wpv_add_edit_taxonomy(div_id, type, 'add')) {
			jQuery('option[value="'+type+'"]').hide();
			jQuery('#popup_add_filter_select option:first').attr('selected', 'selected');
			jQuery('#popup_add_filter_select').trigger('change');
			wpv_initialize_filter_select('popup_add_category_field');
		}
        return;
    }
    
    // get checkboxes
    var checkboxes = new Array();
    var type_temp = type.replace('[', '\\[');
    type_temp = type_temp.replace(']', '\\]');
    jQuery('input[name=' + type_temp + '\\[\\]]').each( function(index) {
        if (jQuery(this).attr('checked')) {
            checkboxes.push(jQuery(this).attr('value'));
        }
    });
    
    // get search if set
    var search = '';
    var mode = '';
    var search_content = '';
    if (jQuery('input[name=post_search_value]').length) {
        search = jQuery('input[name=post_search_value]').val();
        mode = jQuery('input[name=post_search_mode\\[\\]]:checked').val();
	search_content = jQuery('select[name=post_search_content]').val();
    }
    
    // get taxonomy search if set
    var taxonomy_search = '';
    var taxonomy_mode = '';
    if (jQuery('input[name=taxonomy_search_value]').length) {
        taxonomy_search = jQuery('input[name=taxonomy_search_value]').val();
        taxonomy_mode = jQuery('input[name=taxonomy_search_mode\\[\\]]:checked').val();
    }
    
    // get parent if set
    var parent_mode = '';
    var parent_id = '';
    if (jQuery('input[name=parent_mode\\[\\]]').length) {
        parent_mode = jQuery('input[name=parent_mode\\[\\]]:checked').val();
        parent_id = jQuery('select[name=wpv_parent_id_add]').val();
    }
    
    // add a new row to the query filter
    
    // find the last row.
    var temp_index = -1;
    jQuery('tr.wpv_filter_row').each( function(index) {
        var this_row = jQuery(this).attr('id');
        this_row = parseInt(this_row.substr(15));
        if (this_row > temp_index) {
            temp_index = this_row;
        }
    });

    var data = {
        action : 'wpv_get_table_row_ui',
        type_data : type,
        row : temp_index + 1,
        checkboxes : checkboxes,
        search : search,
        mode : mode,
	search_content : search_content,
        taxonomy_search : taxonomy_search,
        taxonomy_mode : taxonomy_mode,
        parent_mode : parent_mode,
        parent_id : parent_id,
        wpv_nonce : jQuery('#wpv_get_table_row_ui_nonce').attr('value')
        
    };

    for (var i = 0; i < wpv_add_filter_callbacks.length; i++) {
        callback = wpv_add_filter_callbacks[i];
        if (eval('typeof(' + callback + ') == \'function\'')) {
            data = eval(callback+'(data);');
        }
    }
    

    if (query_type == 'taxonomy') {
        // get taxonomy parent if set
        if (jQuery('input[name=taxonomy_parent_mode\\[\\]]').length) {
            data['parent_mode'] = jQuery('input[name=taxonomy_parent_mode\\[\\]]:checked').val();
            data['parent_id'] = jQuery('select[name=wpv_taxonomy_parent_id]').val();
            data['taxonomy'] = jQuery('input[name=_wpv_settings\\[taxonomy_type\\]\\[\\]]:checked').val();
        }

        var taxonomy_terms = Array();
        jQuery('.taxonomy-term-div input').each( function(index) {
            if (jQuery(this).is(':checkbox') && jQuery(this).attr('checked')) {
                taxonomy_terms.push(jQuery(this).attr('value'));
            }
        });
        
        data['taxonomy_term_checks'] = taxonomy_terms;
		data['taxonomy_terms_mode'] = jQuery('.taxonomy_terms_mode').val();
    }
        
    
    var td = '';
    jQuery.ajaxSetup({async:false});
    jQuery.post(ajaxurl, data, function(response) {
        td = response;
    });
    
    var row_class = '';
    if (query_type == 'posts') {
        row_class = ' wpv_post_type_filter_row';
    } else if (query_type == 'taxonomy') {
        row_class = ' wpv_taxonomy_filter_row';
    }

    jQuery('#wpv_filter_table').append('<tr class="wpv_filter_row' + row_class + '" id="wpv_filter_row_' + (temp_index + 1) + '">' + td + '</tr>');
    
    on_generate_wpv_filter();
    
    // Remove option
    jQuery('option[value="'+type+'"]').hide();
    if (query_type == 'posts') {
		jQuery('#popup_add_filter_select option:first').attr('selected', 'selected');
		jQuery('#popup_add_filter_select').trigger('change');
    } else if (query_type == 'taxonomy') {
		jQuery('#popup_add_filter_taxonomy_select option:first').attr('selected', 'selected');
		jQuery('#popup_add_filter_taxonomy_select').trigger('change');
	}

    tb_remove();
 
 	show_view_changed_message();
	
	if (mode == 'visitor') {
		// Add filter controls for search
		wpv_add_filter_controls_for_search();
	}
   
}

function on_delete_wpv_filter(index) {
    index = Number(index);
    
    // check for a custom field row
    if (jQuery('#wpv_filter_row_' + index).attr('class').indexOf('wpv_custom_field_edit_row') != -1) {
        // mark as delete and hide
        jQuery('#wpv_filter_row_' + index).attr('class', 'wpv_custom_field_edit_row wpv_custom_field_edit_row_delete wpv_filter_row');
        jQuery('#wpv_filter_row_' + index).hide();
        // The custom field will then be deleted when "OK" is clicked.
        return;
    }
    
    // check for a taxonomy row
    if (jQuery('#wpv_filter_row_' + index).attr('class').indexOf('wpv_taxonomy_edit_row') != -1) {
        // mark as delete and hide
        jQuery('#wpv_filter_row_' + index).attr('class', 'wpv_taxonomy_edit_row wpv_taxomony_edit_row_delete wpv_filter_row');
        jQuery('#wpv_filter_row_' + index).hide();
        // The taxonomy will then be deleted when "OK" is clicked.
        return;
    }

    if(jQuery('#wpv_filter_row_' + index + ' input[name="_wpv_settings\\[post_search\\]"]').length) {
        jQuery('option[value="post_search"]').show();
        // search box manual remove if needed
        /* var meta_content = jQuery('#wpv_filter_meta_html_content').val();
        var search_code = wpv_search_box_code();
        meta_content = meta_content.replace(search_code, '');
        jQuery('#wpv_filter_meta_html_content').val(meta_content); */
    }
    if(jQuery('#wpv_filter_row_' + index + ' input[name="_wpv_settings\\[post_status\\]\\[\\]"]').length) {
        jQuery('option[value="post_status"]').show();
    }
    if(jQuery('#wpv_filter_row_' + index + ' input[name="_wpv_settings\\[parent_mode\\]\\[\\]"]').length) {
        jQuery('option[value="post_parent"]').show();
    }
    if(jQuery('#wpv_filter_row_' + index + ' input[name="_wpv_settings\\[author_mode\\]\\[\\]"]').length) {
	    jQuery('option[value="post_author"]').show();
    }
    
    jQuery('#wpv_filter_row_' + index).remove();
    
    on_generate_wpv_filter(false);
    
    return;
};

