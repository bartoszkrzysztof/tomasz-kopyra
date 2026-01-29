<?php
use App\Core\Blocks\Helpers;

if (!function_exists('lotka_get_classes')) {
    function lotka_get_classes(?array $attrs, $blockName = ''): string
    {
        $helpers = new Helpers();
        return $helpers->getElementClasses($attrs, $blockName);
    }
}

if (!function_exists('lotka_get_block_id')) {
    function lotka_get_block_id($block): string
    {
        $helpers = new Helpers();
        return $helpers->getIdFromBlock($block);
    }
}

if (!function_exists('lotka_get_wrapper')) {
    function lotka_get_wrapper($attrs, $classes = [], $elem = 'div'): array
    {
        $helpers = new Helpers();
        return $helpers->getWrapperData($attrs, $classes, $elem);
    }
}

if (!function_exists('lotka_get_styles')) {
    function lotka_get_styles(?array $attrs): string
    {
        $helpers = new Helpers();
        return $helpers->getElementStyles($attrs);
    }
}

if (!function_exists('lotka_render_reorder_fields')) {
    function lotka_render_reorder_fields(array $block, $names = []): array
    {
        error_log(print_r($names, true));

        $fields = [];
        // if (empty($names)) {
        //     return $fields;
        // }

        // foreach ($names as $name) {
        //     if (!empty($name)) {


        //         $fields[$name] = $name;
        //     }
        // }



        return $fields;
    }
}