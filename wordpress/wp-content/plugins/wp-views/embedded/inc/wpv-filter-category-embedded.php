<?php

/**
 * Modify the query to include filtering by category.
 *
 */

add_filter('wpv_filter_query', 'wpv_filter_post_category', 10, 2);
function wpv_filter_post_category($query, $view_settings) {

	global $WP_Views;
	
	if (!isset($view_settings['taxonomy_relationship'])) {
		$view_settings['taxonomy_relationship'] = 'OR';
	}

	$taxonomies = get_taxonomies('', 'objects');
	foreach ($taxonomies as $category_slug => $category) {
		$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';
		
		if (isset($view_settings[$relationship_name])) {
			
			$save_name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input_' . $category->name;
			
			$attribute_operator = ( isset( $view_settings['taxonomy-' . $category->name . '-attribute-operator'] ) ) ? $view_settings['taxonomy-' . $category->name . '-attribute-operator'] : 'IN';
			
			if ($view_settings['tax_' . $category->name . '_relationship'] == "FROM PAGE") {
				// we need to get the terms from the current page.
				$current_page = $WP_Views->get_current_page();
				if ($current_page) {
				//	$terms = wp_get_post_terms($current_page->ID, $category->name, array("fields" => "ids"));
				//	we can save a SQL query if we use get_the_terms and wp_list_pluck instead of wp_get_post_terms
					$terms = array();
					$term_obj = get_the_terms( $current_page->ID, $category->name );
					if ( $term_obj && !is_wp_error( $term_obj ) ) {
						$terms = array_values( wp_list_pluck( $term_obj, 'term_id' ) );
					}
					if (count($terms)) {
						$query['tax_query'][] = array('taxonomy' => $category->name,
												  'field' => 'id',
												  'terms' => _wpv_get_translated_terms($terms, $category->name),
												  'operator' => "IN");
					} else { // if the current page has no term in the given taxonomy, return nothing
						$query['tax_query'][] = array('taxonomy' => $category->name,
						'field' => 'id',
						'terms' => 0,
						'operator' => "IN");
					}
				}
			} else if ($view_settings['tax_' . $category->name . '_relationship'] == "FROM ATTRIBUTE") {
				$attribute = $view_settings['taxonomy-' . $category->name . '-attribute-url'];
				if (isset($view_settings['taxonomy-' . $category->name . '-attribute-url-format'])) {
					$attribute_format = $view_settings['taxonomy-' . $category->name . '-attribute-url-format'][0];
				} else {
					$attribute_format = 'name';
				}
				$view_attrs = $WP_Views->get_view_shortcodes_attributes();
				if (isset($view_attrs[$attribute])) {

					// support csv terms

					$terms = explode(',', $view_attrs[$attribute]);
					$term_ids = array();
					foreach($terms as $t){
						$term = get_term_by($attribute_format, trim($t), $category->name);
						if ($term){
							array_push($term_ids, $term->term_id);
						}
					}
					
					if (count($term_ids) > 0) {
						$query['tax_query'][] = array('taxonomy' => $category->name,
						'field' => 'id',
						'terms' => _wpv_get_translated_terms($term_ids, $category->name),
						'operator' => $attribute_operator);
					} else if ( count( $terms ) > 1 ) { // if the shortcode attribute exists and is not empty, and no term matches the value, return nothing
						$query['tax_query'][] = array('taxonomy' => $category->name,
						'field' => 'id',
						'terms' => 0,
						'operator' => "IN");
					}
				}
			} else if ($view_settings['tax_' . $category->name . '_relationship'] == "FROM URL") {
				$url_parameter = $view_settings['taxonomy-' . $category->name . '-attribute-url'];
				if (isset($view_settings['taxonomy-' . $category->name . '-attribute-url-format'])) {
					$url_format = $view_settings['taxonomy-' . $category->name . '-attribute-url-format'][0];
				} else {
					$url_format = 'name';
				}
				
				if (isset($_GET[$url_parameter])) {
					if (is_array($_GET[$url_parameter])) {
						$terms = $_GET[$url_parameter];
					} else {
						// support csv terms
						$terms = explode(',', $_GET[$url_parameter]);
					}
					$term_ids = array();
					foreach($terms as $t){
						$term = get_term_by($url_format, trim($t), $category->name);
						if ($term){
							array_push($term_ids, $term->term_id);
						}
					}
					
					if (count($term_ids) > 0) {
						$query['tax_query'][] = array('taxonomy' => $category->name,
						'field' => 'id',
						'terms' => _wpv_get_translated_terms($term_ids, $category->name),
						'operator' => $attribute_operator);
					} else if ( !empty( $_GET[$url_parameter] ) ) { // if the URL parameter exists and is not empty, and no term matches the value, return nothing
						$query['tax_query'][] = array('taxonomy' => $category->name,
						'field' => 'id',
						'terms' => 0,
						'operator' => "IN");
					}
				}
			} else if ($view_settings['tax_' . $category->name . '_relationship'] == "FROM PARENT VIEW") {
	            $parent_term_id = $WP_Views->get_parent_view_taxonomy();
				if ($parent_term_id) {
					$query['tax_query'][] = array('taxonomy' => $category->name,
											  'field' => 'id',
											  'terms' => _wpv_get_translated_terms(array($parent_term_id), $category->name),
											  'operator' => "IN");
				} else { // if the parent View does not set any term, return nothing
					$query['tax_query'][] = array('taxonomy' => $category->name,
						'field' => 'id',
						'terms' => 0,
						'operator' => "IN");
				}
			} else if (isset($view_settings[$save_name])) { // when relationship is IN, NOT IN or AND
			
				$term_ids = $view_settings[$save_name];
			    
				$query['tax_query'][] = array('taxonomy' => $category->name,
										  'field' => 'id',
										  'terms' => _wpv_get_translated_terms($term_ids, $category->name),
										  'operator' => $view_settings['tax_' . $category->name . '_relationship']);
			}
			
			if ( isset( $query['tax_query'] ) ) {
				$query['tax_query']['relation'] = $view_settings['taxonomy_relationship'];
			}
			
		}
    }
    
    return $query;
}

function wpv_get_taxonomy_view_params($view_settings) {
	$results = array();
	
	$taxonomies = get_taxonomies('', 'objects');
	foreach ($taxonomies as $category_slug => $category) {
		$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';
		
		if (isset($view_settings[$relationship_name])) {
			
			$save_name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input_' . $category->name;			
			
			if ($view_settings['tax_' . $category->name . '_relationship'] == "FROM ATTRIBUTE") {
				$attribute = $view_settings['taxonomy-' . $category->name . '-attribute-url'];
				$results[] = $attribute;
			}
		}
    }
    
	return $results;
}


function _wpv_get_translated_terms($term_ids, $category_name) {
	if (function_exists('icl_object_id')) {
		foreach($term_ids as $key => $term_id) {
			$term_id = icl_object_id($term_id, $category_name, true);
			if ($term_id) {
				$term_ids[$key] = $term_id;
			}
		}
	}

	return $term_ids;	
}

add_filter('wpv_filter_requires_current_page', 'wpv_filter_cat_requires_current_page', 10, 2);
function wpv_filter_cat_requires_current_page($state, $view_settings) {
	if ($state) {
		return $state; // Already set
	}

	$taxonomies = get_taxonomies('', 'objects');
	foreach ($taxonomies as $category_slug => $category) {
		$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';
		
		if (isset($view_settings[$relationship_name])) {
			if ($view_settings['tax_' . $category->name . '_relationship'] == "FROM PAGE") {
				$state = true;
				break;
			}
		}
	}
	
	return $state;
	
}
