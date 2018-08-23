jQuery(document).ready(function($) {

    /***** Colour picker *****/

    $('.colorpicker').hide();
    $('.colorpicker').each( function() {
        $(this).farbtastic( $(this).closest('.color-picker').find('.color') );
    });

    $('.color').click(function() {
        $(this).closest('.color-picker').find('.colorpicker').fadeIn();
    });

    $(document).mousedown(function() {
        $('.colorpicker').each(function() {
            var display = $(this).css('display');
            if ( display == 'block' )
                $(this).fadeOut();
        });
    });


    /***** Uploading images *****/

    var file_frame;

    jQuery.fn.uploadMediaFile = function( button, preview_media ) {
        var button_id = button.attr('id');
        var field_id = button_id.replace( '_button', '' );
        var preview_id = button_id.replace( '_button', '_preview' );

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
          file_frame.open();
          return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
          title: jQuery( this ).data( 'uploader_title' ),
          button: {
            text: jQuery( this ).data( 'uploader_button_text' ),
          },
          multiple: false
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
          attachment = file_frame.state().get('selection').first().toJSON();
          jQuery("#"+field_id).val(attachment.id);
          if( preview_media ) {
            jQuery("#"+preview_id).attr('src',attachment.sizes.thumbnail.url);
          }
          file_frame = false;
        });

        // Finally, open the modal
        file_frame.open();
    }

    jQuery('.image_upload_button').click(function() {
        jQuery.fn.uploadMediaFile( jQuery(this), true );
    });

    jQuery('.image_delete_button').click(function() {
        jQuery(this).closest('td').find( '.image_data_field' ).val( '' );
        jQuery(this).closest('td').find( '.image_preview' ).remove();
        return false;
    });

    jQuery(document).ready(function($){
        var isSettingsTab = Boolean($('[name="wpt_lms_base_url"]').length);
        console.log(isSettingsTab);
        if (isSettingsTab) {
            var $span = $('<span></span>');
            $('p').first().append('<br>').append($span);
            setInterval(function(){
                if (ENEXT.amILoggedIn()) {
                    $span.html('Cookie found!');
                } else {
                    $span.html('Cookie not found!');
                }
            }, 100);
        }
    });

});
jQuery(document).ready(function($){
    var isSettingsTab = Boolean($('[name="wpt_lms_base_url"]').length);
    if (isSettingsTab) {
        var $cookie_status = $('<span></span>').css({'font-weight': 'bold'});
        var $fetch_enrollment_status = $('<span></span>').css({'font-weight': 'bold'});
        $('p').first().append('<br>')
          .append($cookie_status)
          .append($fetch_enrollment_status);
        setInterval(function(){
            if (ENEXT.amILoggedIn()) {
                $cookie_status.html('✓ Cookie found!').css({color: 'green'});
            } else {
                $cookie_status.html('✘ Cookie not found! Maybe you are not logged in?').css({color: 'red'});
            }

            ENEXT.getEnrollmentInfo().done(function () {
              $fetch_enrollment_status.html('✓ Enrollment API fetching success!').css({color: 'green'});
            }).fail(function () {
              $fetch_enrollment_status.html('✓ Enrollment API fetching failed!').css({color: 'red'});
            })
        }, 5000);
    }
});