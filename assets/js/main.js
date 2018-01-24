var barcamp = barcamp || {};
var viewportWidth;

barcamp.viewportWidth = function () { // šířka viewportu
    viewportWidth = Math.max($(window).width(), window.innerWidth);
};

barcamp.openNav = function () {

    $('.btn-mobile-menu-open-container').click(function () {
        $('.header-nav').slideToggle(200);
        $(this).find('.btn-mobile-menu-open').toggleClass('active');
        $(this).find('.item-text').text(function (i, text) {
            return text === "Menu" ? "Zavřít" : "Menu";
        })
    });

};

barcamp.slider = function () {
    $('.hero-slider').slick({
        dots: false,
        arrows: false,
        infinite: true,
        fade: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        adaptiveHeight: true,
        autoplay: true,
        autoplaySpeed: 5000
    });

    $('.slider-main').slick({
        dots: true,
        arrows: false,
        infinite: true,
        fade: false,
        slidesToShow: 3,
        slidesToScroll: 3,
        adaptiveHeight: true,
        autoplay: true,
        autoplaySpeed: 5000,
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            },
            {
                breakpoint: 568,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
    });

};

barcamp.accordion = function () {

    $('.accordion-list .accordion-content').hide();

    $('.accordion-heading').click(function () {
        //$(this).parent().parent().find('li').not($(this).parent()).removeClass('accordion-open').find('.accordion-content').slideUp(200);
        $(this).parent().toggleClass('accordion-open').find('.accordion-content').slideToggle(200);
    });
};

barcamp.smoothScroll = function () {

    $('.scrollto').click(function (e) {

        var id = $(this).attr('href');

        var $id = $(id);
        if ($id.length === 0) {
            return;
        }

        e.preventDefault();

        var pos = $id.offset().top;

        $('body, html').animate({scrollTop: pos}, 1000);
    });
};

barcamp.schedule = function() {
    if($('#schedule').length) {
        $(window).scroll(function() {
            var hT = $('#schedule-scroll-point').offset().top,
                hH = $('#schedule-scroll-point').outerHeight(),
                wH = $(window).height(),
                wS = $(this).scrollTop();
            if (wS > (hT+hH-wH) && !$('#schedule').hasClass('animate')){
                $('#schedule').addClass('animate');
                $('#schedule .schedule').addClass('animate');
            }
        });
    }
};

barcamp.lectures = function() {
    var height = 0;
    var element;
    var scroll_over = 0;

    $('.js-lecture-control').click(function() {

        if($(this).closest('li').hasClass('open')) {
            $(this).parent().parent().parent().find('.show-full, .item-content-full').fadeOut(200, function() {
                $(this).parent().parent().find('.item-content-perex').fadeIn(200);
            });
            $(this).parent().parent().parent().removeClass('open').animate({height:110},200);
        } else {

            $(this).parent().parent().parent().find('.open').each(function() {
                $(this).find('.item-content-full, .show-full').fadeOut(200, function() {
                    $(this).parent().find('.item-content-perex').fadeIn(200);
                });
                $(this).removeClass('open').animate({height:110},200);
            });

            $(this).fadeOut(200, function() {
                $(this).parent().parent().find('.item-content-full, .show-full').fadeIn(200);
            });

            $(this).parent().parent().find('.item-content-full').show();
            height = $(this).parent().parent().find('.item-content-full').height();
            $(this).parent().parent().find('.item-content-full').hide();

            element = $(this).parent().parent();

            if(viewportWidth > 568) {
                scroll_over = 200;
            } else {
                scroll_over = 0;
            }

            $(this).parent().parent().animate( {height: height + 51 }, 100,  function() { setTimeout(function(){ element.addClass('open'); }, 200); });
            setTimeout(function(){ $('body, html').animate({scrollTop: element.offset().top - scroll_over}, 800); }, 500);

        }
    });

};

barcamp.forms = function() {

    if($('#textarea').length) {
        var text_max = 220;
        var text_max2 = 560;

        $('#textarea1_count').html('zbývá '+ text_max + ' znaků');
        $('#textarea2_count').html('zbývá '+ text_max2 + ' znaků');

        $('#textarea').keyup(function() {
            var text_length = $('#textarea').val().length;
            var text_remaining = text_max - text_length;

            $('#textarea1_count').html('zbývá '+ text_remaining + ' znaků');
        });
        $('#textarea2').keyup(function() {
            var text_length = $('#textarea2').val().length;
            var text_remaining = text_max2 - text_length;

            $('#textarea2_count').html('zbývá '+ text_remaining  + ' znaků');
        });
    }

};

barcamp.tabs = function() {
    $('#program').tabs();
};

barcamp.program = function() {
    var val,vals = "";

    $('.js-program-filter input').change(function() {
        vals = "";

        if($(this).val() == '*' && $(this).is(":checked")) {
            $('.js-program-filter input:not(.check-all)').each(function() {
                $(this).prop('checked', false);
            });
        } else
        if($(this).val() != '*') {
            $('.js-program-filter input.check-all').prop('checked', false);
        }

        $('.js-program-filter input:checked').each(function() {
            val = $(this).val();
            vals += "."+val+",";
        });

        if(val == '*' || vals == '') {
            $('.js-program-filter input.check-all').prop('checked', true);
            vals = "";
            $(this).parent().parent().parent().find('.program-item').removeClass('active inactive');
        } else {
            $(this).parent().parent().parent().find('.program-item').removeClass('active').addClass('inactive');
            $(this).parent().parent().parent().find(vals.slice(0,-1)).removeClass('inactive').addClass('active');
        }
    });

    function fixedHeader() {
        if(viewportWidth <= 768) {

            var header = $('.program-header');
            var program = $('.program');

            if(header.length == 0 || program.length == 0) {
                return;
            }

            var topofDiv = header.offset().top;
            var topofParent = program.offset().top;
            var scroll = 0;

            $(window).scroll(function(){
                scroll = $(window).scrollTop() - topofParent;

                if($(window).scrollTop() >= topofDiv){
                    if(!header.hasClass('fixed')) {
                        header.addClass('fixed');
                    }
                    header.css( "top", scroll+"px" );
                }
                else{
                    if(header.hasClass('fixed')) {
                        header.removeClass('fixed');
                    }
                }
            });
        }
    }

    function scrollLeftMobile() {
        if(viewportWidth <= 768) {
            $('.program-container').scrollLeft(100);
        }
    }

    fixedHeader();
    scrollLeftMobile();

    $(window).on("orientationchange", function () {
        fixedHeader();
    });

    $(window).on("resize", function () {
        fixedHeader();
    });

};

// TODO: Remove placeholders
barcamp.disabledLinks = function() {
    $('a.disabled').click(function(e){
        e.preventDefault();
        console.log('Clicked to disabled link');
    });
    $('a[href^="https://example.com"]').click(function(e){
        e.preventDefault();
        console.log('Clicked to placeholder link');
        alert('Omlouváme se, tato funkce ještě není dostupná');
    })
}

barcamp.init = function () {
    barcamp.viewportWidth();
    barcamp.openNav();
    barcamp.slider();
    barcamp.accordion();
    barcamp.smoothScroll();
    barcamp.schedule();
    barcamp.lectures();
    barcamp.forms();
    barcamp.tabs();
    barcamp.program();
    barcamp.disabledLinks();
};

$(document).ready(function () {
    barcamp.init();
    $("body").removeClass("preload");
});

$(window).on("orientationchange", function () {
    barcamp.viewportWidth();
});

$(window).on("resize", function () {
    barcamp.viewportWidth();
});