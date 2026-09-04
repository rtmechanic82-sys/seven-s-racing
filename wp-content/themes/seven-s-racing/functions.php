<?php
if (!defined('ABSPATH')) { exit; }

function seven_s_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('woocommerce');
    register_nav_menus(['primary' => 'Primary Navigation']);
    add_image_size('seven-s-hero', 1920, 1080, true);
    add_image_size('seven-s-card', 800, 560, true);
}
add_action('after_setup_theme', 'seven_s_setup');

function seven_s_assets() {
    wp_enqueue_style('seven-s-style', get_stylesheet_uri(), [], wp_get_theme()->get('Version'));
    wp_enqueue_script('seven-s-site', get_template_directory_uri() . '/assets/js/site.js', [], wp_get_theme()->get('Version'), true);
}
add_action('wp_enqueue_scripts', 'seven_s_assets');

function seven_s_asset($file) { echo esc_url(get_template_directory_uri() . '/assets/images/' . $file); }

function seven_s_query($type, $count = 4, $meta_key = '', $order = 'DESC') {
    $args = ['post_type' => $type, 'posts_per_page' => $count, 'post_status' => 'publish', 'order' => $order];
    if ($meta_key) { $args['meta_key'] = $meta_key; $args['orderby'] = 'meta_value'; }
    return new WP_Query($args);
}
