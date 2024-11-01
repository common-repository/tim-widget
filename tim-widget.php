<?php

/*
Plugin Name: Tim Widget
Version: 1.0
Plugin URI: http://www.reflectionmedia.ro/2009/12/tim-widget/
Description: Recent posts from selected category with TimThumb-resized images extracted from the Media Library.
Author: Reflection Media
Author URI: http://www.reflectionmedia.ro/
*/

/*  Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * TimWidget Class
 */
class TimWidget extends WP_Widget {
    /** constructor */
    function TimWidget() {
        parent::WP_Widget(false, $name = 'TimWidget');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        ?>
              <?php echo $before_widget; ?>
					<style type="text/css">
						.tim-widget-post-excerpt {}
					</style>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; ?>
				  <?php
						$my_query = new WP_Query('cat=' . $instance['category'] . '&posts_per_page=' . $instance['number_of_posts']);
						
						echo '<ul class="tim-widget-list">';
						while ($my_query->have_posts()) : $my_query->the_post();
							$excerpt = get_the_excerpt();
							$excerpt = trunc($excerpt, $instance['excerpt_length']);
							
							echo '<li class="tim-widget-post">';
							echo '<img class="tim-widget-post-thumbnail" src="' . the_img($instance['width'],$instance['height']) . '" />';
							echo '<p class="tim-widget-post-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></p>';
							echo '<p class="tim-widget-post-excerpt">' . $excerpt . '</p>';
							echo '</li>';
						endwhile;
						echo '</ul>';
						
				  ?>
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = esc_attr($instance['title']);
        $height = esc_attr($instance['height']);
        $width = esc_attr($instance['width']);
		$selected_category = esc_attr($instance['category']);
		$number_of_posts = esc_attr($instance['number_of_posts']);
		$excerpt_length = esc_attr($instance['excerpt_length']);
		
        ?>
            <p><?php _e('Title:'); ?> <input name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
			<p><?php _e('Category:'); ?>
			<?php
			
				$categories = get_categories();
				
				echo '<select name="' . $this->get_field_name('category') . '">';
				
				foreach ($categories as $category) {
					if( $selected_category == $category->cat_ID) $option_selected = ' selected="selected"';
					else $option_selected = '';
					
					echo '<option value="' . $category->cat_ID . '"' . $option_selected . ' >' . $category->name . '</option>';
				}
				
				echo '</select>';
			
			?>
			</p>
            <p><?php _e('Number of posts:'); ?> <input name="<?php echo $this->get_field_name('number_of_posts'); ?>" type="text" value="<?php echo $number_of_posts; ?>" /></p>
            <p><?php _e('Excerpt length (words):'); ?> <input name="<?php echo $this->get_field_name('excerpt_length'); ?>" type="text" value="<?php echo $excerpt_length; ?>" /></p>
            <p><?php _e('Image height:'); ?> <input name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $height; ?>" /></p>
            <p><?php _e('Image width:'); ?> <input name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" /></p>
        <?php 
    }

} // class TimWidget


// register TimWidget widget
add_action('widgets_init', create_function('', 'return register_widget("TimWidget");'));

// This is for truncating the excerpt
function trunc($phrase, $max_words){
   $phrase_array = explode(' ',$phrase);
   if(count($phrase_array) > $max_words && $max_words > 0)
      $phrase = implode(' ',array_slice($phrase_array, 0, $max_words)) . '...';
   return $phrase;
}

// Thumbnail Image Retriving and Generation
function the_img($width, $height){
		global $post;

		//setup the attachment array
		$att_array = array(
		'post_parent' => $post->ID,
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
		'order_by' => 'menu_order'
	);

	//get the post attachments
	$attachments = get_children($att_array);
	
	if(is_array($attachments)){
		usort( $attachments, "objCompare" );
	}
	
	if ( is_array($attachments) ){
		foreach($attachments as $att){
				$image_src_array = wp_get_attachment_image_src($att->ID, 'full');
				//1 and 2 are the x and y dimensions
				$image_src = $image_src_array[0];
				$image_id = $att->ID;
				$image_caption = $att->post_excerpt;
				//We're using the timthumb.php script to generate our thumbnails for the homepage. For more info: http://www.darrenhoyt.com/2008/04/02/timthumb-php-script-released/
				return WP_PLUGIN_URL.'/tim-widget/scripts/timthumb.php?src='.$image_src.'&h='.$height.'&w='.$width.'" alt="'.$image_caption;
		}
	} else { return false; }
}

// This is so that the sorting in Media Library is used
function objCompare( $obj1, $obj2 ) {
	if ( $obj1->menu_order == $obj2->menu_order )
		return 0;
   else
		if ( $obj1->menu_order < $obj2->menu_order )
			 return -1;
	   else
			return 1;
}