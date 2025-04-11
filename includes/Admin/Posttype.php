<?php
namespace Yusocia\YusocialEventManagment\Admin;

class Posttype {
    public function __construct() {
        add_action('init', [$this, 'yem_register_event_post_type']);
        add_action('add_meta_boxes', [$this, 'yem_add_event_meta_boxes']);
        add_action('save_post', [$this, 'yem_save_event_meta']);
        add_filter('use_block_editor_for_post_type', [$this, 'yem_disable_gutenberg_for_events'], 10, 2);
    }

    public function yem_disable_gutenberg_for_events($use_block_editor, $post_type) {
        if ($post_type === 'event') {
            return false;
        }
        return $use_block_editor;
    }
    

    public function yem_register_event_post_type() {
        register_post_type('event', [
            'labels' => [
                'name' => __('Events'),
                'singular_name' => __('Event'),
                'add_new' => __('Add New'),
                'add_new_item' => __('Add New Event'),
                'edit_item' => __('Edit Event'),
                'new_item' => __('New Event'),
                'view_item' => __('View Event'),
                'search_items' => __('Search Events'),
                'not_found' => __('No events found'),
                'not_found_in_trash' => __('No events found in trash'),
                'all_items' => __('All Events'),
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'events'],
            'supports' => ['title', 'editor', 'thumbnail', 'author'],
            'show_in_rest' => true,
            'rest_base' => 'events',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'menu_icon' => 'dashicons-calendar',
        ]);        
    }

    public function yem_add_event_meta_boxes() {
        add_meta_box(
            'yem_event_details',
            __('Event Details', 'yusocial-event-management'),
            [$this, 'yem_render_event_meta_box'],
            'event',
            'normal',
            'default'
        );
    }

    public function yem_render_event_meta_box($post) {
        $datetime = get_post_meta($post->ID, 'event_datetime', true);
        $location = get_post_meta($post->ID, 'event_location', true);
        $added_by = get_post_meta($post->ID, 'event_added_by', true);
    
        // Get all users
        $users = get_users(); // No role filter, fetches everyone
    
        wp_nonce_field('yem_save_event_meta', 'yem_event_nonce');
        ?>
        <p>
            <label for="event_datetime"><strong>Date & Time:</strong></label><br>
            <input type="datetime-local" id="event_datetime" name="event_datetime" value="<?php echo esc_attr($datetime); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="event_location"><strong>Location:</strong></label><br>
            <input type="text" id="event_location" name="event_location" value="<?php echo esc_attr($location); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="event_added_by"><strong>Added By (User):</strong></label><br>
            <select id="event_added_by" name="event_added_by" style="width:100%;">
                <option value=""><?php _e('-- Select User --', 'yusocial-event-management'); ?></option>
                <?php foreach ($users as $user) : ?>
                    <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($added_by, $user->ID); ?>>
                        <?php echo esc_html($user->display_name . ' (' . $user->user_email . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }
    

    public function yem_save_event_meta($post_id) {
        // Security checks
        if (!isset($_POST['yem_event_nonce']) || !wp_verify_nonce($_POST['yem_event_nonce'], 'yem_save_event_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save meta fields
        if (isset($_POST['event_datetime'])) {
            update_post_meta($post_id, 'event_datetime', sanitize_text_field($_POST['event_datetime']));
        }
        if (isset($_POST['event_location'])) {
            update_post_meta($post_id, 'event_location', sanitize_text_field($_POST['event_location']));
        }
        if (isset($_POST['event_added_by'])) {
            update_post_meta($post_id, 'event_added_by', sanitize_text_field($_POST['event_added_by']));
        }
    }
}
