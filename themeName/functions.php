<?php

////////////////////////////////////////
//	WP Config
////////////////////////////////////////

/**
 * Remove menu options for admin
 *
 */
add_action('admin_menu', 'remove_menus');
function remove_menus(){ // Clean menu items
    remove_menu_page('edit-comments.php'); //Comments
}




////////////////////////////////////////
//	Theme Support
////////////////////////////////////////

/**
 * Image size supports
 *
 * @see don't forget to change that BEFORE you upload images or you'll need to generate thumbnails again.
 */
if (function_exists('add_theme_support')) {
    add_theme_support('post-thumbnails');
    add_image_size('xlarge', 1920, '', true); // Xlarge Thumbnail
    add_image_size('large', 1024, '', true);  // Large Thumbnail
    add_image_size('medium', 680, 383, true); // Medium Thumbnail
    add_image_size('small', 120, '', true);   // Small Thumbnail
}



/**
 * Load scripts in frontend
 *
 * @see we put a timestamp to prevent caching when file is changed.
 * @see WP basicly add his version of jQuery for frontend, but we change jQuery version for the one we need.
 */
add_action('wp_enqueue_scripts', 'custom_scripts');
function custom_scripts() {
    if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
        wp_deregister_script("jquery");
        wp_enqueue_script(
            "jquery",
            "//ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js",
            false,
            true
        ); // Replace wp jQuery version with the one you need
        wp_enqueue_script(
            "vendorsscripts",
            get_template_directory_uri() . "/ressources/dist/js/vendors.min.js",
            array("jquery"),
            filemtime(
                get_stylesheet_directory()."/ressources/dist/js/vendors.min.js"
            ),
            true
        ); // Vendors scripts
        wp_enqueue_script(
            "scripts",
            get_template_directory_uri() . "/ressources/dist/js/scripts.min.js",
            array("jquery"),
            filemtime(
                get_stylesheet_directory()."/ressources/dist/js/scripts.min.js"
            ),
            true
        ); // Custom scripts
    }
}



/**
 * Load styles in frontend
 *
 * @see we put a timestamp to prevent caching when file is changed.
 */
add_action('wp_enqueue_scripts', 'custom_styles');
function custom_styles() {
    if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
        wp_enqueue_style(
            'customstyles',
            get_template_directory_uri() . '/ressources/dist/css/styles.min.css',
            false,
            filemtime(
                get_stylesheet_directory() . '/ressources/dist/css/styles.min.css'
            )
        );
    }
}



/**
 * ACF Options Support
 *
 * @see Uncomment only if you need it && if you have ACF installed
 */
// if(function_exists('acf_add_options_page')) {
//     acf_add_options_page(array(
//         'page_title' 	=> 'Options',
//         'menu_title' 	=> 'Options',
//         'menu_slug' 	=> 'custom-options',
//         'capability' 	=> 'edit_posts',
//         'redirect' 	    => false
//     ));
// }





////////////////////////////////////////
//	Custom Walkers
////////////////////////////////////////
foreach (scandir(dirname('includes/walkers')) as $filename) {
    $path = dirname('includes/walkers') . '/' . $filename;
    if (is_file($path)) {
        require_once $path;
    }
}




////////////////////////////////////////
//	Custom Menus
////////////////////////////////////////
foreach (scandir(dirname('includes/menus')) as $filename) {
    $path = dirname('includes/menus') . '/' . $filename;
    if (is_file($path)) {
        require_once $path;
    }
}





////////////////////////////////////////
//	Custom Post Types
////////////////////////////////////////
foreach (scandir(dirname('includes/post-types')) as $filename) {
    $path = dirname('includes/post-types') . '/' . $filename;
    if (is_file($path)) {
        require_once $path;
    }
}




////////////////////////////////////////
//	Custom Template Partials
////////////////////////////////////////

/**
 * Partials
 *
 * @see Theme helpers for multiple instances of the same element.
 * @param $Partials-> [function name] ( [array of config] );
 */
include('includes/class.partials.php');
$Partials = new Partials();





////////////////////////////////////////
//	Custom Functions
////////////////////////////////////////

/**
 * Pretty_r
 *
 * @see Simple tool for a better view of a PHP print
 */
function pretty_r($var){
    echo "<pre>";
        print_r($var);
    echo "</pre>";
}

/**
* Sanitize upload filename
*
* Remove accent and space in name files
* @param  [type] $filename [filename]
* @return [type] [new filename]
*/
add_filter('sanitize_file_name', 'sanitize_filename_on_upload', 10);
function sanitize_filename_on_upload($filename) {
    $ext = end(explode('.',$filename));
    // Replace all weird characters
    $sanitized = preg_replace('/[^a-zA-Z0-9-_.]/','', substr($filename, 0, -(strlen($ext)+1)));
    // Replace dots inside filename
    $sanitized = str_replace('.','-', $sanitized);
    return strtolower($sanitized.'.'.$ext);
}




 ?>
