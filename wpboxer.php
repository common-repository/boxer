<?php                            

/*
Plugin Name: WP Boxer
Plugin URI: http://wordpress.org/extend/plugins/boxer/
Description: A WordPress plugin that will assist in creating beautiful content blocks by using 1 simple shortcode. Each of these boxes can contain a header, an image, some content and optional links which can point to any given location. Content blocks can be added to any section in your WordPress theme that supports shortcodes, so basically anywhere. 
Author: Mark Boomaars, CodingOurWeb
Version: 2.2.0
Author URI: http://www.codingourweb.com/
*/

#-----------------------------------------------------------------
# Determine the current path and load up COW Plugin Framework
#-----------------------------------------------------------------  

define( 'WPBOXER_ADMIN_TEXTDOMAIN', 'wpboxer' );

$plugin_path = dirname(__FILE__). '/';
if ( class_exists( 'COWPluginFramework' ) != true )
    require_once( $plugin_path. 'framework/cow_plugin_framework.php' );   

require_once( $plugin_path. 'framework/functions.php' );
require_once $plugin_path. 'framework/Twig/Autoloader.php';

Twig_Autoloader::register();

#-----------------------------------------------------------------
# = COWPluginFramework :: Extend the base class
#-----------------------------------------------------------------   

class WPBoxerPlugin extends COWPluginFramework 
{    
    /**
    *   COW plugin framework variables
    */
    var $version = '2.2.0';
    var $name = 'WPBoxer';
    var $slug = 'wpboxer';
    
    var $templates = array();
    var $masks = array();
    var $twig;
    var $types = array();
    
    var $action_links = array(
        array( 
            'title' => 'Settings', 
            'href' => 'options-general.php?page=wpboxer.php' 
        )
    );
    
    var $plugin_css = array(
        '../js/prettyphoto/css/prettyPhoto',
        '../js/nivo-slider/nivo-slider',
        'boxer.style', 
    );
    var $admin_css  = array( 
        'smoothness/jquery-ui',          
        '../js/colorpicker/css/colorpicker',
        'boxer.bootstrap.min', 
        'boxer.admin.style',
    ); 
    
    var $plugin_js  = array(
        'prettyphoto/js/jquery.prettyPhoto',
        'nivo-slider/jquery.nivo.slider.pack',
        'boxer.custom'         
    );
    var $admin_js   = array(          
        'colorpicker/js/colorpicker', 
        'boxer.admin', 
    ); 
    
    var $ajax_actions = array( 
        'admin' => array( 
            'get_templates_cb',   
            'activate',
            'upgrade_cb',            
        ) 
    );
   
    function __construct() 
    {
        parent::__construct( __FILE__ );
        
        if ( $this->get_setting("general", "bootstrap") == 'on' ) {
            $this->plugin_css[] = "boxer.bootstrap.min";   
        }                      
    }

    function activate() 
    {           
        $settings = array (
            'general' => array( // Will be used as Option Table Heading
                'autop' => array(
                    "name" => __( "Enable Automatic Paragraphs", WPBOXER_ADMIN_TEXTDOMAIN ),
                    "type" => "check",
                    "std" => "on",
                    "desc" => __("Wordpress automatically inserts paragraphs for you to separate content breaks within a post or page. De-activate this option if you do not like this feature of WP.", WPBOXER_ADMIN_TEXTDOMAIN)                    
                ),
                'block_seo' => array( 
                    "name" => __( "Do not index individual content block posts", WPBOXER_ADMIN_TEXTDOMAIN ),              
                    "type" => "check",
                    "std" => "off",
                    "desc" => __("If you want to disable the indexing of individual content block posts, activate this option", WPBOXER_ADMIN_TEXTDOMAIN)      
                ),                            
                'bootstrap' => array( 
                    "name" => __( "Enable Twitter Bootstrap", WPBOXER_ADMIN_TEXTDOMAIN ),
                    "type" => "check",
                    "std" => "on",
                    "desc" => __("Sometimes Twitter Bootstrap can interfere with other plugins or themes. Deactivate this option if that is the case", WPBOXER_ADMIN_TEXTDOMAIN)  
                ),  
                'tooltips' => array( 
                    "name" => __( "Show Tooltips in Content Block Options meta box", WPBOXER_ADMIN_TEXTDOMAIN ),              
                    "type" => "check",
                    "std" => "on",
                    "desc" => __("If you want to disable the tooltips within the Content Block Options meta box, activate this option", WPBOXER_ADMIN_TEXTDOMAIN)      
                ),
                'edit_links' => array( 
                    "name" => __( "Enable Edit Links In Content Blocks", WPBOXER_ADMIN_TEXTDOMAIN ),
                    "type" => "check",
                    "std" => "off",
                    "desc" => __("By default content blocks will not display edit links when logged in. Activate this option if you want to display them.", WPBOXER_ADMIN_TEXTDOMAIN)  
                ),   
                'excerpt_length' => array(
                    "name" => __( "Excerpt Length", WPBOXER_ADMIN_TEXTDOMAIN ),                
                    "type" => "text",
                    "size" => 10,
                    "after" => " words",
                    "std" => "40",
                    "desc" => __("Specify how many words should be included in the content block excerpt", WPBOXER_ADMIN_TEXTDOMAIN)                          
                ),                                                                                                   
            ),
            '_block_masks' => array()      
        );
    
        update_option( $this->slug. '_settings', $settings );
    }
    
    /**
    * Code that is run upon plugin initialization
    * 
    * - Register custom post types
    * - Register taxonomies
    * - Retrieve all templates   
    */
    function initialize() 
    {   
        global $wpbp;

        // i18n
        load_plugin_textdomain( WPBOXER_ADMIN_TEXTDOMAIN, true, $this->plugin_dir_name. '/assets/locale' );
        
        // register taxomomies
        $this->register_taxonomy( 'box_sets', __('Block Group', WPBOXER_ADMIN_TEXTDOMAIN), 'box', array('hierarchical' => true) );

        // Register custom post types]
        $this->register_post_type( 'box', __('Content Block', WPBOXER_ADMIN_TEXTDOMAIN), array( 'menu_icon' => $this->get_plugin_uri() . '/assets/images/wpboxerpro-on.png', 'hierarchical' => false, 'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'comments') ) );
 
        // Get all templates
        $this->templates = cow_get_templates();
        
        // Get all template css
        $css = cow_get_all_template_files("css");                                                     
        if ( file_put_contents( $this->plugin_dir. '/assets/css/boxer.templates.css', $css ) > 0 ) {
            $this->plugin_css[] = "boxer.templates";      
        }

        // Get all template js
        $js = cow_get_all_template_files("js");
        if ( file_put_contents( $this->plugin_dir. '/assets/js/boxer.templates.js', $js ) > 0 ) {
            $this->plugin_js[] = "boxer.templates";      
        }

        // Load all masks
        $this->masks = get_option( $this->slug. '_masks' );
        
        // Initialize Twig
        $loader = new Twig_Loader_Filesystem( dirname(__FILE__). '/templates');
        $this->twig = new Twig_Environment( $loader, array(
            //'cache' => dirname(__FILE__). '/framework/cache',
        ));
         
        // Load all box options 
        $fonts_main = array(
            "Arial", "Tahoma", "Verdana", "Geneva", "Helvetica", "Lucida Sans", "Trebuchet", 
            "Times New Roman", "Georgia", 
            "Courier New", "Courier", "Lucida Console", "Monaco", "Segoe UI Light", "Segoe UI"
        );
        $fonts_extended = array(
            "Bebas", "BebasNeue", "Colaborate", "CaviarDreams", "Snickles"
        );

        $font_families = array_merge( $fonts_main, $fonts_extended );
        asort( $font_families );

        $this->bootstrap_icons = array(
            "glass", "music", "search", "envelope", "heart", "star", "star-empty", "user", "film", "th-large", "th", "th-list", "ok",
            "remove", "zoom-in", "zoom-out", "off", "signal", "cog", "trash", "home", "file", "time", "road", "download-alt", "download", 
            "upload", "inbox", "play-circle", "repeat", "refresh", "list-alt", "lock", "flag", "headphones", "volume-off", "volume-down", 
            "volume-up", "qrcode", "barcode", "tag", "tags", "book", "bookmark", "print", "camera", "font", "bold", "italic", "text-height", 
            "text-width", "align-left", "align-right", "align-center", "align-justify", "list", "indent-left", "indent-right", "facetime-video", 
            "picture", "pencil", "map-marker", "adjust", "tint", "edit", "share", "check", "move", "step-backward", "fast-backward", "backward", 
            "play", "pause", "stop", "forward", "fast-forward", "step-forward", "eject", "chevron-left", "chevron-right", "plus-sign", "minus-sign", 
            "remove-sign", "ok-sign", "question-sign", "info-sign", "screenshot", "remove-circle", "ok-circle", "ban-circle", "arrow-left", 
            "arrow-right", "arrow-up", "arrow-down", "share-alt", "resize-full", "resize-small", "plus", "minus", "asterisk", "exclamation-sign", 
            "gift", "leaf", "fire", "eye-open", "eye-close", "warning-sign", "plane", "calendar", "random", "comment", "magnet", "chevron-up", 
            "chevron-down", "retweet", "shopping-cart", "folder-close", "folder-open", "resize-vertical", "resize-horizontal"
        );
        asort( $this->bootstrap_icons );

        $this->options = array (
            'box_general' => array( // Will be used as Option Table Heading
                'type' => array(
                    "name" => __("Block Type", WPBOXER_ADMIN_TEXTDOMAIN),              
                    "type" => "radio",
                    "options" => array(),
                    "mask" => true                      
                ),                            
                'index' => array( 
                    "name" => __("Block Index", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'Specify the content block index. Indexes are really important as they determine the order in which content blocks are displayed.', WPBOXER_ADMIN_TEXTDOMAIN ),               
                    "type" => "range",
                    "min" => 1,
                    "max" => 36,
                    "step" => 1,
                    "size" => 10,
                    "std" => 1,
                    "mask" => false  
                ),     
                'width' => array( 
                    "name" => __("Block Width", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'Select a block width. Always make sure the last block in each row ends with <strong>_last</strong> to avoid rendering issues!', WPBOXER_ADMIN_TEXTDOMAIN ),               
                    "type" => "select",
                    "options" => array(
                        "", "one_half", "one_half_last", "one_third", "one_third_last", "two_third", "two_third_last", "one_fourth", "one_fourth_last", 
                        "three_fourth", "three_fourth_last", "one_fifth", "one_fifth_last", "two_fifth", "two_fifth_last", "three_fifth", "three_fifth_last", 
                        "four_fifth", "four_fifth_last", "one_sixth", "one_sixth_last", "five_sixth", "five_sixth_last"
                    ),
                    "std" => "",
                    "mask" => false       
                ),
                'height' => array( 
                    "name" => __("Block Height", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'Specify the block height. Leave empty if you want it to be dynamic.', WPBOXER_ADMIN_TEXTDOMAIN ),               
                    "type" => "text",
                    "size" => 10,
                    "before" => "",
                    "after" => "px",
                    "std" => "",
                    "mask" => true  
                ),   
                'bgcolor' => array(
                    "name" => __("Background Color", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'Specify a background color for the entire content block.' ),                
                    "type" => "color",
                    "size" => 10,
                    "std" => "",
                    "mask" => true                          
                ), 
                'bgcolor_hover' => array(
                    "name" => __("Background Color (Hover)", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'Specify a background color to be used for a hover effect. You need to implement this inside a template.' ),                
                    "type" => "color",
                    "size" => 10,
                    "std" => "",
                    "mask" => true                          
                ), 
                'bgcolor_override' => array( 
                    "name" => __("Background Color (Override)", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'Check this option if you do not want to apply the bgcolor to the content block but use it for something else', WPBOXER_ADMIN_TEXTDOMAIN ),                
                    "type" => "check",
                    "std" => "off",
                    "mask" => true  
                ),                                             
            ),
            'box_image' => array(
                'height' => array(
                    "name" => __("Image Height", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'Specify the height of the image. When a height has been specified, Timthumb will be used for resizing purposes, unless the <strong>Use TimThumb</strong> option has been deselected', WPBOXER_ADMIN_TEXTDOMAIN ),               
                    "type" => "text",
                    "size" => 10,
                    "before" => "",
                    "after" => "px",
                    "std" => "",
                    "mask" => true                 
                ),
                'width' => array(
                    "name" => __("Image Width", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'Specify the width of the image. When a width has been specified, Timthumb will be used for resizing purposes, unless the <strong>Use TimThumb</strong> option has been deselected', WPBOXER_ADMIN_TEXTDOMAIN ),               
                    "type" => "text",
                    "size" => 10,
                    "before" => "",
                    "after" => "px",
                    "std" => "",
                    "mask" => true                 
                ),
                'link' => array( 
                    "name" => __("Link", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'Specify a destination for the image link. When prepended with a ">" sign, a lightbox will be used', WPBOXER_ADMIN_TEXTDOMAIN ),                
                    "type" => "text",
                    "size" => 60,
                    "setting" => false,
                    "option" => true,
                    "mask" => false  
                ),                                                 
            ),
            'box_heading' => array(
                'show' => array( 
                    "name" => __("Show", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'Uncheck this option if you do not want to display the header', WPBOXER_ADMIN_TEXTDOMAIN ),                
                    "type" => "check",
                    "std" => "on",
                    "mask" => true  
                ), 
                'font_family' => array(
                    "name" => __("Font Family", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => 'The quick brown fox jumped over the fence',               
                    "type" => "select",
                    "options" => $font_families,
                    "std" => "Oswald",
                    "mask" => true                 
                ), 
                'font_size' => array( 
                    "name" => __("Font Size", WPBOXER_ADMIN_TEXTDOMAIN),
                    //"desc" => __( 'Specify a font-size for the box heading. The specified font-size may be overruled by selected box heading classes', WPBOXER_ADMIN_TEXTDOMAIN ),               
                    "type" => "range",
                    "min" => 6,
                    "max" => 72,
                    "step" => 1,
                    "size" => 10,
                    "before" => "",
                    "after" => "px",
                    "std" => "26",
                    "mask" => true  
                ),
                'line_height' => array( 
                    "name" => __("Line Height", WPBOXER_ADMIN_TEXTDOMAIN),               
                    "type" => "range",
                    "min" => 6,
                    "max" => 72,
                    "step" => 1,
                    "size" => 10,
                    "before" => "",
                    "after" => "px",
                    "std" => "30",
                    "mask" => true  
                ),
                'height' => array( 
                    "name" => __("Heading height", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => "Adds a fixed height to the heading so the content will flow more naturally. You can use this when vertically aligning misc. content blocks",               
                    "type" => "range",
                    "min" => 0,
                    "max" => 250,
                    "step" => 1,
                    "size" => 10,
                    "before" => "",
                    "after" => "px",
                    "std" => 0,
                    "mask" => true  
                ),
                'bgcolor' => array(
                    "name" => __("Background Color", WPBOXER_ADMIN_TEXTDOMAIN),              
                    "type" => "color",
                    "size" => 10,
                    "std" => "",
                    "mask" => true                          
                ),                                        
                'color' => array(
                    "name" => __("Color", WPBOXER_ADMIN_TEXTDOMAIN),
                    //"desc" => __( 'Select a color for the box heading. The specified color may be overruled by selected box heading classes', WPBOXER_ADMIN_TEXTDOMAIN ),                
                    "type" => "color",
                    "size" => 10,
                    "before" => "#",
                    "after" => "",
                    "std" => "555555",
                    "mask" => true                      
                ),                
                'link' => array( 
                    "name" => __("Link", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'Specify a destination for the block heading. When prepended with a ">" sign, a lightbox will be used', WPBOXER_ADMIN_TEXTDOMAIN ),                
                    "type" => "text",
                    "size" => 60,
                    "mask" => false  
                ),
                'align' => array(
                    "name" => __("Alignment", WPBOXER_ADMIN_TEXTDOMAIN),
                    //"desc" => __( 'Specify the alignment of the box heading. The specified alignment may be overruled by selected box heading classes', WPBOXER_ADMIN_TEXTDOMAIN ),               
                    "type" => "select",
                    "options" => array(
                        "left", "center", "right", "justify"
                    ),
                    "std" => "left",
                    "mask" => true                 
                ),                 
            ),
            'box_content' => array(
                'columns' => array(
                    "name" => __("Column Count", WPBOXER_ADMIN_TEXTDOMAIN),
                    //"desc" => __( 'Select the number of columns for the content', WPBOXER_ADMIN_TEXTDOMAIN ),               
                    "type" => "select",
                    "options" => array( '1', '2', '3', '4', '5' , '6'),
                    "std" => "1",
                    "mask" => true        
                ),
                'font_family' => array(
                    "name" => __("Font Family", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => 'The quick brown fox jumped over the fence',               
                    "type" => "select",
                    "options" => $font_families,
                    "std" => "Ropa Sans",
                    "mask" => true                 
                ),
                'font_size' => array( 
                    "name" => __("Font Size", WPBOXER_ADMIN_TEXTDOMAIN),
                    //"desc" => __( 'Specify a font-size for the box content. The specified font-size may be overruled by selected box content classes', WPBOXER_ADMIN_TEXTDOMAIN ),               
                    "type" => "range",
                    "min" => 6,
                    "max" => 72,
                    "step" => 1,
                    "size" => 10,
                    "before" => "",
                    "after" => "px",
                    "std" => "14",
                    "mask" => true  
                ),
                'line_height' => array( 
                    "name" => __("Line Height", WPBOXER_ADMIN_TEXTDOMAIN),
                    //"desc" => __( 'Specify a line height for the box content. The specified line height may be overruled by selected box content classes', WPBOXER_ADMIN_TEXTDOMAIN ),               
                    "type" => "range",
                    "min" => 6,
                    "max" => 72,
                    "step" => 1,
                    "size" => 10,
                    "before" => "",
                    "after" => "px",
                    "std" => "18",
                    "mask" => true  
                ),
                'color' => array(
                    "name" => __("Color", WPBOXER_ADMIN_TEXTDOMAIN),
                    //"desc" => __( 'Specify a color for the box content. The specified color may be overruled by selected box content classes', WPBOXER_ADMIN_TEXTDOMAIN ),                
                    "type" => "color",
                    "size" => 10,
                    "before" => "#",
                    "after" => "",
                    "std" => "666666",
                    "mask" => true                      
                ),
                'align' => array(
                    "name" => __("Alignment", WPBOXER_ADMIN_TEXTDOMAIN),
                    //"desc" => __( 'Specify the alignment for the content. The specified alignment may be overruled by selected box content classes', WPBOXER_ADMIN_TEXTDOMAIN ),               
                    "type" => "select",
                    "options" => array(
                        "left", "center", "right", "justify"
                    ),
                    "std" => "left",
                    "mask" => true                 
                ), 
                'autop' => array( 
                    "name" => __("WP AutoP", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'If you do not want WP to automatically insert paragraphs, uncheck this option', WPBOXER_ADMIN_TEXTDOMAIN ),                
                    "type" => "check",
                    "std" => "on",
                    "mask" => true  
                ),                             
                'excerpt' => array( 
                    "name" => __("Excerpt", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'Specify whether you would like display only the excerpt in stead of the entire content. The excerpt length can be set <a href="options-general.php?page=wpboxer.php">here</a>', WPBOXER_ADMIN_TEXTDOMAIN ),                
                    "type" => "check",
                    "std" => "off",
                    "mask" => true  
                ),                                                                                         
            ),
            'box_footer' => array(
                'show' => array( 
                    "name" => __("Show", WPBOXER_ADMIN_TEXTDOMAIN),
                    "desc" => __( 'Check this option if you would like display a footer', WPBOXER_ADMIN_TEXTDOMAIN ),                
                    "type" => "check",
                    "std" => "off",
                    "mask" => true  
                ), 
            ),     
        );                                            
    }
       
    /**
    * Add stuff to the head section of the page
    * Also defines global variables for use in javascript
    * 
    */
    function page_header() 
    {
        if ( ( $this->get_setting("general", "block_seo") == "on" ) && ( is_single() && 'box' == get_post_type() ) )
            echo '<meta name="robots" content="noindex" />'. "\r\n";
?>
    <!--[if IE 7 ]>
    <link href="<?php echo $this->get_plugin_uri();?>/assets/css/ie7.css" media="screen" rel="stylesheet" type="text/css">
    <![endif]-->
    <script>
        <?php echo 'var plugin_uri = "'. $this->get_plugin_uri(). '"'; ?>
    </script>
<?php
    }
    
    /**
    * Code that is run upon initialization of the admin back-end
    * 
    * - Register custom metaboxes
    * - Add metaboxes to all custom post types that are not builtin    
    */
    function admin_init()
    {
        // Check if WP version is at least 3.0
        $this->requires_wordpress_version("3.0");
        
        // Add a Block Options metabox to posts
        $this->register_custom_meta_box( __('Content Block Options', WPBOXER_ADMIN_TEXTDOMAIN), 'post', 'block_options_metablock_cb', 'normal' );
        
        // Add a Block Options metabox to all custom post types
        $custom_post_types = get_post_types( array( 'public' => true, '_builtin' => false ) );
        foreach( $custom_post_types as $custom_post_type ) {
            $this->register_custom_meta_box( __('Content Block Options', WPBOXER_ADMIN_TEXTDOMAIN), $custom_post_type, 'block_options_metablock_cb', 'normal' );    
        }
        
        // Add the donate metabox
        $this->register_custom_meta_box( 'Get WP Boxer Pro', 'box', 'donate_metablock_cb', 'side' );
    }
 
     /**
     * Code that is run upon initialization of the admin menu
     * 
     * - Register a Settings page. You can place the settings menu anywhere within the WP admin menu.
     * @example  $this->register_settings_page( 'submenu_page', array('parent_slug' => 'edit.php?post_type=box', 'menu_title' => 'Settings') );
     */
    function admin_menu()
    {
        $this->register_settings_page( 'options_page', array( 'menu_title' => 'WP Boxer' ) );   
    }
   
    /*
     * Add Support for Thumbnails on Menu Items
     *
     * This function adds support without override the theme's support for thumbnails
     * Note we could just call add_theme_support('post-thumbnails') without specifying a post type,
     * but this would make it look like users could set featured images on themes that don't support it
     * so we don't want that.
     */
    function cow_support_thumbs()
    {        
        global $_wp_theme_features;
        $post_types = array('box');
        
        $already_set = false;
        
        //Check to see if some features are already supported so that we don't override anything
        if ( isset( $_wp_theme_features['post-thumbnails'] ) && is_array( $_wp_theme_features['post-thumbnails'][0] ) ) {
            $post_types = array_merge( $post_types, $_wp_theme_features['post-thumbnails'][0] );
        }
        //If they already tuned it on for EVERY type, then we don't need to do anything more
        elseif ( isset( $_wp_theme_features['post-thumbnails']) && $_wp_theme_features['post-thumbnails'] == 1 ) {
            $already_set = true;
        }
        
        if ( ! $already_set ) add_theme_support( 'post-thumbnails', $post_types );
    }
   
      
#-----------------------------------------------------------------
# = Callbacks
#-----------------------------------------------------------------
        
    /**
    * Default callback function for adding an options page to your plugim
    * 
    */
    function wpboxer_settings_page_cb()
    {              
        echo $this->render( 'settings.php' );
    }

    /**
    * Default callback function for adding a metabox to a (custom) post type
    * 
    */
    function block_options_metablock_cb()
    {
        echo $this->render( 'block_options.php' );    
    }    
    
    /**
    * Default callback function for adding the Donate metabox
    * 
    */
    function donate_metablock_cb()
    {
        echo $this->render( 'donate.php' );    
    }
    
    /**
    * put your comment there...
    * 
    */
    function sanitize_block_link_cb()
    {
        //debugbreak();
    }
                                                               
    /**
    * Saves all options to the WP database
    * 
    * @param mixed $post_id
    */
    function save_postdata( $post_id ) 
    {     
        // Make sure quick edit does not overwrite our stored custom fields
        if ( defined( 'DOING_AJAX' ) )
            return $post_id;
            
        // Verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return $post_id;


        // Check permissions to edit boxes
        if ( isset( $_POST['post_type'] ) && 'box' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'manage_options' ))
                return $post_id;
        }

        // Let's create an array with all posted options
        foreach ( $this->options as $option_group => $options ) {
            if ( isset( $_POST[$option_group] )) {
                foreach( $_POST[$option_group] as $prop => $newval ) {
                    if ( is_array( $newval ) && ($prop != 'links' ) ) {
                        $newval = implode( " ", $newval );   
                    }
                    $new_options[$option_group][$prop] = $newval;    
                }                        
            }                 
        }
 
        // Checkbox and multiselect values are not posted when unchecked or empty, so make sure
        // all properties with type "check" and "multiselect" are present in the new_options array
        foreach ( $this->options as $option_group => $options ) {
            foreach( $options as $opt => $prop ) {
                if ( strtolower( $prop['type'] ) == 'check' ) {
                    if ( empty( $_POST ) && isset($this->options[$option_group][$opt]) ) {
                        $new_options[$option_group][$opt] = $this->options[$option_group][$opt]['std'];
                    } 
                    else {
                        $new_options[$option_group][$opt] = $new_options[$option_group][$opt] ? 'on' : 'off';    
                    }                                           
                }   
                if ( strtolower( $prop['type'] ) == 'multiselect' ) {                   
                    $new_options[$option_group][$opt] = $new_options[$option_group][$opt] ? $new_options[$option_group][$opt] : '';                            
                }                            
            }                
        }

        // Save the options array 
        foreach( $new_options as $option_group => $props ) {
            foreach( $props as $prop => $newval ) {
                // Are we dealing with a links array with more than 2 items?
                if ( $option_group == 'box_content' && is_array( $newval ) && sizeof( $newval ) > 2 ) {
                    $newval = serialize( $newval );                      
                }                                
                $old = get_post_meta( $post_id, $option_group. '_'. $prop, true );
                if ( $newval != $old ) {
                    // If $newval is still an array, we DELETE, because only '#' and 'linktoggle_keys'
                    if ( ! is_array( $newval ) ) {
                        update_post_meta( $post_id, $option_group. '_'. $prop, $newval );    
                    } else {
                        delete_post_meta( $post_id, $option_group. '_'. $prop, $old );    
                    }                                                                     
                } elseif ( '' == $newval && $old ) {
                    delete_post_meta( $post_id, $option_group. '_'. $prop, $old );
                }        
            }        
        }          
    }   

    /**
    * Retrieves all templates and stores them in a select box
    * 
    */
    function get_templates_cb()   
    {
        if ( ! $this->templates ) {
?>
            <div>Please make sure at least one template is present in the templates folder</div>
<?php
            exit();
        }
        
        // The templates have been imported, continue
        
        $val = get_post_meta( $_POST['postid'], 'box_general_type', true );           
        if ( ! $val ) {
            $val = 0;                
        }
        
        ob_start();
        $i = 0;
        foreach( $this->templates as $slug => $tpl ) {        
?>
            <input type="radio" title="<?php echo $tpl["name"]; ?>" name="box_general[type]" value="<?php echo $slug; ?>" <?php if ( $val == $slug ) echo 'checked="checked"'; ?>> <?php if ( $tpl['image'] ): ?><img src='<?php echo $tpl["image"]; ?>' alt='<?php echo $tpl["name"]; ?>' title="<?php echo $tpl["name"]; ?>" /><?php endif; ?>            
<?php 
            $i++;
            if ( $i % 5 == 0 ) {
                echo "<div class='clearboth'></div>";
            }
        } 
        echo ob_get_clean();
        exit();                    
    }  
        
    /**
    * Upgrade your boxes from boxer 1.13 to WP Boxer Pro 2.0
    * 
    * @since 1.02
    */
    function upgrade_cb()
    {
        global $wpdb;
        
        $replacments = array(
            'box-type' => 'box_general_type',
            'box-header-color' => 'box_heading_color',
            'box-text-align' => 'box_content_align',
            'box-index' => 'box_general_index',
            'box-width' => 'box_general_width',
            'box-link' => 'box_content_link',
            'box-link-text' => 'box_content_link_text'            
        );
        
        $querystr = "
            SELECT $wpdb->posts.* 
            FROM $wpdb->posts
            WHERE 
                $wpdb->posts.post_status = 'publish' AND 
                $wpdb->posts.post_type = 'boxes'
        ";
         
        $oldboxes = $wpdb->get_results( $querystr, OBJECT );
        
        if ( ! $oldboxes ) {
            _e('No old boxes were found during the upgrade process.');
            exit();    
        } 
        
        $output = '';
        
        if (count($oldboxes) > 0) {
            foreach( $oldboxes as $box ) {
                // Update the post_type
                $data['ID'] = $box->ID;
                $data['post_type'] = 'box';
                $result = wp_update_post( $data );      
                
                if ( $result == $box->ID ) {
                    // Update all applicable custom fields 
                    foreach( $replacments as $old => $new ) {
                        if ( 'box-type' == $old ) {
                            $wpdb->query( "UPDATE {$wpdb->base_prefix}postmeta SET meta_key = '{$new}', meta_value = CONCAT('general-', meta_value) WHERE meta_key = '{$old}' AND post_id = {$box->ID}" );
                        }
                        else {
                            $wpdb->query( "UPDATE {$wpdb->base_prefix}postmeta SET meta_key = '{$new}' WHERE meta_key = '{$old}' AND post_id = {$box->ID}" );    
                        }
                        
                    }
                    // Update box sets
                    $wpdb->query( "UPDATE {$wpdb->base_prefix}term_taxonomy SET taxonomy = 'box_sets' WHERE taxonomy = 'box-sets'" );
                    
                    $output .= sprintf(__("Box ID%d: upgraded OK.<br>", $box->ID));   
                }            
                else {
                    _e("Something went wrong during the upgrade!", "wpboxer");
                    exit();    
                }                                      
            }    
        } else {
            $output = 'No old content blocks found!';    
        }
         
        
        echo $output;
        exit();         
    }
    
}


#-----------------------------------------------------------------
# = Begin
#-----------------------------------------------------------------

$wpbp = new WPBoxerPlugin();

register_activation_hook( __FILE__, array( &$wpbp, 'activate' ) );

// Register additional actions
add_action( 'init',         array( &$wpbp, 'initialize' ) );
add_action( 'wp_head',      array( &$wpbp, 'page_header' ) );
add_action( 'admin_head',   array( &$wpbp, 'page_header' ) );
add_action( 'admin_init',   array( &$wpbp, 'admin_init' ) );
add_action( 'admin_menu',   array( &$wpbp, 'admin_menu' ) );
add_action( 'save_post',    array( &$wpbp, 'save_postdata' ) );
add_action( 'manage_posts_custom_column', 'cow_admin_blocks_data_row' );
add_action( 'after_setup_theme', array( &$wpbp, 'cow_support_thumbs' ), 500);    //go near the end, so we don't get overridden

// Register additional filters
add_filter( 'manage_edit-post_columns', 'cow_admin_posts_header_columns' );
add_filter( 'manage_edit-box_columns', 'cow_admin_blocks_header_columns' );
add_filter( 'widget_text', 'do_shortcode' );
add_filter( 'widget_title', 'do_shortcode' );
add_filter( 'excerpt_more', 'cow_new_excerpt_more' );
add_filter( 'post_row_actions', 'cow_add_row_actions', 10, 1 );

function cow_add_button() 
{
    if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) )
        return;

    if ( get_user_option( 'rich_editing' ) == 'true' ) {
        add_filter( "mce_external_plugins", "cow_add_plugin" );
        add_filter( 'mce_buttons', 'cow_register_button' );
    }
}
add_action( 'init', 'cow_add_button' );

function cow_add_plugin( $plugin_array ) 
{
    global $wpbp;
    
    $plugin_array['blocks'] = $wpbp->get_plugin_uri().'/framework/admin/js/blocks.js';

    return $plugin_array;
}

function cow_register_button( $buttons ) 
{
    array_push( $buttons, "blocks" );

    return $buttons;
}

/**
* Replace the default excerpt more text
* 
* @param mixed $more
* @return mixed
*/
function cow_new_excerpt_more( $more ) 
{
    global $post;
    
    return ' ...';
}    

/**
* Adds a duplicate row link
* 
* @param mixed $actions
*/
function cow_add_row_actions( $actions )
{
    global $post;
    
    if( get_post_type() === 'box' ) {
        $action_link = get_bloginfo( 'url' ). '/wp-admin/edit.php?cow_admin_action=duplicate_block&block='. $post->ID;
        $actions[] = "<a class='submitduplicate' title='" . esc_attr(__('Duplicate this block', WPBOXER_ADMIN_TEXTDOMAIN)) . "' href='". $action_link. "' data-postid=". $post->ID. ">" . __('Duplicate', WPBOXER_ADMIN_TEXTDOMAIN) . "</a>";    
    }
        
    return $actions;
}

/**
* Add custom columns to Post edit page
* 
* @param mixed $columns
*/
function cow_admin_posts_header_columns( $columns )
{
    $plugin_columns = array(
        "type" => __('Type', WPBOXER_ADMIN_TEXTDOMAIN ),
        "blockindex" => __('Index', WPBOXER_ADMIN_TEXTDOMAIN )
    );

    return wp_parse_args( $plugin_columns, $columns );
}

function cow_admin_posts_data_row( $column_name )
{
    global $wpbp, $post;
    
    switch( $column_name )
    {
        case 'type':
            $type = post_custom( 'box_general_type' );       
            echo $wpbp->templates[$type]["name"];
            break; 
                  
        case 'blockindex':       
            echo post_custom( 'box_general_index' );
            break;
         
        default:
            break;
    }
}

/**
* Add custom columns to our Box edit page
* 
* @param mixed $columns
*/
function cow_admin_blocks_header_columns( $columns )
{
    $plugin_columns = array(
        "id" => __('ID', WPBOXER_ADMIN_TEXTDOMAIN ),
        "blockgroup" => __('Block Group', WPBOXER_ADMIN_TEXTDOMAIN ),
        "blockwidth" => __('Width', WPBOXER_ADMIN_TEXTDOMAIN ),
        "type" => __('Type', WPBOXER_ADMIN_TEXTDOMAIN ),
        "blockindex" => __('Index', WPBOXER_ADMIN_TEXTDOMAIN ),
        "thumbnail" => __('Thumbnail', WPBOXER_ADMIN_TEXTDOMAIN )
    );

    return wp_parse_args( $plugin_columns, $columns );
}

function cow_admin_blocks_data_row( $column_name )
{
    global $wpbp, $post;
    
    switch( $column_name )
    {
        case 'id':       
            echo $post->ID;           
            break;

        case 'blockgroup':       
            echo get_the_term_list( $post->ID, 'box_sets', '', ', ' );
            break;
            
        case 'type':
            $type = post_custom( 'box_general_type' );       
            echo $wpbp->templates[$type]["name"];
            break; 
                  
        case 'blockindex':       
            echo post_custom( 'box_general_index' );
            break;

        case 'blockwidth':       
            echo post_custom( 'box_general_width' );
            break;
                     
        case 'thumbnail':
            echo the_post_thumbnail('thumbnail');
            break; 
                                                  
        default:
            break;
    }
}

/**
* put your comment there...
* 
* @since 1.0.5
* 
* @param mixed $post_id
* @param mixed $custom_field
*/
function cow_determine_link( $post_id, $data = array() )
{   
    $href       = isset( $data['link_url'] ) ? $data['link_url'] : '';
    $text       = isset( $data['link_text'] ) ? $data['link_text'] : '';
    $a_class    = isset( $data['a_class'] ) ? $data['a_class'] : '';
    $target     = isset( $data['link_target'] ) ? $data['link_target'] : '';
    $title      = isset( $data['link_title'] ) ? $data['link_title'] : '';
    $i_class    = "";
    $lightbox   = strtolower( isset( $data['lightbox'] ) ? $data['lightbox'] : '' ) == 'true' ? 'wpbp_lightbox' : '';    

    // Does the link begin with a ">" sign? => Lightbox shortcode
    if ( preg_match( '/^>.+/', $href ) ) {
        $lightbox = 'wpbp_lightbox';
        $href = str_replace( '>', '', $href );    
    }
          
    // Yes, Is it a custom field variable like [[var_name]]
    preg_match( '/\[\[(.+)\]\]/', $href, $parts );
    if ( count( $parts ) > 0 ) { // Custom field variable 
        $cf = get_post_meta( $post_id, $parts[1], true );
        if ( $cf ) { 
            $href = str_replace( $parts[0], $cf, $href );                         
        } else {
            $href = "#";     
        }                        
    }
    
    // Is it an Image, YouTube or Vimeo video, Shockwave/flash, QuickTime or # ?
    if ( ! preg_match( '/(?:\.(jpg|png|bmp|gif|mov|mp4|swf)$|\?src=|\?v=|vimeo.com\/.+)/', $href ) ) {             
        if ( $lightbox == 'wpbp_lightbox' ) {
            $href .=  "?iframe=true&width=90%&height=90%"; // Any other type of url    
        }              
    } 
    
    // Has a style been selected                         
    if ( ! empty( $data['link_style'] ) && $data['link_style'] != 'link' ) {
        $a_class .= "bttn bttn-{$data['link_style']}";                                                                               
    }
    
    // Has an icon been selected
    if ( ! empty( $data['link_icon'] ) ) {
        $icon_color = '';
        if ( $data['icon_color'] == 'light' ) $icon_color = 'icon-white';                             
        $i_class .= "bs-icon icon-{$data['link_icon']} $icon_color";                                    
    }
    
    return sprintf('<a href="%s" class="%s" title="%s" target="%s" rel="%s"><i class="%s"></i> %s</a>', $href, $a_class, $title, $target, $lightbox, $i_class, $text);
         
}

/**
* Get all templates from the filesystem
* 
*/
function cow_get_templates()
{
    global $wpbp;
    
    $templates = array();
    
    foreach( glob( dirname(__FILE__). "/templates/*.tpl" ) as $template ) {
        
        $template_info = pathinfo( $template );
        $folder = array_pop(explode('/', $template_info['dirname']));
        $slug = $template_info["filename"];
        
        $templates[$slug]['name'] = ucfirst( str_replace( '-', ' ', $slug ) );

        // Check for template image
        if ( file_exists( $template_info["dirname"]. "/$slug.png" ) ) {
            $templates[$slug]['image'] = $wpbp->get_plugin_uri(). "/templates/$slug.png";
        } else {
            $templates[$slug]['image'] = $wpbp->get_plugin_uri(). "/assets/images/template.png";
        }        
    }
    
    return $templates;
}

/**
* put your comment there...
* 
*/
function cow_get_all_template_files($ext = 'css')
{
    $files = "";
    
    if (count( glob( dirname(__FILE__). "/templates/*.$ext" ) ) > 0) {
        foreach( glob( dirname(__FILE__). "/templates/*.$ext" ) as $file ) {
            $files .= file_get_contents($file);        
        }        
    }
   
    return $files;
}

/**
* Create a filtering function for our query
* 
* @param mixed $where
*/
function filter_where( $where = '' ) 
{
    $where .= " OR (post_type = 'future' AND post_date >= '" . date('Y-m-d H:i') . "')";
    
    return $where;
}


#-----------------------------------------------------------------
# = SHORTCODES  - Used to include shortcodes
#-----------------------------------------------------------------

/**
* The shortcode that makes it all work!
* 
* @param mixed $atts
* @param mixed $content
* @param mixed $code
*/    
function cow_sc_blocks( $atts, $content = null, $code )
{
    global $wpbp;

    extract( shortcode_atts( array(        
        'set' => '',                // optional When post_type is other then "box" 
        'type' => '',           // optional. Override the boxes' type
        'mask' => '',               // optional. Overrides all box properties
        'post_type' => 'box',       // optional. What post type should be retrieved 
        'ids' => '',                // optional. Which post-ids to retrieve
        'cat' => '',                // optional. Which categories to retrieve
        'tag' => '',                // optional. Which tags to retrieve
        'author' => '',             // optional. From which authors should posts be retrieved
        'columns' => null,          // optional (1 to 6). If columns is specified, box widths that are set will be ignored
        'count' => -1,              // optional. How many box items should be retrieved
        'nopaging' => 'true',       // optional. Should the results be paginated
        'orderby' => 'meta_value_num',
        'meta_key' => 'box_general_index',
        'order' => 'ASC'
    ), $atts ) );
    
    $xlate = array(
        1 => '',
        2 => 'one_half',
        3 => 'one_third',
        4 => 'one_fourth',
        5 => 'one_fifth',
        6 => 'one_sixth',
    );    

    // Check settings
    if ( $post_type == "box" && empty( $set ) )
        return __('<p class="error">Please specify a block group</p>', WPBOXER_ADMIN_TEXTDOMAIN);
    
    // Are we using a box mask?
    $using_mask = false;
    if ( isset( $mask ) && ! empty( $mask ) )
        $using_mask = true;
        
    $q = null;
    
    $args = array(
        'post_status' => array('publish'),          
        'orderby' => $orderby,
        'meta_key' => $meta_key,
        'order' => $order
    ); 
    
    if ( "box" === $post_type ) {        
        if ( isset( $set ) ) $args['box_sets'] = $set;                
    } else { 
        if ( isset( $cat ) && ! empty( $cat ) ) $args['cat'] = $cat;
        if ( isset( $tag ) && ! empty( $tag ) ) $args['tag'] = $tag;   
        if ( isset( $author ) && ! empty( $author ) ) $args['author'] = $author;       
    }
    
    if ( 'false' == $nopaging ) {
        global $wp_version;
        if ( ( is_front_page() || is_home() ) && version_compare( $wp_version, "3.1", '>=' ) ){ //fix wordpress 3.1 paged query 
            $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
        } else {
            $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        }
        $args['paged'] = $paged;
    }     
    if ( isset( $post_type ) ) $args['post_type'] = $post_type;
    if ( isset( $count ) ) $args['posts_per_page'] = $count;
    if ( ! empty( $ids ) ) $args['post__in'] = explode( ',', str_replace( ' ', '', $ids ) ); 
    
    add_filter( 'posts_where', 'filter_where' );
    $q = new WP_Query( $args );
    remove_filter( 'posts_where', 'filter_where' );
    
    $import = $boxes = array();
    
    if ( $q->have_posts() ) {
                
        $i = 1;
        while ( $q->have_posts() ): $q->the_post();

            unset( $custom_fields );
            
            $custom_fields = get_post_custom( $q->post->ID );

            // First check for a box mask in the box properties
            $style = '';
            if ( isset( $custom_fields["block_style_mask"] ) )
                $style = $wpbp->masks[$custom_fields["block_style_mask"][0]];
            
            // If a box mask is specified in the shortcode, it takes precedence
            if ( $using_mask )
                $style = $wpbp->masks[$mask];                           
            if ( is_array( $style ) )
                $custom_fields = array_merge( $custom_fields, $style );      
                            
            // Determine box type
            if ( ! $type ) {
                $block_type = isset( $custom_fields["box_general_type"][0] ) ? $custom_fields["box_general_type"][0] : 'general-2';   
            } else {
                $block_type = $type;
            }
        
            // Determine image source
            $box_image_src = array(); 
            if ( function_exists( 'has_post_thumbnail' ) ) {
                if ( has_post_thumbnail() ) :
                    $box_image_src = wp_get_attachment_image_src( get_post_thumbnail_id( $q->post->ID ), 'large' );
                endif;                        
            }
            
            // Determine box width/column info
            if ( null != $columns ) {
                $block_width = "cow_".(string)$xlate[$columns];
                if ( $i % $columns == 0 ) {
                    $block_width .= ' cow_last';          
                }                            
            } else {
                $block_width = '';
                if (isset( $custom_fields["box_general_width"] ) ) {
                    $width = "cow_".$custom_fields["box_general_width"][0]; 
                    $block_width = str_replace( '_last', ' cow_last', $width );    
                }                    
            }
            
            // Determine box height
            $height = '';
            if ( isset( $custom_fields['box_general_height'][0] ) ) 
                $height = "{$custom_fields['box_general_height'][0]}px;";
            
            // Extract post object into variables 
            extract( (array)$q->post );
            
            $box = array(        
                'author' => array(
                    'ID' => 1,
                    'user_name' => get_the_author_meta('user_login'),
                    'display_name' => get_the_author_meta('display_name'),
                    'homepage' => get_the_author_meta('user_url')
                ),
                'bgcolor' => 'transparent',
                'bgcolor_hover' => 'transparent',
                '_bgcolor' => '',
                'class' => '',
                'content' => array(
                    'align' => 'left',
                    'bgcolor' => '',
                    'color' => '#666666',
                    'columns' => "wpboxer-columns column-count-1",
                    'text' => $post_content,
                    'class' => '',
                    'font_size' => 12,
                    'font_family' => '',
                    'line_height' => 16
                ),
                'date' => date( "M j, Y", strtotime( $post_date ) ),
                'date_format' => 'M j, Y',
                'footer' => array(
                    'show' => 0
                ),
                'header' => array(
                    'align' => 'left',
                    'bgcolor' => 'transparent',
                    'color' => '#555555',
                    'text' => $post_title,
                    'rawtext' => $post_title,
                    'link' => '',
                    'class' => '',
                    'font_size' => 28,
                    'font_family' => '',
                    'line_height' => 28,
                    'height' => '',
                    'show' => 1
                ),
                'height' => $height,
                'ID' => $q->post->ID,
                'image' => array(
                    'class' => '',
                    'src' => '',
                    'path' => '',
                    'height' => '',
                    'width' => ''
                ),
                'link' => get_permalink( $q->post->ID ),
                'pagination' => '',
                'template' => $block_type,
                'time' => date( "G:i", strtotime( $post_date ) ),
                'type' => $post_type,
                'width' => $block_width
            );
            
            if ( ! empty( $custom_fields['box_general_bgcolor'][0] ) && $custom_fields['box_general_bgcolor_override'][0] != 'on' ) {
                $box['bgcolor'] = isset($custom_fields['box_general_bgcolor']) ? "#{$custom_fields['box_general_bgcolor'][0]}" : ''; 
            } else {
                $box['_bgcolor'] = isset( $custom_fields['box_general_bgcolor'] ) ? "#{$custom_fields['box_general_bgcolor'][0]}" : '';           
            }  

            if ( ! empty( $custom_fields['box_general_bgcolor_hover'] ) ) {
                $box['bgcolor_hover'] = "#{$custom_fields['box_general_bgcolor_hover'][0]}";    
            } 
                             
            if ( ! empty( $custom_fields['box_general_class'] ) ) {
                $box['class'] = $custom_fields['box_general_class'][0];    
            } 

            if ( $custom_fields['box_heading_font_family'][0] != $custom_fields['box_content_font_family'][0] ) {                
                $import[] = $custom_fields['box_heading_font_family'][0];
                $import[] = $custom_fields['box_content_font_family'][0];                              
            } else {
                $import[] = $custom_fields['box_heading_font_family'][0];       
            }
           
            // ----- Box Heading 

            if (isset($custom_fields['box_heading_link']) && preg_match('/^>?http:\/\/.+/', $custom_fields['box_heading_link'][0])) {
                // Determine the heading link, taking into consideration it can be anything, empty, a custom url, a custom field, or a combination of both
                $data_heading = array();
                $data_heading['link_url'] = isset( $custom_fields['box_heading_link'][0] ) ? $custom_fields['box_heading_link'][0] : '';
                $data_heading['link_text'] = $post_title;
                if ( isset($custom_fields['box_heading_class']) )
                    $data_heading['a_class'] = $custom_fields['box_heading_class'][0];

                $box['header']['text'] = cow_determine_link( $q->post->ID, $data_heading );         
            }  
            
            if ( isset( $custom_fields['box_heading_show'] ) ) 
                $box['header']['show'] = $custom_fields['box_heading_show'][0] == "on" ? 1 : 0;
                                        
            if ( isset( $custom_fields['box_heading_class'] ) ) 
                $box['header']['class'] = $custom_fields['box_heading_class'][0];
            
            if ( isset( $custom_fields['box_heading_font_family'] ) ) 
                $box['header']['font_family'] = $custom_fields['box_heading_font_family'][0];
            
            if ( isset( $custom_fields['box_heading_font_size'] ) ) 
                $box['header']['font_size'] = $custom_fields['box_heading_font_size'][0];                    
            
            if ( isset( $custom_fields['box_heading_line_height'] ) ) 
                $box['header']['line_height'] = $custom_fields['box_heading_line_height'][0];
            
            if ( isset( $custom_fields['box_heading_height'] ) && $custom_fields['box_heading_height'][0] != 0 ) 
                $box['header']['height'] = $custom_fields['box_heading_height'][0];
                    
            if ( isset( $custom_fields['box_heading_align'] ) ) 
                $box['header']['align'] = $custom_fields['box_heading_align'][0];
            
            if ( isset( $custom_fields['box_heading_bgcolor'] ) ) 
                $box['header']['bgcolor'] = "#{$custom_fields['box_heading_bgcolor'][0]}";
                
            if ( isset( $custom_fields['box_heading_color'] ) ) 
                $box['header']['color'] = "#{$custom_fields['box_heading_color'][0]}";
                      
            if ( isset( $custom_fields['box_heading_link']) ) 
                $box['header']['link'] = $custom_fields['box_heading_link'][0];

            // ----- Box Image
            
            // If there is a featured image
            if ( isset( $box_image_src[0] ) ) {

                if (isset($custom_fields['box_image_height']))
                    $box['image']['height'] = $custom_fields['box_image_height'][0];
                    
                if (isset($custom_fields['box_image_width']))    
                    $box['image']['width'] = $custom_fields['box_image_width'][0];
                    
                if (isset($custom_fields['box_image_class']))
                    $box['image']['class'] = $custom_fields['box_image_class'][0];
                
                $box['image']['path'] = $box_image_src[0];
                
                // If image has height and width 
                if ( ! empty($box['image']['height']) || ! empty($box['image']['width']) ) 
                {
                    $image = "<img src='{$wpbp->cow_crop_image( null, $box_image_src[0], $box['image']['width'], $box['image']['height'], $post_title, 'c' )}' title='{$post_title}'>";
                } 
                else 
                { 
                    $image = "<img src='{$box_image_src[0]}' alt='{$post_title}' title='{$post_title}' />";  
                }
                 
                // Determine the image link                
                if ( isset($custom_fields['box_image_link']) && preg_match( '/^>?http:\/\/.+/', $custom_fields['box_image_link'][0]) ) {
                    $data_image = array();
                    $data_image['link_url'] = isset( $custom_fields['box_image_link'][0] ) ? $custom_fields['box_image_link'][0] : '';
                    $data_image['link_text'] = $image;
                    $data_image['a_class'] = $custom_fields['box_image_class'][0];
                    
                    $box['image']['src'] = cow_determine_link( $q->post->ID, $data_image );    
                } 
                else {
                    $box['image']['src'] = $image;    
                }                                                   
            }
            
            // ----- Box Content 
            
            $block_link = $link_src = $btn_icons = $btn_style = '';                   
                
            // Edit link
            if ( $wpbp->get_setting("general", "edit_links") == "on" && is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
                $block_link = '<a class="bttn" href="'. get_edit_post_link(). '" title="" target="_blank">'. __('Edit', WPBOXER_ADMIN_TEXTDOMAIN). '</a>';    
            }
            
            // Content Links                    
            if ( !empty( $custom_fields['box_content_links'][0] ) ) {
                
                $links = unserialize( get_post_meta($q->post->ID, 'box_content_links', true ) );
                unset($links['#']);
                unset($links['linktoggle_keys']);

                if ( count($links) > 0 ) {
                    foreach( $links as $id => $data ) {
                        $block_link .= cow_determine_link( $q->post->ID, $data );            
                    }                     
                }                                                
            }                     

            // Content 
            if ( ! isset( $custom_fields['box_content_excerpt'][0] ) || $custom_fields['box_content_excerpt'][0] == "off" ) {
                if ( $custom_fields['box_content_autop'][0] == 'on' && $wpbp->get_setting("general", "autop") == 'on') {
                    $box['content']['text'] = wpautop(do_shortcode( $post_content. '<div class="clearboth"></div>'. $block_link ));    
                } else {
                    $box['content']['text'] = do_shortcode( $post_content. '<div class="clearboth"></div>'. $block_link );
                }                                                              
            } else { 
                if ( $post_type == 'box' ) {
                    $excerpt = $wpbp->get_excerpt( $post_content, $wpbp->get_setting( "general", "excerpt_length" ) );
                } else {
                    $excerpt = get_the_excerpt();
                }                                                             
                $box['content']['text'] = $excerpt. '<div class="clearboth"></div><a href="'. get_permalink(). '" class="bttn">'. sprintf('%s &raquo;', __('Read More', WPBOXER_ADMIN_TEXTDOMAIN)). '</a>'. trim( $block_link );
            }
                             
            if ( isset( $custom_fields['box_content_align'][0] ) ) 
                $box['content']['align'] = $custom_fields['box_content_align'][0];

            if ( isset( $custom_fields['box_content_font_family'][0] ) ) 
                $box['content']['font_family'] = $custom_fields['box_content_font_family'][0];
            
            if ( isset( $custom_fields['box_content_font_size'][0] ) ) 
                $box['content']['font_size'] = $custom_fields['box_content_font_size'][0];

            if ( isset( $custom_fields['box_content_line_height'][0] ) ) 
                $box['content']['line_height'] = $custom_fields['box_content_line_height'][0];
                                                            
            if ( isset( $custom_fields['box_content_color'][0] ) ) 
                $box['content']['color'] = "#{$custom_fields['box_content_color'][0]}";
            
            if ( isset( $custom_fields['box_content_class'][0] ) ) 
                $box['content']['class'] = $custom_fields['box_content_class'][0];

            if ( $custom_fields['box_content_columns'][0] > 1 ) {                        
                $box['content']['columns'] = "wpboxer-columns column-count-". $custom_fields['box_content_columns'][0];    
            } 
            
            // ----- Footer
            
            if ( isset( $custom_fields['box_footer_show'] ) ) 
                $box['footer']['show'] = $custom_fields['box_footer_show'][0] == "on" ? 1 : 0;
                
            // ----- Done
            
            $boxes[] = $box;
            
            $i++;
                   
        endwhile;

        // PAGINATION
        $pagination = '';
        if ( ( 'false' === $nopaging ) ) {
            if ( ! $count > 0 ) 
                $pagination = __('<div class="navigation error">If you want to paginate the results, the <code>count</code> parameter is required.</div>', WPBOXER_ADMIN_TEXTDOMAIN);
            
            if ( function_exists( 'wp_pagenavi' ) ) { 
                ob_start();
                wp_pagenavi( array( 'query' => $q ) ); 
                $pagination = ob_get_clean();                
            } else {
                $pagination = __('<div class="navigation">Download and activate <a href="http://wordpress.org/extend/plugins/wp-pagenavi/">wp-pagenavi</a> to enable pagination.</div>', WPBOXER_ADMIN_TEXTDOMAIN);
            }       
        }
    } else {
        return sprintf(__('<p class="error">Please make sure at least one active block uses block group <strong>%s</strong> or adjust your shortcode parameters.</p>', WPBOXER_ADMIN_TEXTDOMAIN), $set);
    }      

    wp_reset_postdata();
  
  
    $fonts = '';
    
    // get all Google fonts from the database
    $google_fonts = get_option( $wpbp->slug. '_google_fonts' );
    
    // get only unique fonts
    $import = array_unique( $import );

    // loop through all selected fonts and check if they are from the Google Font Directory
    // If so, add an import statement
    if (is_array($google_fonts)) {
        foreach( $import as $font ) {
            if ( in_array( $font, $google_fonts ) )
                $fonts .= "@import url('http://fonts.googleapis.com/css?family=". str_replace( " ", "+", $font ). "&text=1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz%20%27%22');";    
        }    
    }
        
    return $wpbp->twig->render( '_partials/blocks.tpl', array( 
        'import' => $fonts, 
        'boxes' => $boxes,
        'columns' => $columns,
        'pagination' => $pagination 
    ));
    
}
add_shortcode( 'wpbp_blocks', 'cow_sc_blocks' );
add_shortcode( 'boxer', 'cow_sc_blocks' ); // Deprecated