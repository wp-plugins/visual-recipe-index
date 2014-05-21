<?php
/**
Administration Page
 */

function riview_admin_scripts() {
 $ri_url = plugin_dir_url(__FILE__);
 $ri_dir = plugin_dir_path(__FILE__);
	    if ( file_exists($ri_dir . 'js/riviewoptions.js') ) {
      		wp_enqueue_script( 'RecipeIndexOptionjs', $ri_url . 'js/riviewoptions.js', array( 'jquery' ));
        }
}

register_activation_hook(__FILE__, 'riview_verify_options');
register_uninstall_hook(__FILE__, 'riview_remove_options');

function riview_admin_css() {
	$riStyleUrl = plugin_dir_url(__FILE__) . 'css/riview-settings.css';
        $riStyleFile = plugin_dir_path(__FILE__) . 'css/riview-settings.css';
        if ( file_exists($riStyleFile) ) {
            wp_register_style('RecipeIndexAdminStyleSheets', $riStyleUrl);
            wp_enqueue_style( 'RecipeIndexAdminStyleSheets');
        }
 }

if (isset($_GET['page']) && $_GET['page'] == 'riview') {
add_action('admin_print_scripts', 'riview_admin_scripts');
add_action('admin_print_styles', 'riview_admin_css' );
}

function riview_upload_dir($dir) {
  $riuploadpath = wp_upload_dir();
  if ($riuploadpath['baseurl']=='') $riuploadpath['baseurl'] = get_bloginfo('siteurl').'/wp-content/uploads';
  return $riuploadpath[$dir];
}

function ri_admin() {
//	add_options_page('Settings - Visual Recipe Index', 'Visual Recipe Index', 'manage_options', riview, 'ri_options');
}

function riview_update_options() {
  check_admin_referer('riview');

  // enable settings for lower level users, but with limitations
  if(!current_user_can('edit_plugins')) wp_die(__('You are not authorised to perform this operation.', 'riview'));
  $options = get_option('riview');

  foreach (riview_default_settings() as $key => $value):
  	if(($key!='default_image')&& ($key!='custom_image')):
   $options[$key] = stripslashes((string)$_POST[$key]);
   endif;
  endforeach;

  
  if(isset($_POST['remove-custom'])):
   $options['custom_image'] = $options['default_image'];
  elseif($_FILES["custom_image"]["type"]):
   $valid = is_valid_riview_image('custom_image');
   if($valid):
    $options['custom_image'] = riview_upload_dir('baseurl'). "/". $_FILES["custom_image"]["name"];
   endif;
  endif;


  update_option('riview', $options);

  // reset?
  if (isset($_POST['reset']))riview_setup_options();

  wp_redirect(admin_url('options-general.php?page=riview&settings-updated=true'));
}


function ri_options() {
	//must check that the user has the required capability 
    if(!current_user_can('edit_plugins')) wp_die(__('You are not authorised to perform this operation.', 'riview'));
   // settings form    
    ?>
    
<div id="riview-container" class="wrap clear-block">
	<div id="riview-settings-left" class="alignleft">
	<div class="icon32" id="icon-options-general"><br></div>
    <h2><?php _e('Visual Recipe Index - Settings','riview'); ?></h2>
    <div class="clear-block menu">
     <ul class="subsubsub">
      <li class="settings"><a href='#riview-settings' id='settings' class='active'><?php _e("Settings","mystique"); ?></a></li> |
      <li class="ri_sc"><a href='#riview-shortcode' id='shortcode'><?php _e("Shortcode Generator","mystique"); ?></a></li>
     </ul>
    </div>
    
    <div id="settings">
	<form action="<?php echo admin_url('admin-post.php?action=riview_update'); ?>" method="post" enctype="multipart/form-data">

   		<?php wp_nonce_field('riview'); ?>

   		<?php //riview_check_update(); ?>

   		<?php if (isset($_GET['settings-updated'])): ?>
   		<div class="updated fade below-h2">
    		<p><?php printf(__('Settings saved. %s', 'riview'),'<a href="' . user_trailingslashit(get_bloginfo('url')) . '">' . __('View site','riview') . '</a>'); ?></p>
   		</div>
   		<?php elseif (isset($_GET['error'])):
     		$errors  = array(
       1 => __("Please upload a valid image file!","riview"),
       2 => __("The file you uploaded doesn't seem to be a valid JPEG, PNG or GIF image","riview"),
       3 => __("The image could not be saved on your server","riview")
     );

   ?>
   
   		<div class="error fade below-h2">
    		<p><?php printf(__('Error: %s', 'riview'),$errors[$_GET['error']]); ?></p>
   		</div>
   		<?php endif; ?>
        

        <table class="form-table">
        	<tr>
        	<th scope="row"><p><?php _e("Thumbnail Source","riview"); ?><span><?php _e("Choose between the first image attached to a post and the featured image as the source of thumbnails.","riview"); ?></span></p></th>
        		<td>
					<label for="image_source"><input name="image_source" type="radio" id="image_source_first" class="radio" value="first" <?php checked('first', get_riview_option('image_source')) ?> /><?php _e("First Image attached in the post","riview"); ?></label>
         			<label for="image_source"><input name="image_source" type="radio" id="image_source_featured" class="radio" value="featured" <?php checked('featured', get_riview_option('image_source')) ?> /><?php _e("Featured Image","riview"); ?></label>

        		</td>
       		</tr>
            
            <tr>
        	<th scope="row"><p><?php _e("Theme","riview"); ?></p></th>
        		<td>
					<label for="color_scheme"><input name="color_scheme" type="radio" id="color_scheme_light" class="radio" value="light" <?php checked('light', get_riview_option('color_scheme')) ?> /><?php _e("Light","riview"); ?></label>
         			<label for="color_scheme"><input name="color_scheme" type="radio" id="color_scheme_dark" class="radio" value="dark" <?php checked('dark', get_riview_option('color_scheme')) ?> /><?php _e("Dark","riview"); ?></label>

        		</td>
       		</tr>
            <tr>
        		<th scope="row"><p><?php _e("Lightbox Dimensions","riview"); ?><span><?php _e("Enter the dimensions of the lightbox in pixels. (Default: 700px x 600px)", "riview"); ?></span></p></th>
        		<td>
         			<input type="text" size="4" name="lightbox_width" value="<?php echo get_riview_option('lightbox_width'); ?>"/>width(px)&nbsp;&nbsp;&nbsp;&nbsp;X&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="text" size="4" name="lightbox_height" value="<?php echo get_riview_option('lightbox_height'); ?>"/>height(px)</br>
                    <span>Note: To make the height of the lightbox dynamic, set Height : 0 px. This will make the lightbox height equal to the length of the post. </span>
         		</td>
         	</tr> 
            <tr>
        		<th scope="row"><p><?php _e("Show Comments","riview"); ?><span><?php _e("If yes, it will load the post comments in the lightbox", "riview"); ?></span></p></th>
        		<td>
         			<label for="comments_tag"><input name="load_comments" id="load_comments" type="checkbox" class="checkbox" value="1" <?php checked(1, get_riview_option('load_comments')) ?> /></label>
         		</td>
         	</tr>           
            <tr>
        		<th scope="row"><p><?php _e("Powered by link","riview"); ?><span><?php _e("Do you wish to support the author by putting a small link at the bottom?", "riview"); ?></span></p></th>
        		<td>
         			<label for="credits_tag"><input name="credits" id="credits" type="checkbox" class="checkbox" value="1" <?php checked(1, get_riview_option('credits')) ?> /></label>
         		</td>
         	</tr>
            
            <tr>
        		<th scope="row"><p><?php _e("Custom default image","riview"); ?><span><?php _e("Upload an image to replace the default image; It is shown when no image is available for a post.","riview"); ?></span></p></th>
        		<td>
          			<?php if(is_writable(riview_upload_dir('basedir'))): ?>
           			<input type="file" name="custom_image" id="custom_image" />
           			<?php if(get_riview_option('custom_image')!=get_riview_option('default_image')): ?>
           			<button type="submit" class="button" name="remove-custom" value="0"><?php _e("Remove current image","riview"); ?></button>
           			<div class="clear-block">
           				<div class="image-preview"><img src="<?php echo get_riview_option('custom_image'); ?>" style="padding:10px;" /></div>
           			</div>
           			<?php endif; ?>
         			<?php else: ?>
         			<p class="error" style="padding: 4px;"><?php printf(__("Directory %s doesn't have write permissions - can't upload!","riview"),'<strong>'.riview_upload_dir('basedir').'</strong>'); ?></p><p><?php _e("Check your upload path in Settings/Misc or CHMOD this directory to 755/777.<br />Contact your host if you don't know how","riview"); ?></p>
         			<?php endif; ?>
         			<input type="hidden" name="custom_image" value="<?php echo get_riview_option('custom_image'); ?>">
        		</td>
       		</tr>
                 
    	</table>
        
         <div class="clear-block">
     		<p class="controls alignleft">
        		<input type="submit" class="button-primary" name="submit" value="<?php _e("Save Changes","riview"); ?>" />
            	<input type="submit" class="button-primary" name="reset" value="<?php _e("Reset to Defaults","riview"); ?>" onclick="if(confirm('<?php _e("Do you really want to reset all the settings to defaults?", "riview"); ?>')) return true; else return false;" />
       		</p>
   		</div>
 		
	</form>
    </div>
    
    <!-- SHORTCODE GENERATOR -->
    <div id="ri_sc">
    <form name="ri_sc" onsubmit="return false;">
        <table class="form-table">
    	<tr>
        		<th scope="row"><p><?php _e("Shortcode Generator","riview"); ?><span><?php _e("Here you can generate your shortcode and then copy-paste it wherever you want it to appear", "riview"); ?></span><br /></p>
             <p class="controls alignright">
        		<input type="submit" class="button-secondary" name="submit_shortcode" value="<?php _e("Generate Shortcode","riview"); ?>"/>
            </p>
            <p class="controls alignright">
        		<input type="submit" class="button-secondary" name="reset_shortcode" value="<?php _e("Reset","riview"); ?>" />
            </p></th>
        		<td>
         			<textarea id="riview_shortcode" rows="4" cols="60" name="riview_shortcode" class="code">[riview id=1]</textarea>
         		</td>
         	</tr>
        </table>
        <table id="shortcode_options" class="form-table">
            <tr>
            	<td><?php _e("Category id","riview"); ?><br />
                	<input type="text" size="15" name="id" value=""/><br />
                               <span> <?php _e("Can have multiple comma seperated values  (ex. 1,3,4)","riview"); ?></span></td>
                <td><?php _e("Category Name","riview"); ?><br />
                	<input type="text" size="15" name="name" value=""/><br />
                              <span><?php _e("Category Slug name.Can have multiple comma seperated values (ex. articles,poems,art","riview"); ?>)</span></td>
                <td><div class="upper"><?php _e("Number of posts to show","riview"); ?><br />
                		<input type="text" size="15" name="num" value=""/></div>
                	<div class="lower"><?php _e("Offset","riview"); ?><br />
                		<input type="text" size="15" name="offset" value=""/></div></td>
                <td><div class="upper"><?php _e("Order Posts by","riview"); ?><br />
						<select name='orderby'>
							<option value='date'><?php _e("Date (Default)","riview"); ?></option>
							<option value='id'><?php _e("ID","riview"); ?></option>
							<option value='author'><?php _e("Author","riview"); ?></option>
							<option value='title'><?php _e("Title","riview"); ?></option>
							<option value='modified'><?php _e("Modified","riview"); ?></option>
							<option value='parent'><?php _e("Parent","riview"); ?></option>
							<option value='rand'><?php _e("Random","riview"); ?></option>
							<option value='comment_count'><?php _e("Comment Count","riview"); ?></option>
                            <option value='menu_order'><?php _e("Menu Order","riview"); ?></option>
                            <option value='meta_value'><?php _e("Meta Value","riview"); ?></option>
                            <option value='meta_value_num'><?php _e("Meta Value Num","riview"); ?></option>
						</select>
                        <input type="hidden" name="orderby_init" value="1"></div>
                        
                        <div class="lower"><?php _e("Order","riview"); ?><br />
							<label for="order"><input name="order" type="radio" id="order_desc" class="radio" value="desc" checked="checked"><?php _e("Descending (Default)","riview"); ?></label><br />
         					<label for="order"><input name="order" type="radio" id="order_asc" class="radio" value="asc" /><?php _e("Ascending","riview"); ?></label>
                    		<input type="hidden" name="order_init" value="1"></div>
                </td>
			</tr>
            <tr>
                <td><?php _e("Tags","riview"); ?><br />
                <input type="text" size="15" name="tags" value=""/><br />
                                <span><?php _e("Display posts that have these tags. Can have multiple comma seperated values  (ex. stars,moon,cats)","riview"); ?></span></td>
                <td><?php _e("Exclude posts","riview"); ?><br />
                <input type="text" size="15" name="excludeposts" value=""/><br />
                                <span><?php _e("Can have multiple comma seperated values (ex. 1,3,4)","riview"); ?></span></td>
                <td><?php _e("Thumbnail Size","riview"); ?><br />
                <input type="text" size="4" name="sizew" value=""/><?php _e(" X ","riview"); ?><input type="text" size="4" name="sizeh" value=""/><br />
                <span><?php _e("Width (px) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Height (px)","riview"); ?></span><br />
                <select name='sizes'>
							<option value='thumbnail'><?php _e("Thumbnail (Default)","riview"); ?></option>
							<option value='medium'><?php _e("Medium","riview"); ?></option>
							<option value='large'><?php _e("Large","riview"); ?></option>
							<option value='other'><?php _e("Other (Please Specify)","riview"); ?></option>
				</select>
                
                                <span><?php _e("Specify the dimensions of the generated thumbnails for each post","riview"); ?></span></td>
                 <td><?php _e("Quality","riview"); ?><br />
                <input type="text" size="3" name="quality" value=""/><br />
                                <span><?php _e("(Default: 75) Defines the quality of the image thumbnail. A number from 1(lowest quality, least size) to 100 (best quality, highest size). ","riview"); ?></span></td>
            </tr>
            <tr>
                <td><?php _e("Custom Field","riview"); ?><br />
                <input type="text" size="15" name="customfield" value=""/><br />
                                <span><?php _e("Display Title from a custom field over the thumbnail instead of the Post Title. If this parameter is not used, the Post Title is used by default.","riview"); ?></span></td>
                <td><?php _e("Show Title","riview"); ?><br />
                <select name='showtitle'>
							<option value='hover'><?php _e("Hover (Default) ","riview"); ?></option>
							<option value='always'><?php _e("Always","riview"); ?></option>
							<option value='never'><?php _e("Never","riview"); ?></option>
				</select><br />
                <input type="hidden" name="title_init" value="1">
                <span><?php _e("Sets the appearance event of the Post Titles","riview"); ?></span>
                </td>
                <td><div class="upper"><?php _e("Paginate After","riview"); ?><br />
                		<input type="text" size="4" name="paginate" value=""/><br />
                        <span><?php _e("The number of posts after which pagination would occur (Default: No Pagination)","riview"); ?></span></div>
                	<div class="lower"><?php _e("Open Posts in a Lightbox","riview"); ?><br />
                		<input name="lightbox" type="checkbox" value="1" checked="checked" /><br /></div>
               </td>
               <td><?php _e("Title","riview"); ?><br />
               		<input type="text" size="15" name="title" value=""/><br />
                        <span><?php _e("Show a Custom Title to be displayed over the Thumbnails. Enter the name of the Custom Field which contains the title value. For posts which do not contain that custom field, Post Title is displayed. (Default: Post Titles are used.)","riview"); ?></span>
               </td></tr>
         </table>
    </form>
   	</div>

</div>
<div id="riview-settings-right" class="alignright" >
<p> <span style="font-size:14px;"><span style="font-size:16px;font-weight:bold";>C</span>ategory <span style="font-size:16px;font-weight:bold";>G</span>rid <span style="font-size:16px;font-weight:bold";>View</span> Gallery v<?php echo PLUGIN_VERSION; ?></span><br />
by <a href="<?php echo AUTHOR_URI; ?>"><?php echo PLUGIN_AUTHOR; ?></a></p>
    <div id="riview-fanbox">
    </div>
<p><span style="font-size:12px;"><a href="<?php echo PLUGIN_URI; ?>"  target="_blank">Plugin Home</a><br /><br />
	<a href="http://www.facebook.com/evilgeniuslabs" target="_blank">Facebook Page</a></span><br /><br />
    <a href="http://www.evilgenius.anshulsharma.in/donate-me/" target="_blank">Support the Author - Donate</a></span><br /><br />
    <span style="color:#aaa;font:19px bold;">.::.</span></p>
</div>
</div>

<?php

 
}


//Check if the image is valid image or not
function is_valid_riview_image($image){
  // check mime type
  if(!eregi('image/', $_FILES[$image]['type'])):
   wp_redirect(admin_url('options-general.php?page=riview&error=1'));
   exit(0);
  endif;

  // check if valid image
  $imageinfo = getimagesize($_FILES[$image]['tmp_name']);
  if($imageinfo['mime'] != 'image/gif' && $imageinfo['mime'] != 'image/jpeg' && $imageinfo['mime'] != 'image/png' && isset($imageinfo)):
   wp_redirect(admin_url('options-general.php?page=riview&error=2'));
   exit(0);
  endif;

  list($width, $height) = $imageinfo;

  $directory = riview_upload_dir('basedir').'/';
  if(!@move_uploaded_file($_FILES[$image]['tmp_name'],$directory.$_FILES[$image]["name"])):
   wp_redirect(admin_url('options-general.php?page=riview&error=3'));
   exit(0);
  else:
   return $width.'x'.$height;
  endif;
}


add_action('admin_menu', 'ri_admin');
add_action('admin_post_riview_update', 'riview_update_options');
add_action('admin_init', 'riview_verify_options');

?>
