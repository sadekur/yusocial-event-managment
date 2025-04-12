<?php
namespace Yusocia\YusocialEventManagment\Frontend;

class Front {
    public function __construct() {
        add_action( 'init', [$this, 'yem_handle_profile_update'] );
    }

    public function yem_handle_profile_update() {
        if (!is_user_logged_in() || !isset($_POST['yem_profile_submit'])) return;

        $user_id = get_current_user_id();

        wp_update_user([
            'ID' => $user_id,
            'display_name' => sanitize_text_field($_POST['yem_name']),
        ]);

        update_user_meta($user_id, 'yem_age', sanitize_text_field($_POST['yem_age']));
        update_user_meta($user_id, 'yem_bio', sanitize_textarea_field($_POST['yem_bio']));

        if (!empty($_FILES['yem_profile_picture']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            $upload = wp_handle_upload($_FILES['yem_profile_picture'], ['test_form' => false]);
            if (!isset($upload['error'])) {
                update_user_meta($user_id, 'yem_profile_picture', esc_url_raw($upload['url']));
            }
        }

        wp_redirect(site_url('/profile'));
        exit;
    }
    
}