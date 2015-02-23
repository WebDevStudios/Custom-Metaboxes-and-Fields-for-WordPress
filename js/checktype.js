/**
 *	Display post format meta boxes as needed
 *  Change meta boxes id to metabox-post-FORMAT
**/

jQuery(document).ready(function($){

	$('#post-formats-select input').change(checkFormat);
	$('.wp-post-format-ui .post-format-options > a').click(checkFormat);
	 
	function checkFormat(){
		var format = $('#post-formats-select input:checked').attr('value');
		if( typeof format != 'undefined'){
			$('#post-body div[id^=metabox-post-]').hide();
			$('#post-body #metabox-post-'+format+'').stop(true,true).fadeIn(500);		
		}
	}
	 
	$(window).load( function(){
		checkFormat();
	})	
    
});


