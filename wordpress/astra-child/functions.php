<?php

function mi_tema_hijo_styles() {
    wp_enqueue_style(
        'tema-padre',
        get_template_directory_uri() . '/style.css'
    );

    wp_enqueue_style(
        'tema-hijo',
        get_stylesheet_directory_uri() . '/style.css',
        array('tema-padre'),
        wp_get_theme()->get('Version')
    );
}

add_action('wp_enqueue_scripts', 'mi_tema_hijo_styles');
