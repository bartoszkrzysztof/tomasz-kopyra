<?php

/**
 * ACF Blocks configuration
 * 
 * Definicje własnych bloków ACF
 */
$image_text = include_once __DIR__ . '/blocks/image_text.php';
$text_editor = include_once __DIR__ . '/blocks/text_editor.php';
$gallery = include_once __DIR__ . '/blocks/gallery.php';
return [
    'texteditor' => $text_editor,
    'imagetext' => $image_text,
    'gallery' => $gallery,
];