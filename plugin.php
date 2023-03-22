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

class CACOS
{
    private $colors_meta_key = 'custom_admin_color_scheme';
    private $version_meta_key = 'custom_admin_color_scheme_version';
    private $nonce_action = 'save_custom_admin_color_scheme';
    private $color_scheme_handle = 'custom_scheme';
    private $color_scheme_name = 'Custom Color Scheme';

    public function __construct()
    {
        add_action('admin_init', array($this, 'register_color_scheme'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('show_user_profile', array($this, 'add_color_scheme_field'));
        add_action('edit_user_profile', array($this, 'add_color_scheme_field'));
        add_action('wp_ajax_save_custom_admin_color_scheme', array($this, 'save_color_scheme'));
        add_action('admin_init', array($this, 'replace_default_color_scheme'));
    }

    public function get_default_colors(){
        return array('#096484', '#4796b3', '#52accc', '#74B6CE', '#e5f8ff', '#fff', '#fff');
    }

// Register Custom Admin Color Scheme
    public function register_color_scheme()
    {
//        wp_admin_css_color(
//            $this->color_scheme_handle,
//            __($this->color_scheme_name, 'custom-admin-color-scheme'),
//            plugins_url('custom-admin-color-scheme/css/colors.css', __FILE__),
//            $this->get_default_colors()
//        );
    }

    public function enqueue_scripts($hook)
    {
        if ('profile.php' !== $hook) return;

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('custom-admin-color-scheme', plugin_dir_url(__FILE__) . 'css/custom-admin-color-scheme.css');

        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('custom-admin-color-scheme', plugin_dir_url(__FILE__) . 'js/custom-admin-color-scheme.js', array('jquery', 'wp-color-picker'), '', true);

        wp_localize_script('custom-admin-color-scheme', 'custom_admin_color_scheme_data', array(
            'security' => wp_create_nonce($this->nonce_action),
            'plugin_url' => plugin_dir_url(__FILE__),
        ));
    }

    public function add_color_scheme_field($user)
    {
        $color_scheme = get_user_meta($user->ID, $this->colors_meta_key, true);
        $has_custom_scheme = !empty($color_scheme);

        $js = <<<EOT
        <script>
        (function($) {
            $(document).ready(function() {
                console.log('ready');
                const customColorRow = $('#custom_admin_color_scheme_row');
                const adminColorRow = $('tr.user-admin-color-wrap');
                customColorRow.insertAfter(adminColorRow);
                
                const checkbox = $('#enable_cacos');
                const colorSchemeTable = $('#custom_admin_color_scheme_row').closest('.form-table');
                const isCustomSchemeEnabled = checkbox.prop('checked');
                
                if (isCustomSchemeEnabled) {
                    //colorSchemeTable.show();
                    $('body').addClass('cacos-enabled');
                } else {
                    //colorSchemeTable.hide();
                    $('body').removeClass('cacos-enabled');
                }

                checkbox.on('change', function() {
                    if (this.checked) {
                        //colorSchemeTable.show();
                        $('body').addClass('cacos-enabled');
                    } else {
                        //colorSchemeTable.hide();
                        $('body').removeClass('cacos-enabled');
                    }
                });
            });
        })(jQuery);
        </script>
    EOT;

        echo $js;

        $colors = json_decode($color_scheme, true);
        if (!is_array($colors)) {
            $colors = $this->get_default_colors();
        }

        ?>
        <table class="form-table" style="display:none;">
            <tr id="custom_admin_color_scheme_row" class="custom-admin-color-scheme-section">
                <th scope="row">Custom Admin Color Scheme</th>
                <td>
                    <label for="enable_cacos" id="enable_cacos_label">
                        <input
                                type="checkbox"
                                id="enable_cacos"
                                class="custom-admin-color-scheme-toggle"
                                <?php echo $has_custom_scheme ? 'checked="checked"' : ''; ?>
                                value="true">
                        Enable
                    </label>&nbsp;
                    <?php for ($i = 1; $i <= 7; $i++) : ?>
                        <?php if (!isset($colors[$i - 1])) {
                            continue;
                        } ?>
                        <input type="text" class="custom-admin-color-scheme-picker" id="color-<?php echo $i; ?>"
                               value="<?php echo esc_attr($colors[$i - 1]); ?>"/>
                    <?php endfor; ?>
                    <input type="button" id="save_custom_admin_color_scheme" class="button button-primary"
                           value="Save Color Scheme"/>
                </td>
            </tr>
        </table>
        <?php
    }


    public function save_color_scheme()
    {
        check_ajax_referer($this->nonce_action, 'security');
        $user_id = get_current_user_id();
        $color_scheme = sanitize_text_field($_POST['custom_admin_color_scheme']);

        if ($user_id && !empty($color_scheme)) {
            update_user_meta($user_id, $this->colors_meta_key, $color_scheme);

            // Increment the version number
            $css_version = (int)get_user_meta($user_id, $this->version_meta_key, true);
            update_user_meta($user_id, $this->version_meta_key, $css_version + 1);

            wp_send_json_success();
        } else {
            wp_send_json_error('Invalid color scheme or user ID.');
        }

    }

    public function replace_default_color_scheme()
    {
        $colors = $this->get_custom_colors();
        wp_admin_css_color(
            $this->color_scheme_handle,
            __($this->color_scheme_name, 'custom-admin-color-scheme'),
            '',
            array_slice($colors, 0, 4),
            array_slice($colors, 4)
        );

    }

    public function get_custom_colors()
    {
        $user_id = get_current_user_id();
        $color_scheme = get_user_meta($user_id, $this->colors_meta_key, true);

        if (!$color_scheme) {
            return [];
        }

        return json_decode($color_scheme, true);
    }
}

new CACOS();