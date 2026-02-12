<?php 
return [
    'enabled' => true,
    'callback' => \App\Blocks\TextEditorBlock::class,
    'block_definition' => [
        'name' => 'texteditor',
        'title' => 'Edytor tekstowy',
        'description' => '',
        'category' => 'theme',
        'icon' => 'editor-alignleft',
        'keywords' => ['texteditor', 'content'],
        'mode' => 'edit',
        'supports' => [
            'align' => false,
            'anchor' => true,
            'customClassName' => true,
            'jsx' => true,
        ],
    ],
    'fields' => include_once __DIR__ . '/text_editor-fields.php',
];