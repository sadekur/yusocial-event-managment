<?php
namespace Yusocia\YusocialEventManagment;

/**
 * Ajax handler class
 */
class Ajax {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('wp_ajax_yem_delete_user', [$this, 'yem_ajax_delete_user']);
        add_action('wp_ajax_nopriv_yem_ajax_register', [$this, 'yem_handle_ajax_registration']);
    }

    /**
     * Handle user registration via AJAX
     */
    public function yem_handle_ajax_registration() {

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'yem-nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid nonce', 'yusocial-event-managment' ) ] );
            return;
        }
        $username = sanitize_user($_POST['yem_username']);
        $email    = sanitize_email($_POST['yem_email']);
        $password = $_POST['yem_password'];
    
        $errors = new \WP_Error();
    
        if (username_exists($username)) {
            $errors->add('username_exists', 'Username already exists');
        }
        if (!is_email($email) || email_exists($email)) {
            $errors->add('email_invalid', 'Invalid or existing email');
        }
    
        if (!empty($errors->errors)) {
            $messages = '';
            foreach ($errors->get_error_messages() as $error) {
                $messages .= '<p>' . esc_html($error) . '</p>';
            }
            wp_send_json_error($messages);
        }
    
        $user_id = wp_create_user($username, $password, $email);
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        wp_send_json_success(['redirect' => site_url('/profile')]);
    }

    /**
     * Handle user deletion via AJAX
     */
    public function yem_ajax_delete_user() {
        if ( ! current_user_can('administrator') ) {
            wp_send_json_error(['message' => 'Unauthorized user']);
        }

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'yem-nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid nonce', 'yusocial-event-managment' ) ] );
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/user.php';

        $result = wp_delete_user( $user_id );

        if ( $result ) {
            wp_send_json_success( [ 'message' => __( 'User deleted', 'yusocial-event-managment' ) ] );
        } else {
            wp_send_json_error( [ 'message' => __( 'Failed to delete user', 'yusocial-event-managment' ) ] );
        }
    }
}
