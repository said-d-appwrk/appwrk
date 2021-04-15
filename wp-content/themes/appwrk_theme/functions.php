<?php
//$end_wp_theme_tmp
if (!function_exists('appwrk_setup')):

    function appwrk_setup()
    {

        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
        */
        /* Appwrk generated Load Text Domain Begin */
        load_theme_textdomain('appwrk', get_template_directory() . '/languages');
        /* Appwrk generated Load Text Domain End */

        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        /*
         * Let WordPress manage the document title.
        */
        add_theme_support('title-tag');

        /*
         * Enable support for Post Thumbnails on posts and pages.
        */
        add_theme_support('post-thumbnails');
        set_post_thumbnail_size(825, 510, true);

        // Add menus.
        register_nav_menus(array(
            'primary' => __('Primary Menu', 'appwrk') ,
            'social' => __('Social Links Menu', 'appwrk') ,
        ));

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
        */
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption'
        ));

        /*
         * Enable support for Post Formats.
        */
        add_theme_support('post-formats', array(
            'aside',
            'image',
            'video',
            'quote',
            'link',
            'gallery',
            'status',
            'audio',
            'chat'
        ));
    }
endif; // appwrk_setup
add_action('after_setup_theme', 'appwrk_setup');

if (!function_exists('appwrk_init')):

    function appwrk_init()
    {

        // Use categories and tags with attachments
        register_taxonomy_for_object_type('category', 'attachment');
        register_taxonomy_for_object_type('post_tag', 'attachment');

        /*
         * Register custom post types. You can also move this code to a plugin.
        */
        /* Appwrk generated Custom Post Types Begin */
        register_post_type('home', array(
            'labels' => array(
                'name' => __('Home') ,
                'singulat_name' => __('Home Page')
            ) ,
            'description' => __('Home') ,
            'public' => true,
            'hierarchical' => true,
            'supports' => array(
                'title',
                'editor',
                'thumbnail',
                'custom-fields'
            ) ,
            'has_archive' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-visibility'
        ));

        register_post_type('jobs', array(
            'labels' => array(
                'name' => __('Jobs') ,
                'singulat_name' => __('Job')
            ) ,
            'description' => __('Job') ,
            'capability_type' => 'post',
            'public' => true,
            'show_ui' => true,
            'taxonomies' => array(
                'post_tag'
            ) ,
            'hierarchical' => true,
            'supports' => array(
                'title',
                'editor',
                'thumbnail',
                'custom-fields'
            ) ,
            'has_archive' => true,

            'rewrite' => array(
                'slug' => 'jobs',
                'with_front' => true
            ) ,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-welcome-learn-more'
        ));
        register_post_type('blogs', array(
            'labels' => array(
                'name' => __('Blogs'),
                'singulat_name' => __('Blog')
            ),
            'description' => __('Blog') ,
            'public' => true,
            'show_ui' => true,
            'capability_type' => 'post',
            'taxonomies' => array(
                'post_tag',
                'category'
            ) ,
            'hierarchical' => true,
            'supports' => array(
                'title',
                'editor',
                'thumbnail',
                'custom-fields',
                'author',
                'comments'
            ) ,
            'has_archive' => true,
            'rewrite' => array(
                'slug' => 'blog',
                'with_front' => true
            ) ,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-format-status'
        ));

        /* Appwrk generated Custom Post Types End */

        /*
         * Register custom taxonomies. You can also move this code to a plugin.
        */
        /* Appwrk generated Taxonomies Begin */

        /* Appwrk generated Taxonomies End */
    }
endif; // appwrk_setup
add_action('init', 'appwrk_init');

if (!function_exists('appwrk_widgets_init')):

    function appwrk_widgets_init()
    {
        if (is_category())
        { //adds the category parameter in the query if we display a category
            $cat = get_queried_object();
            return array(
                'posts_per_page' => 10, //set the number you want here
                'no_found_rows' => true,
                'post_status' => 'publish',
                'ignore_sticky_posts' => true,
                'cat' => $cat->term_id
                //the current category id
                
            );
        }
        else
        { //keeps the normal behaviour if we are not in category context
            return $args;
        }
    }
    add_action('widgets_init', 'appwrk_widgets_init');
endif; // appwrk_widgets_init


if (!function_exists('appwrk_customize_register')):

    function appwrk_customize_register($wp_customize)
    {
        // Do stuff with $wp_customize, the WP_Customize_Manager object.
        /* Appwrk generated Customizer Controls Begin */

        /* Appwrk generated Customizer Controls End */
    }
    add_action('customize_register', 'appwrk_customize_register');
endif; // appwrk_customize_register


if (!function_exists('appwrk_enqueue_scripts')):

    function my_enqueue_stuff()
    {

        // "page-templates/about.php" is the path of the template file. If your template file is in Theme's root folder, then use it as "about.php".
        if (is_page_template('work.php'))
        {
            wp_enqueue_script('lightgallery-js', get_template_directory_uri() . '/dist/fullpage.js');
            wp_deregister_style('fullpane');
            wp_enqueue_style('fulllpane', get_template_directory_uri() . '/dist/fullpage.css', false, null, 'all');
        }
    }
    add_action('wp_enqueue_scripts', 'my_enqueue_stuff');
    

    add_action( 'init', function() {
        remove_action( 'init', 'wp_sitemaps_get_server' );
        }, 5 );


    // css derister end
    function appwrk_enqueue_scripts()
    {

        /* Appwrk generated Enqueue Scripts Begin */

        wp_deregister_script('jquery');
        wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js', false, null, false);

        wp_deregister_script('wow');
        wp_enqueue_script('wow', 'https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js', false, null, true);

        wp_deregister_script('popper');
        wp_enqueue_script('popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js', false, null, true);

        wp_deregister_script('bootstrap');
        wp_enqueue_script('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', false, null, true);

        wp_deregister_script('slick');
        wp_enqueue_script('slick', get_template_directory_uri() . '/js/slick.min.js', false, null, true);

        wp_deregister_script('custom');
        wp_enqueue_script('custom', get_template_directory_uri() . '/custom.js', false, null, true);

        /* Appwrk generated Enqueue Scripts End */

        wp_deregister_style('bootstrap');
        wp_enqueue_style('bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css', false, null, 'all');

        // SLICK SLIDER  CSS
        wp_deregister_style('slick');
        wp_register_style('slick', get_template_directory_uri() . '/css/slick.css', false, null, 'all');

        //  SLICK SLIDER CSS HOME PAGE CSS

        if (is_page( 103 )) {
            wp_enqueue_style('slick');
        }      
        // SLICK SLIDER  CSS  ENDm');

        
        wp_enqueue_style('custom', get_template_directory_uri() . '/css/custom.css', false, null, 'all');

        wp_deregister_style('style-3');
        wp_enqueue_style('style-3', get_template_directory_uri() . '/style.css', false, null, 'all');
		wp_enqueue_style('mainstyle', get_template_directory_uri() . '/mainstyle.css', false, null, 'all');
        wp_enqueue_style('custom-elementor', get_template_directory_uri() . '/customelementor.css', false, null, 'all');



        /* Appwrk generated Enqueue Styles End */
    }
    add_action('wp_enqueue_scripts', 'appwrk_enqueue_scripts');
endif;


add_action('wp_enqueue_scripts', 'remove_default_stylesheet', 20); 
function remove_default_stylesheet() { 
    // $remove_js_pages = array(    'hire-python-developers' , 'hire-php-developers' , 'hire-reactjs-developers' , 'home');
    // if ( is_page($remove_js_pages) ) {
    wp_deregister_style('elementor-icons'); 
    wp_deregister_style('elementor-animations'); 
    wp_deregister_style('font-awesome-5-all-css'); 

    
    //}

}




/*
 * Resource files included by Appwrk.
*/

/* Appwrk generated Include Resources Begin */
//require_once "inc/bootstrap/wp_bootstrap_navwalker.php";
/* Appwrk generated Include Resources End */

/**
 * Social media share buttons
 */
function wcr_share_buttons()
{
    $url = urlencode(get_the_permalink());
    $title = urlencode(html_entity_decode(get_the_title() , ENT_COMPAT, 'UTF-8'));
    $media = urlencode(get_the_post_thumbnail_url(get_the_ID() , 'full'));

    include (locate_template('share-template.php', false, false));
}
function wpse28145_add_custom_types($query)
{
    if (is_tag() && $query->is_main_query())
    {

        // this gets all post types:
        $post_types = get_post_types();
        $query->set('post_type', $post_types);
    }
}
add_filter('pre_get_posts', 'wpse28145_add_custom_types');

function remove_menus()
{
    remove_menu_page('edit.php'); //Posts
    
}
add_action('admin_menu', 'remove_menus');

function comment_support_for_my_custom_post_type()
{
    add_post_type_support('Blogs', 'comments');
}
add_action('init', 'comment_support_for_my_custom_post_type');

add_filter('jpeg_quality', function ($arg)
{
    return 75;
});



