/* Author: Simon Austin (simon@kremental.com) 
based on code originally writte by Anshul Sharma (contact@anshulsharma.in) */
$j = jQuery.noConflict();
jQuery(document).ready(function($j) {

$j(function() {
         $j("a#settings").click(function() {
            $j("div#settings").show();
			$j("div#ri_sc").hide();
			$j("a[id='settings']").addClass("active");
			$j("a[id='shortcode']").removeClass("active");
            return false;
         });
	
      });   
$j(function() {
         $j("a#shortcode").click(function() {
            $j("div#settings").hide();
			$j("div#ri_sc").show();
			$j("a[id='settings']").removeClass("active");
			$j("a[id='shortcode']").addClass("active");
            return false;
         });
	
      }); 

$j(function() {
  $j( "input[type=radio]" ).fix_radios();
  $j("input[name='color_scheme']").change(function(){
    if ($j("input[name='color_scheme']:checked").val() == 'light'){
      alert($j("input[name='color_scheme']:checked").val());
        } else {alert($j("input[name='color_scheme']:checked").val()); }
      });
});

/* This function called when the "Generate Recipe Index code" button is clicked */
$j('form[name=ri_sc] input[name=submit_shortcode]').click(function(){
var newtext = "";
	/* loop through and create multiple entries */
	var sel = $j('#ri_categories').val();
	$j.each(sel, function(key,value){
		newtext += "<h2>" + idcat[value] + "</h2>\n";
		newtext += "[riview id=" + value; 
		newtext += " num=2000";
		newtext += " orderby=title";
		newtext += " order=asc";
		if ((document.ri_sc.sizew.value != "") && (document.ri_sc.sizeh.value != "")) {
			newtext += " size=" + document.ri_sc.sizew.value + "x" + document.ri_sc.sizeh.value;
			}
		newtext += " showtitle=always" 
		newtext += " lightbox=0" ;
		newtext += "]\n\n";
	});
	var isChecked = $j('#credit').prop('checked');
	if(isChecked) {
		newtext += "Recipe Index created by <a href='http://strawberriesforsupper.com/recipe-index'>Visual Recipe Index</a>";
	}
	
	document.ri_sc.riview_shortcode.value = newtext;
}); 

$j('input[name=reset_shortcode]').click(function(){
$j('#ri_categories option:selected').removeAttr('selected');
document.ri_sc.id.value = "";
document.ri_sc.categories.value = "";
document.ri_sc.sizew.value = "";
document.ri_sc.sizeh.value = "";
document.ri_sc.sizes.value = "thumbnail";
document.ri_sc.riview_shortcode.value = "[riview id=1]";
}); 


$j('form[name=ri_sc] select[name=sizes]').change(function(){
	var imgsize = document.ri_sc.sizes.value
	switch (imgsize){
		case "thumbnail":	$j('input:text[name=sizew]').val("140");
							$j('input:text[name=sizeh]').val("140");
							break;
		case "medium":		$j('input:text[name=sizew]').val("180");
							$j('input:text[name=sizeh]').val("180");
							break;
		case "large":		$j('input:text[name=sizew]').val("300");
							$j('input:text[name=sizeh]').val("300");
							break;
		default :	$j('input:text[name=sizew]').val("");
							$j('input:text[name=sizeh]').val("");
	}
      });
 
});

