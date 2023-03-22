<?php

require_once('../../../wp-load.php');

$is_template = isset($_GET['template']) && $_GET['template'] === 'true';

$colors = ACOS::get_custom_colors();

if(empty($colors)){
    exit;
}

if($is_template){
    $colors = array('$1', '$2', '$3', '$4', '$5', '$6', '$7');
}

$color_replacements = [];
$counter = 0;
$default_colors = ACOS::get_default_colors();

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
header('Cache-Control: public, max-age=86400');
echo $css;

//function generate_custom_color_scheme_css($colors = null) {
//
//    $is_template = isset($_GET['template']) && $_GET['template'] === 'true';
//
//    $colors = ACOS::get_custom_colors();
//
//    if(empty($colors)){
//        exit;
//    }
//
//    if($is_template){
//        $colors = array('$1', '$2', '$3', '$4', '$5', '$6', '$7');
//    }
//
//    $color_replacements = [];
//    $counter = 0;
//    $default_colors = ACOS::get_default_colors();
//
//    foreach ((array) $default_colors as $dc) {
//        $color_replacements[$dc] = $colors[$counter];
//        $counter++;
//    }
//
//    $input_css = ABSPATH . 'wp-admin/css/colors/blue/colors.min.css';
//    $css = file_get_contents($input_css);
//
//    foreach ($color_replacements as $search => $replace) {
//        $css = str_replace($search, $replace, $css);
//    }
//
//    return $css;
//}
//
////header('Content-Type: text/css');
////header('Cache-Control: public, max-age=86400'); // Cache for 1 day
//
//header('Content-Type: text/css; charset=utf-8');
//header('Cache-Control: public, max-age=86400');
//echo acos_generate_custom_scheme(ACOS::get_custom_colors());


