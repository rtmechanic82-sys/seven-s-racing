<?php
/**
 * Plugin Name: Seven S Core
 * Description: Racing content, sponsor management, and a simplified owner dashboard for Seven S Racing.
 * Version: 0.1.0
 * Author: Patriot Digital Collective
 */

if (!defined('ABSPATH')) { exit; }

final class Seven_S_Core {
    const OWNER_ROLE = 'seven_s_owner';

    public static function boot() {
        add_action('init', [__CLASS__, 'register_content']);
        add_action('add_meta_boxes', [__CLASS__, 'register_meta_boxes']);
        add_action('save_post', [__CLASS__, 'save_meta']);
        add_action('admin_menu', [__CLASS__, 'owner_dashboard']);
        add_action('admin_init', [__CLASS__, 'redirect_owner']);
        add_action('admin_head', [__CLASS__, 'owner_admin_styles']);
        add_filter('manage_seven_s_sponsor_posts_columns', [__CLASS__, 'sponsor_columns']);
        add_action('manage_seven_s_sponsor_posts_custom_column', [__CLASS__, 'sponsor_column_values'], 10, 2);
    }

    public static function activate() {
        add_role(self::OWNER_ROLE, 'Seven S Owner', [
            'read' => true,
            'upload_files' => true,
            'edit_posts' => true,
            'edit_published_posts' => true,
            'publish_posts' => true,
            'delete_posts' => true,
        ]);
        self::register_content();
        flush_rewrite_rules();
    }

    public static function register_content() {
        $types = [
            'seven_s_sponsor' => ['Sponsors', 'Sponsor', 'dashicons-heart', ['title', 'editor', 'thumbnail']],
            'seven_s_event' => ['Race Schedule', 'Race', 'dashicons-calendar-alt', ['title', 'editor', 'thumbnail']],
            'seven_s_result' => ['Results', 'Result', 'dashicons-awards', ['title', 'editor', 'thumbnail']],
            'seven_s_gallery' => ['Gallery', 'Gallery Photo', 'dashicons-format-gallery', ['title', 'thumbnail']],
        ];
        foreach ($types as $slug => $config) {
            register_post_type($slug, [
                'labels' => ['name' => $config[0], 'singular_name' => $config[1], 'add_new_item' => 'Add ' . $config[1], 'edit_item' => 'Edit ' . $config[1]],
                'public' => true,
                'show_in_rest' => true,
                'menu_icon' => $config[2],
                'supports' => $config[3],
                'has_archive' => $slug !== 'seven_s_sponsor',
                'rewrite' => ['slug' => str_replace('seven_s_', '', $slug)],
            ]);
        }
        register_taxonomy('seven_s_gallery_category', 'seven_s_gallery', [
            'label' => 'Gallery Categories', 'public' => true, 'show_in_rest' => true, 'hierarchical' => true,
        ]);
    }

    public static function register_meta_boxes() {
        add_meta_box('seven-s-sponsor-details', 'Sponsor Details', [__CLASS__, 'sponsor_box'], 'seven_s_sponsor', 'normal', 'high');
        add_meta_box('seven-s-event-details', 'Race Details', [__CLASS__, 'event_box'], 'seven_s_event', 'normal', 'high');
        add_meta_box('seven-s-result-details', 'Result Details', [__CLASS__, 'result_box'], 'seven_s_result', 'normal', 'high');
    }

    private static function field($post, $key, $label, $type = 'text', $help = '') {
        $value = get_post_meta($post->ID, $key, true);
        echo '<p><label for="' . esc_attr($key) . '"><strong>' . esc_html($label) . '</strong></label><br>';
        echo '<input style="width:100%;max-width:680px" type="' . esc_attr($type) . '" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
        if ($help) echo '<br><small>' . esc_html($help) . '</small>';
        echo '</p>';
    }

    public static function sponsor_box($post) {
        wp_nonce_field('seven_s_save_meta', 'seven_s_nonce');
        self::field($post, '_seven_s_url', 'Website or Facebook URL', 'url');
        self::field($post, '_seven_s_level', 'Display level', 'text', 'primary, major, or supporting');
        self::field($post, '_seven_s_order', 'Display order', 'number', 'Lower numbers appear first.');
    }
    public static function event_box($post) {
        wp_nonce_field('seven_s_save_meta', 'seven_s_nonce');
        self::field($post, '_seven_s_date', 'Date and time', 'datetime-local');
        self::field($post, '_seven_s_location', 'City, State');
        self::field($post, '_seven_s_event_url', 'Event or track URL', 'url');
    }
    public static function result_box($post) {
        wp_nonce_field('seven_s_save_meta', 'seven_s_nonce');
        self::field($post, '_seven_s_result_date', 'Race date', 'date');
        self::field($post, '_seven_s_finish', 'Feature finish', 'text', 'Example: 1st Place');
        self::field($post, '_seven_s_class', 'Class');
    }

    public static function save_meta($post_id) {
        if (!isset($_POST['seven_s_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['seven_s_nonce'])), 'seven_s_save_meta')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        $fields = ['_seven_s_url', '_seven_s_level', '_seven_s_order', '_seven_s_date', '_seven_s_location', '_seven_s_event_url', '_seven_s_result_date', '_seven_s_finish', '_seven_s_class'];
        foreach ($fields as $field) {
            if (!isset($_POST[$field])) continue;
            $value = wp_unslash($_POST[$field]);
            $clean = strpos($field, 'url') !== false ? esc_url_raw($value) : sanitize_text_field($value);
            update_post_meta($post_id, $field, $clean);
        }
    }

    public static function owner_dashboard() {
        add_menu_page('Seven S Dashboard', 'Seven S Home', 'edit_posts', 'seven-s-dashboard', [__CLASS__, 'render_dashboard'], 'dashicons-flag', 2);
    }

    public static function render_dashboard() {
        $cards = [
            ['Upload Gallery Photos', 'Add race-night, victory-lane, car, team, or behind-the-scenes photos.', 'post-new.php?post_type=seven_s_gallery', 'dashicons-format-gallery'],
            ['Manage Gallery', 'Edit captions, featured images, categories, or remove a photo.', 'edit.php?post_type=seven_s_gallery', 'dashicons-images-alt2'],
            ['Write a Race Report', 'Create a new update or weekly race recap.', 'post-new.php', 'dashicons-edit-page'],
            ['Manage Race Reports', 'Update drafts and published stories.', 'edit.php', 'dashicons-media-document'],
            ['Update Next Race', 'Add or change the next track, date, location, and event link.', 'edit.php?post_type=seven_s_event', 'dashicons-calendar-alt'],
            ['Manage Sponsors', 'Add primary/local partners, logos, links, and display order.', 'edit.php?post_type=seven_s_sponsor', 'dashicons-heart'],
        ];
        echo '<div class="wrap seven-s-admin"><div class="seven-s-admin__hero"><span class="seven-s-admin__mark">7<sup>S</sup></span><div><h1>Seven S Racing</h1><p>Choose what you want to update.</p></div></div><div class="seven-s-admin__grid">';
        foreach ($cards as $card) echo '<a class="seven-s-admin__card" href="' . esc_url(admin_url($card[2])) . '"><span class="dashicons ' . esc_attr($card[3]) . '"></span><strong>' . esc_html($card[0]) . '</strong><small>' . esc_html($card[1]) . '</small></a>';
        echo '</div></div>';
    }

    public static function redirect_owner() {
        if (!is_admin() || wp_doing_ajax() || !current_user_can('edit_posts') || current_user_can('manage_options')) return;
        global $pagenow;
        if ($pagenow === 'index.php') wp_safe_redirect(admin_url('admin.php?page=seven-s-dashboard'));
    }

    public static function owner_admin_styles() {
        echo '<style>.seven-s-admin{max-width:1050px}.seven-s-admin__hero{display:flex;align-items:center;gap:24px;background:#101216;color:#fff;padding:28px;margin:20px 0}.seven-s-admin__hero h1{color:#fff;margin:0}.seven-s-admin__hero p{color:#b9c0ca;margin:5px 0}.seven-s-admin__mark{font:900 64px/1 Arial;color:#ff5a1f;font-style:italic}.seven-s-admin__mark sup{font-size:.36em;color:#2774ff}.seven-s-admin__grid{display:grid;grid-template-columns:repeat(2,minmax(260px,1fr));gap:16px}.seven-s-admin__card{display:grid;grid-template-columns:48px 1fr;gap:5px 14px;background:#fff;border-left:5px solid #ff5a1f;padding:22px;text-decoration:none;box-shadow:0 3px 12px #0001}.seven-s-admin__card .dashicons{grid-row:1/3;font-size:38px;width:48px;height:48px;color:#2774ff}.seven-s-admin__card strong{font-size:18px;color:#101216}.seven-s-admin__card small{color:#505864}@media(max-width:700px){.seven-s-admin__grid{grid-template-columns:1fr}}</style>';
    }

    public static function sponsor_columns($columns) { $columns['seven_s_level'] = 'Level'; $columns['seven_s_order'] = 'Order'; return $columns; }
    public static function sponsor_column_values($column, $post_id) { if ($column === 'seven_s_level') echo esc_html(get_post_meta($post_id, '_seven_s_level', true)); if ($column === 'seven_s_order') echo esc_html(get_post_meta($post_id, '_seven_s_order', true)); }
}

register_activation_hook(__FILE__, ['Seven_S_Core', 'activate']);
Seven_S_Core::boot();
