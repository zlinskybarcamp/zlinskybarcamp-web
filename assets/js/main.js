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

barcamp.schedule = function () {
    var $schedule = $('#schedule');
    var $scheduleScrollPoint = $('#schedule-scroll-point');

    if (!$schedule.length) {
        return;
    }

    var config = barcamp.scheduleConfig;
    if (!config) {
        console.warn && console.warn("Unable to config Schedule, no config available");
        return;
    }

    var renderWhenScrollpoint = function () {
        var hT = $scheduleScrollPoint.offset().top,
            hH = $scheduleScrollPoint.outerHeight(),
            wH = $(window).height(),
            wS = $(this).scrollTop();
        if (wS > (hT + hH - wH) && !$schedule.hasClass('animate')) {
            $schedule.addClass('animate');
            $('.schedule', $schedule).addClass('animate');
            return true;
        }
        return false;
    };

    var onScrollHandler = function () {
        if (renderWhenScrollpoint()) {
            $(window).off('scroll', '', onScrollHandler);
        }
    };

    var resetConfig = function () {
        $('li', $schedule).removeClass('item-active item-done');
        $('div.progress', $schedule).each(function () {
            var $slider = $(this);
            setSliderEmpty($slider);
            $slider.removeClass('active');
        });
    };

    var setConfig = function () {
        config.steps.forEach(function (item) {
            var key = item.key;
            var $step = $('li[data-step-name="' + key + '"]', $schedule);
            var $sliderBefore = $('div.progress-before', $step);
            var $sliderAfter = $('div.progress-after', $step);
            if (item.isCurrent) {
                $step.addClass('item-active');
                setSliderFull($sliderBefore);

                $sliderAfter.addClass('active');
                setSliderPercentagle($sliderAfter);
            }
            if (item.isDone) {
                $step.addClass('item-done');
                setSliderFull($sliderBefore);
                setSliderFull($sliderAfter);
            }
            if (item.isNext) {
                $sliderBefore.addClass('active');
                setSliderPercentagle($sliderBefore);
            }
        });
    };

    var getIntervalRatio = function () {
        var start = new Date(config.dates.scheduleBegin).getTime();
        var end = new Date(config.dates.scheduleEnd).getTime();
        var current = new Date().getTime();

        var isInvalid = false;
        [start, end, current].forEach(function (val) {
            if (isNaN(val)) {
                isInvalid = true;
            }
        });
        if (isInvalid || start >= end || start > current) {
            return 0;
        }
        if (current > end) {
            return 1;
        }

        return (current - start) / (end - start);
    };
    var setSliderSizes = function ($slider, size) {
        $slider.css({
            'width': size[0] + '%',
            'height': size[1] + '%',
        });
    };
    var setSliderPercentagle = function ($slider) {
        if ($slider.length === 0) return;

        var percent = getIntervalRatio();
        var limits = parseSliderLimits($slider);

        var sizes = [
            limits.min[0] + (limits.max[0] - limits.min[0]) * percent,
            limits.min[1] + (limits.max[1] - limits.min[1]) * percent,
        ];
        setSliderSizes($slider, sizes);
    };
    var setSliderEmpty = function ($slider) {
        if ($slider.length === 0) return;
        var limits = parseSliderLimits($slider);
        setSliderSizes($slider, limits.empty);
    };
    var setSliderFull = function ($slider) {
        if ($slider.length === 0) return;
        var limits = parseSliderLimits($slider);
        setSliderSizes($slider, limits.full);
    };
    var parseSliderLimits = function ($slider) {
        var seg = $slider.data('visualLimits').split(';').map(parseFloat);
        return {
            'empty': [seg[0], seg[1]],
            'min': [seg[2], seg[3]],
            'max': [seg[4], seg[5]],
            'full': [seg[6], seg[7]],
        };
    };

    //try to render on load
    if (!renderWhenScrollpoint()) {
        //else render it on scroll
        $(window).on('scroll', '', onScrollHandler);
    }


    resetConfig();
    setConfig();

};

barcamp.lectures = function () {
    var height = 0;
    var element;
    var scroll_over = 0;

    $('.js-lecture-control').click(function () {

        if ($(this).closest('li').hasClass('open')) {
            $(this).parent().parent().parent().find('.show-full, .item-content-full').fadeOut(200, function () {
                $(this).parent().parent().find('.item-content-perex').fadeIn(200);
            });
            $(this).parent().parent().parent().removeClass('open').animate({height: 110}, 200);
        } else {

            $(this).parent().parent().parent().find('.open').each(function () {
                $(this).find('.item-content-full, .show-full').fadeOut(200, function () {
                    $(this).parent().find('.item-content-perex').fadeIn(200);
                });
                $(this).removeClass('open').animate({height: 110}, 200);
            });

            $(this).fadeOut(200, function () {
                $(this).parent().parent().find('.item-content-full, .show-full').fadeIn(200);
            });

            $(this).parent().parent().find('.item-content-full').show();
            height = $(this).parent().parent().find('.item-content-full').height();
            $(this).parent().parent().find('.item-content-full').hide();

            element = $(this).parent().parent();

            if (viewportWidth > 568) {
                scroll_over = 200;
            } else {
                scroll_over = 0;
            }

            $(this).parent().parent().animate({height: height + 51}, 100, function () {
                setTimeout(function () {
                    element.addClass('open');
                }, 200);
            });
            setTimeout(function () {
                $('body, html').animate({scrollTop: element.offset().top - scroll_over}, 800);
            }, 500);

        }
    });

};

barcamp.tabs = function () {
    $('#program').tabs();
};

barcamp.program = function () {
    var val, vals = "";

    $('.js-program-filter input').change(function () {
        vals = "";

        if ($(this).val() == '*' && $(this).is(":checked")) {
            $('.js-program-filter input:not(.check-all)').each(function () {
                $(this).prop('checked', false);
            });
        } else if ($(this).val() != '*') {
            $('.js-program-filter input.check-all').prop('checked', false);
        }

        $('.js-program-filter input:checked').each(function () {
            val = $(this).val();
            vals += "." + val + ",";
        });

        if (val == '*' || vals == '') {
            $('.js-program-filter input.check-all').prop('checked', true);
            vals = "";
            $(this).parent().parent().parent().find('.program-item').removeClass('active inactive');
        } else {
            $(this).parent().parent().parent().find('.program-item').removeClass('active').addClass('inactive');
            $(this).parent().parent().parent().find(vals.slice(0, -1)).removeClass('inactive').addClass('active');
        }
    });

    function fixedHeader() {
        if (viewportWidth <= 768) {

            var header = $('.program-header');
            var program = $('.program');

            if (header.length == 0 || program.length == 0) {
                return;
            }

            var topofDiv = header.offset().top;
            var topofParent = program.offset().top;
            var scroll = 0;

            $(window).scroll(function () {
                scroll = $(window).scrollTop() - topofParent;

                if ($(window).scrollTop() >= topofDiv) {
                    if (!header.hasClass('fixed')) {
                        header.addClass('fixed');
                    }
                    header.css("top", scroll + "px");
                }
                else {
                    if (header.hasClass('fixed')) {
                        header.removeClass('fixed');
                    }
                }
            });
        }
    }

    function scrollLeftMobile() {
        if (viewportWidth <= 768) {
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

barcamp.avatarUploader = function () {
    var $button = $('#avatar-upload-button');
    if ($button.length === 0) {
        return;
    }

    var $input = $('#avatar-upload-input');
    var $image = $('#avatar');
    var uploadUrl = $button.attr('href');

    $button.click(function (e) {
        e.preventDefault();
        $input.click();
    });

    $input.change(function () {
        $image.addClass('pulse');
        var file = this.files[0];
        var form = new FormData();
        form.append('file', file);
        upload(form);
    });

    var upload = function (form) {
        fetch(uploadUrl, {
            method: 'POST',
            body: form
        })
        .then(function (response) {
            $image.removeClass('pulse');
            return response.json();
        })
        .then(function (json) {
            var value = 'url(\'' + json.avatarUrl + '\')';
            $image.css('background-image', value);
        })
        .catch(function(error) {
            $image.removeClass('pulse');
            alert('Tento obrázek není možné načíst, zkuste jej prosím zmenšit.');
            console.log(error);
        });
    };
};

barcamp.talkVote = function () {
    var $list = $('.lectures-list');

    if($list.length === 0) {
        return;
    }

    $list.on('click', '.vote-ajax', function(e) {
        e.preventDefault();
        var $button = $(this);
        $button.addClass('disabled');
        var $item = $button.closest('li');
        var url = $button.attr('href');

        $.ajax({
            url: url,
            dataType: 'json'
        }).done(function(json) {
            $button.removeClass('disabled');
            $('.item-count', $item).text(json.votes);
            $('.is-voted,.is-not-voted', $item).toggle();
        }).fail(function(error) {
            alert('Váš hlas se nepovedlo uložit. Omlouváme se. Zkuste to prosím znovu.');
            console.log(error);
        });
    });

};

// TODO: Remove placeholders
barcamp.disabledLinks = function () {
    $('a.disabled').click(function (e) {
        e.preventDefault();
        console.log('Clicked to disabled link');
    });
    $('a[href^="https://example.com"]').click(function (e) {
        e.preventDefault();
        console.log('Clicked to placeholder link');
        alert('Omlouváme se, tato funkce ještě není dostupná');
    })
};

barcamp.netteInit = function () {
    $.nette.init();
};

barcamp.init = function () {
    barcamp.netteInit();
    barcamp.viewportWidth();
    barcamp.openNav();
    barcamp.slider();
    barcamp.accordion();
    barcamp.smoothScroll();
    barcamp.schedule();
    barcamp.lectures();
    barcamp.tabs();
    barcamp.program();
    barcamp.avatarUploader();
    barcamp.talkVote();
    barcamp.disabledLinks();
};

$(document).ready(function () {
    barcamp.init();
    $("body").removeClass("preload").removeClass("no-js");
});

$(window).on("orientationchange", function () {
    barcamp.viewportWidth();
});

$(window).on("resize", function () {
    barcamp.viewportWidth();
});