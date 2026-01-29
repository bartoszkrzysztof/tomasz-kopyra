<?php 
return [
    'enabled' => true,
    // 'callback' => \App\Blocks\BoxesListBlock::class,
    'block_definition' => [
        'name' => 'boxes-list',
        'title' => 'Boxes List',
        'description' => 'Lista boxów z zawartością',
        'category' => 'theme',
        'icon' => 'list-view',
        'keywords' => ['boxes', 'list', 'content'],
        'mode' => 'edit',
        'supports' => [
            'align' => false,
            'anchor' => true,
            'customClassName' => true,
            'jsx' => true,
        ],
    ],
    'fields' => include_once __DIR__ . '/boxes-list-fields.php',
];