<?php
use App\Core\Blocks\Helpers;

if (!function_exists('lotka_block_wrapper')) {
    function lotka_block_wrapper(?array $block, $exclude = [], $wrapperElem = 'div', $wrapperClasses = []): array
    {
        $customClasses = ($wrapperClasses) ? implode(' ', $wrapperClasses) : '';
        $wrapper = [
            'start' => '',
            'end' => '',
        ];

        if (!$block) {
            return $wrapper;
        }
        
        // excluded 'classes', 'id', 'styles', 'wrapper'

        $wrapper_start = '<' . $wrapperElem;
        $wrapper_end = '</' . $wrapperElem . '>';
        if (!in_array('classes', $exclude)) {
            $classes = lotka_get_classes($block['attrs'] ?? [], $block['blockName'] ?? '');
            if ($classes) {
                $wrapper_start .= ' class="' . $classes . ' ' . $customClasses . '"';
            }
        }
        if (!in_array('id', $exclude)) {
            $id = lotka_get_block_id($block);
            if ($id) {
                $wrapper_start .= ' id="' . $id . '"';
            }
        }
        if (!in_array('styles', $exclude)) {
            $styles = lotka_get_styles($block['attrs'] ?? []);
            if ($styles) {
                $wrapper_start .= ' ' . $styles;
            }
        }
        $wrapper_start .= '>';

        if (!in_array('wrapper', $exclude)) {
            $wrap = lotka_get_wrapper($block['attrs'] ?? [], ['container'], $wrapperElem);
            $wrapper['start'] = $wrap['start'] .  $wrapper_start ?? $wrapper_start;
            $wrapper['end'] = $wrapper_end . $wrap['end'] ?? $wrapper_end;
        }
        else {
            $wrapper['start'] = $wrapper_start;
            $wrapper['end'] = $wrapper_end;
        }

        return $wrapper;
    }
}

if (!function_exists('lotka_render_inner_blocks')) {
    function lotka_render_inner_blocks(?array $block): string
    {
        $helpers = new Helpers();
        return $helpers->renderInnerBlock($block);
    }
}