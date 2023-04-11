<?php
/*
    Plugin Name: ACOS - Custom Admin Color Scheme
    Plugin URI: https://github.com/plugna/acos
    Description: Adds a custom color scheme to the user profile section below the default admin color schemes.
    Version: 1.2
    Author: Plugna
    Author URI: https://plugna.com/
    License: GPLv2 or later
    Text Domain: acos
*/

class ACOS_Plugin
{
    private static $colors_meta_key = 'acos_colors';
    private static $version_meta_key = 'acos_version';
    private static $nonce_action = 'acos_nonce';

    private static $version = '1.2';

    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('show_user_profile', array($this, 'add_color_scheme_field'));
        add_action('edit_user_profile', array($this, 'add_color_scheme_field'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_dynamic_script'), 100);
        //add_action( 'wp_enqueue_scripts', array($this, 'enqueue_dynamic_script'), 100 ); //enable for public styles
        add_action('personal_options_update', array($this, 'save_color_scheme')); //on update
        add_action('edit_user_profile_update', array($this, 'save_color_scheme')); //on update
        add_filter( 'query_vars', array($this, 'query_vars') );
        add_action('init', array($this, 'add_rewrite_rule') );
        add_action('template_redirect', array($this, 'generate_css') );
    }


    public function add_rewrite_rule(){
        add_rewrite_endpoint( 'acos_css', EP_ROOT );
    }

    public function query_vars($qvars){
        $qvars[] = 'acos_css';
        return $qvars;
    }

    public function generate_css(){
        global $wp_query;

        if ( isset( $wp_query->query_vars['acos_css'] ) ) {
            $this->print_css();
            exit;
        }
    }

    public function print_css(){

        $colors = self::get_custom_colors();

        if (empty($colors)) {
            exit;
        }

        if (isset($_GET['template']) && $_GET['template'] === 'true') {
            $colors = array('$1', '$2', '$3', '$4', '$5', '$6', '$7');
        }

        $color_replacements = [];
        $counter = 0;
        $default_colors = self::get_default_colors();

        foreach ((array)$default_colors as $dc) {
            $color_replacements[$dc] = $colors[$counter];
            $counter++;
        }

        $input_css = ABSPATH . 'wp-admin/css/colors/blue/colors.min.css';
        $css = file_get_contents($input_css);

        foreach ($color_replacements as $search => $replace) {
            $css = str_replace($search, $replace, $css);
        }

        header('Content-Type: text/css; charset=utf-8');
        header('Cache-Control: public, max-age=31536000'); // 1 year cache

        echo wp_kses_post($css);

    }


    /**
     * Get the default colors from 'blue' color scheme
     *
     * $scheme-name: "blue";
     * $base-color: #52accc;
     * $icon-color: #e5f8ff;
     * $highlight-color: #096484;
     * $notification-color: #e1a948;
     * $button-color: #e1a948;
     *
     * $menu-submenu-text: #e2ecf1;
     * $menu-submenu-focus-text: #fff;
     * $menu-submenu-background: #4796b3;
     * @return string[]
     */
    public static function get_default_colors()
    {
        return array('#52accc', '#e5f8ff', '#096484', '#e1a948', '#e3af55', '#e2ecf1', '#4796b3');
    }

    public function get_acos_css_url(){
        return site_url() . '?acos_css';
    }

    public function enqueue_dynamic_script()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $userId = get_current_user_id();
        if (!empty($userId)) {
            $version = get_user_meta($userId, self::$version_meta_key, true);
            if (!empty($version)) {
                wp_enqueue_style('acos-dynamic', $this->get_acos_css_url(), array(), $userId . '-' . $version);
            }
        }
    }

    public function enqueue_scripts($hook)
    {
        if ('profile.php' !== $hook && 'user-edit.php' !== $hook) return;

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('acos', plugin_dir_url(__FILE__) . 'css/acos.css', array(), self::$version);

        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('acos', plugin_dir_url(__FILE__) . 'js/acos.js', array('jquery', 'wp-color-picker'), self::$version, true);

        wp_localize_script('acos', 'acos_data', array(
            'security' => wp_create_nonce(self::$nonce_action),
            'acos_css_template_url' => $this->get_acos_css_url() . '&template=true&v=' . self::$version,
        ));
    }

    public function add_color_scheme_field($user)
    {
        $color_scheme = get_user_meta($user->ID, self::$colors_meta_key, true);
        $has_custom_scheme = !empty($color_scheme);

        $colors = json_decode($color_scheme, true);
        if (!is_array($colors)) {
            $colors = ['', '', '', '', '', '', ''];
        }

        ?>
        <table class="form-table" style="display:none;">
            <tr id="acos-row" class="acos-section">
                <th scope="row">Custom Admin Color Scheme</th>
                <td>
                    <label for="enable_acos" id="enable_acos_label">
                        <input
                                type="checkbox"
                                id="enable_acos"
                                name="enable_acos"
                                class="acos-toggle"
                            <?php echo $has_custom_scheme ? 'checked="checked"' : ''; ?>
                                value="true">
                        Enable
                    </label>&nbsp;
                    <?php for ($i = 1; $i <= 7; $i++) : ?>
                        <?php if (!isset($colors[$i - 1])) {
                            continue;
                        } ?>
                        <input type="text"
                               class="acos-picker"
                               id="acos_color_<?php echo esc_attr($i); ?>"
                               name="acos_color_<?php echo esc_attr($i); ?>"
                               value="<?php echo esc_attr($colors[$i - 1]); ?>"/>
                    <?php endfor; ?>
                </td>
            </tr>
        </table>
        <?php
    }


    public function save_color_scheme($user_id)
    {
        if (isset($_POST['acos_color_1'])) {
            $color_scheme = array();
            for ($i = 1; $i <= 7; $i++) {
                $color_scheme[] = sanitize_text_field($_POST['acos_color_' . $i]);
            }
            $color_scheme = json_encode($color_scheme);
        }

        if ($user_id) {

            // Increment the version number
            $css_version = (int) get_user_meta($user_id, self::$version_meta_key, true);
            update_user_meta($user_id, self::$version_meta_key, $css_version + 1);

            if (isset($_POST['enable_acos']) && $_POST['enable_acos'] == 'true') {
                update_user_meta($user_id, self::$colors_meta_key, $color_scheme);
            } else {
                delete_user_meta($user_id, self::$colors_meta_key);
            }
        }
    }

    public static function get_custom_colors()
    {
        $user_id = get_current_user_id();
        $color_scheme = get_user_meta($user_id, self::$colors_meta_key, true);

        if (!$color_scheme) {
            return [];
        }

        return json_decode($color_scheme, true);
    }

}

new ACOS_Plugin();