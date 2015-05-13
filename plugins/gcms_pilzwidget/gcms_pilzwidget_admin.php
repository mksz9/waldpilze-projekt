<?php
	
if (!function_exists('add_filter')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class Pilzwidget_Admin {

	const SEASON_TAXONOMY_NAME = 'seasons';

	public function init() {
		if (!is_admin()) return;

		add_action('init', array($this, 'initSeasonsTaxonomy'), 0);
		add_action('admin_head', array($this, 'addAdminCSS'));
		add_action('admin_menu', array($this, 'addSeasonsBoxToPostType'));
		add_action('save_post', array($this, 'saveSeasonsData'));

		$this->addWidgetSettings();
		$this->addWidgetBoxToTheme();
	}

	public function initSeasonsTaxonomy() {

		$theTerms = array(__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December'));
		
		if(!taxonomy_exists(self::SEASON_TAXONOMY_NAME)) {
			$args = array(
				'labels' => array(
								'name' => _x( 'Seasons', 'Taxonomy plural name', 'pilz-widget' ),
								'singular_name'	=> _x( 'Season', 'Taxonomy singular name', 'pilz-widget' )
							),
				'query_var' => 'season',
				'hierarchical' => false,
				'rewrite' => array('slug' => __(self::SEASON_TAXONOMY_NAME)),
				'show_ui' => false,
				'show_admin_column' => true
			);
			register_taxonomy(self::SEASON_TAXONOMY_NAME, 'pilze', $args );

			foreach($theTerms as $term) {
				wp_insert_term($term, self::SEASON_TAXONOMY_NAME);
			}

		}
	}

	public function addSeasonsBoxToPostType() {
		//remove standard box
		remove_meta_box('tagsdiv-'.self::SEASON_TAXONOMY_NAME, 'pilze', 'core');
		//adding custom box
		add_meta_box('box-'.self::SEASON_TAXONOMY_NAME, 'Season', array($this, 'seasonsBoxOutput'), 'pilze', 'side', 'core');
	}

	public function seasonsBoxOutput($post) {
		
		//add security nonce check
		echo '<input type="hidden" name="taxonomy_noncename" id="taxonomy_noncename" value="' . 
            wp_create_nonce( 'taxonomy_'.self::SEASON_TAXONOMY_NAME ) . '" />';

        $allSeasons = get_terms(self::SEASON_TAXONOMY_NAME, 'hide_empty=0&orderby=id'); //$themes
        echo '<select name="pilze_seasons[]" id="pilze_seasons" multiple>';

        $pilzSeasons = wp_get_object_terms($post->ID, self::SEASON_TAXONOMY_NAME); //$names

        foreach($allSeasons as $season) {
        	if (!is_wp_error($pilzSeasons) && !empty($pilzSeasons) && $this->checkSelectedSlug($season, $pilzSeasons)) {
        		echo '<option class="season-option" value="'.$season->slug.'" selected>'.$season->name.'</option>';
        	} else {
        		echo '<option class="season-option" value="'.$season->slug.'">'.$season->name.'</option>';        	
        	}
        }
        echo '</select>';
	}

	private function checkSelectedSlug($taxonomy, $taxonomyCollection) {
		if (is_array($taxonomyCollection)) {
			foreach ($taxonomyCollection as $singleTaxonomy) {
				if (!strcmp($taxonomy->slug, $singleTaxonomy->slug)) {
					return true;
				}
			}
		}
		return false;
	}

	public function saveSeasonsData($post_id) {
		
		//check if nonce is correct
		if (!wp_verify_nonce($_POST['taxonomy_noncename'], 'taxonomy_'.self::SEASON_TAXONOMY_NAME)) {
			return $post_id;
		}

		//not saving on autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}

		$post = get_post($post_id);

		if ($post->post_type == 'pilze') {
			$season = $_POST['pilze_seasons'];
			wp_set_object_terms($post_id, $season, self::SEASON_TAXONOMY_NAME);
			return $season;
		}

		return $post_id;
	}

	public function addAdminCSS() {
		 echo ' 
		 	 <style>
			    #pilze_seasons {
		 	 		width: 100%;
		 	 		height: 120px;
		 		} 
			 </style>
		';
	}

	private function addWidgetBoxToTheme() {

	}

	private function addWidgetSettings() {

	}
}