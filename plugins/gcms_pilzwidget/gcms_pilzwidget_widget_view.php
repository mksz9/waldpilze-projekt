<?php

class Pilzwidget_Widget_View {

	private $postObject;
	private $showThumbnail;
	private $showException;

	public function __construct() {
		$this->postObject = null;
		$this->showThumbnail = false;
		$this->showException = false;
	}

	public function setPostObject($postObject) {
		$this->postObject = $postObject;
	}

	public function activateThumbnail() {
		$this->showThumbnail = true;
	} 

	public function activateException() {
		$this->showException = true;
	}

	public function render() {
		if (!is_null($this->postObject)) {
			if ($this->postObject->have_posts()) {
				while ($this->postObject->have_posts()) {
					$this->postObject->the_post();
					
					echo '<a class="pilzwidget_link" href="'.get_permalink().'">'.get_the_title().'</a>';
					
					if ($this->showThumbnail) {
						echo '<img src="'.$this->fetchPostThumbnail(get_the_id()).'">';
					}

					if ($this->showException) {
						the_excerpt();
					}
				}
				wp_reset_query();
				return;
			}
		}
		echo __('Sorry but there aren\'t any mushrooms in this month', 'gcms_pilzwidget');
	}

	private function fetchPostThumbnail($postId) {
		$thumbnail = plugins_url().'/gcms_pilzwidget/img/default_thumb.png'; //default thumbnail
		if (has_post_thumbnail($postId)) {
			$thumbnail =  wp_get_attachment_url(get_post_thumbnail_id($postId));
		}
		return $thumbnail;
	}
}