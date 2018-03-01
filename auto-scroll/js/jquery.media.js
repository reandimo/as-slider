// The "Upload" button
$(document).on('click', '.upload_image_button', function() {
    var send_attachment_bkp = wp.media.editor.send.attachment;
    var button = $(this);
    wp.media.editor.send.attachment = function(props, attachment) {
        $(button).parent().prev().attr('src', attachment.url);
        $(button).prev().val(attachment.id);
        wp.media.editor.send.attachment = send_attachment_bkp;
    }
    wp.media.editor.open(button);
    return false;
});

// The "Remove" button (remove the value from input type='hidden')
$(document).on('click', '.remove_image_button', function() {
    var answer = confirm('Desea borrar esta imagen?');
    if (answer == true) {
        var src = $(this).closest('tr').remove();
    }
    return false;
});

//Add row button
$('.add_row_image').click(function() {

    var html= '<tr valign="top"><th scope="row">Imagen #:</th><td><div class="upload"><img src="" height="60px" /><div><input type="text" name="as_images" value="" /><button type="submit" class="upload_image_button button">Cargar</button><button type="submit" class="remove_image_button button">&times;</button></div></div></td></tr>';

    $('#add_row').before(html);


});

        //SAVE BUTTON
        $('#save-as').click(function(){

            var inputImages = '';

            $('input[name*=as_images]').each(function(){

                inputImages+= $(this).val()+'|';

            });

            var images = inputImages.slice(0,-1);

            var as_id = $('input[name=as_id]').val(), as_name = $('input[name=as_name]').val(), as_time_scroll = $('input[name=as_time_scroll]').val();


            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'as_ajax_post' , as_id: as_id, as_name : as_name, as_time_scroll: as_time_scroll, as_images: images }
              }).done(function( msg ) {

                     var res = $.parseJSON(msg);

                     $('#ajax-res').append(res.message);

             });

        });

    //SET CLASS FOR ADMIN PANEL EFFECT
    $('.scrollable').each(function(){

        $(this).find('img:eq(0)').addClass('first');

    });