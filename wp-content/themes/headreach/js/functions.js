;(function($, window, document, undefined) {
    
    var $win = $(window),
        $doc = $(document);

    $doc.ready(function(){

        $( "#phone" ).intlTelInput({
            // allowDropdown: false,
            // autoHideDialCode: true,
            // autoPlaceholder: "off",
            // dropdownContainer: "body",
            // excludeCountries: ["us"],
            // formatOnDisplay: false,
            // geoIpLookup: function(callback) {
            //   $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
            //     var countryCode = (resp && resp.country) ? resp.country : "";
            //     callback(countryCode);
            //   });
            // },
            // initialCountry: "auto",
            nationalMode: true,
            // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
            // placeholderNumberType: "MOBILE",
            preferredCountries: ['us', 'au', 'ca', 'gb', 'de', 'fr', 'it', 'es'],
            separateDialCode: true,
            utilsScript: "../wp-content/themes/headreach/js/iti/utils.js"
        });

        $(document).foundation();

        $( '#popup-cancel' ).on('click', '.button', function(e){
            e.preventDefault();

            var $container = $( '#popup-cancel' ),
                $form = $container.find( 'form' ),
                $errorHolder = $container.find( '.callout-admin' ),
                checkedBoxes = $form.find( 'input.checkbox:checked' );

            var $cancelButton = $( '.subscription_details .button.cancel' ),
                redirectURL = $cancelButton.data( 'href' );

            $errorHolder.hide();

            if ( checkedBoxes.length === 0 ) {
                $errorHolder.show();
                return false;
            }

            var args = {
                'action' : 'user_cancellation_handler',
                'data' : $form.serialize()
            };

            $.ajax({
                url: ajaxUrl,
                type: $form.attr( 'method' ),
                dataType: 'json',
                data: args,
                beforeSend: function(jqXHR, settings) {
                },
                success: function(data) {
                    window.location.href = redirectURL;
                },
                error: function(jqXHR, textStatus, errorThrown) { 
                    alert( 'There was an unexpected error' );
                }
            });

            return true;
        });

        showImages('.outreach-marketing-image-popup');

        // Ajax login form
        $doc.on('submit', '.form-sign-in', function( e ) {

            e.preventDefault();
         
            var $f = $(this),
                redirectURL = $f.find('input[name="redirect-url"]').val();

            var formData = {
                'action'        : 'login',
                'email'         : $f.find('input[name="email_address"]').val(),
                'user_password' : $f.find('input[name="user_password"]').val(),
                'security'      : $f.find('input[name="security"]').val()
            };
     
            handleAjaxForms( $f, formData, redirectURL );

        });

        // Ajax User registration form
        $doc.on('submit', '.form-new-members', function( e ) {

            e.preventDefault();

            var $f = $(this),
                redirectURL = $f.find('input[name="redirect-url"]').val();

            var phoneNumber = $( '#phone' ).intlTelInput( 'getNumber' );

            var formData = {
                'action'            : 'register_user',
                'first_name'        : $f.find('input[name="first_name"]').val(),
                'last_name'         : $f.find('input[name="last_name"]').val(),
                'email_address'     : $f.find('input[name="email_address"]').val(),
                'phone_number'      : phoneNumber,
                'user_password'     : $f.find('input[name="user_password"]').val(),

                // Verification
                'security'          : $f.find('input[name="security"]').val()
            };

            handleAjaxForms( $f, formData, redirectURL );

        });
    
        if ($(window).width() > 1100) {
            
            $('li.date').hover(function() {
                $( '.blue1' ).fadeIn();
                $( '.blue2' ).fadeIn();
              }, function() {
                $( '.blue1').fadeOut();
                $( '.blue2').fadeOut();
            });

            $('li.mail').hover(function() {
                $( '.green' ).fadeIn();
              }, function() {
                $( '.green').fadeOut();
            });

            $('li.social').hover(function() {
                $( '.orange-dot' ).fadeIn();
              }, function() {
                $( '.orange-dot').fadeOut();
            });

            $('li.data').hover(function() {
                $( '.purple' ).fadeIn();
              }, function() {
                $( '.purple').fadeOut();
            });
         }

    });

    $win.on('scroll', function() {
        showImages('.outreach-marketing-image-popup');
    });

    function handleAjaxForms( $form, args, redirectURL ) {

        var $infoHolder = $form.find('.callout');

        $.ajax({
            url: ajaxUrl,
            type: $form.attr( 'method' ),
            dataType: 'json',
            data: args,
            beforeSend: function(jqXHR, settings) {
                $( '.box-resized' ).remove();
                $infoHolder.hide();
                $infoHolder.html('');
                $form.find( '.overlay' ).show();

                $infoHolder.removeClass( 'alert' );
            },
            success: function(data) {
                
                // reload on success
                if ( typeof(data.status) !== 'undefined' ) {
                    
                    var status = data.status;

                    var text = '';

                    for ( var x in data.message ) {
                        text += '<p>' + data.message[x] + '</p>';
                    }

                    if ( typeof( $infoHolder ) !== 'undefined' ) {
                        
                        $infoHolder
                            .html( text )
                            .addClass( status )
                                .fadeIn( 400 );

                    }

                    if ( typeof(data.redirect_url) !== 'undefined' ) {
                        
                        $infoHolder.addClass( 'success' );
                        $infoHolder.fadeIn( 400 );
                        
                        var remoteURL = data.redirect_url;
                        setTimeout(function() {
                            window.location.href = remoteURL;
                        }, 300);

                    } else {


                        $('html, body').animate({
                            scrollTop: $form.offset().top - $( '#main-header' ).outerHeight() - 30
                        }, 1000);

                    }

                    $form.find( '.overlay' ).hide();
                }

            },
            error: function(jqXHR, textStatus, errorThrown) { 
                $infoHolder
                    .html('<p>There was an unexpected error</p>')
                    .fadeIn( 400 );
            }
        });

    }

    // Animate Popup
    function showImages(el) {
        var windowHeight = jQuery( window ).height();
        $(el).each(function(){
            var thisPos = $(this).offset().top;

            var topOfWindow = $(window).scrollTop();
            if (topOfWindow + windowHeight - 800 > thisPos ) {
                $(this).addClass("fade-in");
            }
        });
    }

})(jQuery, window, document);