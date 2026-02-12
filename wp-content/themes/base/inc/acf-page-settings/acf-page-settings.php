<?php

// Dodaj stronę opcji ACF
add_action('acf/init', function() {
    if( function_exists('acf_add_options_page') ) {
        
        acf_add_options_page(array(
            'page_title'    => 'Ustawienia motywu',
            'menu_title'    => 'Ustawienia motywu',
            'menu_slug'     => 'theme-general-settings',
            'capability'    => 'edit_posts',
            'redirect'      => false
        ));
        
        // Dodaj podstronę stopka
        acf_add_options_sub_page(array(
            'page_title'    => 'Ustawienia stopki',
            'menu_title'    => 'Stopka',
            'parent_slug'   => 'theme-general-settings',
        ));
        
        
        // Dodaj podstronę lista obrazów
        acf_add_options_sub_page(array(
            'page_title'    => 'Ustawienia listy',
            'menu_title'    => 'Ustawienia listy',
            'parent_slug'   => 'edit.php?post_type=paint',
        ));
    }
});