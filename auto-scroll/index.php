<?php
/*
* Plugin Name: Banners de Scroll Infinito
* Plugin URI: https://nativoteam.com
* Description: Crea banners con scroll infinito para tu web
* Version: 1.0
* Author: Renan Diaz
* Author URI: https://github.com/reandimo/
* License: MIT
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


$as_db_version = '1.0';


//INSTALL DATABASE

function nativo_as_install() {

    global $wpdb;
    global $as_db_version;

    $table_name = $wpdb->prefix . 'auto_scroll';
    
    if($wpdb->get_var( "show tables like '$table_name'" ) != $table_name) 
    {

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE `$table_name` (
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

}

    //Install Table
    register_activation_hook( __FILE__, 'nativo_as_install' );

/*----------------------------------------------------------------------------------------
|
|
|									Admin Section function
|
|
|----------------------------------------------------------------------------------------*/


//Menu Pages
function nativo_as_menu() 
{
	//Menu pages
	add_menu_page( 'Auto Scroll Slider', 'Sliders', 'manage_options', 'as-slider', 'nativo_get_as_grid', 'dashicons-format-gallery' );
	//Submenu pages
	add_submenu_page( 'as-slider', 
    				  'Scroll Banner', 
    				  'Crear Slider',
    				  'manage_options', 
    				  'as-new',
    				  'nativo_as_new_slider' );

	add_submenu_page( 'as-slider', 
    				  'Scroll Banner', 
    				  'Editar Slider',
    				  'manage_options', 
    				  'as-edit',
    				  'nativo_get_as_slider' );

    add_submenu_page( 'as-slider', 
    				  'Scroll Banner', 
    				  'Ajustes',
    				  'manage_options', 
    				  'as-opt',
    				  'nativo_as_options' );

}

/**
 * Load scripts and style sheet for settings page
 */
function nativo_as_load_scripts_admin() {

    // WordPress library
    wp_enqueue_media();
    //Media uploader and single slider settings scripts
    wp_register_script( "auto_scroll_mediaup", plugins_url( "auto-scroll/js/jquery.media.js" ), array('jquery'), '1.0.0', true );
    wp_enqueue_script( "auto_scroll_mediaup" );
    //Media uploader and single slider settings styles
    wp_register_style( "auto-scroll", plugins_url( "auto-scroll/css/admin-styles.css" ) );
    wp_enqueue_style( "auto-scroll" );

}

//Option Pages Set
add_action( 'admin_menu', 'nativo_as_menu' );

//Include Admin Scripts
add_action( 'admin_enqueue_scripts', 'nativo_as_load_scripts_admin' );

//Ajax Post - Update Slider Data
add_action( 'wp_ajax_nativo_as_update_ajax_post', 'nativo_as_update_ajax_post' );

//Ajax Post - New Slider Data
add_action( 'wp_ajax_nativo_as_new_ajax_post', 'nativo_as_new_ajax_post' );

//Ajax Post - Delete Slider Data
add_action( 'wp_ajax_nativo_as_delete_ajax_post', 'nativo_as_delete_ajax_post' );

/*----------------------------------------------------------------------------------------
|
|
|									Edit page functions
|
|
|----------------------------------------------------------------------------------------*/

//Get slider by id - Edit Page
function nativo_get_as_slider(){

	if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'No tieme permiso suficientes para acceder a esta pagina.' ) );
		}

	echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
    echo '<h2>Editar Slider</h2>';
    echo '</div>';

	//Get ID
	$id = $_GET['as-id'];

	//Set DB Data
    global $wpdb;
    $table_name = $wpdb->prefix . 'auto_scroll';

    $row = $wpdb->get_results( "SELECT * FROM $table_name WHERE as_id = $id", ARRAY_A );

    if ( !empty( $row ) ) {

?>

	<div id="ajax-res"></div>

		<form id="as-form" method="post"> 

    			<table class="form-table" id="as-images-table"> 

<?php
    	foreach ( $row as $field ) {
?>

					<tr valign="top">
    				    <th scope="row">Nombre:</th>
    				    <td>        
    				     	<input type="text" name="as_name" id="as_name" value="<?= $field['as_name'] ?>"/>
    				     	    
    				     	    <!-- HIDDEN INPUT ID -->
    							<input type="hidden" name="as_id" id="as-carousel-name" value="<?= $field['as_id'] ?>"/>

    				    </td>
    				    <th scope="row">Shortcode:</th>
    				    <td>        
    				     	<input type="text" readonly name="as_shortcode" id="shortcode" value='[as-gallery id="<?= $field['as_id'] ?>"]'/>
    				     	    
    				    </td>
    				 </tr>

    				<tr valign="top">
    				    <th scope="row">Tiempo:</th>
    				    <td>        
    				     	<input type="text" name="as_time_scroll" id="as_time_scroll" value="<?= $field['as_time_scroll'] ?>"/>
    				    </td>
    				 </tr>	

<?php
			//Get each image ID
    		$scrollImages = explode("|", $field['as_images']);

    		foreach ( $scrollImages as $image ) {
	    	    // Print HTML field
?>

						    	<tr valign="top">
			    				    <th scope="row">Imagen #:</th>
			    				    <td>
			    				    <div class="upload">
						            	<img src="<?= wp_get_attachment_url( $image ) ?>" height="60px" />
						            	<div>
							            	<input type="hidden" name="as_images" value="<?= $image ?>" />
							                <button type="submit" class="upload_image_button button">Cargar</button>
							                <button type="submit" class="remove_image_button button">&times;</button>
						                </div>
						            </div>
						            </td>
			    				  </tr>
 <?php
    		}

    	}
?>

		<tr id="add_row">
			<td>
			  	 <div class="upload">
			     	 <div>
			          <a class="add_row_image button">Nueva imagen</a>
			         </div>
			     </div>
			</td>
		</tr>
		

		</table> <!-- END TABLE -->

		<a id="save-as" class="button button-primary">Guardar</a>

	</form>

<?php

    	}else{

    		nativo_get_as_grid();


    	} 

} //End function


/*----------------------------------------------------------------------------------------
|
|
|									Grid page functions
|
|
|----------------------------------------------------------------------------------------*/

function nativo_get_as_grid(){

if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'No tieme permiso suficientes para acceder a esta pagina.' ) );
	}

	    echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
        echo '<h2>Sliders</h2>';
    	echo '</div>';

		//Set DB Data
	    global $wpdb;
	    $table_name = $wpdb->prefix . 'auto_scroll';

		$row = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );

        $image = $default_image;

        //CHECK IF QUERY IS NOT EMPTY
		if ( !empty( $row ) ) {

		?>
	    	    <!-- One Field -->
				<ul class="list">
		<?php


			foreach ( $row as $field ) {

		?>

				<li class="list__item">
					<figure class="list__item__inner">
						<div class="scrollable">
		<?php

					       	$scrollImages = explode("|",$field['as_images']);

					        foreach ($scrollImages as $attachment_id) {

		?>

							
								<img src="<?= wp_get_attachment_url( $attachment_id ) ?>" alt="">
								

		<?php

					       	}

		?>
					</div>
						<figcaption>
							<p>

							<b>Nombre: </b>
							<span><?= $field['as_name'] ?></span>
							<br>
							<b>Tiempo de animacion: </b>
							<span><?= $field['as_time_scroll'] ?></span>
							<br>
							<b>Creado: </b>
							<span><?= $field['as_created'] ?></span>
							<br>
							<b>Actualizado: </b>
							<span><?= $field['as_updated'] ?></span>
							<br>
							<label>Shortcode:</label>
							<input type="text" readonly="" value='[as-gallery id="<?= $field['as_id'] ?>"]'>

							</p>

							<section align="center">

								<a class="button button-primary button-large" href="?page=as-edit&as-id=<?= $field['as_id'] ?>"><span style="margin-top: 4px;" class="dashicons dashicons-edit"></span> Editar</a>
								
								<a slider-id="<?= $field['as_id'] ?>" class="button button-primary delete-btn delete-as button-large"><span style="margin-top: 4px;" class="dashicons dashicons-no"></span> Eliminar</a>
							
							</section>
						</figcaption>
					</figure>
				</li>				    <!-- other items -->

		<?php } ?>

				</ul>
				<!-- end grid -->
		<?php 

		}//Empty check

}//End function


/*----------------------------------------------------------------------------------------
|
|
|									New Slider functions
|
|
|----------------------------------------------------------------------------------------*/

function nativo_as_new_slider(){

?>

<div class="wrap"><div id="icon-tools" class="icon32"></div>
	<h2>Nuevo Slider</h2>
</div>

	<div id="ajax-res"></div>

		<form id="as-form" method="post"> 

    			<table class="form-table" id="as-images-table"> 
					<tr valign="top">
    				    <th scope="row">Nombre:</th>
    				    <td>        
    				     	<input type="text" name="as_name" id="as_name" value="<?= $field['as_name'] ?>"/>
    				     	    
    				     	    <!-- HIDDEN INPUT ID 
    							<input type="hidden" name="as_id" id="as-carousel-name" value=""/>-->

    				    </td>
    				    <th scope="row">Shortcode:</th>
    				    <td>        
    				     	<input type="text" readonly name="as_shortcode" id="shortcode" value='[as-gallery id=""]'/>
    				     	    
    				    </td>
    				  </tr>

    				<tr valign="top">
    				    <th scope="row">Tiempo:</th>
    				    <td>        
    				     	<input type="text" name="as_time_scroll" id="as_time_scroll"/>
    				    </td>
    				  </tr>
						    	<tr valign="top">
			    				    <th scope="row">Imagen #:</th>
			    				    <td>
			    				    <div class="upload">
						            	<img src="<?= plugins_url( "auto-scroll/img/no-image.png" ) ?>" height="60px" />
						            	<div>
							            	<input type="hidden" name="as_images" value="<?= $image ?>" />
							                <button type="submit" class="upload_image_button button">Cargar</button>
							                <button type="submit" class="remove_image_button button">&times;</button>
						                </div>
						            </div>
						            </td>
			    				  </tr>
		<tr id="add_row">
			<td>
			  	 <div class="upload">
			     	 <div>
			          <a class="add_row_image button">Nueva imagen</a>
			         </div>
			     </div>
			</td>
		</tr>
		

		</table> <!-- END TABLE -->

		<a id="save-new-as" class="button button-primary">Guardar</a>

	</form>

<?php


}




/*----------------------------------------------------------------------------------------
|
|
|									Ajax Slider functions
|
|
|----------------------------------------------------------------------------------------*/


//Ajax Post - Insert Slider Data
function nativo_as_new_ajax_post() {

    global $wpdb;
       
    $table_name = $wpdb->prefix . 'auto_scroll';

    $save_as = $wpdb->query("INSERT INTO `$table_name`( 
    						`as_images`, 
					    	`as_name`, 
					    	`as_time_scroll`, 
					    	`as_created`, 
					    	`as_updated`) 

					    	VALUES ( '". $_POST['as_images'] ."',
					    	'". $_POST['as_name'] ."',
					    	'". $_POST['as_time_scroll'] ."',
					    	NOW(),
					    	NOW() )");

    //ERROR HANDLER
    if ($save_as == true) {
    	
    	$response = array('code' => 1, 'message' => '<div class="updated fade"><p><strong>Slider Agregado!</p></div>', 'as_id' => $wpdb->insert_id );
    
    }else{

		if($wpdb->last_error !== ''){

	        $str   = htmlspecialchars( $wpdb->last_result, ENT_QUOTES );
	        $query = htmlspecialchars( $wpdb->last_query, ENT_QUOTES );

	        $msg = '<div class="error fade"><p><strong>Ups! Hubo un error y no se pudo agregar el Slider.</strong></p></div>';

	    }

    	$response = array('code' => 0, 'message' => $msg);

    }

    //PRINT MESSAGE
    echo json_encode($response);

	wp_die(); // this is required to terminate immediately and return a proper response

}


//Ajax Post - Update Slider Data
function nativo_as_update_ajax_post() {

    global $wpdb;
       
    $table_name = $wpdb->prefix . 'auto_scroll';

    $save_as = $wpdb->query("UPDATE $table_name 

    			  SET `as_images`='". $_POST['as_images'] ."',
	    			  `as_name`='". $_POST['as_name'] ."',
	    			  `as_time_scroll`='". $_POST['as_time_scroll'] ."',
	    			  `as_updated`=NOW() 

    			  WHERE `as_id`='". $_POST['as_id'] ."'");

    //ERROR HANDLER
    if ($save_as == true) {
    	
    	$response = array('code' => 1, 'message' => '<div class="updated fade"><p><strong>Slider Actualizado!</p></div>');
    
    }else{

		if($wpdb->last_error !== ''){

	        $str   = htmlspecialchars( $wpdb->last_result, ENT_QUOTES );
	        $query = htmlspecialchars( $wpdb->last_query, ENT_QUOTES );

	        $msg = '<div class="error fade"><p><strong>Ups! Hubo un error y no se pudo actualizar.</strong></p></div>';

	    }

    	$response = array('code' => 0, 'message' => $msg );

    }

    //PRINT MESSAGE
    echo json_encode($response);

	wp_die(); // this is required to terminate immediately and return a proper response

}

//Ajax Post - Delete Slider Data
function nativo_as_delete_ajax_post() {

    global $wpdb;
       
    $table_name = $wpdb->prefix . 'auto_scroll';

    $delete_as = $wpdb->query("DELETE FROM `$table_name` WHERE `as_id`='". $_POST['as_id'] ."'");

    //ERROR HANDLER
    if ($delete_as == true) {
    	
    	$response = array('code' => 1, 'message' => '<div class="updated fade"><p><strong>Slider Eliminado!</p></div>', 'as_id' => $_POST['as_id'] );
    
    }else{

		if($wpdb->last_error !== ''){

	        $str   = htmlspecialchars( $wpdb->last_result, ENT_QUOTES );
	        $query = htmlspecialchars( $wpdb->last_query, ENT_QUOTES );

	        $msg = '<div class="error fade"><p><strong>Ups! Hubo un error y no se pudo eliminar el Slider.</strong></p></div>';

	    }

    	$response = array('code' => 0, 'message' => $msg);

    }

    //PRINT MESSAGE
    echo json_encode($response);

	wp_die(); // this is required to terminate immediately and return a proper response

}

/*----------------------------------------------------------------------------------------
|
|
|									Option Page functions
|
|
|----------------------------------------------------------------------------------------*/


//Plugin Options
function nativo_as_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'No tieme permiso suficientes para acceder a esta pagina.' ) );
	}
	echo '<div class="wrap">';
	echo '<h1>Ajustes Generales</h1>';

?>

<form>

<label>Label</label>
<input type="text" name="">
<br>
<label>Label</label>
<input type="text" name="">
<br>
<label>Label</label>
<input type="text" name="">
<br>
<label>Custom CSS</label>
<textarea name="as-css"></textarea>

</form>

<?php 

}


/*----------------------------------------------------------------------------------------
|
|
|									Shortcode function
|
|
|----------------------------------------------------------------------------------------*/



    //SHORTCODE
    function nativo_print_auto_scroll( $atts ){

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

    //[as-gallery]
    add_shortcode('as-gallery', 'nativo_print_auto_scroll');

/*----------------------------------------------------------------------------------------
|
|
|									Script n Styles function
|
|
|----------------------------------------------------------------------------------------*/


    // STYLES AND SCRIPTS
    function nativo_auto_scroll_res(){
        
        //Sliders Shortcode CSS
        wp_register_style( "auto-scroll", plugins_url( "auto-scroll/css/styles.css" ) );
        //Sliders Required Keyframes
        wp_register_script( "auto_scroll_main_js", plugins_url( "auto-scroll/js/jquery.keyframes.js" ), array('jquery'), '', false );
        //Sliders Script
        wp_register_script( "auto_scroll_main_slide", plugins_url( "auto-scroll/js/printSlide.js" ), array('jquery'), '1.0.0', true );

        wp_enqueue_style( "auto-scroll" );
        wp_enqueue_script( "auto_scroll_main_js" );
        wp_enqueue_script( "auto_scroll_main_slide" );

    }

    //Add Styles and Scripts
    add_action('wp_enqueue_scripts', 'nativo_auto_scroll_res');
