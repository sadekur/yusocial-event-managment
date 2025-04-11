<?php
namespace Yusocia\YusocialEventManagment\Frontend;
use Yusocia\YusocialEventManagment\Classes\Helper\Utility; 

class Shortcode {
    public function __construct() {
        add_shortcode( 'yem_user_registration', [$this, 'yem_render_registration_form'] );
        add_action( 'init', [$this, 'yem_handle_registration'] );
        add_shortcode( 'yem_user_login', [$this, 'yem_render_login_form'] );
        add_shortcode( 'yem_user_profile', [$this, 'yem_render_profile_form'] );
        add_action( 'init', [$this, 'yem_handle_profile_update'] );
        add_shortcode( 'yem_public_profile', [$this, 'yem_render_public_profile'] );
        add_shortcode('yem_admin_user_list', [$this, 'yem_render_user_list']);
        add_action('admin_post_yem_delete_user', [$this, 'yem_handle_user_deletion']);

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

    public function yem_render_login_form() {
        ob_start();
        if ( is_user_logged_in() ) {
            echo '<p>You are already logged in.</p>';
        } else {
            $args = [
                'redirect' => site_url('/profile'),
                'form_id' => 'yem_loginform',
                'label_username' => __('Username'),
                'label_password' => __('Password'),
                'label_log_in' => __('Login'),
                'remember' => true,
            ];
            wp_login_form($args);
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
    
    
    public function yem_handle_user_deletion() {
        if (!current_user_can('administrator')) {
            wp_die('Unauthorized user');
        }
    
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        if (!$user_id || !wp_verify_nonce($_POST['_wpnonce'], 'yem_delete_user_' . $user_id)) {
            wp_die('Invalid request');
        }
    
        require_once(ABSPATH . 'wp-admin/includes/user.php');
        wp_delete_user($user_id);
    
        wp_redirect(wp_get_referer());
        exit;
    }
    
}