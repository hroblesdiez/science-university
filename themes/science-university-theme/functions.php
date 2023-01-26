<?php
require get_theme_file_path('/inc/like-route.php');
require get_theme_file_path('/inc/search-route.php');

//Adding a new field to the JSON Object response 

function su_custom_rest()
{
    register_rest_field('post', 'authorName', array(
        'get_callback'  => function () {
            return get_the_author();
        }
    ));
    register_rest_field('note', 'userNoteCount', array(
        'get_callback'  => function () {
            return count_user_posts(get_current_user_id(), 'note');
        }
    ));
}
add_action('rest_api_init', 'su_custom_rest');




//Function for the Banner 

function pageBanner($args = NULL)
{

    if (!$args['title']) {
        $args['title'] = get_the_title();
    }
    if (!$args['subtitle']) {
        $args['subtitle'] = get_field('page_banner_subtitle');
    }
    if (!$args['photo']) {
        if (get_field('page_banner_background_image') and !is_archive() and !is_home()) {
            $args['photo'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
        } else {
            $args['photo'] = get_theme_file_uri('/images/ocean.jpg');
        }
    }

?>
    <div class="page-banner">
        <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>);"></div>
        <div class="page-banner__content container container--narrow">
            <h1 class="page-banner__title"><?php echo $args['title']; ?></h1>
            <div class="page-banner__intro">
                <p><?php echo $args['subtitle']; ?></p>
            </div>
        </div>
    </div>
<?php }




//Enqueueing assets 
add_action('wp_enqueue_scripts', 'su_enqueue_assets');

function su_enqueue_assets()
{
    wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style('su_main_styles', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('su_extra_styles', get_theme_file_uri('/build/index.css'));
    wp_enqueue_script('font-awesome', 'https://kit.fontawesome.com/03d62932bb.js');
    wp_enqueue_script('su_main_js', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);

    wp_localize_script('su_main_js', 'suData', array(
        'root_url'  => get_site_url(),
        'nonce'     => wp_create_nonce('wp_rest') //
    ));
}

//Adding feature setup
add_action('after_setup_theme', 'su_features');

function su_features()
{
    register_nav_menu('headerMenu', 'Header Menu');
    register_nav_menu('footerMenu1', 'Footer Menu 1');
    register_nav_menu('footerMenu2', 'Footer Menu 2');
    add_theme_support('title');
    add_theme_support('post-thumbnails');
    add_image_size('professorLandscape', 400, 260, true);
    add_image_size('professorPortrait', 480, 650, true);
    add_image_size('pageBanner', 1500, 350, true);
    add_image_size('slideHome', 1200, 800, true);
}

//Modify the excerpt 
add_filter('excerpt_more', 'su_custom_excerpt', 999);

function su_custom_excerpt($more)
{
    return '';
}

//Adjusting queries 

add_action('pre_get_posts', 'su_adjust_queries');

function su_adjust_queries($query)
{
    $today = date('Ymd');
    if (!is_admin() && is_post_type_archive('event') && $query->is_main_query()) {
        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');
        $query->set('meta_query', array(
            array(
                'key'      => 'event_date',
                'compare'  => '>=',
                'value'    =>  $today,
                'type'      => 'numeric'
            )
        ));
    }
    if (!is_admin() && is_post_type_archive('program') && $query->is_main_query()) {
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
        $query->set('posts_per_page', -1);
    }
    if (!is_admin() && is_post_type_archive('campus') && $query->is_main_query()) {
        $query->set('posts_per_page', -1);
    }
}

//Redirect subscribers account onto home page 

add_action('admin_init', 'redirect_subs_to_frontend');

function redirect_subs_to_frontend()
{
    $ourCurrentUser = wp_get_current_user();

    if (count($ourCurrentUser->roles) == 1 &&  $ourCurrentUser->roles[0] == 'subscriber') {
        wp_redirect(site_url('/'));
        exit;
    }
}

add_action('wp_loaded', 'noSubsAdminBar');

function noSubsAdminBar()
{
    $ourCurrentUser = wp_get_current_user();

    if (count($ourCurrentUser->roles) == 1 &&  $ourCurrentUser->roles[0] == 'subscriber') {
        show_admin_bar(false);
    }
}

//Customize login screen 

add_filter('login_headerurl', 'suHeaderURL');

function suHeaderURL()
{
    return esc_url(site_url('/'));
}

//Load the css styles to customize login screen 
add_action('login_enqueue_scripts', 'su_login_css');

function su_login_css()
{
    wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style('su_main_styles', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('su_extra_styles', get_theme_file_uri('/build/index.css'));
    wp_enqueue_script('font-awesome', 'https://kit.fontawesome.com/03d62932bb.js');
}

//Changing the image or text in the login screen 

add_filter('login_headertitle', 'su_login_title');

function su_login_title()
{
    return get_bloginfo('name');
}

//Force note posts be private 

add_filter('wp_insert_post_data', 'makeNotePrivate', 10, 2);

function makeNotePrivate($data, $postarr)
{
    if ($data['post_type'] == 'note') {
        if (count_user_posts(get_current_user_id(), 'note') > 5 && !$postarr['ID']) {
            die("You have reached yur note limit");
        }

        $data['post_content'] = sanitize_textarea_field($data['post_content']);
        $data['post_title'] = sanitize_text_field($data['post_title']);
    }

    if ($data['post_type'] == 'note' && $data['post_status'] != 'trash') {

        $data['post_status'] = "private";
    }
    return $data;
}

//Exlude files from the export to the Production Site 

add_filter('ai1wm_exclude_content_from_export', 'su_ignore_files');

function su_ignore_files($exclude_filters)
{

    $exclude_filters[] = 'themes/science-university-theme/node_modules';
    return $exclude_filters;
}
