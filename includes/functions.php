<?php
function yem_find_dashboard_page_url() {
    $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ]);

    foreach ($pages as $page) {
        if ( has_shortcode( $page->post_content, 'yem_user_dashboard' ) ) {
            return get_permalink($page->ID);
        }
    }

    // fallback to home if not found
    return home_url();
}
