<?php

/**
 *
 * Class FPW Widget
 *
 * @ Featured Post Widget
 *
 * building the actual widget
 *
 */
class Featured_Post_Widget extends WP_Widget {
	
	const language_file = 'postfeature';
	
	private static $options;
 
	function __construct() {
		
		$widget_opts = array( 'description' => __('You can feature a certain post in this widget and display it, where and however you want, in your widget areas. A backup post can be given to avoid dubble content.', self::language_file) );
		$control_opts = array( 'width' => 400 );
		
		parent::WP_Widget(false, $name = 'Featured Post Widget', $widget_opts, $control_opts);
		
		self::$options = get_option('pf_options');
	
	}
	 
	function form($instance) {
		
		$defaults = array(
			'title' => NULL,
			'thumb' => false,
			'image' => NULL,
			'article' => NULL,
			'backup' => NULL,
			'id' => NULL,
			'bid' => NULL,
			'class' => false,
			'headclass' => NULL,
			'dateclass' => NULL,
			'width' => get_option('thumbnail_size_w'),
			'imgborder' => NULL,
			'headline' => NULL,
			'h' => 3,
			'date' => NULL,
			'excerpt' => NULL,
			'linespace' => false,
			'alignment' => NULL,
			'noshorts' => false,
			'readmore' => false,
			'rmclass' => NULL,
			'rmtext' => NULL,
			'filter' => NULL,
			'style' => NULL
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		if (isset($instance['notext'])) $instance['alignment'] = 'notext';
		
		$title = esc_attr($instance['title']);
		$thumb = esc_attr($instance['thumb']);
		$image = esc_attr($instance['image']);
		$article = esc_attr($instance['article']);
		$backup = esc_attr($instance['backup']);
		$class = esc_attr($instance['class']);
		$headclass = esc_attr($instance['headclass']);
		$dateclass = esc_attr($instance['dateclass']);
		$width = esc_attr($instance['width']);
		$headline = esc_attr($instance['headline']);
		$date = esc_attr($instance['date']);
		$excerpt = esc_attr($instance['excerpt']);
		$linespace = esc_attr($instance['linespace']);
		$alignment = esc_attr($instance['alignment']);
		$noshorts = esc_attr($instance['noshorts']);
		$readmore = esc_attr($instance['readmore']);
		$rmclass = esc_attr($instance['rmclass']);
		$rmtext = esc_attr($instance['rmtext']);
		$style = esc_attr($instance['style']);
		$h=esc_attr($instance['h']);
		$filter=esc_attr($instance['filter']);
		$id=esc_attr($instance['id']);
		$bid=esc_attr($instance['bid']);
		$imgborder=esc_attr($instance['imgborder']);
		
		$args = array(
			'posts_per_page' => -1,
			'post_status' => 'publish'
		);
		
		$features = get_posts($args);
		foreach ( $features as $feature ) :
		
			$posts[] = array($feature->ID, $feature->post_title );
		
		endforeach;
		
		$options = array (array('top', __('Above thumbnail', self::language_file)) , array('bottom', __('Under thumbnail', self::language_file)), array('middel', __('Under date', self::language_file)), array('none', __('Don&#39;t show title', self::language_file)));
		
		$items = array (array('none', __('Under image', self::language_file)), array('right', __('Left of image', self::language_file)), array('left', __('Right of image', self::language_file)), array('notext', __('Don&#39;t show excerpt', self::language_file)));
		
		$date_options = array (array('top', __('Above post', self::language_file)), array('middel', __('Under thumbnail', self::language_file)), array('bottom', __('Under post', self::language_file)), array('none', __('Don&#39;t show date', self::language_file)));
		
		$headings = array(array('1', 'h1'), array('2', 'h2'), array('3', 'h3'), array('4', 'h4'), array('5', 'h5'), array('6', 'h6'));
		
		$base_id = 'widget-'.$this->id_base.'-'.$this->number.'-';
		$base_name = 'widget-'.$this->id_base.'['.$this->number.']';
		
		
		a5_text_field($base_id.'title', $base_name.'[title]', $title, __('Title:', self::language_file), array('space' => true, 'class' => 'widefat'));
		a5_select($base_id.'article', $base_name.'[article]', $posts, $article, __('Choose here the post, you want to appear in the widget.', self::language_file), __('Take a random post', self::language_file), array('space' => true, 'class' => 'widefat'));
		a5_select($base_id.'backup', $base_name.'[backup]',$posts,  $backup, __('Choose here the backup post. It will appear, when a single post page shows the featured article.', self::language_file), __('Take a random post', self::language_file), array('space' => true, 'class' => 'widefat'));
		a5_number_field($base_id.'id', $base_name.'[id]', $id, __('Post ID (if you don&#39;t want to use the dropdown menu):', self::language_file), array('space' => true, 'size' => 4, 'step' => 1));
		a5_number_field($base_id.'bid', $base_name.'[bid]', $bid, __('ID for backup post (if you don&#39;t want to use the dropdown menu):', self::language_file), array('space' => true, 'size' => 4, 'step' => 1));
		a5_checkbox($base_id.'class', $base_name.'[class]', $class, __('I want to style the headline and the date in this widget with my own class(es).', self::language_file), array('space' => true));
		a5_text_field($base_id.'headclass', $base_name.'[headclass]', $headclass, __('Write here the name of your class for the headline:', self::language_file), array('space' => true, 'class' => 'widefat'));
		a5_text_field($base_id.'dateclass', $base_name.'[dateclass]', $dateclass, __('Write here the name of your class for the date:', self::language_file), array('space' => true, 'class' => 'widefat'));
		a5_text_field($base_id.'image', $base_name.'[image]', $image, sprintf(__('To use an image of the post instead of the post thumbnail, enter the number of that image. The word %s will bring the last image of the post.', self::language_file), '&#39;last&#39;'), array('space' => true, 'size' => 6));
		a5_number_field($base_id.'width', $base_name.'[width]', $width, __('Width of the thumbnail (in px):', self::language_file), array('space' => true, 'size' => 4, 'step' => 1));
		a5_text_field($base_id.'imgborder', $base_name.'[imgborder]', $imgborder, sprintf(__('If wanting a border around the image, write the style here. %s would make it a black border, 1px wide.', self::language_file), '<strong>1px solid #000000</strong>'), array('space' => true, 'class' => 'widefat'));
		a5_checkbox($base_id.'thumb', $base_name.'[thumb]', $thumb, sprintf(__('Check to %snot%s display the thumbnail of the post.', self::language_file), '<strong>', '</strong>'), array('space' => true));
		a5_select($base_id.'headline', $base_name.'[headline]', $options, $headline, __('Choose, whether or not to display the title and whether it comes above or under the thumbnail.', self::language_file), false, array('space' => true));
		a5_select($base_id.'h', $base_name.'[h]', $headings, $h, __('Weight of the Post Title:', self::language_file), false, array('space' => true));
		a5_select($base_id.'date', $base_name.'[date]', $date_options, $date, __('Choose, whether or not to display the publishing date and whether it comes above or under the post.', self::language_file), false, array('space' => true));
		a5_textarea($base_id.'excerpt', $base_name.'[excerpt]', $excerpt, __('If the excerpt of the post is not defined, by default the first 3 sentences of the post are shown. You can enter your own excerpt here, if you want.', self::language_file), array('space' => true, 'class' => 'widefat', 'style' => 'height: 60px;'));
		a5_select($base_id.'alignment', $base_name.'[alignment]', $items, $alignment, __('Choose, whether or not to display the excerpt and whether it comes under the thumbnail or next to it.', self::language_file), false, array('space' => true));
		a5_checkbox($base_id.'linespace', $base_name.'[linespace]', $linespace, __('Check to have each sentence in a new line.', self::language_file), array('space' => true));
		a5_checkbox($base_id.'noshorts', $base_name.'[noshorts]', $noshorts, __('Check to suppress shortcodes in the widget (in case the content is showing).', self::language_file), array('space' => true));
		a5_checkbox($base_id.'filter', $base_name.'[filter]', $filter, __('Check to return the excerpt unfiltered (might avoid interferences with other plugins).', self::language_file), array('space' => true));
		a5_checkbox($base_id.'readmore', $base_name.'[readmore]', $readmore, __('Check to have an additional &#39;read more&#39; link at the end of the excerpt.', self::language_file), array('space' => true));	
		a5_text_field($base_id.'rmtext', $base_name.'[rmtext]', $rmtext, sprintf(__('Write here some text for the &#39;read more&#39; link. By default, it is %s:', self::language_file), '[&#8230;]'), array('space' => true, 'class' => 'widefat'));
		a5_text_field($base_id.'rmclass', $base_name.'[rmclass]', $rmclass, __('If you want to style the &#39;read more&#39; link, you can enter a class here.', self::language_file), array('space' => true, 'class' => 'widefat'));	
		if(empty(self::$options['css'])) a5_textarea($base_id.'style', $base_name.'[style]', $style, sprintf(__('Here you can finally style the widget. Simply type something like%sto get just a gray outline and a padding of 10 px. If you leave that section empty, your theme will style the widget.', self::language_file), '<br /><strong>border: 2px solid;<br />border-color: #cccccc;<br />padding: 10px;</strong><br />'), array('space' => true, 'class' => 'widefat', 'style' => 'height: 60px;'));
		a5_resize_textarea(array($base_id.'excerpt', $base_id.'style'), true);
		
	} // form
	 
	function update($new_instance, $old_instance) {
		 
		$instance = $old_instance;
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['article'] = strip_tags($new_instance['article']);
		$instance['backup'] = strip_tags($new_instance['backup']);	 
		$instance['class'] = @$new_instance['class'];
		$instance['headclass'] = strip_tags($new_instance['headclass']);
		$instance['dateclass'] = strip_tags($new_instance['dateclass']);
		$instance['thumb'] = @$new_instance['thumb'];	
		$instance['image'] = strip_tags($new_instance['image']);	 
		$instance['width'] = strip_tags($new_instance['width']);	 
		$instance['headline'] = strip_tags($new_instance['headline']);
		$instance['date'] = strip_tags($new_instance['date']);
		$instance['excerpt'] = strip_tags($new_instance['excerpt']);
		$instance['linespace'] = @$new_instance['linespace'];
		$instance['alignment'] = strip_tags($new_instance['alignment']);
		$instance['noshorts'] = @$new_instance['noshorts'];
		$instance['readmore'] = @$new_instance['readmore'];
		$instance['rmtext'] = strip_tags($new_instance['rmtext']);
		$instance['rmclass'] = strip_tags($new_instance['rmclass']);
		$instance['h'] = strip_tags($new_instance['h']);
		$instance['style'] = strip_tags($new_instance['style']);
		$instance['filter'] = @$new_instance['filter'];
		$instance['id'] = strip_tags($new_instance['id']);
		$instance['bid'] = strip_tags($new_instance['bid']);
		$instance['imgborder'] = strip_tags($new_instance['imgborder']);
		
		return $instance;
		
	} // update
	
	function widget($args, $instance) {
		
		extract( $args );
		
		$title = apply_filters('widget_title', $instance['title']);
		
		if (!empty($instance['style'])) :
			
			$style=str_replace(array("\r\n", "\n", "\r"), '', $instance['style']);
			
			$before_widget = str_replace('>', 'style="'.$style.'">', $before_widget);
		
		endif;
		
		// widget starts
		
		echo $before_widget;
		
		if ( $title ) echo $before_title . $title . $after_title;
		
		global $wp_query, $post;
		
		$article = ($instance['id']) ? $instance['id'] : $instance['article'];
		$backup = ($instance['bid']) ? $instance['bid'] : $instance['backup'];
			
		$fpw_post_id = get_post($article);
		
		$fpw_post_name = $fpw_post_id->post_name;
		
		$fpw_post = ($article == $wp_query->get( 'p' ) || $fpw_post_name == $wp_query->get ( 'name' )) ? 'p='.$backup : 'p='.$article;
		
		if ($fpw_post=='p=') $fpw_post = 'posts_per_page=1&orderby=rand';
		
		/* This is the actual function of the plugin, it fills the widget with the customized post */
		
		$fpw_posts = new WP_Query($fpw_post);
		
		while($fpw_posts->have_posts()) :
				
			$fpw_posts->the_post();
	 
			$fpw_tags = A5_Image::tags(self::language_file);
			
			$fpw_image_alt = $fpw_tags['image_alt'];
			$fpw_image_title = $fpw_tags['image_title'];
			$fpw_title_tag = $fpw_tags['title_tag'];
			
			$fpw_style = ($instance['alignment'] != 'notext' && $instance['alignment'] != 'none') ? ' style="text-align: '.$instance['alignment'].';"' : '';
			
			$eol = "\r\n";
			
			// headline, if wanted
			
			if ($instance['headline'] != 'none') :
			
				$head_class = ($instance['class']) ? ' class="'.$instance['headclass'].'"' : '';
				
				$fpw_headline = $eol.'<h'.$instance['h'].$head_class.$fpw_style.'><a href="'.get_permalink().'" title="'.$fpw_title_tag.'">'.get_the_title().'</a></h'.$instance['h'].'>';
				
			endif;
			
			if ($instance['date'] != 'none') :
			
				$date_class = ($instance['class']) ? ' class="'.$instance['dateclass'].'"' : '';
				
				$post_date = $eol.'<p'.$date_class.$fpw_style.'>'.get_the_date().'</p>';
			
			endif;
			
			// thumbnail, if wanted
			
			if (!$instance['thumb']) :
			
				$fpw_float = ($instance['alignment'] != 'notext') ? $instance['alignment'] : 'none';
				
				$fpw_margin = '';
				if ($instance['alignment'] == 'left') $fpw_margin = ' margin-right: 1em;';
				if ($instance['alignment'] == 'right') $fpw_margin = ' margin-left: 1em;';
				
				$fpw_imgborder = (!empty($instance['imgborder'])) ? ' border: '.$instance['imgborder'].';' : '';
				
				$id = get_the_ID();
				
				$number = ($instance['image']) ? $instance['image'] : NULL;
					
				$args = array (
					'id' => $id,
					'option' => 'pf_options',
					'width' => $instance['width'],
					'number' => $number
				);
					
				$fpw_image_info = A5_Image::thumbnail($args);
				
				$fpw_thumb = $fpw_image_info[0];
				
				$fpw_width = $fpw_image_info[1];
		
				$fpw_height = $fpw_image_info[1];
				
				$fpw_height = ($fpw_image_info[2]) ? ' height="'.$fpw_image_info[2].'"' : '';
					
				if ($fpw_thumb) $fpw_img_tag = '<img title="'.$fpw_image_title.'" src="'.$fpw_thumb.'" alt="'.$fpw_image_alt.'" width="'.$fpw_width.'"'.$fpw_height.' style="float: '.$fpw_float.';'.$fpw_margin.$fpw_imgborder.'" />';
			
				$fpw_image = (isset($fpw_img_tag)) ? '<a href="'.get_permalink().'">'.$fpw_img_tag.'</a>'.$eol : '';
				
			endif;
			
			// excerpt, if wanted
			
			if ($instance['alignment'] != 'notext') :
			
				$rmtext = ($instance['rmtext']) ? $instance['rmtext'] : '[&#8230;]';
				$filter = ($instance['filter']) ? false : true;
					
				$shortcode = ($instance['noshorts']) ? false : true;
				
				$class = (!empty($instance['rmclass'])) ? $instance['rmclass'] : false;
			
				$args = array(
					'usertext' => $instance['excerpt'],
					'excerpt' => $post->post_excerpt,
					'content' => $post->post_content,
					'shortcode' => $shortcode,
					'linespace' => $instance['linespace'],
					'link' => get_permalink(),
					'title' => $fpw_title_tag,
					'readmore' => $instance['readmore'],
					'rmtext' => $rmtext,
					'class' => $class,
					'filter' => $filter
				);
		
				$fpw_text = A5_Excerpt::text($args);
			
			endif;
			
			// writing the stuff in the widget
			
			if ($instance['headline'] == 'top') echo $fpw_headline.$eol;
			
			if ($instance['date'] == 'top') echo $post_date.$eol;
			
			if ($instance['date'] == 'top' && $instance['headline'] == 'middel') echo $fpw_headline.$eol;
			
			if (!$instance['thumb']) echo $fpw_image;
			
			if ($instance['headline'] == 'bottom') echo $fpw_headline.$eol;
			
			if ($instance['date'] == 'middel') echo $post_date.$eol;
			
			if ($instance['date'] == 'middel' && $instance['headline'] == 'middel') echo $fpw_headline.$eol;
			
			if ($instance['alignment'] == 'left' || $instance['alignment'] == 'right') echo $eol.do_shortcode($fpw_text).$eol;
			
			echo '<div style="clear: both;"></div>'.$eol;
			
			if ($instance['alignment'] == 'none') echo do_shortcode($fpw_text).$eol;
			
			if ($instance['date'] == 'bottom') echo $post_date.$eol;
			
			if ($instance['date'] == 'bottom' && $instance['headline'] == 'middel') echo $fpw_headline.$eol;
			
		endwhile;
		
		// Restore original Query & Post Data
		wp_reset_query();
		wp_reset_postdata();
		
		echo $after_widget;
	
	} // widget
 
} // Featured Post Widget

add_action('widgets_init', create_function('', 'return register_widget("Featured_Post_Widget");'));

?>