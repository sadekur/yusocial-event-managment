<?php
namespace Yusocia\YusocialEventManagment\Frontend;

class Front {
    public function __construct() {
        add_action( 'init', [$this, 'yem_handle_registration'] );
        add_action( 'init', [$this, 'yem_handle_profile_update'] );
    }
    public function yem_handle_registration() {
        if ( isset( $_POST['yem_register_submit'] ) ) {
            $username = sanitize_user( $_POST['yem_username'] );
            $email = sanitize_email( $_POST['yem_email'] );
            $password = $_POST['yem_password'];

            $errors = new \WP_Error();

            if ( username_exists( $username ) ) {
                $errors->add('username_exists', 'Username already exists');
            }
            if (!is_email($email) || email_exists($email)) {
                $errors->add('email_invalid', 'Invalid or existing email');
            }

            if (empty($errors->errors)) {
                $user_id = wp_create_user($username, $password, $email);
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                wp_redirect(site_url('/profile'));
                exit;
            } else {
                foreach ($errors->get_error_messages() as $error) {
                    echo '<p style="color:red;">' . esc_html($error) . '</p>';
                }
            }
        }
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