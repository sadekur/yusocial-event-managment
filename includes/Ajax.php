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
        // if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'yem-nonce' ) ) {
        //     wp_send_json_error( [ 'message' => __( 'Invalid nonce', 'yusocial-event-managment' ) ] );
        //     return;
        // }

        require_once ABSPATH . 'wp-admin/includes/user.php';

        $result = wp_delete_user( $user_id );

        if ( $result ) {
            wp_send_json_success( [ 'message' => __( 'User deleted', 'yusocial-event-managment' ) ] );
        } else {
            wp_send_json_error( [ 'message' => __( 'Failed to delete user', 'yusocial-event-managment' ) ] );
        }
    }
}
