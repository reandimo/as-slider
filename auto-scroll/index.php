<?php
/*
* Plugin Name: Infinite Scroll Images
* Plugin URI: https://nativoteam.com
* Description: Crea banners con scroll infinito para tu web
* Version: 1.0
* Author: Renan Diaz
* Author URI: https://github.com/reandimo/
* License: MIT
 */

 require('as-options.php');

 defined( 'ABSPATH' ) or die( 'Get out!' );


    global $as_db_version;
    $as_db_version = '1.0';


//INSTALL DATABASE
function as_install() {
    global $wpdb;
    global $as_db_version;

    $table_name = $wpdb->prefix . 'auto_scroll';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE `{$wpdb->base_prefix}auto_scroll` (
        as_id mediumint(9) NOT NULL AUTO_INCREMENT,
        as_images varchar(500) NOT NULL,
        as_name varchar(55) DEFAULT '' NOT NULL,
        as_time_scroll varchar(10) DEFAULT '10s' NOT NULL,
        as_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        as_updated datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (as_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta( $sql );

    add_option( 'as_db_version', $as_db_version );

}


//INSTALL DATA
function as_install_data() {
    global $wpdb;
       
    $table_name = $wpdb->prefix . 'auto_scroll';
    
    $wpdb->insert( 
        $table_name, 
        array( 
            'as_images' => 'http://www.bullytest.zuaru.com/bt/wp-content/uploads/2018/01/lona-mesa-recogida-juguetes.png|http://www.bullytest.zuaru.com/bt/wp-content/uploads/2018/01/Lona-mesa-GG-Bullys-Micro-exotic-line.png', 
            'as_name' => 'as-demo', 
            'as_time_scroll' => '10s', 
            'as_created' => current_time( 'mysql' ),
            'as_updated' => current_time( 'mysql' )
        ) 
    );
}


    //INSTALL TABLE
    register_activation_hook( __FILE__, 'as_install' );
    //INSTALL DEMO DATA
    register_activation_hook( __FILE__, 'as_install_data' );

    
    //SHORTCODE
    function print_auto_scroll( $atts ){

    // get attibutes and set defaults
        extract(shortcode_atts(array(
                'id' => 0,
       ), $atts));

    global $wpdb;
    $table_name = $wpdb->prefix . 'auto_scroll';

    $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE as_id = $id", ARRAY_A );
     
    $slide = '<div id="container-scroll">
                <div class="photobanner-scroll">';

        foreach ( $results as $result )  {

           $scrollImages = explode("|",$result['as_images']);

           foreach ($scrollImages as $attachment_id) {
               
               $slide .= '<img src="'. wp_get_attachment_url( $attachment_id ) .'" />';

                //$slide .= '<img src="'.$image.'" />';

           }

           $slide .= '<img class="last-scroll" src="'. wp_get_attachment_url( $scrollImages[0] ) .'" />'; //REAPEAT FIRST IMAGE

        }

    $slide .=   '       </div>
                    <input type="hidden" id="as-time" value="'.$result["as_time_scroll"].'">
                </div>';


    echo $slide;


    }//END print_auto_scroll



    // STYLES AND SCRIPTS
    function auto_scroll_res(){
        
        wp_register_style( "auto-scroll", plugins_url( "auto-scroll/css/styles.css" ) );

        wp_register_script( "auto_scroll_main_js", plugins_url( "auto-scroll/js/jquery.keyframes.js" ), array('jquery'), '', false );
        wp_register_script( "auto_scroll_main_slide", plugins_url( "auto-scroll/js/printSlide.js" ), array('jquery'), '1.0.0', true );

        wp_enqueue_style( "auto-scroll" );
        wp_enqueue_script( "auto_scroll_main_js" );
        wp_enqueue_script( "auto_scroll_main_slide" );

    }


    //ADD STYLES AND SCRIPTS
    add_action('wp_enqueue_scripts', 'auto_scroll_res');

    
    //[as-gallery]
    add_shortcode('as-gallery', 'print_auto_scroll');



?>

