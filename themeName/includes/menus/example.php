<?php

function example_main_nav() {
    global $lang;
    $menu = array(
        'theme_location'  => 'header-menu',
        'container'       => 'nav',
        'menu'            => 'main',
        // 'container_class' => '',
        // 'container_id'    => '',
        // 'menu_class'      => '',
        // 'menu_id'         => '',
        // 'echo'            => true,
        // 'fallback_cb'     => 'wp_page_menu',
        // 'before'          => '',
        // 'after'           => '',
        // 'link_before'     => '',
        // 'link_after'      => '',
        // 'items_wrap'      => '',
        // 'depth'           => 0,
        // 'walker'          =>
    );
    // if ($lang  == "fr_CA" || $lang == "fr") {
    //     $menu["menu"] = "main-fr";
    // }
    
    wp_nav_menu($menu);
}

register_nav_menu('example_main_nav', 'primary site menu'); ?>
