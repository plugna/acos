<?php

require_once('../../../wp-load.php');

$colors = ACOS_Plugin::get_custom_colors();

if(empty($colors)){
    exit;
}

if(isset($_GET['template']) && $_GET['template'] === 'true'){
    $colors = array('$1', '$2', '$3', '$4', '$5', '$6', '$7');
}

$color_replacements = [];
$counter = 0;
$default_colors = ACOS_Plugin::get_default_colors();

foreach ((array) $default_colors as $dc) {
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