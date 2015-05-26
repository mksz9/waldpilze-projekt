<?php
	
if (!function_exists('add_filter')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class Pilzwidget_Admin {

	const SEASON_TAXONOMY_NAME = 'seasons';

	private $theTerms;

	public function __construct() {

		load_plugin_textdomain('gcms_pilzwidget', false, 'gcms_pilzwidget/languages');

		$taxonomyStyle = get_option('pilzwidget-taxonomy-style');

		if ($taxonomyStyle == 'seasonly') {
			$this->theTerms = $this->widgetShowSeasonly();
		} else {
			$this->theTerms = $this->widgetShowMonthly();
		}		
	}

	public function init() {
		if (!is_admin()) return;
		add_action('admin_head', array($this, 'addAdminCSS'));
		add_action('admin_menu', array($this, 'addSeasonsBoxToPostType'));
		add_action('save_post', array($this, 'saveSeasonsData'));
		add_action("admin_init", array($this, 'registerWidgetSettings'));
		add_action("update_option_pilzwidget-taxonomy-style", array($this, 'widgetDisplayOptionChanged'), 1, 2);
	}

	public function widgetDisplayOptionChanged($oldVal, $newVal) {
		//delete old taxonomy-terms
		$actualTerms = get_terms(self::SEASON_TAXONOMY_NAME, array('fields' => 'ids','hide_empty' => false));
		foreach($actualTerms as $term) { wp_delete_term($term, self::SEASON_TAXONOMY_NAME); }
		//when updated the option, then fire function:
		add_action("updated_option", array($this, 'widgetDisplayOptionChangedReady'), 1);
	}	

	public function widgetDisplayOptionChangedReady() {
		//rebuild taxonomy array, based on option
		self::__construct();
		//insert the new taxonomy terms
		$this->insertTermsInTaxonomy();
	}

	public function activate() {
		add_option('pilzwidget-taxonomy-style', 'monthly');
		$this->insertTermsInTaxonomy();
	}

	public function initSeasonsTaxonomy() {
		if(!taxonomy_exists(self::SEASON_TAXONOMY_NAME)) {
			$args = array(
				'labels' => array(
								'name' => __( 'Seasons', 'gcms_pilzwidget'),
								'singular_name'	=> __( 'Season', 'gcms_pilzwidget')
							),
				'hierarchical' => false,
				'rewrite' => array('slug' => __(self::SEASON_TAXONOMY_NAME)),
				'show_ui' => false,
				'show_admin_column' => true
			);
			register_taxonomy(self::SEASON_TAXONOMY_NAME, 'pilze', $args );
		}
	}

	public function insertTermsInTaxonomy() {
		$this->initSeasonsTaxonomy();
		
		foreach($this->theTerms as $term) {
			wp_insert_term($term, self::SEASON_TAXONOMY_NAME);
		}

		flush_rewrite_rules();
	}

	private function widgetShowSeasonly() {
		return array(__('Summer', 'gcms_pilzwidget'), __('Autumn', 'gcms_pilzwidget'), __('Winter', 'gcms_pilzwidget'), __('Spring', 'gcms_pilzwidget'));
	}

	private function widgetShowMonthly() {
		return array(__('January', 'gcms_pilzwidget'), __('February', 'gcms_pilzwidget'), __('March', 'gcms_pilzwidget'), __('April', 'gcms_pilzwidget'), __('May', 'gcms_pilzwidget'), __('June', 'gcms_pilzwidget'), __('July', 'gcms_pilzwidget'), __('August', 'gcms_pilzwidget'), __('September', 'gcms_pilzwidget'), __('October', 'gcms_pilzwidget'), __('November', 'gcms_pilzwidget'), __('December', 'gcms_pilzwidget'));
	}

	public function addSeasonsBoxToPostType() {
		//remove standard box
		remove_meta_box('tagsdiv-'.self::SEASON_TAXONOMY_NAME, 'pilze', 'core');
		//adding custom box
		add_meta_box('box-'.self::SEASON_TAXONOMY_NAME, __('Season', 'gcms_pilzwidget'), array($this, 'seasonsBoxOutput'), 'pilze', 'side', 'core');
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

	public function registerWidgetSettings() {
		add_settings_section("taxonomy-style", __('Appearance of the Seasonal Taxonomies', 'gcms_pilzwidget'), null, "widgetsettings");
	    add_settings_field("pilzwidget-taxonomy-style", __('Style', 'gcms_pilzwidget'), array($this, 'displayTaxonomyStyleSettings'), "widgetsettings", "taxonomy-style");
	    register_setting("taxonomy-style", "pilzwidget-taxonomy-style");
	}

	public function pilzwidgetRegisterSettingsPage() {
		add_submenu_page('edit.php?post_type=pilze', __('Widget Settings', 'gcms_pilzwidget'), __('Widget Settings', 'gcms_pilzwidget'), 'manage_options', 'widgetsettings', array($this, 'pilzwidgetSettingsPage'));
	}

	public function pilzwidgetSettingsPage() {	
		echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
			echo '<h2>Widget Settings</h2>';
			echo '<form method="post" enctype="multipart/form-data" action="options.php">';
				settings_fields("taxonomy-style");
            	do_settings_sections("widgetsettings");
            		   echo '<p class="description" id="tagline-description">'.__('We highly recommend you to only setup this options once after plugin installation and never touch it again!', 'gcms_pilzwidget').'</p>';
			echo '<p class="submit"><input name="Submit" type="submit" class="button-primary" value="'.__('Save Changes', 'gcms_pilzwidget').'"/></p>';
			echo '</form>';
		echo '</div>';
	}

	public function displayTaxonomyStyleSettings() {
	   echo '<p><label><input type="radio" name="pilzwidget-taxonomy-style" value="seasonly" '.checked('seasonly', get_option('pilzwidget-taxonomy-style'), false).'>'.__('Seasonly', 'gcms_pilzwidget').'</label></p>';
	   echo '<p><label><input type="radio" name="pilzwidget-taxonomy-style" value="monthly" '.checked('monthly', get_option('pilzwidget-taxonomy-style'), false).'>'.__('Monthly', 'gcms_pilzwidget').'</label></p>';
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
}