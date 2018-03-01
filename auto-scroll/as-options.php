<?php

//OPTION PAGE

/** Step 2 (from text above). */
add_action( 'admin_menu', 'as_menu' );

//MENU Y DB
function as_menu() 
{

	add_menu_page( 'Auto Scroll Carousel', 'Auto Scroll Carousel', 'manage_options', 'as-opt', 'as_options' );
}


//UPLAOD
function as_carousel_images() {

	//NO-IMAGE
	$default_image = plugins_url('img/no-image.png', __FILE__);

	//AS ID
	$id = (isset($_GET['as-id'])) ? $_GET['as-id'] : '' ;

    global $wpdb;
    $table_name = $wpdb->prefix . 'auto_scroll';

if ($id !== '') {

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
							            	<input type="text" name="as_images" value="<?= $image ?>" />
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

    	} 

	}else{  //ID CHECK

		$row = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );

        $image = $default_image;

        //GET USER DATA
		$user = wp_get_current_user();
        $allowed_roles = array('administrator', 'editor', 'author');

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
							<b>Actualizado el: </b>
							<span><?= $field['as_updated'] ?></span>
							<br>
							<label>Shortcode:</label>
							<input type="text" readonly="" value='[as-gallery id="<?= $field['as_id'] ?>"]'>

							</p>

							<section align="center">
								<a class="button button-primary button-large" href="?page=as-opt&as-id=<?= $field['as_id'] ?>"><span class="dashicons dashicons-edit"></span> Editar</a>
							</section>
						</figcaption>
					</figure>
				</li>				    <!-- other items -->

		<?php } ?>

				</ul>
				<!-- end grid -->
		<?php 

		}//EMPTY CHECK

    }//END - ELSE ID CHECK

	}//END FUNCTION



/**
 * Load scripts and style sheet for settings page
 */
function as_load_scripts_admin() {

    // WordPress library
    wp_enqueue_media();
    wp_register_script( "auto_scroll_mediaup", plugins_url( "auto-scroll/js/jquery.media.js" ), array('jquery'), '1.0.0', true );
    wp_enqueue_script( "auto_scroll_mediaup" );

    wp_register_style( "auto-scroll", plugins_url( "auto-scroll/css/admin-styles.css" ) );
    wp_enqueue_style( "auto-scroll" );

}

//INCLUDE SCRIPTS
add_action( 'admin_enqueue_scripts', 'as_load_scripts_admin' );

//AJAX HANDLER
function as_ajax_post() {

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
    	
    	$response = array('code' => 1, 'message' => '<div class="updated fade"><p><strong>Carousel Actualizado!</p></div>');
    
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

add_action( 'wp_ajax_as_ajax_post', 'as_ajax_post' );

//PLUGIN OPT
function as_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'No tieme permiso suficientes para acceder a esta pagina.' ) );
	}
	echo '<div class="wrap">';
	echo '<h1>Auto Scroll Carousel</h1>';

?>


	<?php

	as_carousel_images();

	?>


</div>

<?php 

}








