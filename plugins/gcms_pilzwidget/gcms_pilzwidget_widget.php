<?php

class Pilzwidget_Widget extends WP_Widget {

	public function __construct() {
    parent::__construct('grcms_pilzwidget_widget', __('Seasonal Mushrooms', 'gcms_pilzwidget'), array( 'description' => __('Displays the Mushrooms depending on their season.', 'gcms_pilzwidget'),));
	}
		
	public function form($instance) {
    	$instance = wp_parse_args( (array) $instance, array('title' => __('Seasonal Mushrooms', 'gcms_pilzwidget')));
    	$title = $instance['title'];
    	$number = isset($instance['number']) ? absint($instance['number']) : 1;
    	$show_thumb = isset($instance['show_thumb'] ) ? (bool) $instance['show_thumb'] : false;
    	$show_exception = isset($instance['show_exception'] ) ? (bool) $instance['show_exception'] : false;
		
		  echo '<p><label for="'.$this->get_field_id('title').'">'.__('Title:', 'gcms_pilzwidget').'</label> <input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr($title).'" /></p>';
  		echo '<p><label for="'.$this->get_field_id('number').'">'.__('Number of Mushrooms to show:', 'gcms_pilzwidget').'</label> <input id="'.$this->get_field_id('number').'" name="'.$this->get_field_name('number').'" type="text" value="'.$number.'" size="3" /></p>';
  		echo '<p><input class="checkbox" type="checkbox" '.checked($show_thumb, 1, 0).' id="'.$this->get_field_id('show_thumb').'" name="'.$this->get_field_name('show_thumb').'" /><label for="'.$this->get_field_id('show_thumb').'">'.__('Display post thumbnail?', 'gcms_pilzwidget').'</label></p>';
  		echo '<p><input class="checkbox" type="checkbox" '.checked($show_exception, 1, 0).' id="'.$this->get_field_id('show_exception').'" name="'.$this->get_field_name('show_exception').'" /><label for="'.$this->get_field_id('show_exception').'">'.__('Display post exception?', 'gcms_pilzwidget').'</label></p>';
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
    $title = (!empty($instance['title'])) ? $instance['title'] : __('Current mushrooms by season', 'gcms_pilzwidget');
 
    if (!empty($title)) {
      echo $before_title . $title . $after_title;
    }

    $widgetContent = new Pilzwidget_Widget_View();
    $widgetContent->setPostObject($this->getSeasonalPosts($instance['number'])); 
    if ($instance['show_thumb']) { $widgetContent->activateThumbnail(); }
    if ($instance['show_exception']) { $widgetContent->activateException(); }
    $widgetContent->render();

    echo $after_widget;
  }

  private function getSeasonalPosts($count) {
    $taxonomyStyle = get_option('pilzwidget-taxonomy-style');

    if ($taxonomyStyle == 'seasonly') {
      $taxTerm = $this->widgetQuerySeasonly();
    } else {
      $taxTerm = $this->widgetQueryYearly();
    }

    $args = array(
  			'post_type' => pilzDb::_POST_TYPE_NAME,
  			'orderby' => 'rand',
  			'posts_per_page' => $count,
        'tax_query' => array(
  				array(
  					'taxonomy' => Pilzwidget_Admin::SEASON_TAXONOMY_NAME,
  					'field' => 'name',
  					'terms' => $taxTerm,
  					'include_children' => false,
  				),
  			),
  	 );
      return new WP_Query($args);
  	}

    private function widgetQuerySeasonly() {
      $season;
      $dayOfYear = date('z');
      if ($dayOfYear >= 79 && $dayOfYear <= 171) { $season = __('Spring', 'gcms_pilzwidget'); } 
      else if ($dayOfYear >= 172 && $dayOfYear <= 264) { $season = __('Summer', 'gcms_pilzwidget'); } 
      else if ($dayOfYear >= 265 && $dayOfYear <= 355) { $season = __('Autumn', 'gcms_pilzwidget'); } 
      else { $season = __('Winter', 'gcms_pilzwidget'); }
      return $season;
    }

    private function widgetQueryYearly() {
      return __(date('F'));
    }
}