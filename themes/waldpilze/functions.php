<?php

//register sidebar:
function waldpilze_widgets_init()
{
    register_sidebar(array(
        'name' => __('Widget Bereich'),
        'id' => 'sidebar-1',
        'description' => __('Fügt Widgets der Sidebar hinzu.', 'gcms_waldpilzTheme'),
        'before_widget' => '<div class="waldpilze_widget">',
        'after_widget' => '</div>',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
}

add_action('widgets_init', 'waldpilze_widgets_init');

//top navigation menu:
register_nav_menus(
    array(
        'head' => __('Top Menü')
    )
);

add_theme_support('post-thumbnails');


function waldlpilz_customize_register( $wp_customize )
{
    $wp_customize->add_section('previewPageSettings', array(
        'title'    => __('Waldpilze', 'gcms_waldpilzTheme'),
        'description' => '',
        'priority' => 120,
    ));

    //  =============================
    //  = Page Dropdown             =
    //  =============================
    $wp_customize->add_setting('fristPage', array(
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
    ));

    $wp_customize->add_control('fristPageControl', array(
        'label'      => __('Erste Box', 'gcms_waldpilzTheme'),
        'section'    => 'previewPageSettings',
        'type'    => 'dropdown-pages',
        'settings'   => 'fristPage',
    ));

    //  =============================
    //  = Page Dropdown             =
    //  =============================
    $wp_customize->add_setting('secondPage', array(
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
    ));

    $wp_customize->add_control('secondPageControl', array(
        'label'      => __('_Zweite Box', 'gcms_waldpilzTheme'),
        'section'    => 'previewPageSettings',
        'type'    => 'dropdown-pages',
        'settings'   => 'secondPage',
    ));

    //  =============================
    //  = Page Dropdown             =
    //  =============================
    $wp_customize->add_setting('thirdPage', array(
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
    ));

    $wp_customize->add_control('thirdPageControl', array(
        'label'      => __('Dritte Box', 'gcms_waldpilzTheme'),
        'section'    => 'previewPageSettings',
        'type'    => 'dropdown-pages',
        'settings'   => 'thirdPage',
    ));

    //  =============================
    //  = Page Dropdown             =
    //  =============================
    $wp_customize->add_setting('mainPage', array(
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
    ));

    $wp_customize->add_control('mainPageControl', array(
        'label'      => __('Info Box', 'gcms_waldpilzTheme'),
        'section'    => 'previewPageSettings',
        'type'    => 'dropdown-pages',
        'settings'   => 'mainPage',
    ));
}

add_action( 'customize_register', 'waldlpilz_customize_register' );

?>