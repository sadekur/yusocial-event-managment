<?php
namespace Yusocia\YusocialEventManagment;

use Yusocia\YusocialEventManagment\Classes\Trait\Hookable;

class Assets {
    use Hookable;

    public function __construct() {
        $this->action( 'wp_enqueue_scripts', [ $this, 'yem_frontend_assets' ] );
        $this->action( 'admin_enqueue_scripts', [ $this, 'yem_admin_assets' ] );
    }

    public function yem_frontend_assets() {
        wp_enqueue_script(
            'yem-frontend-script',
            YUSOCIAL_EM_ASSETS . 'js/frontend.js',
            ['jquery'],
            filemtime(YUSOCIAL_EM_PATH . 'assets/js/frontend.js'),
            true
        );
        wp_localize_script('yem-frontend-script', 'YUSOCIAL_EM', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'adminurl' => admin_url(),
            'nonce'   => wp_create_nonce('yem-nonce'),
            'error'   => __('Something went wrong', 'yusocial-event-managment'),
        ]);

        // Enqueue frontend styles
        wp_enqueue_style(
            'yem-frontend-style',
            YUSOCIAL_EM_ASSETS . '/css/frontend.css',
            [],
            filemtime(YUSOCIAL_EM_PATH . 'assets/css/frontend.css')
        );
    }

    public function yem_admin_assets() {
        wp_enqueue_script(
            'yem-admin-script',
            YUSOCIAL_EM_ASSETS . '/js/admin.js',
            ['jquery'],
            filemtime(YUSOCIAL_EM_PATH . 'assets/js/admin.js'),
            true
        );

        wp_localize_script('yem-admin-script', 'YUSOCIAL_EM', [
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'adminurl' => admin_url(),
            'ajaxurl'  => admin_url( 'admin-ajax.php' ),
            'apiurl'   => untrailingslashit( rest_url( 'yusocial-event-managment/v1' ) ),
            'error'    => __( 'Something went wrong', 'yusocial-event-managment' ),
        ]);

        wp_enqueue_style(
            'yem-admin-style',
            YUSOCIAL_EM_ASSETS . '/css/admin.css',
            [],
            filemtime(YUSOCIAL_EM_PATH . 'assets/css/admin.css')
        );
    }
}
