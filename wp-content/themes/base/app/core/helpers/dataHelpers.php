<?php
if (!function_exists('get_lotka_logo')) {
    function get_lotka_logo($white = false): string
    {
        $frontPageId = intval(get_option('page_on_front'));
        $key = $white ? 'logo_white' : 'logo';
        $logoId = get_field($key, $frontPageId);
        
        return $logoId ?? '';
    }
}
if (!function_exists('get_lotka_colors')) {
    function get_lotka_colors(): string
    {
        $frontPageId = intval(get_option('page_on_front'));
        $colors = get_field('colors', $frontPageId);
        
        return $colors ?? 'studio';
    }
}
if (!function_exists('get_lotka_copyright')) {
    function get_lotka_copyright(): string
    {
        $frontPageId = intval(get_option('page_on_front'));
        $colors = get_field('copyright', $frontPageId);
        
        return $colors ?? 'Lotka';
    }
}