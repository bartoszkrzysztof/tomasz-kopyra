<?php 
return [
    'enabled' => true,
    'callback' => \App\Blocks\AnimationDoubleBlock::class,
    'block_definition' => [
        'name' => 'animation-double',
        'title' => 'Animacja - sekcja podwójna',
        'description' => '',
        'category' => 'theme',
        'icon' => 'align-left',
        'keywords' => ['animation', 'double', 'content'],
        'mode' => 'edit',
        'supports' => [
            'align' => false,
            'anchor' => true,
            'customClassName' => true,
            'jsx' => true,
        ],
    ],
    'fields' => include_once __DIR__ . '/animation-double-fields.php',
];