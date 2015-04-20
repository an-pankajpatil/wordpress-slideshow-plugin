<?php
/*
* Plugin Name: SlideShow
* Description: This plugin will provide a slideshow
* Plugin URI: http://www.applicationnexus.com
* Author: Pankaj Patil
* Author URI: http://www.applicationnexus.com
* Version: 1.0.0
* License: GPL2
*/
function slideshow_activation() {
//here we can create database tables, custom option if required for the plugin
}
register_activation_hook(__FILE__, 'slideshow_activation');
function slideshow_deactivation() {
//here we can remove database tables, custom option if created during plugin activation
}
register_deactivation_hook(__FILE__, 'slideshow_deactivation');

//add action hook to include javascript files
function slideshow_scripts() {
	wp_enqueue_script('jquery');
	
	wp_register_script('slideshow_core', plugins_url('js/jquery.flexslider-min.js', __FILE__));
	wp_enqueue_script('slideshow_core');

	wp_register_script('slideshow_init', plugins_url('js/jquery.easing.js', __FILE__));
	wp_enqueue_script('slideshow_init');
}
add_action('wp_enqueue_scripts', 'slideshow_scripts');

//add action hook to include stylesheets
function slideshow_style() {
	wp_register_style('slideshow_style', plugins_url('css/flexslider.css', __FILE__));
	wp_enqueue_style('slideshow_style');
}
add_action('wp_enqueue_scripts', 'slideshow_style');

function addScript($gallery_setting, $id) {
	wp_register_script('slideshow_demo', plugins_url('js/demo.js?v='.$id, __FILE__),'','',true);
	wp_enqueue_script('slideshow_demo');	

    $config_array = array(
            'gallery_setting' => $gallery_setting,
            'animation' => $gallery_setting[0],
            'slideshow' => $gallery_setting[1],
            'slideshowSpeed' => $gallery_setting[2],
            'id' => "#ANSlide".$id
        );
 	wp_localize_script('slideshow_demo', 'setting', $config_array);
}


//shortcode to include the slideshow inside posts/pages
function slideshow_display_shortcode($attr, $content) {
	extract(shortcode_atts(array('id' => ''),$attr));

	$gallery_images = get_post_meta($id, "_slideshow_gallery_images", true);
	$gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();
	$gallery_setting = get_post_meta($id, "_slideshow_gallery_setting", true);
	$gallery_setting = ($gallery_setting != '') ? json_decode($gallery_setting) : array();
    
	$plugins_url = plugins_url();
	$html = '<div id="ANSlide'.$id.'"><div class="flexslider">
          <ul class="slides">';
    foreach($gallery_images as $img) {
    	if($img!="") {
    		$html .= '<li>
  	    	    <img src="'.$img.'" />
  	    		</li>';
    	}
    }
    $html .= '</ul>
        </div></div>';
    $html .= '<script type="text/javascript">jQuery(window).load(function() { jQuery("#ANSlide'.$id.' .flexslider").flexslider({
                prevText: "Prev",
                nextText: "Next",';
       $html1 = 'animation: "'.$gallery_setting[0].'",';
      $html1 .= 'slideshow: "'.$gallery_setting[1].'",'; 
      $html1 .= 'slideshowSpeed: "'.$gallery_setting[2].'"';
    
    $html .= $html1 .'}); });</script>';

    return $html;
}
add_shortcode('slideshow', 'slideshow_display_shortcode');

//creating custom post type
function slideshow_register_slides() {
	$labels = array('menu_name' => _x('Gallery','flexslider'), );
	$args = array(
		'labels' => $labels, 
		'hierarchical' => true, 
		'description' => 'Slideshows', 
		'supports' => array('title','editor'),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'show_in_nav_menus' => true,
		'publicly_querable' => true,
		'exclude_from_search' => false,
		'has_archive' => true,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => true,
		'capability_type' => 'post'
		);
	register_post_type('flexslider', $args);
}
add_action('init', 'slideshow_register_slides');

//Display shortcode column in gallery listing
function flexslider_cpt_columns($columns) {

	$new_columns = array(
		'shortcode' => __('Shortcode', 'ThemeName')
	);
    return array_merge($columns, $new_columns);
}
add_filter('manage_flexslider_posts_columns' , 'flexslider_cpt_columns');

//Display Shortcode with post id
function slideshow_flexslider_custom_shortcode_column($column, $post_id) {
	$slider_meta = get_post_meta($post_id, "_slideshow_gallery_images", true);
    $slider_meta = ($slider_meta != '') ? json_decode($slider_meta) : array();

    switch ($column) {
        case 'shortcode':
            echo "[slideshow id='$post_id' /]";
            break;

    }
}
add_action('manage_flexslider_posts_custom_column', 'slideshow_flexslider_custom_shortcode_column', 10, 2);




//add input boxes to add images to gallery
function slideshow_slider_inputs() {
	add_meta_box('slideshow-slides', "Slideshow Images", 'slideshow_slider_images_box', 'flexslider', 'normal');
}
add_action('add_meta_boxes', 'slideshow_slider_inputs');

function slideshow_slider_images_box() {
	global $post;
	/*
	$fp = fopen('post.log', 'w+');
	fwrite($fp, "ID :".$post->ID);
	fclose($fp);
	*/
	$gallery_images = get_post_meta($post->ID, "_slideshow_gallery_images", true);
	$gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();
	// Use nonce for verification
    $html =  '<input type="hidden" name="slideshow_slider_box_nonce" value="'. wp_create_nonce(basename(__FILE__)). '" />';
 
    $html .= '<table class="form-table">
<tbody>
<tr>
<th><label for="Upload Images">Image 1</label></th>
<td><input id="slideshow_slider_upload" size="50" type="text" name="gallery_img[]" value="'.$gallery_images[0].'" /></td>
</tr>
<tr>
<th><label for="Upload Images">Image 2</label></th>
<td><input id="slideshow_slider_upload" size="50" type="text" name="gallery_img[]" value="'.$gallery_images[1].'" /></td>
</tr>
<tr>
<th><label for="Upload Images">Image 3</label></th>
<td><input id="slideshow_slider_upload" size="50" type="text" name="gallery_img[]" value="'.$gallery_images[2].'" /></td>
</tr>
<tr>
<th><label for="Upload Images">Image 4</label></th>
<td><input id="slideshow_slider_upload" size="50" type="text" name="gallery_img[]" value="'.$gallery_images[3].'" /></td>
</tr>
<tr>
<th><label for="Upload Images">Image 5</label></th>
<td><input id="slideshow_slider_upload" size="50" type="text" name="gallery_img[]" value="'.$gallery_images[4].'" /></td>
</tr>
</tbody>
</table>';
 
        echo $html;

}

//Gallery wise setting
function slideshow_slider_setting() {
	add_meta_box('slideshow-setting', "Slideshow Setting", 'slideshow_slider_setting_box', 'flexslider', 'normal');
}
add_action('add_meta_boxes', 'slideshow_slider_setting');

function slideshow_slider_setting_box() {
	global $post;
	/*
	$fp = fopen('post.log', 'w+');
	fwrite($fp, "ID :".$post->ID);
	fclose($fp);
	*/
	$gallery_setting = get_post_meta($post->ID, "_slideshow_gallery_setting", true);
	$gallery_setting = ($gallery_setting != '') ? json_decode($gallery_setting) : array();
	$gallery_slide = ($gallery_setting[0] == "slide") ? "selected" : "";
	$gallery_fade = ($gallery_setting[0] == "fade") ? "selected" : "";
	$gallery_autoplay = ($gallery_setting[1]=="enabled") ? "checked" : "";

	// Use nonce for verification
    $html =  '<input type="hidden" name="slideshow_slider_box_nonce" value="'. wp_create_nonce(basename(__FILE__)). '" />';
 
    $html .= '<table class="form-table">
<tbody>
<tr>
<th><label for="Gallery Setting">Slider Effect</label></th>
<td><select name="slideshow_effect"><option value="slide" '.$gallery_slide.' >Slide</option><option value="fade" '.$gallery_fade.'>Fade</option></select></td>
</tr>
<tr>
<th><label for="Gallery Setting">Enable Autoplay</label></th>
<td><input type="checkbox" name="slideshow_autoplay" value="enabled" '.$gallery_autoplay.' /></td>
</tr>
<tr>
<th><label for="Gallery Setting">Slideshow timer</label></th>
<td><input id="slideshow_slider_upload" size="50" type="text" name="slideshow_interval" value="'.$gallery_setting[2].'" /></td>
</tr>
</tbody>
</table>';
 
        echo $html;

}


//save slide images
function slideshow_save_gallery() {
	 global $post;
	 // verify nonce
    if (!wp_verify_nonce($_POST['slideshow_slider_box_nonce'], basename(__FILE__))) {
       return $post_id;
    }
    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
       return $post_id;
    }

    if(!$post_id) {
    	$post_id = $post->ID;
    }
 
    // check permissions
    if ('flexslider' == $_POST['post_type'] && current_user_can('edit_post', $post_id)) {
       	/* Save Slider Images */

       	$gallery_images = (isset($_POST['gallery_img']) ? $_POST['gallery_img'] : '');
       	$gallery_images = strip_tags(json_encode($gallery_images));

       	//$gallery_setting = array();
       	if(isset($_POST['slideshow_effect']) && ($_POST['slideshow_effect'] != '')) {
       		$gallery_setting[0] = $_POST['slideshow_effect'];
       	} else {
       		$gallery_setting[0] = "slide";
       	}
       	if(isset($_POST['slideshow_autoplay']) && ($_POST['slideshow_autoplay'] != '')) {
       		$gallery_setting[1] = $_POST['slideshow_autoplay'];
       	} else {
       		$gallery_setting[1] = "";
       	}

       	if(isset($_POST['slideshow_interval']) && ($_POST['slideshow_interval'] != '')) {
       		$gallery_setting[2] = $_POST['slideshow_interval'];
       	} else {
       		$gallery_setting[2] = "2000";
       	}


       	//$gallery_setting = (isset($_POST['gallery_setting']) ? $_POST['gallery_setting'] : '');
       	$gallery_setting = strip_tags(json_encode($gallery_setting));

       	/*
       	$fp = fopen('post1.log', 'w+');
       	fwrite($fp, "ID :".$_POST['slideshow_autoplay']);
       	fwrite($fp, '\n\nID1 :'.$_POST['slideshow_effect'].'\n\n');
    	fwrite($fp,print_r($gallery_setting, true));
    	fclose($fp);
		*/
       	update_post_meta($post_id, "_slideshow_gallery_images", $gallery_images);
       	update_post_meta($post_id, "_slideshow_gallery_setting", $gallery_setting);
     } else {
        return $post_id;
     }
}
add_action('save_post', 'slideshow_save_gallery');


//adding settings for slideshows
function slideshow_display_settings() {
	$slide_effect = (get_option('slideshow_effect') == 'slide') ? 'selected' : '';
	$fade_effect = (get_option('slideshow_effect') == 'fade') ? 'selected' : '';
	$time_interval = (get_option('slideshow_interval') != '') ? get_option('slideshow_interval') : '1500';
	$autoplay  = (get_option('slideshow_autoplay') == 'enabled') ? 'checked' : '' ;
    $html = '</pre>
<div class="wrap"><form action="options.php" method="post" name="options">
<h2>Select Your Settings</h2>
' . wp_nonce_field('update-options') . '
<table class="form-table" width="100%" cellpadding="10">
<tbody>
<tr>
<td scope="row" align="left">
 <label>Slider Effect</label>
<select name="slideshow_effect"><option value="slide" '.$slide_effect.' >Slide</option><option value="fade" '.$fade_effect .'>Fade</option></select></td>
</tr>
<tr>
<td scope="row" align="left">
 <label>Enable Auto Play</label><input type="checkbox" name="slideshow_autoplay" value="enabled" '.$autoplay.' /></td>
</tr>
<tr>
<td scope="row" align="left">
 <label>Transition Interval</label><input type="text" name="slideshow_interval" value="' . $time_interval . '" /></td>
</tr>
</tbody>
</table>
 <input type="hidden" name="action" value="update" />
 
 <input type="hidden" name="page_options" value="slideshow_autoplay,slideshow_effect,slideshow_interval" />
 
 <input type="submit" name="Submit" value="Update" /></form></div>
<pre>
';
 
    echo $html;
}
function slideshow_plugin_settings() {
 
    add_menu_page('Gallery Settings', 'Gallery Settings', 'administrator', 'slideshow_settings', 'slideshow_display_settings');
 
}
add_action('admin_menu', 'slideshow_plugin_settings');


?>