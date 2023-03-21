<?php
/*
Plugin Name: Custom Admin Color Scheme
Plugin URI: https://github.com/plugna/custom-admin-color-scheme
Description: Adds a custom color scheme to the user profile section in the "Admin Color Scheme" section.
Version: 1.0
Author: Plugna
Author URI: https://plugna.com/
License: GPLv2 or later
Text Domain: custom-admin-color-scheme
*/

// Register Custom Admin Color Scheme
function custom_admin_color_scheme() {
    wp_admin_css_color(
        'custom_scheme',
        __('Custom Color Scheme', 'custom-admin-color-scheme'),
        plugins_url('custom-admin-color-scheme/css/colors.css', __FILE__),
        array('#333333', '#0073aa', '#0073aa', '#00a0d2')
    );
}
add_action('admin_init', 'custom_admin_color_scheme');

function enqueue_color_scheme_css() {
    $user_id = get_current_user_id();
    $css_version = get_user_meta($user_id, 'custom_admin_color_scheme_version', true);
    wp_enqueue_style('custom-color-scheme', plugin_dir_url(__FILE__) . 'dynamic-css.php', array(), $css_version);
}
add_action('admin_enqueue_scripts', 'enqueue_color_scheme_css');


// Enqueue scripts and styles
function custom_admin_color_scheme_enqueue_scripts($hook) {
    if ('profile.php' !== $hook) return;

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_style('custom-admin-color-scheme', plugin_dir_url(__FILE__) . 'css/custom-admin-color-scheme.css');

    wp_enqueue_script('wp-color-picker');
    wp_enqueue_script('custom-admin-color-scheme', plugin_dir_url(__FILE__) . 'js/custom-admin-color-scheme.js', array('jquery', 'wp-color-picker'), '', true);

    wp_localize_script('custom-admin-color-scheme', 'custom_admin_color_scheme_data', array(
        'security' => wp_create_nonce('save_custom_admin_color_scheme'),
        'plugin_url' => plugin_dir_url(__FILE__),
    ));
}
add_action('admin_enqueue_scripts', 'custom_admin_color_scheme_enqueue_scripts');



function custom_admin_color_scheme_field($user) {
    $color_scheme = get_user_meta($user->ID, 'custom_admin_color_scheme', true);

    // JavaScript code to move the custom color scheme section
    $js = <<<EOT
    <script>
    (function($) {
        $(document).ready(function() {
            const customColorRow = $('#custom_admin_color_scheme_row');
            const adminColorRow = $('input[name=admin_color]').closest('tr');
            customColorRow.insertAfter(adminColorRow);
        });
    })(jQuery);
    </script>
EOT;

    echo $js;

    $colors = json_decode($color_scheme, true);
    if (!is_array($colors)) {
        $colors = array('#333333', '#0073aa', '#0073aa', '#00a0d2');
    }

    ?>
    <table class="form-table" style="display:none;">
        <tr id="custom_admin_color_scheme_row">
            <th scope="row">Custom Admin Color Scheme</th>
            <td>
                <?php for ($i = 1; $i <= 4; $i++) : ?>
                    <input type="text" class="custom-admin-color-scheme-picker" id="color-<?php echo $i; ?>" value="<?php echo esc_attr($colors[$i - 1]); ?>" />
                <?php endfor; ?>
                <input type="button" id="save_custom_admin_color_scheme" class="button button-primary" value="Save Color Scheme" />
            </td>
        </tr>
    </table>
    <?php
}


add_action('show_user_profile', 'custom_admin_color_scheme_field');
add_action('edit_user_profile', 'custom_admin_color_scheme_field');

function custom_admin_color_scheme_dynamic_css() {
    if (isset($_GET['dynamic-css']) && $_GET['dynamic-css'] === 'custom-admin-color-scheme') {
        $is_template = isset($_GET['template']) && $_GET['template'] === 'true';

        header('Content-Type: text/css; charset=utf-8');
        header('Cache-Control: public, max-age=86400');

        $colors = $is_template ? array('$1', '$2', '$3', '$4') : get_custom_colors();
        echo generate_dynamic_css($colors);
        exit;
    }
}
add_action('wp_ajax_get_custom_color_scheme_css', 'custom_admin_color_scheme_dynamic_css');




// Save color picker value
function custom_admin_color_scheme_save_color_picker_fieldold($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    update_user_meta($user_id, 'custom_admin_color_scheme', $_POST['custom_admin_color_scheme']);
}
add_action('personal_options_update', 'custom_admin_color_scheme_save_color_picker_field');
add_action('edit_user_profile_update', 'custom_admin_color_scheme_save_color_picker_field');

function custom_admin_color_scheme_ajax_save() {
    check_ajax_referer('save_custom_admin_color_scheme', 'security');
    $user_id = get_current_user_id();
    $color_scheme = sanitize_text_field($_POST['custom_admin_color_scheme']);

    if ($user_id && !empty($color_scheme)) {
        update_user_meta($user_id, 'custom_admin_color_scheme', $color_scheme);

        // Increment the version number
        $css_version = (int) get_user_meta($user_id, 'custom_admin_color_scheme_version', true);
        update_user_meta($user_id, 'custom_admin_color_scheme_version', $css_version + 1);

        wp_send_json_success();
    } else {
        wp_send_json_error('Invalid color scheme or user ID.');
    }
}
add_action('wp_ajax_save_custom_admin_color_scheme', 'custom_admin_color_scheme_ajax_save');


function get_custom_colors() {
    $user_id = get_current_user_id();
    $color_scheme = get_user_meta($user_id, 'custom_admin_color_scheme', true);
    $colors = json_decode($color_scheme, true);

    if (!is_array($colors) || count($colors) != 4) {
        $colors = array('#333333', '#0073aa', '#0073aa', '#00a0d2');
    }

    return $colors;
}

function replace_default_color_scheme() {
    $colors = get_custom_colors();

    wp_admin_css_color(
        'custom_scheme',
        __('Custom Color Scheme', 'custom-admin-color-scheme'),
        '',
        $colors
    );
}
add_action('admin_init', 'replace_default_color_scheme');

function custom_admin_color_scheme_ajax_get_css() {

    check_ajax_referer('save_custom_admin_color_scheme', 'security');
    $colors = json_decode(sanitize_text_field($_POST['colors']));
    print_r($colors);die;

    if (!is_array($colors) || count($colors) != 4) {
        wp_send_json_error('Invalid colors');
    } else {
        require_once(plugin_dir_path(__FILE__) . 'dynamic-css.php');
        $css = generate_custom_color_scheme_css($colors);

        echo $css;die;
        wp_send_json_success($css);
    }
}
add_action('wp_ajax_get_custom_color_scheme_css', 'custom_admin_color_scheme_ajax_get_css');

