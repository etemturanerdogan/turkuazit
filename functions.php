<?php
// ================================================
// TURKUAZIT THEME CORE
// Custom Header / Footer / Sidebar Overrides
// ================================================


// -----------------------
// 1) CSS dosyamızı yükleyelim
// -----------------------
function turkuazit_enqueue_assets() {
    wp_enqueue_style(
        'turkuazit-ui',
        get_template_directory_uri() . '/assets/css/turkuazit-ui.css',
        array(),
        filemtime(get_template_directory() . '/assets/css/turkuazit-ui.css')
    );
}
add_action('wp_enqueue_scripts', 'turkuazit_enqueue_assets');