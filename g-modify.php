<?php

/**
* Plugin Name: g-modify
* Description: All site-wide wordpress modifiers. Adds mataomo widgets to dashboard. Allows styling of Wordpress login and admin area.
* Comment: Script now only modifies PHP.
* Author: Gugulethu Hlekwayo
* Version: 4.1.0 (1 March 2021)
* URL: https://gugulet.hu
*/

// Disable page title
function ele_disable_page_title( $return ) {
   return false;
}
add_filter( 'hello_elementor_page_title', 'ele_disable_page_title' );

// Disable wp-login.php redirect
function stop_redirect($scheme)
{
    if ( $user_id = wp_validate_auth_cookie( '',  $scheme) ) {
        return $scheme;
    }

    global $wp_query;
    $wp_query->set_404();
    get_template_part( 404 );
    exit();
}
add_action('init', 'remove_default_redirect');
function remove_default_redirect()
{
    remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);
}
add_filter('auth_redirect_scheme', 'stop_redirect', 9999);

// Hide the Wordpress version
function wpversion_remove_version() {
    return '';
}
add_filter('the_generator', 'wpversion_remove_version');

// Hide login hints
function no_wordpress_errors(){
  return 'Something is wrong!';
}
add_filter( 'login_errors', 'no_wordpress_errors' );

// Hide the Wordpress content editor
function remove_textarea() {
    remove_post_type_support( 'page', 'editor' );
}
add_action('admin_init', 'remove_textarea');

// Hide admin footer
function change_footer_admin () {return ' ';}
add_filter('admin_footer_text', 'change_footer_admin', 9999);
function change_footer_version() {return ' ';}
add_filter( 'update_footer', 'change_footer_version', 9999);

// Disable WooCommerce marketing
add_filter( 'woocommerce_admin_features', function( $features ) {
	return array_values(
		array_filter( $features, function($feature) {
			return $feature !== 'marketing';
		} ) 
	);
} );

// Customise admin side bar
add_action(
    'wp_before_admin_bar_render', function () {
        global $wp_admin_bar;

        $wp_admin_bar->remove_menu('wp-logo'); 
        $wp_admin_bar->remove_menu('about');
        $wp_admin_bar->remove_menu('wporg');
        $wp_admin_bar->remove_menu('documentation');
        $wp_admin_bar->remove_menu('support-forums'); 
        $wp_admin_bar->remove_menu('feedback'); 

        $wp_admin_bar->remove_menu('site-name');
        $wp_admin_bar->remove_menu('view-site');
        $wp_admin_bar->remove_menu('dashboard');
        $wp_admin_bar->remove_menu('menus');

        $wp_admin_bar->remove_menu('customize');

        $wp_admin_bar->remove_menu('comments');

        // $wp_admin_bar->remove_menu('new-content');  
        // $wp_admin_bar->remove_menu('new-post');
        $wp_admin_bar->remove_menu('new-media');
        $wp_admin_bar->remove_menu('edit-comments');
        $wp_admin_bar->remove_menu('new-page');
        $wp_admin_bar->remove_menu('new-user');

        $wp_admin_bar->remove_menu('edit');

        $wp_admin_bar->remove_menu('my-account');
        $wp_admin_bar->remove_menu('search');
        
        $wp_admin_bar->remove_menu('happy-addons');
    }, 999
); 

// Remove comments from menu
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

// Disable emoji support
add_action(
    'init', function () {
        // Front-end
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');

        // Admin
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');

        // Feeds
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');

        // Embeds
        remove_filter('embed_head', 'print_emoji_detection_script');

        // Emails
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

        // Disable from TinyMCE editor. Disabled in block editor by default
        add_filter(
            'tiny_mce_plugins', function ( $plugins ) {
                if (is_array($plugins) ) {
                    $plugins = array_diff($plugins, array( 'wpemoji' ));
                }

                return $plugins;
            }
        );
        
        if ((int) get_option('use_smilies') === 1 ) {
            update_option('use_smilies', 0);
        }
    }
);

// Add customer to the authors dropdown list
function filter_authors( $args ) {
	if ( isset( $args['who'])) {
		$args['role__in'] = ['author', 'editor', 'administrator', 'customer'];
		unset( $args['who']);
	}
	return $args;
}
add_action('wp_dropdown_users_args', 'filter_authors');

// Redirect private page to login before viewing
function private_content_redirect_to_login() {
  global $wp_query,$wpdb;
  if (is_404()) {
    $private = $wpdb->get_row($wp_query->request);
    $location = wp_login_url($_SERVER["REQUEST_URI"]);
    if( 'private' == $private->post_status  ) {
      wp_safe_redirect($location);
      exit;
    }
  }
}
add_action('template_redirect', 'private_content_redirect_to_login', 9);

// Remove "Private: " from titles
function remove_private_prefix($title) {
	$title = str_replace('Private: ', '', $title);
	return $title;
}
add_filter('the_title', 'remove_private_prefix');

/* Remove superflous roles */
// Remove Wordpress roles

if ( get_role( 'contributor' ) ) {
    remove_role( 'contributor' );
}

// Remove Woocommerce Shop Manager` role
if ( get_role( 'shop_manager' ) ) {
    remove_role( 'shop_manager' );
}

// Remove BackWPup roles
if ( get_role( 'backwpup_admin' ) ) {
    remove_role( 'backwpup_admin' );
}
if ( get_role( 'backwpup_check' ) ) {
    remove_role( 'backwpup_check' );
}
if ( get_role( 'backwpup_func' ) ) {
    remove_role( 'backwpup_func' );
}
if ( get_role( 'backwpup_helper' ) ) {
    remove_role( 'backwpup_helper' );
}

// Go straight to checkout
function add_to_cart_redirect() {
    global $woocommerce;
    $checkout_url = wc_get_checkout_url();
    return $checkout_url;
}
add_filter('woocommerce_add_to_cart_redirect', 'add_to_cart_redirect');

// Redirect to custom thank you page
function thanks_redirectcustom( $order_id ){
    $order = wc_get_order( $order_id );
    $url = 'https://website.com/checkout/thank-you';
    if ( ! $order->has_status( 'failed' ) ) {
        wp_safe_redirect( $url );
        exit;
    }
}
add_action( 'woocommerce_thankyou', 'thanks_redirectcustom');

// Hide editor on products page
function remove_product_editor() {
  remove_post_type_support( 'product', 'editor' );
}
add_action( 'init', 'remove_product_editor' );

/* Create dashboard widgets */
// Visitor Log
function visitor_log_matomo() {
global $wp_meta_boxes;
wp_add_dashboard_widget('custom_visitor_log', 'Visitor Log', 'visitor_log');
}
function visitor_log() {
echo '<div id="widgetIframe"><iframe width="100%" height="880" src="https://website.com?token_auth=1234567&module=Widgetize&action=iframe&forceView=1&viewDataTable=VisitorLog&small=1&disableLink=0&widget=1&moduleToWidgetize=Live&actionToWidgetize=getLastVisitsDetails&idSite=1&period=range&date=last7&disableLink=1&widget=1" scrolling="yes" frameborder="0" marginheight="0" marginwidth="0"></iframe></div>';
}
add_action('wp_dashboard_setup', 'visitor_log_matomo');

// Visits over time
function visits_time_matomo() {
global $wp_meta_boxes;
wp_add_dashboard_widget('custom_visits_time', 'Visits over time', 'visits_time');
}
function visits_time() {
echo '<div id="widgetIframe"><iframe width="100%" height="880" src="https://website.com?token_auth=1234567&module=Widgetize&action=iframe&containerId=VisitOverviewWithGraph&disableLink=0&widget=1&moduleToWidgetize=CoreHome&actionToWidgetize=renderWidgetContainer&idSite=1&period=range&date=last7&disableLink=1&widget=1" scrolling="yes" frameborder="0" marginheight="0" marginwidth="0"></iframe></div>';
}
add_action('wp_dashboard_setup', 'visits_time_matomo');

// Visits map
function map_matomo() {
global $wp_meta_boxes;
wp_add_dashboard_widget('custom_map', 'Map', 'map');
}
function map() {
echo '<div id="widgetIframe"><iframe width="100%" height="220" src="https://website.com?token_auth=1234567&module=Widgetize&action=iframe&disableLink=0&widget=1&moduleToWidgetize=UserCountryMap&actionToWidgetize=visitorMap&idSite=1&period=range&date=last30&disableLink=1&widget=1" scrolling="yes" frameborder="0" marginheight="0" marginwidth="0"></iframe></div>';
}
add_action('wp_dashboard_setup', 'map_matomo');

// Visits Cities
function cities_matomo() {
global $wp_meta_boxes;
wp_add_dashboard_widget('custom_cities', 'Cities', 'Cities');
}
function cities() {
echo '<div id="widgetIframe"><iframe width="100%" height="290" src="https://website.com?token_auth=1234567&module=Widgetize&action=iframe&disableLink=0&widget=1&moduleToWidgetize=UserCountry&actionToWidgetize=getCity&idSite=1&period=range&date=last30&disableLink=1&widget=1" scrolling="yes" frameborder="0" marginheight="0" marginwidth="0"></iframe></div>';
}
add_action('wp_dashboard_setup', 'cities_matomo');

// Channels
function channels_matomo() {
global $wp_meta_boxes;
wp_add_dashboard_widget('custom_channels', 'Channels', 'Channels');
}
function channels() {
echo '<div id="widgetIframe"><iframe width="100%" height="200" src="https://website.com?token_auth=1234567&module=Widgetize&action=iframe&disableLink=0&widget=1&module=Widgetize&action=iframe&disableLink=0&widget=1&moduleToWidgetize=Referrers&actionToWidgetize=getReferrerType&idSite=1&period=range&date=last30&disableLink=1&widget=1" scrolling="yes" frameborder="0" marginheight="0" marginwidth="0"></iframe></div>';
}
add_action('wp_dashboard_setup', 'channels_matomo');

/* Styling variables */
// Colours
$primary_colour = "#323036";
$secondary_colour = "#CECCD2";
$tertiary_colour = "#FAF9F6";
$quaternary_colour = "#828089";
$quinary_colour = "#E6E6E6";

// Fonts
$import_fonts = "@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@100;400;700&display=swap');@import url('https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100;400;700&display=swap');";
$primary_font = "'Roboto'";
$secondary_font = "'Roboto Slab'";

/* Change the login page styling */
// Change logo link
function custom_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'custom_login_logo_url' );
function custom_login_logo_url_title() {
    return 'website.com';
}
add_filter( 'login_headertitle', 'custom_login_logo_url_title' );

// Change login logo
function custom_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(https://website.com/wp-admin/uploads/login-logo-300x100.png);
		height:100px;
		width:300px;
		background-size: 300px 100px;
		background-repeat: no-repeat;
        padding-bottom: 10px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'custom_login_logo' );

// Change the page styling
function custom_login_style() { ?>
    <style type="text/css">
    <?php echo $GLOBALS['import_fonts']; ?>
        body.login {
            background: #FFFFFF;
            color: <?php echo $GLOBALS['primary_colour']; ?>;
            font-family: <?php echo $GLOBALS['primary_font']; ?>, Helvetica, sans-serif;
            font-weight: 100;
        }
        #login a:hover {
            color:<?php echo $GLOBALS['secondary_colour']; ?>;
        }
        div#login {
            width:500px;
        }
        #loginform {
            border: none;
            background: <?php echo $GLOBALS['tertiary_colour']; ?>;
            padding: 50px;
        }
        label {
            font-weight: 200;
            font-size:0.6em;
        }
        label[for=user_login] {
            visibility:hidden;
            position:relative;
        }
        label[for=user_login]:after {
            visibility: visible;
            position: absolute;
            top: 0;
            left: 0;
            content: "Email";
        }
        input#user_login, input#user_pass {
            background: #FAF9F6;
            border-left:none;
            border-right:none;
            border-top:none;
            border-bottom: 1px solid <?php echo $GLOBALS['primary_colour']; ?>;
            border-radius:0px;
        }
        input#user_login:focus, input#user_pass:focus {
            border-color:<?php echo $GLOBALS['primary_colour']; ?>;
            box-shadow:none;
        }
        p.forgetmenot {
          display:none;  
        }
        .login .button-primary {
            width:100%;
        }
        input#wp-submit {
            background: <?php echo $GLOBALS['tertiary_colour']; ?>;
            color: <?php echo $GLOBALS['primary_colour']; ?>;
            border: none;
            border-color: transparent;
            text-transform: uppercase;
            font-size: 1.3em;
            letter-spacing: 3px;
            font-weight: bold;
            outline: none;
            box-shadow:none;
        }
        input#wp-submit:hover {
            color: <?php echo $GLOBALS['secondary_colour']; ?>;
            outline: none;
            box-shadow:none;
        }
        span.dashicons.dashicons-visibility {
            color: <?php echo $GLOBALS['quaternary_colour']; ?>;
        }
        p#nav, p#backtoblog {
            text-align:center;
            font-family: <?php echo $GLOBALS['secondary_font']; ?>, Helvetica, sans-serif;
        }
        a.privacy-policy-link,  #login p#backtoblog > a,  #login p#nav > a {
            color: <?php echo $GLOBALS['quaternary_colour']; ?>;
            font-family: <?php echo $GLOBALS['secondary_font']; ?>, Helvetica, sans-serif;
            font-weight:100;
        }
        #login p#backtoblog > a:hover,#login p#nav > a:hover {
            color: <?php echo $GLOBALS['secondary_colour']; ?>;
            font-weight:100;
        } 
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'custom_login_style' );

/* Style wp-admin page */
function my_admin_style() { ?>
    <style type="text/css">
    <?php echo $GLOBALS['import_fonts']; ?>
        body {
            background: white;
            color: <?php echo $GLOBALS['primary_colour']; ?>;
            font-family:<?php echo $GLOBALS['primary_font']; ?>, Helvetica, sans-serif;
            font-weight: 100;
        }
        a {
            color: <?php echo $GLOBALS['quaternary_colour']; ?>;
        }
        a:hover, a:focus {
            color:<?php echo $GLOBALS['secondary_colour']; ?>;
            box-shadow:none;
            outline:none;
        }
        h1, h2, h3 {
            font-family:<?php echo $GLOBALS['secondary_font']; ?>, serif;
            font-weight: 700;
        }
        h4, h5, h6, h7 {
            font-weight: 700;
        }
        div#wpadminbar {
            display:none;
        }
        #adminmenu, #adminmenuback, #adminmenuwrap {
            background: white;
        }
        #screen-meta-links .show-settings {
            border: none;
        }
        .wp-core-ui select, input[type=search], input[type=text], input[type=number], input[type=email], input[type=url] {
            border-radius: 0;
            border-top:none;
            border-left:none;
            border-right:none;
            border-bottom: 1px solid <?php echo $GLOBALS['primary_colour']; ?>;
            background:transparent;
        }
        .wp-core-ui select:focus, input[type=search]:focus, input[type=text]:focus, input[type=number]:focus, input[type=email]:focus, input[type=url]:focus {
            box-shadow:none;
            border-color:<?php echo $GLOBALS['primary_colour']; ?>;
            background:transparent;
            color:<?php echo $GLOBALS['secondary_colour']; ?>;
        }
        #adminmenu .wp-menu-image img {
            opacity: 0.5;
            filter: invert(100%);
        }
        #adminmenu .wp-menu-image img:hover, #adminmenu .wp-menu-image img:focus {
            opacity: 1;
            filter: invert(0%);
        }
        #adminmenu .wp-has-current-submenu .wp-submenu a:hover, #adminmenu .wp-submenu a:hover, .folded #adminmenu .wp-has-current-submenu .wp-submenu a:hover {
            color:<?php echo $GLOBALS['secondary_colour']; ?>;
        }
        #adminmenu li.wp-menu-separator {
            display:none;
        }
        #adminmenu li.menu-top {
            margin-top:3px;
        }
        .postbox {
            border: none;
            background: <?php echo $GLOBALS['tertiary_colour']; ?>;
        }
        .wp-core-ui .button, .wrap .page-title-action, .wrap .page-title-action:active, body.elementor-editor-active #elementor-switch-mode-button, .wp-core-ui .button, .wp-core-ui .button-secondary, .wp-core-ui .button-primary {
            background: transparent;
            text-transform: uppercase;
            border: none;
            letter-spacing: 3px;
            font-weight: bold;
            color: <?php echo $GLOBALS['primary_colour']; ?>;
            box-shadow:none;
            outline:none;
        }
        .wp-core-ui .button-secondary:hover, .wp-core-ui .button.hover, .wp-core-ui .button:hover, .wrap .page-title-action:hover, .wrap .page-title-action:active:hover, body.elementor-editor-active #elementor-switch-mode-button:hover, .wp-core-ui .button:focus, .wp-core-ui .button:focus, .wp-core-ui .button-secondary:focus, .wp-core-ui .button-primary:focus, .wp-core-ui .button-primary:hover {
            background: transparent;
            color: <?php echo $GLOBALS['secondary_colour']; ?>;
            border:none;
            box-shadow:none;
            outline:none;
        }
        body.elementor-editor-active button#elementor-switch-mode-button {
            box-shadow:none !important;
            outline:none;
        }
        table.widefat {
            border:none;
        }
        div.wp-filter, div.tablenav, form#posts-filter, form#activity-filter {
            border:none;
            box-shadow:none;
            background:<?php echo $GLOBALS['tertiary_colour']; ?>;
        }
        .nav-tab {
            background: transparent;
            border:none;
        }
        .nav-tab-active, .nav-tab-active:focus, .nav-tab-active:focus:active, .nav-tab-active:hover {
            border: none;
            color:<?php echo $GLOBALS['primary_colour']; ?>;
            background:<?php echo $GLOBALS['tertiary_colour']; ?>;
        }
    } 
    </style>
<?php }
add_action('admin_head', 'my_admin_style');

?>