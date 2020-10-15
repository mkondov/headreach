;(function($, window, document, undefined) {
	var $win = $(window);
	var $doc = $(document);

	$doc.ready(function() {

		var findButtonPressed = 0;
		// var siteURL = 'http://dminchev.me/headreach-app';
		var siteURL = 'https://headreach.com/app';

		if ( typeof(parameters) != 'undefined' ) {
			json = $.parseJSON( parameters );
			newJson = json;
		}

		$doc.on('click', '.callout .close', function( e ) {
			e.preventDefault();
			
			$( this )
				.parent()
				.fadeOut( 400 );
		});

		$( '.upgrade-slider' ).on('click', '.close-button', function( e ){
			e.preventDefault();
			$( '.upgrade-slider' ).fadeOut( 100 );
			$( '.upgrade-slider' ).remove();

			createCookie('hr-user-closed-credits', 'closed previously', 1);
		});

		$( '.autocomplete-field' ).on('keypress', function( e ){
			var keycode = (e.keyCode ? e.keyCode : e.which);
				
			if ( keycode == 39 || keycode == 34 ) {
				return false;
			}
		});

		$( '.autocomplete-field' ).on('paste', function () {
			var element = $(this);
			setTimeout(function () {
				element.val(element.val().replace(/['"]/g, ""));
			}, 1);
		});

		$( '.filter-content-wrapper input' ).on('focus', function(){
			var $handler = $( this ).siblings( '.press-enter' ).addClass( 'visible' );

			// setTimeout(function() {
			// 	$handler.removeClass( 'visible' );
			// }, 3000);
		});

		$( '.filter-content-wrapper input' ).on('focusout', function(){
			$( this ).siblings( '.press-enter' ).removeClass( 'visible' );
		});


		$( '.filter-content-wrapper input:not(.autocomplete-field)' ).on('focusout', function( e ){
			var e = $.Event( 'keypress', { which: 13 } );
			$( this ).trigger(e);
		});

		$( '.autocomplete-field' ).each(function(){

			var $this = $( this ),
				format = $this.data( 'format' ),
				url = $this.data( 'entries-url' );

			var searchParams = {
				serviceUrl: url,
				onSelect: function (suggestion) {
					// $( this ).trigger({type: 'keypress', which: 13, keyCode: 13});

					var $this = $( this ),
						$filter = $( this ).parents( '.filter-content' ),
						$wrapper = $filter.find( '.filter-content-wrapper' ),
						$tags = $wrapper.find( 'ul.tags' ),
						filterParam = $filter.data( 'param' ),
						value = suggestion.value,
						filterData = newJson[filterParam];

					if ( !value || $filter.length < 1 ) {
						return false;
					}
					
					// Missing list, create one
					if ( $tags.length < 1 ) {
						$wrapper.append( '<ul class="tags clearfix subpixel"></ul>' );
						$tags = $wrapper.find( 'ul.tags' );
					}

					// probably validate the value here
					$tags.append( '<li><span class="tag"><a data-value="'+ value +'" href="#" class="close">×</a>'+ value +'</span></li>' );

					$this.siblings( '.press-enter' ).removeClass( 'visible' );

					$this.val( '' );
					newJson[filterParam].push( value );
				}
			};

			if ( format == 'position' ) {
				
				var customParams = {
					minChars: 4
				}

				jQuery.extend( searchParams, customParams );
			}

			if ( format == 'html' ) {

				var customHTML = {
					formatResult: function(suggestion, currentValue) {
						var html = '<div class="row align-middle">';
							html += '<div class="shrink column">';
							html += '<span class="filter-autocomplete-avatar" style="background-image: url('+ suggestion.data +');"></span>';
							html += '</div>';
							html += '<div class="column">'+ suggestion.value +'</div>';
							html += '</div>';

						return html;
					}
				}

				jQuery.extend( searchParams, customHTML );
			}

			$this.autocomplete( searchParams );

		});

		// Prototype for clearing an array
		Array.prototype.clear = function() {
			while (this.length) {
				this.pop();
			}
		}

		$.fn.enterPress = function (fnc) {
			return this.each(function () {
				$(this).keypress(function (ev) {
					var keycode = (ev.keyCode ? ev.keyCode : ev.which);
					if (keycode == '13') {
						fnc.call(this, ev);
					}
				})
			})
		}

		$( '.filter-content-wrapper input' ).enterPress(function () {
		    
		    var $this = $( this ),
				$filter = $( this ).parents( '.filter-content' ),
				$wrapper = $filter.find( '.filter-content-wrapper' ),
				$tags = $wrapper.find( 'ul.tags' ),
				filterParam = $filter.data( 'param' ),
				value = $this.val(),
				filterData = newJson[filterParam];

			if ( !value ) {
				return false;
			}
			
			// Missing list, create one
			if ( $tags.length < 1 ) {
				$wrapper.append( '<ul class="tags clearfix subpixel"></ul>' );
				$tags = $wrapper.find( 'ul.tags' );
			}

			$this.siblings( '.press-enter' ).removeClass( 'visible' );

			// probably validate the value here
			$tags.append( '<li><span class="tag"><a data-value="'+ value +'" href="#" class="close">×</a>'+ value +'</span></li>' );

			$this.val( '' );

			newJson[filterParam].push( value );

			console.log( newJson );

		});

		$( '.degree-filter' ).on('change', 'select', function(e){
			var $this = $this,
				$filter = $( this ).parents( '.filter-content' ),
				filterParam = $filter.data( 'param' ),
				value = $(this).find('option:selected').val(),
				filterData = newJson[filterParam];

			newJson[filterParam].clear();
			newJson[filterParam].push( value );
		});

		$doc.on('click', '.load-more-button a.load-button', function( e ){
			e.preventDefault();

			var $this = $( this ),
				queryID = $this.data( 'query-id' );

			var query = {
				'queryID': queryID
			};

			var $loadButton = $( '.load-more-button' );

			$.ajax({
				type: 'post',
				url: siteURL + '/web/prospector/loadmoreresults',
				async: true, // do not block browser
				data: query,
				beforeSend: function() {
					$loadButton.find( 'a' ).hide();
					$loadButton.append( getLoader() );
				},
				success: function ( data ){
					
					var $newData = $( data ),
						$results = $newData.find( 'tbody.tmp-body >' ),
						$nav = $newData.find( '.nav-contents >' );

					$results.hide();

					$( '.table-listing tbody.main-tbody' ).append( $results );
					$results.fadeIn( 400 );

					// $('html, body').animate({
					// 	scrollTop: $results.offset().top - 56
					// }, 600);

					if ( $nav.length > 0 ) {
						$loadButton.html( $nav );
						// $loadButton.fadeIn( 400 );
					}

					// Re-init foundation
					$(document).foundation();
				}
			});

		});

		$doc.on('click', '.tag .close', function( e ){
			e.preventDefault();

			var $this = $( this ),
				$filter = $( this ).parents( '.filter-content' ),
				$wrapper = $filter.find( '.filter-content-wrapper' ),
				$tags = $wrapper.find( 'ul.tags' ),
				filterParam = $filter.data( 'param' ),
				value = $this.data( 'value' ),
				filterData = newJson[filterParam];

			var arr = newJson[filterParam];

			$this
				.parents( 'li' )
				.remove();

			arr.splice( $.inArray(value, arr), 1 );

			if ( $tags.find( 'li' ).length < 1 ) {
				$tags.remove();
			}
		});

		$( '.date-picker' ).datepicker( {
			changeMonth: true,
			changeYear: true,
			showButtonPanel: true,
			dateFormat: 'MM yy',
			yearRange: "-20:+0",
			defaultDate: null,
			constrainInput: true,
			// closeText: 'Clear', // Text to show for "close" button
			onClose: function(dateText, inst) {

				var $this = $( this ),
					$filter = $( this ).parents( '.filter-content' ),
					filterParam = $filter.data( 'param' ),
					filterData = newJson[filterParam];

                var event = arguments.callee.caller.caller.arguments[0];
                if ( $(event.delegateTarget).hasClass('ui-datepicker-close') ) {
					$(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
					
					// Get the updated value
					var value = $this.val();
					newJson[filterParam].clear();
					newJson[filterParam].push( value );
                }
			}
		});

		$( '.date-picker' ).on('keypress', function( ev ) {
			var keycode = (ev.keyCode ? ev.keyCode : ev.which);
			if ( keycode != 8 ) {
				ev.preventDefault();
			} else {

				var $this = $( this ),
					$filter = $( this ).parents( '.filter-content' ),
					filterParam = $filter.data( 'param' ),
					value = $this.val();

				if ( !value ) {
					newJson[filterParam].clear();
				}
			}
		});

		$( '.filter-current' ).on('change', function(){

			var $this = $( this ),
				filterParam = $this.data( 'param' );

			if ( $(this).is(':checked') ) {
				newJson[filterParam].push( 'true' );
			} else {
				newJson[filterParam].clear();
			}

		});

		$doc.on('click', '.adv-search a.button', function( e ){
			e.preventDefault();

			var value = 'Advanced search';

			var isValid = false;

			for (key in newJson) {

				if ( key != 'current_only' ) {
					if ( newJson[key].length > 0 ) {
						isValid = true;
					}
				}

			}

			if ( !isValid ) {
				$( '.validation-message p' ).fadeIn( 400 );
				return false;
			}

			var query = {
				'search_type': 5, // Advanced search
				'parameter': value,
				'advanced_params': newJson,
			};

			var request = $.ajax({
				type: 'post',
				url: siteURL + '/web/task/executequery',
				async: true, // do not block browser
				data: query,
				beforeSend: function() {
					var msg = getReportMessage();
					$( 'body' ).prepend( msg );

					var $msgObj = $( 'body' ).find( '.callout.success' );

					$msgObj.fadeIn( 600 );
				},
				success: function ( data ){
					var dataObj = jQuery.parseJSON( data ),
						url = dataObj.query_url;

					window.location.href = url;
				}
			});
		});

		$( '.filter-content-wrapper' ).on( 'keydown', 'input', function ( evt ) {
			if ( evt.keyCode == 13 ) {	
			}
		});

		if ( typeof(do_poll) != 'undefined' && do_poll == true ) {
			setInterval(function() {
				checkReportStatus();
			}, 1500);
		}

		$( '.search-form' ).on('submit', function( e ){
			e.preventDefault();

			$( this )
				.find( 'a.button' )
				.trigger( 'click' );
		});

		$( '.search-form' ).on('click', 'a', function( e ){
			e.preventDefault();

			var $form = $( this ).parents( 'form' ),	
				value = $form.find( '.input-group-field' ).val(),
				search_type = $form.data( 'search_type' ),
				action = $form.attr( 'action' );

			if ( !value ) {
				$form.addClass( 'field-error' );
				return false;
			}

			if ( search_type == 3 ) {
				// Validate for domain name here
				// $form.addClass( 'field-error' );
				// return false;
			}

			var query = {
				'search_type': search_type,
				'parameter': value,
			};

			var request = $.ajax({
				type: 'post',
				url: action,
				async: true, // do not block browser
				data: query,
				beforeSend: function() {
					var msg = getReportMessage();
					$( 'body' ).prepend( msg );

					var $msgObj = $( 'body' ).find( '.callout.success' );

					$msgObj.fadeIn( 600 );
					$form.removeClass( 'field-error' );
				},
				success: function ( data ){
					var dataObj = jQuery.parseJSON( data ),
						url = dataObj.query_url,
						inserted = dataObj.inserted;

					if ( inserted ) {
						window.location.href = url;
					} else {
						var $msgObj = $( 'body' ).find( '.callout.success' );
						$msgObj.html( getAlertMessage() );
						$msgObj
							.removeClass( 'success' )
							.addClass( 'alert' );
					}

				}
			});

		});

		$doc.on('click', '.find-socials a', function( e ){
			e.preventDefault();

			findButtonPressed++;

			// if ( findButtonPressed > 3 ) {
			// 	$( '.find-socials a' ).attr('disabled', 'disabled');
			// 	return false;
			// }

			var $this = $( this ),
				$container = $this.parent(),
				$loader = $container.find( '.loading-socials' ),
				$row = $container.parent(),
				name = $this.data( 'name' ),
				influencerID = $this.data( 'influencer-id' );

			var query = {
				'id': influencerID,
			};

			var backURL = siteURL + '/web/task/findsocials/';
			var isRerun = false;

			if ( $container.hasClass( 're-run' ) ) {
				backURL = siteURL + '/web/task/rerunsocials/';
				isRerun = true;
			}

			var request = $.ajax({
				type: 'post',
				url: backURL,
				accepts: 'application/json',
				async: true, // do not block browser
				data: query,
				beforeSend: function() {
					$this.remove();
					$loader.fadeIn( 400 );
				},
				success: function ( data ){

					findButtonPressed--;

					// if ( findButtonPressed < 3 ) {
					// 	$( '.find-socials a' ).removeAttr( 'disabled' );
					// }

					if ( isRerun ) {
						$container
							.siblings( '.social' )
							.remove();
					}

					$container
						.fadeOut( 400 )
						.remove();

					var newData = $( data );
					newData.hide();

					$row.append( newData );
					newData.fadeIn( 400 );

					// Re-init foundation
					$(document).foundation();

					if ( data.match('data-entry-found') != null ) {
						precalculateCredits();
					}

					if ( isRerun ) {
						var msg = getUpdateMessage( name );
					} else {
						var msg = getSuccessMessage( name );
					}

					$( 'body' ).prepend( msg );

					var msgObj = $( 'body' ).find( '.callout.success' );

					setTimeout(function() {
						msgObj.fadeOut( 400 );
						msgObj.remove();
					}, 2000);

				}
			});
		});

		$doc.on('click', '.name-link', function( e ){
			e.preventDefault();

			var id = $( this ).data( 'id' ),
				$tableHolder = $( '.table-listing' );

			var query = {
				'id': id
			};

			var request = $.ajax({
				type: 'post',
				url: siteURL + '/web/prospector/influencer/',
				accepts: 'application/json',
				async: true, // do not block browser
				data: query,
				beforeSend: function() {
					$( '.tabs-content' ).hide();
					$( '.popup-overlay' ).show();
					$( '#influencer-modal' )
						.foundation( 'open' );
				},
				success: function ( data ){

					// console.log( 'called' );
					setTimeout(function() {
					}, 1000);
					
					$( '#influencer-modal' )
						// .foundation( 'open' )
						.html( data );


					$( '.tabs-content' ).fadeIn( 600 );
					$( '.popup-overlay' ).hide();

					$(document).foundation();

					// $( '#influencer-modal' ).foundation( 'reflow' );
				}
			});

		});

		$( document ).on('click', '.callout.success', function( e ){
			e.preventDefault();
			$( this ).remove();
		});

		function getSuccessMessage( name ) {
			var html = '<div class="callout floating success">'+ name +' was added to your Contacts. <span class="close" aria-hidden="true" data-tooltip aria-haspopup="true" title="Dismiss">&times;</span></div>';

			return html;
		}

		function getUpdateMessage( name ) {
			var html = '<div class="callout floating success">'+ name +' was successfully updated. <span class="close" aria-hidden="true" data-tooltip aria-haspopup="true" title="Dismiss">&times;</span></div>';

			return html;
		}

		function getReportMessage() {
			var html = '<div class="callout floating success hidden">Finding people... Please wait.<span class="close" aria-hidden="true" data-tooltip aria-haspopup="true" title="Dismiss">&times;</span></div>';

			return html;
		}

		function getAlertMessage() {
			var html = 'You have used all of your searches. ★ Please upgrade<span class="close" aria-hidden="true" data-tooltip aria-haspopup="true" title="Dismiss">&times;</span>';

			return html;
		}

		function getLoader() {
			var html = '<div class="loading-bars-wrapper"><div class="loading-bar"></div> <div class="loading-bar"></div> <div class="loading-bar"></div> <div class="loading-bar"></div> <div class="loading-bar"></div></div>';

			return html;
		}

		function precalculateCredits() {
			var $holder = $( '.dropdown  .progress-wrapper' ),
				$usedCreditsHolder = $( '#credits-used' ),
				$pregressMeter = $( '.dropdown .progress-meter' ),
				currentTotalUsed = $( '#credits-used' ).text(),
				step = $holder.data( 'step' );

			var newTotal = parseInt(currentTotalUsed) + 1;

			$usedCreditsHolder.html( newTotal );

			var newPercent = newTotal * step;
			$pregressMeter.css({
				'width' : newPercent + '%'
			});
		}

		function checkReportStatus() {
			var query = {
				'id': maskedID,
			};

			var request = $.ajax({
				type: 'post',
				url: siteURL + '/web/prospector/checkstatus/',
				accepts: 'application/json',
				async: true, // do not block browser
				data: query,
				success: function ( data ){
					var dataObj = jQuery.parseJSON( data ),
						status = dataObj.status;

					if ( status == 'ready' ) {
						location.reload();
					}
				}
			});
		}

		function createCookie(name,value,days) {
			if (days) {
				var date = new Date();
				date.setTime(date.getTime()+(days*24*60*60*1000));
				var expires = "; expires="+date.toGMTString();
			} else var expires = "";

			document.cookie = name+"="+value+expires+"; path=/";
		}

		function readCookie(name) {
			var nameEQ = name + "=";
			var ca = document.cookie.split(';');
			
			for(var i=0;i < ca.length;i++) {
				var c = ca[i];
				while (c.charAt(0)==' ') c = c.substring(1,c.length);
				if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
			}

			return null;
		}

		function eraseCookie(name) {
			createCookie(name,"",-1);
		}

	});

})(jQuery, window, document);