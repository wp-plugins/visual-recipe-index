<?php
/**
Settings for Visual Recipe Index
Author : Simon Austin (simon@kremental.com)
Inspired by the Category Grid View Plugin by Anshul Sharma
 */

//Default Plugin Settings
function riview_default_settings(){
	$defaults = array(
    'default_image' => plugin_dir_url(__FILE__) . '/includes/default.jpg',
	'custom_image' => plugin_dir_url(__FILE__)  .'/includes/default.jpg',
    'credits' => 1,
    'color_scheme' => 'light',
    'image_source' => 'featured',
	'lightbox_width' => '700',
	'lightbox_height' => '400',
	'load_comments' => 0);
  return $defaults;
}

 
function riview_verify_options(){
  $default_settings = riview_default_settings();
  $current_settings = get_option('riview');
  if(!$current_settings):
   riview_setup_options();
  else:
  //   if (version_compare($current_settings['plugin_version'], PLUGIN_VERSION, '!=')):
     // check for new options
     foreach($default_settings as $option=>$value):
      if(!array_key_exists($option, $current_settings)) $current_settings[$option] = $default_settings[$option];
     endforeach;

    // update theme version
   // $current_settings['plugin_version'] = $plugin_data['Version'];
    update_option('riview' , $current_settings);

 // endif;
  endif;
      do_action('riview_verify_options');
}

function riview_setup_options() {
 
  riview_remove_options();
  $default_settings = riview_default_settings();
  update_option('riview' , $default_settings);
  do_action('riview_setup_options');
}


function riview_remove_options() {
  delete_option('riview');
  do_action('riview_remove_options');
}

function get_riview_option($option) {
  $get_riview_options = get_option('riview');
  return $get_riview_options[$option];
}


function print_riview_option($option) {
  $get_riview_options = get_option('riview');
  echo $get_riview_options[$option];
}


