<?php

class Pilzwidget_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct('grcms_pilzwidget_widget', __('Seasonal Mushrooms', 'pilz-widget'), array( 'description' => __('Displays the Mushrooms depending on their season.', 'pilz-widget'),));
	}
		
	public function form($instance) {
    	$instance = wp_parse_args( (array) $instance, array('title' => __('Seasonal Mushrooms', 'pilz-widget')));
    	$title = $instance['title'];
    	$number = isset($instance['number']) ? absint($instance['number']) : 1;
    	$show_thumb = isset($instance['show_thumb'] ) ? (bool) $instance['show_thumb'] : false;
    	$show_exception = isset($instance['show_exception'] ) ? (bool) $instance['show_exception'] : false;
		
		echo '<p><label for="'.$this->get_field_id('title').'">'.__( 'Title:' ).'</label> <input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr($title).'" /></p>';
  		echo '<p><label for="'.$this->get_field_id('number').'">'.__('Number of Mushrooms to show:', 'pilz-widget').'</label> <input id="'.$this->get_field_id('number').'" name="'.$this->get_field_name('number').'" type="text" value="'.$number.'" size="3" /></p>';
  		echo '<p><input class="checkbox" type="checkbox" '.checked($show_thumb, 1, 0).' id="'.$this->get_field_id('show_thumb').'" name="'.$this->get_field_name('show_thumb').'" /><label for="'.$this->get_field_id('show_thumb').'">'.__('Display post thumbnail?', 'pilz-widget').'</label></p>';
  		echo '<p><input class="checkbox" type="checkbox" '.checked($show_exception, 1, 0).' id="'.$this->get_field_id('show_exception').'" name="'.$this->get_field_name('show_exception').'" /><label for="'.$this->get_field_id('show_exception').'">'.__('Display post exception?', 'pilz-widget').'</label></p>';
  	}
 
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = absint($new_instance['number']);
		$instance['show_thumb'] = isset($new_instance['show_thumb']) ? (bool) $new_instance['show_thumb'] : false;
		$instance['show_exception'] = isset($new_instance['show_exception']) ? (bool) $new_instance['show_exception'] : false;

		return $instance;
	}
 
  	public function widget($args, $instance) {
    	extract($args, EXTR_SKIP);
 
    	echo $before_widget;
    	$title = (!empty($instance['title'])) ? $instance['title'] : __('Current mushrooms by season', 'pilz-widget');
 
    	if (!empty($title)) {
      		echo $before_title . $title . $after_title;;
    	}
 
		//widget content which is shown on page widgetarea
		$seasonalPosts = $this->getRandomSeasonalPosts($instance['number']);

		if ($seasonalPosts->have_posts()) {
			while ($seasonalPosts->have_posts()) {
				$seasonalPosts->the_post();
				echo '<p><a href="'.get_permalink().'">'.get_the_title().'</a></p>';
			}
		} else {
			echo __('Sorry but there aren\'t any mushrooms in this month', 'pilz-widget');
		}

		wp_reset_query();

		echo $after_widget;
  	}

  	private function getRandomSeasonalPosts($count) {
  		$args = array(
  			"post_type" => pilzDb::_POST_TYPE_NAME,
  			"orderby" => "rand",
  			"posts_per_page" => $count,
  			"tax_query" => array(
  				array(
  					"taxonomy" => Pilzwidget_Admin::SEASON_TAXONOMY_NAME,
  					"field" => 'name',
  					"terms" => __(date('F'))
  				),
  			)
  		);

  		//add filter to terms for editing the initial seasons
		$args = apply_filters('initQueryArgumentsForPilzWidget', $args);
error_log(print_r($args, true));
		return new WP_Query($args);
  	}
}