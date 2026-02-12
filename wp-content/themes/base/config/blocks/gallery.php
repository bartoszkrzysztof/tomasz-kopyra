<?php 
return [
    'enabled' => true,
    'callback' => \App\Blocks\GalleryBlock::class,
    'block_definition' => [
        'name' => 'gallery',
        'title' => 'Galeria',
        'description' => '',
        'category' => 'theme',
        'icon' => 'grid-view',
        'keywords' => ['gallery', 'images', 'content'],
        'mode' => 'edit',
        'supports' => [
            'align' => false,
            'anchor' => true,
            'customClassName' => true,
            'jsx' => true,
        ],
    ],
    'fields' => include_once __DIR__ . '/gallery-fields.php',
];