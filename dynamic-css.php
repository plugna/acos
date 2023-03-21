<?php
require_once('../../../wp-load.php');

function generate_custom_color_scheme_css($colors = null) {
    $user_id = get_current_user_id();

    $color_scheme = get_user_meta($user_id, 'custom_admin_color_scheme', true);
    if(!$colors) {
        $colors = json_decode($color_scheme, true);
    }

    if (!is_array($colors) || count($colors) != 4) {
        $colors = array('#333333', '#0073aa', '#0073aa', '#00a0d2');
    }

    $color_replacements = array(
        '#52accc' => $colors[0],
        '#e5f8ff' => $colors[1],
        '#096484' => $colors[2],
        '#e1a948' => $colors[3]
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


