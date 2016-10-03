<?php
/*
 * owmsvelden. 
 *
 * Plugin Name: ICTU / WP - OWMS-velden
 * Plugin URI:  https://wbvb.nl/plugins/rhswp-owms-velden/
 * Description: De mogelijkheid om OWMS velden toe te voegen aan content
 * Version:     0.0.1
 * Author:      Paul van Buuren
 * Author URI:  https://wbvb.nl
 * License:     GPL-2.0+
 *
 * Text Domain: owmsvelden-translate
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access
}

if ( ! class_exists( 'OWMSvelden' ) ) :

/**
 * Register the plugin.
 *
 * Display the administration panel, insert JavaScript etc.
 */
class OWMSvelden {

    /**
     * @var string
     */
    public $version = '0.0.1';


    /**
     * @var owmsvelden
     */
    public $owmsvelden = null;


    /**
     * Init
     */
    public static function init() {

        $DO_OWMS_this = new self();

    }


    /**
     * Constructor
     */
    public function __construct() {

        $this->define_constants();
        $this->includes();
        $this->setup_actions();
        $this->setup_filters();
        $this->append_comboboxes();


    }


    /**
     * Define owmsvelden constants
     */
    private function define_constants() {

      $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';

      define( 'DO_OWMS_VERSION',    $this->version );
      define( 'DO_OWMS_FOLDER',     'rhswp-owms-velden' );
      define( 'DO_OWMS_BASE_URL',   trailingslashit( plugins_url( DO_OWMS_FOLDER ) ) );
      define( 'DO_OWMS_ASSETS_URL', trailingslashit( DO_OWMS_BASE_URL . 'assets' ) );
      define( 'DO_OWMS_MEDIAELEMENT_URL', trailingslashit( DO_OWMS_BASE_URL . 'mediaelement' ) );
      define( 'DO_OWMS_PATH',       plugin_dir_path( __FILE__ ) );
      define( 'DO_OWMS_FIELD',      'owmsvelden_pf_' ); // prefix for owmsvelden metadata fields
      define( 'DO_OWMS_DO_DEBUG',   false );
      define( 'DO_OWMS_USE_CMB2',   true ); 

    }


    /**
     * All owmsvelden classes
     */
    private function plugin_classes() {

        return array(
            'owmsveldenSystemCheck'  => DO_OWMS_PATH . 'inc/owmsvelden.systemcheck.class.php',
        );

    }


    /**
     * Load required classes
     */
    private function includes() {
    
      if ( DO_OWMS_USE_CMB2 ) {
        // load CMB2 functionality
        if ( ! defined( 'CMB2_LOADED' ) ) {
          // cmb2 NOT loaded
          if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
            require_once dirname( __FILE__ ) . '/cmb2/init.php';
          }
          elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
            require_once dirname( __FILE__ ) . '/CMB2/init.php';
          }
        }
      }
      
      
      
      $autoload_is_disabled = defined( 'DO_OWMS_AUTOLOAD_CLASSES' ) && DO_OWMS_AUTOLOAD_CLASSES === false;
      
      if ( function_exists( "spl_autoload_register" ) && ! ( $autoload_is_disabled ) ) {
        
        // >= PHP 5.2 - Use auto loading
        if ( function_exists( "__autoload" ) ) {
          spl_autoload_register( "__autoload" );
        }
        spl_autoload_register( array( $this, 'autoload' ) );
        
      } 
      else {
        // < PHP5.2 - Require all classes
        foreach ( $this->plugin_classes() as $id => $path ) {
          if ( is_readable( $path ) && ! class_exists( $id ) ) {
            require_once( $path );
          }
        }
        
      }
    
    }







    /**
     * filter for when the CPT is previewed
     */
    public function content_filter_for_preview($content = '') {

      global $post;
      
      
      // lets go
      return $content; // . ' / ' . $this->rhswp_append_data_to_header( $post->ID );
      
    }


    /**
     * Autoload owmsvelden classes to reduce memory consumption
     */
    public function autoload( $class ) {

        $classes = $this->plugin_classes();

        $class_name = strtolower( $class );

        if ( isset( $classes[$class_name] ) && is_readable( $classes[$class_name] ) ) {
            require_once( $classes[$class_name] );
        }

    }



    /**
     * Hook owmsvelden into WordPress
     */
    private function setup_actions() {

      add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
      
      add_action( 'wp_head', array( $this, 'rhswp_append_data_to_header' ), 4 );

    }



    /**
     * Hook owmsvelden into WordPress
     */
    private function setup_filters() {

      	// content filter
        add_filter( 'the_content', array( $this, 'content_filter_for_preview' ) );


    }




    /**
     * Initialise translations
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain( "owmsvelden-translate", false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


    }

    


    /**
     * Register admin-side styles
     */
    public function register_admin_styles() {

        wp_enqueue_style( 'owmsvelden-admin-styles', DO_OWMS_ASSETS_URL . 'css/admin.css', false, DO_OWMS_VERSION );

        do_action( 'DO_OWMS_register_admin_styles' );

    }


    /**
     * Register admin JavaScript
     */
    public function register_admin_scripts() {

        // media library dependencies
        wp_enqueue_media();

        // plugin dependencies
        wp_enqueue_script( 'jquery-ui-core', array( 'jquery' ) );
//        wp_enqueue_script( 'jquery-ui-sortable', array( 'jquery', 'jquery-ui-core' ) );

        wp_dequeue_script( 'link' ); // WP Posts Filter Fix (Advanced Settings not toggling)
        wp_dequeue_script( 'ai1ec_requirejs' ); // All In One Events Calendar Fix (Advanced Settings not toggling)


        do_action( 'DO_OWMS_register_admin_scripts' );

    }


    //========================================================================================================
    /**
     * Output the HTML
     */
    public function rhswp_append_data_to_header($postid) {
      
      global $post;
      
      if ( $postid ) {
        $postid = $postid;
      }
      else {
        $postid = $post->ID;
      }
      

      $owms_language  = $this->get_stored_values( $postid, DO_OWMS_FIELD . 'language', '' );

      if ( $owms_language ) {
        echo '<meta name="DCTERMS.language" title="XSD.language" content="' . $owms_language . "\"/>\n";      
      } 

      $owms_rights  = $this->get_stored_values( $postid, DO_OWMS_FIELD . 'rights', '' );

      if ( $owms_rights ) {
        echo '<meta name="DCTERMS.rights" content="' . $owms_rights . "\"/>\n";      
      } 


      $owms_permalink  = get_permalink( $postid );

      if ( $owms_permalink ) {
        echo '<meta name="DCTERMS.identifier" title="XSD.anyURI" content="' . $owms_permalink . "\"/>\n";      
      } 





return;
    


        return $returnstring;
    }

    //========================================================================================================

    private function get_stored_values( $postid, $postkey, $defaultvalue = '' ) {

      if ( DO_OWMS_DO_DEBUG ) {
        $returnstring = $defaultvalue;
      }
      else {
        $returnstring = '';
      }

      $temp = get_post_meta( $postid, $postkey, true );
      if ( $temp ) {
        $returnstring = $temp;
      }
      
      return $returnstring;
    }

    //========================================================================================================
    
    
    
    public function append_comboboxes() {

       //  echo '<h1 style="border: 1px solid black; z-index: 9999; position: absolute; bottom: 10px; right: 10px; background: white; padding: 10px;">append_comboboxes</h1>';
    
    if ( DO_OWMS_USE_CMB2 ) {
      
      if ( ! defined( 'CMB2_LOADED' ) ) {
        die( ' CMB2_LOADED not loaded ' );
        return false;
        // cmb2 NOT loaded
      }
      else {
       //  echo '<h1 style="border: 1px solid black; z-index: 9999; position: absolute; bottom: 40px; right: 10px; background: white; padding: 10px;">Gebruik die velden!</h1>';
      }
    
      add_action( 'cmb2_admin_init', 'rhswp_register_metabox_owmsvelden' );
    
      /**
       * Hook in and add a demo metabox. Can only happen on the 'cmb2_admin_init' or 'cmb2_init' hook.
       */
      function rhswp_register_metabox_owmsvelden() {

       //  echo '<h1 style="border: 1px solid black; z-index: 9999; position: absolute; bottom: 90px; right: 10px; background: white; padding: 10px;">rhswp_register_metabox_owmsvelden</h1>';


      	/**
      	 * Metabox with fields for the video
      	 */
      	$cmb2_metafields = new_cmb2_box( array(
          'id'            => DO_OWMS_FIELD . 'metabox',
          'title'         => __( 'OWMS velden', "owmsvelden-translate" ),
          'object_types'  => array( 'page', 'post' ), // Post type
      	) );
    
      	/**
      	 * The fields
      	 */


        $cmb2_metafields->add_field( array(
        'name'             => __( 'Taal van deze pagina', "owmsvelden-translate" ),
        'desc'             => __( 'Taal van deze pagina', "owmsvelden-translate" ),
        'id'            => DO_OWMS_FIELD . 'language',
        'type'             => 'select',
        'show_option_none' => false,
        'options'          => array(
          'nl-NL'   => __( 'Nederlands (nl-NL)', "owmsvelden-translate" ),
          'en-GB'   => __( 'Engels (en-GB)', "owmsvelden-translate" ),
          'en-US'   => __( 'Engels (n-US)', "owmsvelden-translate" ),
          'pap-AW'  => __( 'Papiamento (pap-AW)', "owmsvelden-translate" ),
          'pap-CW'  => __( 'Papiamentu (pap-CW)', "owmsvelden-translate" ),
        ),
        ) );

        $cmb2_metafields->add_field( array(
        'name'             => __( 'Gebruiksrechten voor de pagina', "owmsvelden-translate" ),
        'desc'             => __( '(auteursrechtelijke licentie)', "owmsvelden-translate" ),
        'id'            => DO_OWMS_FIELD . 'rights',
        'type'             => 'select',
        'show_option_none' => true,
        'options'          => array(
          'CC0 1.0 Universal'   => __( 'CC0 1.0 Universal', "owmsvelden-translate" ),
        ),
        ) );


        require_once dirname( __FILE__ ) . '/inc/cmb2-check-required-fields.php';



      }
    
    
    }  // DO_OWMS_USE_CMB2
    
}    

    //========================================================================================================
    



    /**
     * Check our WordPress installation is compatible with owmsvelden
     */
    public function do_system_check() {

        $systemCheck = new owmsveldenSystemCheck();
        $systemCheck->check();

    }









}

endif;

add_action( 'plugins_loaded', array( 'OWMSvelden', 'init' ), 10 );