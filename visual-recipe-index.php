<?php
/**
 * @ Recipe_Index
 * @version 1.2.8
 */
/*
Plugin Name: Visual Recipe Index
Plugin URI: http://wordpress.org/extend/plugins/recipe_index/
Description: This plugin allows for the easy creation of a recipe index. 
Inspired by the Category Grid View Plugin by Anshul Sharma
Author: Kremental
Version: 1.2.8
Author URI: http://strawberriesforsupper.com/recipe-index
*/


/* Copyright 2012 Original Author: Anshul Sharma  (email : contact@anshulsharma.in)
   Copyright 2015 Author: Kremental/Simon Austin (email: simon@kremental.com)

This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Sorry, Dude. You are not allowed to call this page directly.'); }

require_once 'includes/RecipeIndexView.php';
if(is_admin()) {
require_once 'includes/Settings.php';
require_once 'includes/Options.php';
add_action('admin_menu','add_ri_settings');
}

function add_ri_settings(){
add_options_page('Settings - Visual Recipe Index','Visual Recipe Index', 'manage_options', 'visual-recipe-index', 'recipe_index_options');
}

define('PLUGIN_AUTHOR', 'Kremental');
define('AUTHOR_URI','http://strawberriesforsupper.com/');
define('PLUGIN_URI','http://strawberriesforsupper.com/recipe-index');

class RecipeIndex{
    /* Get the parameters from shortcodes,
	set defaults and initialize the object
	Inspired by List category posts plugin by Fernando Briano */
    function recipe_index($atts, $content = null) {
            $atts = shortcode_atts(array(
                            'id' => '0',
                            'name' => '',
                            'orderby' => 'date',
                            'order' => 'desc',
                            'num' => '-1',
                            'excludeposts' => '0',
                            'offset' => '0',
							'tags' => '',
                            'size' => 'thumbnail',
							'quality' => '100',
							'showtitle' => 'hover',
							'lightbox' => '1',
							'paginate' => '0',
							'customfield' => '',
							'customfieldvalue' => '',
							'title' => ''
                    ), $atts);

       		global $ri_output;
            $ri_output = new RecipeIndexView($atts);
	        return $ri_output->display();
			
		

    }

}


function ri_set_locations(){ 
 if ( ! function_exists( 'is_ssl' ) ) {
  function is_ssl() {
   if ( isset($_SERVER['HTTPS']) ) {
    if ( 'on' == strtolower($_SERVER['HTTPS']) )
     return true;
    if ( '1' == $_SERVER['HTTPS'] )
     return true;
   } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
    return true;
   }
   return false;
  }
 }
}
 

function enqueue_ri_styles() {
	$riStyleUrl = plugins_url('css/style.css',__FILE__);
        $riStyleFile = plugin_dir_path(__FILE__) . 'css/style.css';
        if ( file_exists($riStyleFile) ) {
			if(!is_admin()){
            wp_register_style('RecipeIndexStyleSheets', $riStyleUrl);
            wp_enqueue_style( 'RecipeIndexStyleSheets');
			}
        }

 } 

function enqueue_ri_scripts() {
	$ri_url = plugin_dir_url(__FILE__);
	$ri_dir = plugin_dir_path(__FILE__);
	if(!is_admin()){
	    if ( file_exists($ri_dir . 'js/riview.js') ) {
      		wp_enqueue_script( 'CatGridjs', $ri_url . 'js/riview.js', array( 'jquery' ));
        }
		if ( file_exists($ri_dir . '/js/jquery.colorbox-min.js')) {
      		wp_enqueue_script( 'Colorbox', $ri_url . 'js/jquery.colorbox-min.js', array( 'jquery' ));
        }
		if ( file_exists($ri_dir . '/js/easypaginate.min.js')) {
      		wp_enqueue_script( 'EasyPaginate', $ri_url . 'js/easypaginate.min.js', array( 'jquery' ));
        }
	}
}     

add_action( 'wp_print_scripts', 'enqueue_ri_scripts' );
add_action( 'wp_print_styles', 'enqueue_ri_styles' );
add_action( 'init', 'ri_set_locations' );
add_action( 'wp_print_footer_scripts', 'ri_init_js' );

add_shortcode( 'riview', array('RecipeIndex', 'recipe_index') );

/** Create Admin Menu options - function not in Cat Grid View */
function recipe_index_options() {
	
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
        wp_enqueue_script( 'recipe_index', plugins_url('js/recipe_index_options.js',__FILE__), array( 'jquery' ));

	echo '<div class="wrap">
	<table>
	<tr><td>
	<h1>Visual Recipe Index</h1>This plugin will create an automatically updating recipe index with pictures.<br>Note that this plugin requires the feature image is set for each post.  once you have created your visual recipe index it is easy to identify which posts do not have a feature image set.';
	echo '<h2>Step 1: </h2>create a blank page where you want to post your automatically updating visual recipe index.  <a href="../wp-admin/post-new.php?post_type=page&post_title=Recipe Index#content-html" target="_blank">Click here</a> to open up a new window to do this
<h2>Step 2: </h2>Choose your options below
	</td><td><a href="http://kremental.com/visual-recipe-index"><img src="';
	echo plugins_url('includes/Sign-up.png',__FILE__);
	echo '" alt="Sign up for updates and exclusive early release pricing for the pro version">';
	echo '</a></td>
	</tr>
	</table>
	
	</div>';
?>
	
    <!-- SHORTCODE GENERATOR -->
    <div id="ri_sc">
    <form name="ri_sc" onsubmit="return false;">
        <table id="shortcode_options" class="form-table">
            <tr>
                <td><?php _e("Choose Categories to display in Visual Recipe Index","riview"); /* need to create category chooser here from list above */ ?><br />
	<select name="categories" id="ri_categories" multiple="multiple">
<?php
	/** Choose from a list of categories to include in recipe index */	
	$args = array(
	  'orderby' => 'name',
	  'order' => 'ASC'
	  );
	$categories = get_categories($args);
	  foreach($categories as $category) { 
	    echo '<option value='.$category->term_id.'>'. $category->name."</option>\n";
	  } 
?>
		</select>
<script type="text/javascript">
var idcat=new Array();
<?php // pass in id and category name into javascript array so I can access the lookup table on the page
          foreach($categories as $category) {
	    echo "idcat[$category->term_id] = '".addslashes($category->name)."';\n";
	  }
?>
</script>
                    <br />
                               <span> <?php _e("Choose multiple categories by holding down the Ctrl key","riview"); ?></span></td>
                               
</div>
                <td><?php _e("Thumbnail Size","riview"); ?><br />
                <input type="text" size="4" name="sizew" value=""/><?php _e(" X ","riview"); ?><input type="text" size="4" name="sizeh" value=""/><br />
                <span><?php _e("Width (px) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Height (px)","riview"); ?></span><br />
                <select name='sizes'>
                                                        <option value='thumbnail'><?php _e("Thumbnail (Default)","riview"); ?></option>
                                                        <option value='medium'><?php _e("Medium","riview"); ?></option>
                                                        <option value='large'><?php _e("Large","riview"); ?></option>
                                                        <option value='other'><?php _e("Other (Please Specify)","riview"); ?></option>
                                </select>

                                <span><?php _e("Specify the dimensions of the generated thumbnails for each post","riview"); ?><br></span>
				<span>Support the Author with a link:<input type="checkbox" name="credit" id="credit" class="checkbox" checked></span>
		</td>
		<br /></div>
            </tr>
         </table>
<h2>Step 3:</h2> Click "Generate Visual Recipe Index code", highlight the generated code, and copy it into your clipboard.  Important - you may have to scroll within the box to get all of the code.
        <table class="form-table">
        <tr>
                        <th scope="row">
             <p class="controls alignright">
                        <input type="submit" class="button-secondary" name="submit_shortcode" value="<?php _e("Generate Visual Recipe Index code","riview"); ?>"/>
            </p>
            <p class="controls alignright">
                        <input type="submit" class="button-secondary" name="reset_shortcode" value="<?php _e("Reset","riview"); ?>" />
            </p></th>
                        <td>
                                <textarea id="riview_shortcode" rows="4" cols="60" name="riview_shortcode" class="code">Your automatically updating recipe index code will be generated here.  After it is generated, be sure to scroll down to copy all of it.</textarea>
                        </td>
                </tr>
        </table>
    </form>
        </div>
	<div class="wrap">
<h2>Step 4: </h2>Return to the window you created in Step 1, ensure you are in text mode, and paste the code you created in Step 3.  Publish the page and note the URL.
<h2>Step 5: </h2>Check your new recipe index page for posts that don't have an image showing.  Edit those pages and set the feature image.
<h2>Step 6:</h2>Modify the <a href="../wp-admin/nav-menus.php" target="_blank">Apperance->Menu</a> to make a link to your new menu appear in the nav
	</div>
<?php

}
?>
