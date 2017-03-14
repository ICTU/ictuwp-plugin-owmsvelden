<?php
/*
 * owmsvelden. 
 *
 * Plugin Name:   ICTU / WP - OWMS-velden
 * Plugin URI:    https://wbvb.nl/plugins/rhswp-owms-velden/
 * Description:   De mogelijkheid om OWMS velden toe te voegen aan content
 * Version:       0.0.5
 * Version desc:  Taalcode voor niet-standaard pagina's
 * Author:        Paul van Buuren
 * Author URI:    https://wbvb.nl
 * License:       GPL-2.0+
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
        if ( DO_OWMS_DO_DEBUG ) {
          $this->setup_debug_filters();
        }
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

      if ( ! defined( 'RHSWP_CT_DOSSIER' ) ) {
        define( 'RHSWP_CT_DOSSIER', 'dossiers' );       // taxonomy used in theme 'wp-rijkshuisstijl'
      }

      
    }



    public $spatial = array(    
      '1' => array( 
        'label' =>  'Nederland',
        'term'  =>  'overheid:Nederland',
        'uri'   =>  'http://standaarden.overheid.nl/owms/terms/Nederland'
      ),
      '2' => array( 
        'label' =>  'Nederlandse Antillen',
        'term'  =>  'overheid:Nederlandse_Antillen',
        'uri'   =>  'http://standaarden.overheid.nl/owms/terms/Nederlandse_Antillen'
      ),
      '3' => array( 
        'label' =>  'Sint Maarten',
        'term'  =>  'overheid:Sint_Maarten_(Nederlandse_Antillen)',
        'uri'   =>  'http://standaarden.overheid.nl/owms/terms/Sint_Maarten_(Nederlandse_Antillen)'
      ),
      '4' => array( 
        'label' =>  'Aruba',
        'term'  =>  'overheid:Aruba',
        'uri'   =>  'http://standaarden.overheid.nl/owms/terms/Aruba'
      ),
      '5' => array( 
        'label' => 'Curaçao', 
        'term' => 'overheid:Curacao', 
        'uri' =>'http://standaarden.overheid.nl/owms/terms/Curacao'
      )
    );

    public $ministeries = array(    

      '1' => array( 
          'label'   => 'Ministerie van Algemene Zaken', 
          'term'    => 'overheid:Ministerie_van_Algemene_Zaken', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Algemene_Zaken'
         ),
      '2' => array( 
          'label'   => 'Ministerie van Binnenlandse Zaken en Koninkrijksrelaties', 
          'term'    => 'overheid:Ministerie_van_Binnenlandse_Zaken_en_Koninkrijksrelaties', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Binnenlandse_Zaken_en_Koninkrijksrelaties'
         ),
      '3' => array( 
          'label'   => 'Ministerie van Buitenlandse Zaken', 
          'term'    => 'overheid:Ministerie_van_Buitenlandse_Zaken', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Buitenlandse_Zaken'
         ),
      '4' => array( 
          'label'   => 'Ministerie van Defensie', 
          'term'    => 'overheid:Ministerie_van_Defensie', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Defensie'
         ),
      '5' => array( 
          'label'   => 'Ministerie van Economische Zaken', 
          'term'    => 'overheid:Ministerie_van_Economische_Zaken', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Economische_Zaken'
         ),
      '6' => array( 
          'label'   => 'Ministerie van Economische Zaken, Landbouw en Innovatie', 
          'term'    => 'overheid:Ministerie_van_Economische_Zaken_Landbouw_en_Innovatie', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Economische_Zaken_Landbouw_en_Innovatie'
         ),
      '7' => array( 
          'label'   => 'Ministerie van Financiën', 
          'term'    => 'overheid:Ministerie_van_Financien', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Financien'
         ),
      '8' => array( 
          'label'   => 'Ministerie van Infrastructuur en Milieu', 
          'term'    => 'overheid:Ministerie_van_Infrastructuur_en_Milieu', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Infrastructuur_en_Milieu'
         ),
      '9' => array( 
          'label'   => 'Ministerie van Justitie', 
          'term'    => 'overheid:Ministerie_van_Justitie', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Justitie'
         ),
      '10' => array( 
          'label'   => 'Ministerie van Landbouw, Natuur en Voedselkwaliteit', 
          'term'    => 'overheid:Ministerie_van_Landbouw,_Natuur_en_Voedselkwaliteit', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Landbouw,_Natuur_en_Voedselkwaliteit'
         ),
      '11' => array( 
          'label'   => 'Ministerie van Onderwijs, Cultuur en Wetenschap', 
          'term'    => 'overheid:Ministerie_van_Onderwijs,_Cultuur_en_Wetenschap', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Onderwijs,_Cultuur_en_Wetenschap'
         ),
      '12' => array( 
          'label'   => 'Ministerie van Sociale Zaken en Werkgelegenheid', 
          'term'    => 'overheid:Ministerie_van_Sociale_Zaken_en_Werkgelegenheid', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Sociale_Zaken_en_Werkgelegenheid'
         ),
      '13' => array( 
          'label'   => 'Ministerie van Veiligheid en Justitie', 
          'term'    => 'overheid:Ministerie_van_Veiligheid_en_Justitie', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Veiligheid_en_Justitie'
         ),
      '14' => array( 
          'label'   => 'Ministerie van Verkeer en Waterstaat', 
          'term'    => 'overheid:Ministerie_van_Verkeer_en_Waterstaat', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Verkeer_en_Waterstaat'
         ),
      '15' => array( 
          'label'   => 'Ministerie van Volksgezondheid, Welzijn en Sport', 
          'term'    => 'overheid:Ministerie_van_Volksgezondheid,_Welzijn_en_Sport', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Volksgezondheid,_Welzijn_en_Sport'
         ),
      '16' => array( 
          'label'   => 'Ministerie van Volkshuisvesting, Ruimtelijke Ordening en Milieubeheer', 
          'term'    => 'overheid:Ministerie_van_Volkshuisvesting,_Ruimtelijke_Ordening_en_Milieubeheer', 
          'uri'     => 'http://standaarden.overheid.nl/owms/terms/Ministerie_van_Volkshuisvesting,_Ruimtelijke_Ordening_en_Milieubeheer'
         )
      );    



    /**
     * All owmsvelden classes
     */
    private function plugin_classes() {

        return array(
            'owmsveldenSystemCheck'  => DO_OWMS_PATH . 'inc/owmsvelden.systemcheck.class.php',
        );

    }

    /**
     * Check page type
     */
    private function check_page_type() {

        global $wp_query;
        $loop = 'notfound';
    
        if ( $this->is_posts_page() ) {
            $loop = 'blog';
        } elseif ( $wp_query->is_page ) {
            $loop = is_front_page() ? 'front' : 'page';
        } elseif ( $wp_query->is_home ) {
            $loop = 'home';
        } elseif ( $wp_query->is_single ) {
            $loop = ( $wp_query->is_attachment ) ? 'attachment' : 'single';
        } elseif ( $wp_query->is_category ) {
            $loop = 'category';
        } elseif ( $wp_query->is_tag ) {
            $loop = 'tag';
        } elseif ( $wp_query->is_tax ) {
            $loop = 'tax';
        } elseif ( $wp_query->is_archive ) {
            if ( $wp_query->is_day ) {
                $loop = 'day';
            } elseif ( $wp_query->is_month ) {
                $loop = 'month';
            } elseif ( $wp_query->is_year ) {
                $loop = 'year';
            } elseif ( $wp_query->is_author ) {
                $loop = 'author';
            } else {
                $loop = 'archive';
            }
        } elseif ( $wp_query->is_search ) {
            $loop = 'search';
        } elseif ( $wp_query->is_404 ) {
            $loop = 'notfound';
        }
    
        return $loop;
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
    public function owms_debug_info($content = '') {

      global $post;


      $output = $this->get_header_data( $post->ID );
      $output = str_replace( '>', "&gt;", $output );
      $output = str_replace( '<', "&lt;", $output );


      if ( DO_OWMS_DO_DEBUG && WP_DEBUG ) {
        return '<pre>' . $output . '</pre>' . $content;
      }
      else {
        return $content; 
      }
      
      
    }


  	public function is_posts_page() {
  		return ( is_home() && 'page' == get_option( 'show_on_front' ) );
  	}


    /**
     * filter for when the CPT is previewed
     */
    public function owms_debug_info_title(  $title, $id = null ) {

      global $post;

      
      $output = $this->get_header_data( $post->ID );
      $output = str_replace( '>', "&gt;", $output );
      $output = str_replace( '<', "&lt;", $output );


      if ( DO_OWMS_DO_DEBUG && WP_DEBUG ) {
    
        $currenturl = get_page_uri( $post->ID  );
        $postpage   = get_permalink( get_option( 'page_for_posts' ) );

        echo '<div style="border: 1px solid black; white-space: pre-wrap; font-size: 80%; padding: 1em; margin: 1em;"><p>OWMS debug info:</p><pre>' . $output . '</pre></div>';
        

      }
      
      
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
      
      add_action( 'wp_head', array( $this, 'write_header_data' ), 4 );

//      add_action( 'wp_head', array( $this, 'owms_add_lang_metatag' ), 5 );

      add_action( 'language_attributes', array( $this, 'owms_change_html_lang_attr' ) );

      add_action( 'wpseo_locale', array( $this, 'owms_get_lang_code' ) );

    }



    /**
     * Hook owmsvelden into WordPress
     */
    private function setup_debug_filters() {

      	// content filter
      	

        global $wp_query;
        $loop = 'notfound';

      	if ( $wp_query->is_single ) {
        	
          add_filter( 'the_content', array( $this, 'owms_debug_info' ) );
          
      	}
      	else {

          // Find Genesis Theme Data
          $checkgenesis = wp_get_theme( 'genesis' );
          
          if ( $checkgenesis ) {
            // genesis is available.
  
            $theme_info = wp_get_theme();
            
            $genesis_flavors = array(
              'genesis',
              'genesis-trunk',
            );
            
            if ( in_array( $theme_info->Template, $genesis_flavors ) ) {
  
              //* Add some debug info to the genesis_before action.
              add_action( 'genesis_before', array( $this, 'owms_debug_info_title' ), 10, 2 );            
  
            }
            else {
              // genesis exists, but is not activated
              add_filter( 'the_content', array( $this, 'owms_debug_info' ) );
              
            }
          }
          else {
            // genesis does not exist in this environment
            add_filter( 'the_content', array( $this, 'owms_debug_info' ) );

          }
  
      	}



    }




    /**
     * Write out the header data
     */
    public function write_header_data() {

      global $post;
      
      if ( is_object( $post ) ) {
        echo $this->get_header_data( $post->ID );
      }

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

        wp_dequeue_script( 'link' ); // WP Posts Filter Fix (Advanced Settings not toggling)
        wp_dequeue_script( 'ai1ec_requirejs' ); // All In One Events Calendar Fix (Advanced Settings not toggling)


        do_action( 'DO_OWMS_register_admin_scripts' );

    }


    //========================================================================================================
    /**
     * Output the HTML
     */
    public function get_header_data( $postid ) {
      
      global $post;
      global $wp_query;
      
      if ( $postid ) {
        $postid = $postid;
      }
      else {
        $postid = $post->ID;
      }

      if ( $this->is_posts_page() ) {
        $postid = get_option( 'page_for_posts' );
      }


      $returnstring         = '';      
      $owms_title           = '';
      $owms_type            = 'webpagina';
      $owms_identifier       = get_permalink( $postid );
      $owms_language        = $this->get_stored_values( $postid, DO_OWMS_FIELD . 'language', '' );
      $owms_rights          = $this->get_stored_values( $postid, DO_OWMS_FIELD . 'rights', '' );
      $currentposttype      = get_post_type( $postid );

      $owms_authority       = $this->get_stored_values( $postid, DO_OWMS_FIELD . 'authority', '' );
      $owms_creator         = $this->get_stored_values( $postid, DO_OWMS_FIELD . 'creator', '' );
      $owms_spatial         = $this->get_stored_values( $postid, DO_OWMS_FIELD . 'spatial', '' );
      
      
      $owms_date_modified   = get_the_modified_time( 'Y-m-d\TH:i:s' );
      $owms_date_published  = get_the_date( get_option( 'date_format' ), $postid );
      

      if ( $currentposttype === 'post' ) {
        $owms_type      = 'nieuwsbericht';
      }
      
      $pagetype  = $this->check_page_type( $postid );

      if ( $pagetype && DO_OWMS_DO_DEBUG && WP_DEBUG ) {
        $returnstring .= '<meta name="OWMS-plugin" type="debug-info-pagetype" content="' . $pagetype . " (posttype: " . $currentposttype . ")\"/>\n";      
      } 
      

      switch ($pagetype) {


        case 'blog':

          $owms_title   = get_the_title( $postid );
          $owms_type    = 'overzichtspagina';

          break;

        case 'tax':

          $term = is_tax() ? get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ) : $wp_query->get_queried_object();

          if ( $term->name ) {
            $owms_title  = get_the_title( $term->name );
          }
          else {
            $owms_title  = __( 'Onbekende taxonomie', "owmsvelden-translate" );
          }

          $owms_type = 'overzichtspagina';

          break;

        default:

          $owms_title  = get_the_title( $postid );

          break;

      }      

// OWMS-Kern bestaat uit
  
//-  dcterms:identifier (Verwijzing)
//-  dcterms:title (Titel)
//-  dcterms:type (Informatietype)
//-  dcterms:language (Taal)
//-  overheid:authority (Eindverantwoordelijke)
//-  dcterms:creator (Maker)
//-  dcterms:modified (Wijzigingsdatum)
//-  dcterms:temporal (Dekking in tijd) (NVT)
//-  dcterms:spatial (Locatie)
        
      
      if ( $owms_identifier ) {
        $returnstring .= '<meta name="DCTERMS.identifier" title="XSD.anyURI" content="' . $owms_identifier . "\"/>\n";      
      } 

      if ( $owms_title ) {
        $returnstring .= '<meta name="DCTERMS.title" content="' . $owms_title . "\"/>\n";      
      } 

      if ( $owms_type ) {
        $returnstring .= '<meta name="DCTERMS.type" title="RIJKSOVERHEID.Informatietype" content="' . $owms_type . "\"/>\n";      
      } 

      if ( $owms_language ) {
        $returnstring .= '<meta name="DCTERMS.language" title="XSD.language" content="' . $owms_language . "\"/>\n";      
      } 

      if ( $owms_authority ) {

// <meta name="OVERHEID.authority" title="RIJKSOVERHEID.Organisatie" content="Ministerie van Algemene Zaken"/>-->        

        $returnstring .= '<meta name="OVERHEID.authority" title="' . $this->ministeries[$owms_authority]['label'] . '" content="' . $this->ministeries[$owms_authority]['label'] . "\"/>\n";      

        if ( $owms_rights ) {
          $returnstring .= '<meta name="DCTERMS.rightsHolder" title="RIJKSOVERHEID.Organisatie" content="' . $this->ministeries[$owms_authority]['label'] . "\"/>\n";      
          $returnstring .= '<meta name="DCTERMS.rights" content="' . $owms_rights . "\"/>\n";      
        } 
      } 


      if ( $owms_creator ) {
        $returnstring .= '<meta name="DCTERMS.creator" title="RIJKSOVERHEID.Organisatie" content="' . $this->ministeries[$owms_creator]['label'] . "\"/>\n";      
      } 

      if ( $owms_date_modified ) {
        $returnstring .= '<meta name="DCTERMS.modified" title="XSD.dateTime" content="' . $owms_date_modified . "\"/>\n";    
      } 

      if ( $owms_spatial ) {
        $returnstring .= '<meta name="DCTERMS.spatial" title="OVERHEID.Koninkrijksdeel" content="' . $this->spatial[$owms_spatial]['label'] . "\"/>\n";    
      } 



      // assume RHSWP_CT_DOSSIER to be a valid value for DCTERMS.subject
      // this taxonomy is active in theme 'wp-rijkshuisstijl'
      if ( taxonomy_exists( RHSWP_CT_DOSSIER ) ) {
        $terms = get_the_terms( $postid , RHSWP_CT_DOSSIER );
        if ( $terms && ! is_wp_error( $terms ) ) { 
          foreach($terms as $term) {
            $returnstring .= '<meta name="DCTERMS.subject" content="' . $term->name . "\"/>\n"; 
          }
        }
      }
      elseif ( taxonomy_exists( 'category' ) ) {
        $terms = get_the_terms( $postid , 'category' );
        if ( $terms && ! is_wp_error( $terms ) ) { 
          foreach($terms as $term) {
            $returnstring .= '<meta name="DCTERMS.subject" content="' . $term->name . "\"/>\n"; 
          }
        }
      }

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

      if ( DO_OWMS_USE_CMB2 ) {
        
        if ( ! defined( 'CMB2_LOADED' ) ) {
          die( ' CMB2_LOADED not loaded ' );
          return false;
          // cmb2 NOT loaded
        }
        else {
          // okidokie!
        }
      
        add_action( 'cmb2_admin_init', 'rhswp_register_metabox_owmsvelden' );
      
        /**
         * Hook in and add a demo metabox. Can only happen on the 'cmb2_admin_init' or 'cmb2_init' hook.
         */
        function rhswp_register_metabox_owmsvelden() {
  
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
          'show_option_none' => false,
          'options'          => array(
            ''   => __( 'Niet van toepassing', "owmsvelden-translate" ),
            'CC0 1.0 Universal'   => __( 'CC0 1.0 Universal', "owmsvelden-translate" ),
          ),
          ) );
  
          $activeministeries = array( 
              '' => __( 'Niet van toepassing', "owmsvelden-translate" ),
              '2' => 'Ministerie van Binnenlandse Zaken en Koninkrijksrelaties',
              '5' => 'Ministerie van Economische Zaken'
            );
  
          $spatialused = array(    
            '1' => 'Nederland',
            '2' => 'Nederlandse Antillen',
            '3' => 'Sint Maarten',
            '4' => 'Aruba',
            '5' => 'Curaçao', 
          );
  
          $cmb2_metafields->add_field( array(
          'name'             => __( 'authority', "owmsvelden-translate" ),
          'desc'             => __( 'authority', "owmsvelden-translate" ),
          'id'            => DO_OWMS_FIELD . 'authority',
          'type'             => 'select',
          'show_option_none' => false,
          'options'          => $activeministeries,
          ) );
  
          $cmb2_metafields->add_field( array(
          'name'             => __( 'creator', "owmsvelden-translate" ),
          'desc'             => __( 'creator', "owmsvelden-translate" ),
          'id'            => DO_OWMS_FIELD . 'creator',
          'type'             => 'select',
          'show_option_none' => false,
          'options'          => $activeministeries,
          ) );
  
          $cmb2_metafields->add_field( array(
          'name'             => __( 'spatial', "owmsvelden-translate" ),
          'desc'             => __( 'spatial', "owmsvelden-translate" ),
          'id'            => DO_OWMS_FIELD . 'spatial',
          'type'             => 'select',
          'show_option_none' => false,
          'options'          => $spatialused,
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
  

    //========================================================================================================
    function owms_add_lang_metatag (){
  
      global $post;
      $postid = get_the_ID();
  
      if ( $postid ) {
        $postid = $postid;
      }
      else {
        $postid = $post->ID;
      }
      if ( $postid ) {
        $owms_language        = $this->get_stored_values( $postid, DO_OWMS_FIELD . 'language', '' );
        if ( $owms_language ) {
          echo "<meta http-equiv=\"content-language\" content=\"" . $owms_language . "\">";
        }
      }
    }
  

    //========================================================================================================
    function owms_change_html_lang_attr() {
      return "lang=\"" . $this->owms_get_lang_code() . "\"";
    }
  

    //========================================================================================================
    function owms_get_lang_code() {
      $owms_language = 'nl-NL'; // default language
  
      global $post;
      $postid = get_the_ID();
  
      if ( ( !$postid ) && ( is_object( $post ) ) ){
        $postid = $post->ID;
      }

      if ( $postid ) {
        $owms_language        = $this->get_stored_values( $postid, DO_OWMS_FIELD . 'language', '' );
        if ( $owms_language ) {
          return $owms_language;
        }
      }
      return $owms_language;
      
    }
  



}

endif;

add_action( 'plugins_loaded', array( 'OWMSvelden', 'init' ), 10 );