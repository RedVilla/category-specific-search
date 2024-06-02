jQuery( document ).ready( function($) {
	 "use strict";
		var currentRequest = null;
		
		var cssfw_ajax_function = function( $el ){
			
        	var charaters		 = $el.data('charaters'),
				functiontype 	 = $el.data('functiontype'),
				input_val	 	 = $el.val(),
				load_result 	 = $el.parents('.cssfw-search-wrap').find('.cssfw_ajax_result'),
				category_val 	 = $el.parents('.cssfw-search-wrap').find('.cssfw-category-items').val(),
				action_url 		 = $el.parents('.cssfw-search-wrap').find('form').attr('action');
				
		if (typeof category_val == 'undefined') {
			category_val = '';
		}
		
		if( input_val == ''){ load_result.html(''); }	
			if( input_val.length >= charaters && functiontype != 'simple'){
				
			currentRequest = jQuery.ajax({
				url : cssfw_localize.ajaxurl,
				type : 'post',
				data : {
					action 		: 'cssfw_get_woo_search_result',
					keyword 	: input_val,
					category 	: category_val
				},
				beforeSend : function()    {
					if(currentRequest != null) {
						currentRequest.abort();
					}
					$el.parents('.cssfw-search-wrap').find('.cssfw_loader').show();
					
				},
				success : function( response ) {
					var json 			= jQuery.parseJSON( response ),
					   $html 			= '<div class="cssfw_result_wrap"><ul class="cssfw_data_container">',
					   all_search_btn   = false,
					   featured			= '';
					
					$.each(json, function(idx, data) {  featured = '' ;
						if( data.id == 0 ){ 
							$html += '<li class="cssfw_empty"><span class="cssfw_result_item_empty">'+ data.title +'</span></li>';
						}else{
							
							all_search_btn = true;
							
						if( typeof data.featured !== "undefined"  && data.featured == true  ){
							featured = 'cssfw_featured'
						}
					    $html += '<li class='+ featured +'> <a href="'+ data.url +'">';
			
					if( typeof data.img_url !== "undefined"  && data.img_url != ""  ){
						$html +='<span class="cssfw_img_product"> <img src="'+ data.img_url +'" alt="" ></span>';
					}
				
					$html +='<span class="cssfw-info-product"><span class="cssfw-name"> ';
					$html +=  data.title;
					$html +='</span> ';
					
					
					if( typeof data.price !== "undefined"  && data.price != ""  ){
						$html +=' <span class="cssfw-price"> '+ data.price +' </span>';
					}
					if( typeof data.content !== "undefined"  && data.content != ""  ){
						$html +=' <span class="cssfw_result_excerpt"> '+ data.content +' </span>';
				   	}
					if( typeof data.rating !== "undefined"  && data.rating != ""  ){
						$html +=' <span class="cssfw_result_rating"> '+ data.rating +' </span>';
				   	}
					if( typeof data.category !== "undefined"  && data.category != ""  ){
						$html +=' <span class="cssfw_result_category"> '+ data.category +' </span>';
				   	}
					if( typeof data.sku !== "undefined"  && data.sku != ""  ){
						$html +=' <span class="cssfw_result_category"> Sku : '+ data.sku +' </span>';
				   	}
					if( typeof data.stock !== "undefined"  && data.stock != ""  ){
						$html +=' <span class="cssfw_result_stock"> '+ data.stock +' </span>';
				   	}
					if( typeof data.on_sale !== "undefined"  && data.on_sale != ""  ){
						$html +=' <span class="cssfw_result_on_sale"> '+ data.on_sale +' </span>';
				   	}
					
				
				 $html +='</span> '+
				'</a>'+
				'<div class="clearfix"></div>'+
				'</li>';
				
							
						}
					});
					
					if( all_search_btn == true && cssfw_localize.view_text != "" ){
						$html +='<li class="cssfw_empty"><a href="'+ action_url +'?s='+input_val+'&post_type=product&category='+category_val+'" class="cssfw_view_all_product"> '+ cssfw_localize.view_text +' </a></li>';
					}
					$html += '</ul></div>';
					load_result.html($html);
					$('.cssfw_loader').hide();
					
				}
			});		
	
					
			}
				//alert( charaters );
        };
		

	jQuery(document).on('input',".cssfw-search-input",function(e){		
		e.stopPropagation();
		
		cssfw_ajax_function( $( this ) );
		
	});

	jQuery(document).on('click',".cssfw-search-input",function(e){	
		e.stopPropagation();
		
		cssfw_ajax_function( $( this ) );
		
	});
	
	
	jQuery(document).on('submit',".cssfw-search-form.ajax",function(e){
		 e.preventDefault();
	});
	
	jQuery(document).on('click',".cssfw_view_all_product",function(e){
		e.stopPropagation();
		$(this).parents('.cssfw-search-wrap').find('form').submit();
		
	});
	
	
	/*jQuery(document).on('click','body',function(e){
		e.stopPropagation();
		
		alert('test');
		
	});*/
	
	$(document).mouseup(function(e) 
	{
		var container = $(".cssfw-search-wrap");
	
		// if the target of the click isn't the container nor a descendant of the container
		if (!container.is(e.target) && container.has(e.target).length === 0) 
		{
			  container.find('.cssfw_ajax_result').html('');
		}
	});
		
	


	
});

