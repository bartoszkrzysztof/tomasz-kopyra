<?php 
return [
    'enabled' => true,
    'callback' => \App\Blocks\PaneFlowBlock::class,
    'block_definition' => [
        'name' => 'paneflow',
        'title' => 'PaneFlow',
        'description' => '',
        'category' => 'theme',
        'icon' => 'format-gallery',
        'keywords' => ['paneflow', 'content'],
        'mode' => 'edit',
        'supports' => [
            'align' => false,
            'anchor' => true,
            'customClassName' => true,
            'jsx' => true,
        ],
    ],
    'fields' => include_once __DIR__ . '/paneflow-fields.php',
];