<?php
namespace Yusocia\YusocialEventManagment\Frontend;
use Yusocia\YusocialEventManagment\Classes\Helper\Utility; 

class Shortcode {
    public function __construct() {
        add_shortcode( 'yem_user_registration', [$this, 'yem_render_registration_form'] );
        add_shortcode( 'yem_user_login', [$this, 'yem_render_login_form'] );
        add_shortcode( 'yem_user_profile', [$this, 'yem_render_profile_form'] );
        add_shortcode( 'yem_public_profile', [$this, 'yem_render_public_profile'] );
        add_shortcode( 'yem_admin_user_list', [$this, 'yem_render_user_list'] );
        add_shortcode( 'yem_user_dashboard', [$this, 'yem_render_user_dashboard'] );

    }

    public function yem_render_registration_form() {
        ob_start();
        if ( is_user_logged_in() ) {
            echo '<p>You are already logged in.</p>';
        } else {
        ?>
        <form method="post">
            <p><input type="text" name="yem_username" placeholder="Username" required></p>
            <p><input type="email" name="yem_email" placeholder="Email" required></p>
            <p><input type="password" name="yem_password" placeholder="Password" required></p>
            <p><input type="submit" name="yem_register_submit" value="Register"></p>
        </form>
        <?php
        }
        return ob_get_clean();
    }

    public function yem_render_login_form() {
        ob_start();
    
        if ( is_user_logged_in() ) {
            echo '<p>You are already logged in.</p>';
        } else {
            $current_user = wp_get_current_user();
            $is_admin = in_array( 'administrator', (array) $current_user->roles, true );
    
            $redirect_url = $is_admin ? admin_url() : yem_find_dashboard_page_url();
    
            $args = [
                'redirect' => $redirect_url,
                'form_id' => 'yem_loginform',
                'label_username' => __('Username'),
                'label_password' => __('Password'),
                'label_log_in' => __('Login'),
                'remember' => true,
            ];
    
            wp_login_form( $args );
        }
    
        return ob_get_clean();
    }        

    public function yem_render_profile_form() {
        if (!is_user_logged_in()) {
            return '<p>Please log in to view your profile.</p>';
        }

        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        $age = get_user_meta($user_id, 'yem_age', true);
        $bio = get_user_meta($user_id, 'yem_bio', true);
        $profile_picture = get_user_meta($user_id, 'yem_profile_picture', true);

        ob_start(); ?>
        <form method="post" enctype="multipart/form-data">
            <p><input type="text" name="yem_name" value="<?php echo esc_attr($user->display_name); ?>" placeholder="Name" required></p>
            <p><input type="number" name="yem_age" value="<?php echo esc_attr($age); ?>" placeholder="Age"></p>
            <p><textarea name="yem_bio" placeholder="Bio"><?php echo esc_textarea($bio); ?></textarea></p>
            <p><input type="file" name="yem_profile_picture"></p>
            <?php if ($profile_picture): ?>
                <p><img src="<?php echo esc_url($profile_picture); ?>" width="100"></p>
            <?php endif; ?>
            <p><input type="submit" name="yem_profile_submit" value="Update Profile"></p>
        </form>
        <?php
        return ob_get_clean();
    }



    public function yem_render_public_profile() {
        // Utility::pri($_GET['user_id']);
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
        if (!$user_id) {
            return '<p>No user specified.</p>';
        }
    
        $user = get_userdata($user_id);
        if (!$user) {
            return '<p>User not found.</p>';
        }
    
        $age = get_user_meta($user->ID, 'yem_age', true);
        $bio = get_user_meta($user->ID, 'yem_bio', true);
        $profile_picture = get_user_meta($user->ID, 'yem_profile_picture', true);
    
        ob_start(); ?>
        <div class="yem-public-profile">
            <?php if ($profile_picture): ?>
                <img src="<?php echo esc_url($profile_picture); ?>" width="120" />
            <?php endif; ?>
            <h2><?php echo esc_html($user->display_name); ?></h2>
            <p><strong>Age:</strong> <?php echo esc_html($age); ?></p>
            <p><strong>Bio:</strong><br><?php echo nl2br(esc_html($bio)); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function yem_render_user_list() {
        if (!current_user_can('administrator')) {
            return '<p>You are not allowed to view this page.</p>';
        }
    
        $users = get_users();
        ob_start(); ?>
    
        <div id="yem-status-message"></div>
    
        <table border="1" cellpadding="8">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr id="user-row-<?php echo esc_attr($user->ID); ?>">
                        <td><?php echo esc_html($user->display_name); ?></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td>
                            <?php if ($user->ID !== get_current_user_id()): ?>
                                <button class="yem-delete-user-btn" data-user-id="<?php echo esc_attr($user->ID); ?>">
                                    Delete
                                </button>
                            <?php else: ?>
                                (You)
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>    
        <?php
        return ob_get_clean();
    }
    
    public function yem_render_user_dashboard() {
        if (!is_user_logged_in()) {
            return '<p>You must be logged in to view your dashboard.</p>';
        }
    
        $user_id = get_current_user_id();
    
        // Example: Assuming 'event' is a custom post type
        $posted_events = get_posts([
            'post_type' => 'event',
            'author' => $user_id,
            'post_status' => 'publish',
            'fields' => 'ids',
        ]);
        
        // You must have a way to store applied events (e.g., user meta or a custom table)
        $applied_events = get_user_meta($user_id, 'yem_applied_events', true); // Assume it's an array
        if (!is_array($applied_events)) $applied_events = [];
    
        ob_start(); ?>
    
        <div class="yem-dashboard">
            <h2>Welcome to Your Dashboard</h2>
    
            <div class="yem-stats">
                <p><strong>Events You Posted:</strong> <?php echo count($posted_events); ?></p>
                <p><strong>Events You Applied For:</strong> <?php echo count($applied_events); ?></p>
            </div>
    
            <h3>Available Events</h3>
    
            <div class="yem-event-cards" style="display: flex; flex-wrap: wrap; gap: 20px;">
                <?php
                $events = get_posts([
                    'post_type' => 'event',
                    'post_status' => 'publish',
                    'post__not_in' => $posted_events,
                    'posts_per_page' => 10,
                ]);
    
                foreach ($events as $event):
                    $image = get_the_post_thumbnail_url($event->ID, 'medium');
                    $datetime = get_post_meta($event->ID, 'event_datetime', true);
                    $location = get_post_meta($event->ID, 'event_location', true);
                    ?>
                    <div class="event-card" style="border: 1px solid #ccc; padding: 15px; width: 300px;">
                        <?php if ($image): ?>
                            <img src="<?php echo esc_url($image); ?>" alt="" style="width:100%; height:auto;" />
                        <?php endif; ?>
                        <h4><?php echo esc_html($event->post_title); ?></h4>
                        <p><strong>Date/Time:</strong> <?php echo esc_html($datetime); ?></p>
                        <p><?php echo wp_trim_words($event->post_content, 20); ?></p>
                        <p><strong>Location:</strong> <?php echo esc_html($location); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    
        <?php
        return ob_get_clean();
    }
    
}