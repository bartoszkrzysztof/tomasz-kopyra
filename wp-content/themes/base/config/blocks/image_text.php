<?php 
return [
    'enabled' => true,
    'callback' => \App\Blocks\TextImageBlock::class,
    'block_definition' => [
        'name' => 'imagetext',
        'title' => 'Obraz + tekst',
        'description' => 'Blok z obrazkiem i tekstem',
        'category' => 'theme',
        'icon' => 'align-pull-left',
        'keywords' => ['image', 'text', 'content'],
        'mode' => 'edit',
        'supports' => [
            'align' => false,
            'anchor' => true,
            'customClassName' => true,
            'jsx' => true,
        ],
    ],
    'fields' => include_once __DIR__ . '/image_text-fields.php',
];