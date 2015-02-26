<?php
/**
Functions to retrieve the posts, get the Title, tbe first image and 
to generate thumbnails.
Author : Simon Austin (simon@kremental.com)
Inspired by the Category Grid View Plugin by Anshul Sharma
 */
 
 if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Sorry, Dude. You are not allowed to call this page directly.'); }
 
 require_once 'RecipeIndexData.php';
 
 class RecipeIndexView{
 	
    private $params = array();
    private $rioutput;
	private $riposts;
	private $ridata;
	private $size = array();
	
	
	public function __construct($atts) {
        $this->params = $atts;
        $this->ridata = new RecipeIndexData($atts);
		$this->ri_build_output();

    }
	
	 private function ri_build_output(){
	 	global $paginateVal;
	 	$this->rioutput='<div class="riview '.get_ri_option('color_scheme').'">';
		$this->rioutput.= '<ul id="ri-ul">'."\n"; 
        //Posts loop
        foreach ($this->ridata->ri_get_posts() as $single):
                $this->rioutput .= $this->ri_build_item($single)."\n";
        endforeach;
		$this->rioutput.= '</ul>';
//		if(get_ri_option('credits')){ $this->rioutput.= '<div id="ri-credits">Powered by <a href="'.PLUGIN_URI.'" target="_blank">CGView</a></div>'; }
		$this->rioutput.= '</div>'."\n";
		$paginateVal = $this->params['paginate'];
    }

    /*
	Build each item
     */
    private function ri_build_item($single){
		$size=array();
		$size=$this->ri_get_size();
	/* Simon - Add 60px to height to allow for space below image for text */
	$size[1] += 60;
		
        $riitem='<li id="ri-'.$single->ID.'" style="width:'.$size[0].'px;height:'.$size[1].'px;">';
		
        $riitem.= $this->ri_get_image($single);
		
		if(((int)$size[0]>=100||(int)$size[1]>=100))
		$riitem.= $this->ri_get_title($single);
		
		$riitem.= '</li>';
		
		$this->ri_active_post = $single->post_content;
		
        return $riitem;
    }
	
	private function ri_get_image($single){
		$ri_img = '';
  		ob_start();
  		ob_end_clean();
		if(get_ri_option('image_source')=='featured'){
			if (has_post_thumbnail($single->ID )){
				$image = wp_get_attachment_image_src(get_post_thumbnail_id( $single->ID ), 'single-post-thumbnail' );
				$ri_img = $image[0];
			}
			else {
				$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $single->post_content, $matches);
 				$ri_img = $matches [1] [0];
			}
		}
		else {
 			$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $single->post_content, $matches);
 			$ri_img = $matches [1] [0];
		}

  		if(empty($ri_img)){ //Defines a default image
    			$ri_img = get_ri_option('custom_image');
		}
		
		$size=array();
		$size=$this->ri_get_size();
			
			if((!is_numeric($this->params['quality']))||(int)$this->params['quality']>100)
				$this->params['quality']='75';
		//uses TimThumb to generate thumbnails on the fly	
		$ri_url = plugin_dir_url(__FILE__);
		$returnlink = ($this->params['lightbox'])? ('"'.$ri_url.'RecipeIndexPost.php?ID='.$single->ID.'" class="ripost"') : ('"'.get_permalink($single->ID)).'"';	
		return '<a href='.$returnlink.'><img src="'.$ri_url.'timthumb.php?src='.urlencode($ri_img).'&amp;h='.$size[1].'&amp;w='.$size[0].'&amp;zc=1&amp;q='.$this->params['quality'].'" alt="'.$single->post_title.'" title="'.$single->post_title.'"/></a>';
		

	}
	
	private function ri_get_title($single){
		$ri_url = plugin_dir_url(__FILE__);
		if($this->params['title']){
			$title_array = get_post_meta($single->ID, $this->params['title']);
			$title = $title_array[0];
			if(!$title){$title = $single->post_title;}
		}
		else { $title = $single->post_title;}
		$returnlink = ($this->params['lightbox'])? ('"'.$ri_url.'RecipeIndexPost.php?ID='.$single->ID.'" class="ripost"') : ('"'.get_permalink($single->ID)).'"';
		$rifontsize=$this->ri_get_font_size();
		$rititle='<div class="riback rinojs '.$this->params['showtitle'].'"></div><div class="rititle rinojs '.$this->params['showtitle'].'"><p style="font-size:'.$rifontsize.'px;line-height:'.(1.2*$rifontsize).'px;"><a href='.$returnlink.'>'.$title.'</a></p></div>';
		return $rititle;
	}
	
	public function display(){
        return $this->rioutput;
    }
	
	public function ri_get_size(){
		$size=array();
		switch ($this->params['size']) {
			case 'medium':
				$size=array('180','180');
				break;
			case 'large':
				$size=array('300','300');
				break;
			case 'thumbnail':
				$size=array('140','140');
				break;
			default:
				if(preg_match('/\b[0-9]{1,4}[xX][0-9]{1,4}\b/',$this->params['size']))
					$size = preg_split('/[xX]+/',$this->params['size'],-1,PREG_SPLIT_NO_EMPTY);	
				else
					$size=array('140','140');					
			}
	
	return $size;
	}
	
//Adjust fontsize according to the thumbnail size. Dont show title if either height or width < 100px	
	public function ri_get_font_size(){
		$size=array();
		$size=$this->ri_get_size();
		$rifontsize = (4/50)*(int)$size[1];
		if ($rifontsize>16)
			return 16;
		if ($rifontsize<8)
			return NULL;
		else
			return $rifontsize;
	}
	
}

 function ri_init_js(){
	 global $paginateVal;
	 if (is_null($paginateVal)) {
		 $paginateVal = 0;
	 }
    echo '<script type="text/javascript">';
    echo 'paginateVal = '.$paginateVal.';';
    echo '</script>';
    do_action('ri_init_js');
}

function get_ri_option($option) {
  $get_riview_options = get_option('riview');
  return $get_riview_options[$option];
}

