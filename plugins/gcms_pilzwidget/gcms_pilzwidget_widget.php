<?php

class Pilzwidget_Widget extends WP_Widget {

	function __construct() {
		parent::__construct('grcms_pilzwidget_widget', __('Seasonal Mushroom Widget', 'pilz-widget'), array( 'description' => __('Displays the Mushrooms depending on their season.', 'pilz-widget'),));
	}
		
	function form($widgetInstance) {
    	$widgetInstance = wp_parse_args( (array) $widgetInstance, array('title' => __('Seasonal Mushrooms', 'pilz-widget')));
    	$title = $widgetInstance['title'];
		echo '<p><label for="'.$this->get_field_id('title').'">'.__('Title', 'pilz-widget').': <input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr($title).'" /></label></p>';
  	}
 
	function update($widgetInstanceNew, $widgetInstanceOld) {
		$widgetInstance = $widgetInstanceOld;
		$widgetInstance['title'] = $widgetInstanceNew['title'];
		return $widgetInstance;
	}
 
  	function widget($args, $widgetInstance) {
    	extract($args, EXTR_SKIP);
 
    	echo $before_widget;
    	$title = empty($widgetInstance['title']) ? '' : apply_filters('widget_title', $widgetInstance['title']);
 
    	if (!empty($title)) {
      		echo $before_title . $title . $after_title;;
    	}
 
		//widget content which is shown on page widgetarea
		echo "<h1>Hello here comes the shroom widget</h1>";

		echo $after_widget;
  	}
}