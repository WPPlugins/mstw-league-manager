jQuery(function($) {
	//These are legacy. 
	//Most have been replaced in the click functions below
	var left_indent = parseInt($('.lm-schedule-slider').css('left'));
	var block_width = $('.game-block').outerWidth();
	var slider_width = $('.lm-schedule-slider').outerWidth();
	
	//KEEP THIS ONE
	var left_stop = 0; 
	
	var view_width = $('.lm-slider-area').outerWidth();
	var nbr_blocks = Math.floor( view_width/block_width );

	//10 to acccount for extra width of slider
	var right_stop = -slider_width + nbr_blocks*block_width + 10; 
	
	//console.log( 'start view_width = ' + view_width );
	//console.log( 'start nbr_blocks = ' + nbr_blocks );
	
	
	$('.lm-slider-right-arrow').click(function(){
		//console.log( "---------------------" );
		//console.log( "RIGHT ARROW PRESSED" );
		var arrow = $(this);
		
		var slider_id = '.'+arrow.attr('id').replace('lm-slider-right-arrow', 'lm-schedule-slider');
		
		var suff = arrow.attr('id').replace('lm-slider-right-arrow', '');
		
		var block_width = $('.lm-schedule-slider' + suff + ' .game-block').outerWidth();

		var slider_width = $('.lm-schedule-slider'+suff).outerWidth();

		var view_width = $('.lm-slider-area'+suff).outerWidth();
		//console.log( 'view_width = ' + view_width );
		
		var nbr_blocks = Math.floor( view_width/block_width );
		
		//fix for 1 block wide slider
		var slide_distance = ( nbr_blocks <= 1 ) ? block_width : (nbr_blocks-1)*block_width;
		
		//10 to acccount for extra width of slider
		var right_stop = -slider_width + nbr_blocks*block_width + 10;
		
		/*
		console.log( 'block_width = ' + block_width );
		console.log( 'view_width = ' + view_width );
		console.log( 'nbr_blocks = ' + nbr_blocks );
		
		console.log( 'slider_width = ' + slider_width );
		console.log( 'nbr_blocks = ' + nbr_blocks );
		console.log( 'right_stop = ' + right_stop );
		*/

		var left_indent = parseInt($('.lm-schedule-slider'+suff).css('left'));
		//console.log( 'left_indent = ' + left_indent );
		left_indent = Math.max( left_indent - slide_distance, right_stop );
		
		//console.log( 'left_indent = ' + left_indent );
		
		$(slider_id).css( {'left' : left_indent } );
		
	});
	
	$('.lm-slider-left-arrow').click(function(){
		var arrow = $(this);
		
		var slider_id = '.'+arrow.attr('id').replace('lm-slider-left-arrow', 'lm-schedule-slider');
		
		var suff = arrow.attr('id').replace('lm-slider-left-arrow', '');
		
		var block_width = $('.lm-schedule-slider' + suff + ' .game-block').outerWidth();

		var slider_width = $('.lm-schedule-slider'+suff).outerWidth();

		var view_width = $('.lm-slider-area'+suff).outerWidth();
		
		var nbr_blocks = Math.floor( view_width/block_width );

		//fix for 1 block wide slider
		var slide_distance = ( nbr_blocks <= 1 ) ? block_width :(nbr_blocks-1)*block_width;
		
		//10 to acccount for extra width of slider
		var right_stop = -slider_width + nbr_blocks*block_width + 10;

		var left_indent = parseInt($('.lm-schedule-slider'+suff).css('left'));
	
		left_indent = Math.min( left_indent + slide_distance, left_stop );
		
		$(slider_id).css( {'left' : left_indent } );
			
	});
});