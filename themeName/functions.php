<?php


////////////////////////////////////////
//	WP Config
////////////////////////////////////////
/**
 * Theme path
 *
 * @see You'll need that later to include some files
 */
$includesPath = '..' . str_replace(site_url(), '', get_template_directory_uri()) . '/includes/';



/**
 * Maintenance mode
 *
 * @see Easy way to put your website in maintenance mode (show a custom page if people open your website)
 */
add_action('get_header', 'maintenace_mode');
function maintenace_mode() {
    if (file_exists(ABSPATH . '.maintenance') && !(current_user_can('administrator') || current_user_can('super admin')) ) {
        include_once(get_template_directory() . '/maintenance.php');
        die();
    }
}



/**
 * Admins options
 *
 */
add_action('admin_menu', 'remove_menus');
function remove_menus(){ // Clean menu items
    remove_menu_page('edit-comments.php'); //Comments
    remove_menu_page('edit.php'); //Posts
}

$user_editor = get_role( 'editor' );
$user_editor->add_cap( 'edit_theme_options' );
load_theme_textdomain('themename', get_template_directory() . '/languages');


/**
 * Globalise elements
 *
 * @see Import variables such as polylang $lang.
 */

add_action( 'parse_query', 'global_lang_vars' );
function global_lang_vars() {

    global $lang;
    $lang = get_locale();
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
 * Unable SVG uploads
 *
 */
add_filter('upload_mimes', 'cc_mime_types');
function cc_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
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
            get_template_directory_uri() . "/ressources/dist/js/vendors.min.js?time=" . filemtime(get_stylesheet_directory()."/ressources/dist/js/scripts.min.js"),
            array("jquery"),
            false,
            true
        ); // Vendors scripts
        wp_enqueue_script(
            "scripts",
            get_template_directory_uri() . "/ressources/dist/js/scripts.min.js?time=" . filemtime(get_stylesheet_directory()."/ressources/dist/js/scripts.min.js"),
            array("jquery"),
            false,
            true
        ); // Custom scripts
    }
}

/**
 * Load styles in frontend
 *
 * @see we put a timestamp to prevent caching when the file is changed.
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
if(function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title' 	=> 'Options',
        'menu_title' 	=> 'Options',
        'menu_slug' 	=> 'custom-options',
        'capability' 	=> 'edit_posts',
        'redirect' 	    => false
    ));
}






////////////////////////////////////////
//	Custom Walkers
////////////////////////////////////////

/**
 * Import Walkers
 *
 * @see Import any custom walkers in `includes/walkers` folder
 */
foreach (glob($includesPath . 'walkers/*.php') as $file) {
    include $file;
}



////////////////////////////////////////
//	Custom Menus
////////////////////////////////////////

/**
 * Import Menus
 *
 * @see Import any custom menu in `includes/menu` folder
 */
foreach (glob($includesPath . 'menus/*.php') as $file) {
    include $file;
}



/**
 * BEM Menu
 *
 * @see Say goodbye to badly named menus in Wordpress and say hello to Wordpress BEM Menus!
 * @see Then insert the following function into your theme. The first argument is the theme location (as defined in wp-admin) and the second argument is the class prefix you would like to use for this particular menu. The class prefix will be applied to the menu <ul>, every child <li> and <a> as the 'block'. The third optional argument accepts either an array() or a string.
 * @param bem_menu('menu_location', 'my-menu', 'my-menu--my-modifier');
 */
include('includes/class.menu.php');





////////////////////////////////////////
//	Custom Post Types
////////////////////////////////////////

/**
 * Import Post Types
 *
 * @see Import any custom post types in `includes/post-types` folder
 */
foreach (glob($includesPath . 'post-types/*.php') as $file) {
    include $file;
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
 * Render svg file uploaded from amin
 *
 * @see 
 */
function renderSVG($image) {
    $url = $image['url'];
    $file = file_exists($_SERVER['DOCUMENT_ROOT'].preg_replace('#^'.WP_SITEURL.'#', '', $url));

    if($file === true) {
        echo file_get_contents($url);
    } else {
        echo "no image";
    }
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
