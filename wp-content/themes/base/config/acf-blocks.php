<?php

/**
 * ACF Blocks configuration
 * 
 * Definicje własnych bloków ACF
 */
$image_text = include_once __DIR__ . '/blocks/image_text.php';
$text_editor = include_once __DIR__ . '/blocks/text_editor.php';
$gallery = include_once __DIR__ . '/blocks/gallery.php';
$paneflow = include_once __DIR__ . '/blocks/paneflow.php';
$animation_double = include_once __DIR__ . '/blocks/animation-double.php';
$animation_accordion = include_once __DIR__ . '/blocks/animation-accordion.php';
$animation_gallery = include_once __DIR__ . '/blocks/animation-gallery.php';
return [
    'texteditor' => $text_editor,
    'imagetext' => $image_text,
    'gallery' => $gallery,
    'paneflowblock' => $paneflow,
    'animation-double' => $animation_double,
    'animation-accordion' => $animation_accordion,
    'animation-gallery' => $animation_gallery,
];