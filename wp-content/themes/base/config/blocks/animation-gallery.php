<?php 
return [
    'enabled' => true,
    'callback' => \App\Blocks\AnimationGalleryBlock::class,
    'block_definition' => [
        'name' => 'animation-gallery',
        'title' => 'Animacja - galeria',
        'description' => '',
        'category' => 'theme',
        'icon' => 'screenoptions',
        'keywords' => ['animation', 'gallery', 'content'],
        'mode' => 'edit',
        'supports' => [
            'align' => false,
            'anchor' => true,
            'customClassName' => true,
            'jsx' => true,
        ],
    ],
    'fields' => include_once __DIR__ . '/animation-gallery-fields.php',
];