<?php
require_once('../../../wp-load.php');

function generate_custom_color_scheme_css($colors = null) {

    $is_template = isset($_GET['template']) && $_GET['template'] === 'true';

    if($is_template){
        header('Content-Type: text/css; charset=utf-8');
        header('Cache-Control: public, max-age=86400');

        $colors = $is_template ? array('$1', '$2', '$3', '$4', '$5', '$6', '$7') : get_custom_colors();
    }else {
        $user_id = get_current_user_id();

        $color_scheme = get_user_meta($user_id, 'custom_admin_color_scheme', true);
        //print_r($colors);die;
        if(!$colors) {
            $colors = json_decode($color_scheme, true);
        }
    }

    if (!is_array($colors) || count($colors) != 7) {
        $colors = array('#096484', '#4796b3', '#52accc', '#74B6CE', '#e5f8ff', '#fff', '#fff');
    }

    $color_replacements = array(
        '#096484' => $colors[0],
        '#4796b3' => $colors[1],
        '#52accc' => $colors[2],
        '#74B6CE' => $colors[3],
        '#e5f8ff' => $colors[4],
        //'#fff' => $colors[5],
        //'#fff' => $colors[6],
//        $_wp_admin_css_colors['blue']->colors[0] => $colors[0],
//        $_wp_admin_css_colors['blue']->colors[1] => $colors[1],
//        $_wp_admin_css_colors['blue']->colors[2] => $colors[2],
//        $_wp_admin_css_colors['blue']->colors[3] => $colors[3],
//        $_wp_admin_css_colors['blue']->icon_colors['base'] => $colors[4],
//        $_wp_admin_css_colors['blue']->icon_colors['focus'] => $colors[5],
//        $_wp_admin_css_colors['blue']->icon_colors['current'] => $colors[6],
    );

    $input_css = ABSPATH . 'wp-admin/css/colors/blue/colors.min.css';
    $css = file_get_contents($input_css);

    foreach ($color_replacements as $search => $replace) {
        $css = str_replace($search, $replace, $css);
    }



    return $css;
}

//header('Content-Type: text/css');
//header('Cache-Control: public, max-age=86400'); // Cache for 1 day

header('Content-Type: text/css');
echo generate_custom_color_scheme_css(get_custom_colors());


