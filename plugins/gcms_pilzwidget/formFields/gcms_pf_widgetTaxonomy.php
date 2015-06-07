<?php

class gcms_pf_widgetTaxonomy
{
    function __construct()
    {
        add_filter('pilzformular_addFormField', array($this, 'printHtml'), 50);
        add_filter('pilzformular_getFieldData', array($this, 'getTaxData'));
        add_filter('pilzformular_editPost', array($this, 'addTaxContentToPost'), 10, 2);
    }

    public function printHtml($htmlForm)
    {
        $allSeasons = get_terms(Pilzwidget_Admin::SEASON_TAXONOMY_NAME, 'hide_empty=0&orderby=id');
        $htmlForm .= '<p>';
        $htmlForm .= __('Season', 'gcms_pilzwidget') . ':<br>';
        $htmlForm .= '<select name="pilze_seasons[]" id="pilze_seasons" multiple>';
        foreach ($allSeasons as $season) {

            $htmlForm .= '<option class="season-option" value="' . $season->slug . '">' . $season->name . '</option>';
        }
        $htmlForm .= '</select>';
        $htmlForm .= '</p>';

        return $htmlForm;
    }

    public function getTaxData($data)
    {
        if (isset($_POST['pilze_seasons'])) {
            $data['seasons'] = ($_POST['pilze_seasons']);
        } else {
            $data['seasons'] = '';

        }
        return $data;
    }

    public function addTaxContentToPost($post, $data)
    {
        if (is_array($data['seasons'])) {
            $post['tax_input'] = array(Pilzwidget_Admin::SEASON_TAXONOMY_NAME => $data['seasons']);
        }
        return $post;
    }
}