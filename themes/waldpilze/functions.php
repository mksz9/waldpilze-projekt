<?php

//register sidebar:
function waldpilze_widgets_init()
{
    register_sidebar(array(
        'name' => __('Widget Bereich'),
        'id' => 'sidebar-1',
        'description' => __('Fügt Widgets der Sidebar hinzu.'),
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


add_filter('posts_where', 'letter_where');

function letter_where($where)
{
    global $wp_query;
    global $wpdb;

    if (isset($wp_query->query_vars['letter'])) {
        $where .= " AND $wpdb->posts.post_title LIKE '" . $wp_query->query_vars['letter'] . "%'";
    }
    return $where;
}

?>