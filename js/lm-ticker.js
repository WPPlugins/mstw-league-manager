jQuery(function($) {
	$('.lmt-next').click(function(){
		var arrow = $(this);
		//alert( "arrow.attr('id') = " + arrow.attr('id') );
		
		var slider_id = '#'+arrow.attr('id').replace('lmt-next', 'schedule-ticker');
		var suff = arrow.attr('id').replace('lmt-next', '');
		
		//alert( 'Right Arrow: ' + 'slider_id: ' + slider_id  + ' suff: ' + suff );
		
		var block_width = $( '.lmt-list-item' ).outerWidth( true );

		//var view_width = $('#lmt-ticker-content'+suff).outerWidth();
		var view_width = $('.lmt-viewport'+suff).outerWidth();
		
		//alert( 'block_width: ' + block_width  + ' view_width: ' + view_width );
		
		var nbr_games = $('li.lmt-list-item').length;
		
		
		var viewable_games = Math.floor( view_width/block_width );
		var nbr_blocks     = viewable_games;
		
		
		/*
		console.log( '-------------------------' );
		console.log( "view_width = " + view_width );
		console.log( "nbr_games = " + nbr_games );
		console.log( 'viewable_games = ' + viewable_games );
		
		alert( 'nbr_games: ' + nbr_games  + ' viewable_games: ' + viewable_games );
		
		console.log( '-------------------------' );
		console.log( "Next/right arrow pressed" );
		console.log( 'block_width = ' + block_width );
		console.log( 'view_width = ' + view_width );
		console.log( 'nbr_games = ' + nbr_games );
		*/
		
		
		if ( nbr_games < viewable_games ) {
			//alert( 'We don\'t do nuthin\' ' );
		}
		else {
			var curr_left_pos = parseInt($('.lmt-viewport' + suff + ' ul').css('left'));
			
			//fix for 1 block wide slider
			var slide_distance = ( nbr_blocks <= 1 ) ? block_width : (nbr_blocks-1)*block_width;
		
			var right_stop = -(nbr_games*block_width - view_width );
			
			/*
			console.log( '-------------------------' );
			console.log( 'block_width = ' + block_width );
			console.log( 'nbr_games = ' + nbr_games );
			console.log( 'view_width = ' + view_width );
			console.log( 'right_stop = ' + right_stop );
			*/
			
			//alert( ' view_width: ' + view_width + ' nbr_games*block_width: ' + nbr_games*block_width );
			
			//alert( ' curr_left_pos: ' + curr_left_pos + ' right_stop: ' + right_stop );
			
			//new_left_pos = Math.max( curr_left_pos - view_width, right_stop );
			new_left_pos = Math.max( curr_left_pos - slide_distance, right_stop );
			
			//console.log( 'new_left_pos = ' + new_left_pos );
			
			//alert( 'next arrow new_left_pos: ' + new_left_pos );
			
			//$('.schedule-slider').css( {'left' : new_left_pos } );
			$('.lmt-viewport' + suff + ' ul').css( {'left' : new_left_pos } );
		}
	});
	
	$('.lmt-prev').click(function(){
		var arrow = $(this);
		
		var slider_id = '#'+arrow.attr('id').replace('lmt-prev', 'schedule-ticker');
		
		var suff = arrow.attr('id').replace('lmt-prev', '');
		
		//alert( 'Prev Arrow: ' + 'slider_id: ' + slider_id  + ' suff: ' + suff );
		
		var block_width = $( '.lmt-list-item' ).outerWidth( true );

		var view_width = $('.lmt-viewport' + suff).outerWidth();
		
		//alert( 'block_width: ' + block_width  + ' view_width: ' + view_width );
		
		var nbr_games = $('li.lmt-list-item').length;
		
		var viewable_games = Math.floor( view_width/block_width );
		var nbr_blocks     = viewable_games;
		
		/*
		console.log( '-------------------------' );
		console.log( "Prev/left arrow pressed" );
		console.log( 'block_width = ' + block_width );
		console.log( 'view_width = ' + view_width );
		console.log( 'nbr_games = ' + nbr_games );
		console.log( 'viewable_games = ' + viewable_games );
		*/
		//alert( 'nbr_games: ' + nbr_games  + ' viewable_games: ' + viewable_games );
	
		if ( nbr_games > viewable_games ) {
			var curr_left_pos = parseInt($('.lmt-viewport'+suff + ' ul').css('left'));
			
			var left_stop = 0;
			
			//fix for 1 block wide slider
			var slide_distance = ( nbr_blocks <= 1 ) ? block_width : (nbr_blocks-1)*block_width;
		
			//alert( ' view_width: ' + view_width + ' nbr_games*block_width: ' + nbr_games*block_width );
			
			//alert( ' curr_left_pos: ' + curr_left_pos + ' left_stop: ' + left_stop );
			
			new_left_pos = Math.min( curr_left_pos + slide_distance, left_stop );
			
			//alert( 'next arrow new_left_pos: ' + new_left_pos );
			
			//$('.schedule-slider').css( {'left' : new_left_pos } );
			$('.lmt-viewport'+suff + ' ul').css( {'left' : new_left_pos } );
		}	
	});
});