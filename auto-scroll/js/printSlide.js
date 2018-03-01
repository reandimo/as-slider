
(function($) {

$(window).on( 'load', function banner_animate(){

        var time = $('#as-time').val();

        var w = 0;

        $('.photobanner-scroll').each(function(){

            $(this).find('img:eq(0)').addClass('first-scroll');

        });

        $('.photobanner-scroll img').each(function(){

            if ( $(this).attr('class') !== 'first-scroll') {
                
                //Get the width of each img
                w = w + $(this).width();

                //Clone each img and append to parent div, for animation purposes
                $(this).clone().appendTo( $(this).parent() );

            }

        });

        w = w*(-1);

        console.log(w);

        $.keyframe.define({
            name: 'bannermove',
            '0%': {
                'margin-left': '0px' //Note that 'transform' will be autoprefixed for you
            },
            '100%': {
                'margin-left': w+'px' //Note that 'transform' will be autoprefixed for you
            }
        });

        // move with easing
          $('.photobanner-scroll').playKeyframe({
                name: 'bannermove', // name of the keyframe you want to bind to the selected element
                duration: time, // [optional, default: 0, in ms] how long you want it to last in milliseconds
                timingFunction: 'linear', // [optional, default: ease] specifies the speed curve of the animation
                iterationCount: 'infinite', //[optional, default:1]  how many times you want the animation to repeat
                direction: 'normal', //[optional, default: 'normal']  which direction you want the frames to flow
                fillMode: 'forwards', //[optional, default: 'forward']  how to apply the styles outside the animation time, default value is forwards

          });

    }

);


})( jQuery );