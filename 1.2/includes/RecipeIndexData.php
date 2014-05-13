<?php
/**
Query to get the posts
Author : Anshul Sharma (contact@anshulsharma.in)
 */
 
 if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Sorry, Dude. You are not allowed to call this page directly.'); }

class RecipeIndexData{
    private $params = array();
    private $riposts = array();
    private $riid = 0;

    /**
     * Constructor gets the shortcode attributes as parameter
     * @param array $atts
     */
    public function __construct($atts) {
        $this->params = $atts;
        //Get the category posts:
         $this->lcp_set_categories();
		 	
    }

    /**
     * Get the categories & posts
     */
    private function lcp_set_categories(){
        if($this->params['name'] != '' && $this->params['id'] == '0'){
            $this->riid = $this->get_category_id_by_name($this->params['name']);
        }else{
            $this->riid = $this->params['id'];
        }

        $lcp_category = 'cat=' . $this->riid;
	
        //Build the query for get_posts()
        $riquery = array(		'cat' => $this->riid ,
								'posts_per_page' => $this->params['num'] ,
                                'orderby' => $this->params['orderby'] ,
                                'order' => $this->params['order'] ,
                                'post__not_in' => explode(',',$this->params['excludeposts']) ,
                                'tag' => $this->params['tags'] ,
                                'offset' => $this->params['offset'],
								'meta_key' => $this->params['customfield'],
								'meta_value' => $this->params['customfieldvalue']
								);
		$tmp_query = new WP_Query($riquery);
		$this->riposts = $tmp_query->posts;
       
    }

    /**
     * Get the category id from its name
     * by Eric Celeste / http://eric.clst.org
     */
    private function get_category_id_by_name($cat_name){
        //We check if the name gets the category id, if not, we check the slug.
        $term = get_term_by('slug', $cat_name, 'category');
        if (!$term):
            $term = get_term_by('name', $cat_name, 'category');
        endif;

        return ($term) ? $term->term_id : 0;
    }


    public function ri_get_posts(){
        return $this->riposts;
    }

    
    

}
