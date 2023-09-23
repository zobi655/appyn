'use strict';

var brpx = document.querySelector('.box-rating');
if( brpx !== null ) {
    var l = localStorage.getItem('px_rating-'+brpx.dataset.postId);
    if( l ) {
        brpx.classList.add('voted');
    }
}

if (text_ === true) {
    var div = document.getElementsByClassName('entry-limit');
    if (div.length > 0) {
        div = div[0];
        var height_content = div.offsetHeight;
        if (height_content > 160) {
            document.querySelectorAll('.app-s #descripcion .entry')[0].outerHTML += '<span class="readmore readdescripcion" style="cursor:pointer;">' + text_leer_mas + '</span>';
            document.querySelectorAll('.app-s #descripcion .entry')[0].style.height = "160px";
            document.querySelectorAll('.app-s #descripcion .entry')[0].classList.add("limit");
        }
    }
}

function support_format_webp() {
    var elem = document.createElement('canvas');

    if (!!(elem.getContext && elem.getContext('2d'))) {
        return elem.toDataURL('image/webp').indexOf('data:image/webp') == 0;
    } else {
        return false;
    }
}

(function($) {

    function pxloadimage(data) {
        var lazyImages = [].slice.call(document.querySelectorAll(".lazyload"));
        
        if ("IntersectionObserver" in window) {
            var lazyImageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                var lazyImage = entry.target;
                lazyImage.src = lazyImage.dataset.src;
                lazyImage.classList.add("imgload");
                lazyImage.parentElement.classList.add("bi_ll_load");

                if( lazyImage.dataset.bgsrc )
                    lazyImage.style.backgroundImage = "url('"+lazyImage.dataset.bgsrc+"')";


                lazyImageObserver.unobserve(lazyImage);
                }
            });
            });
        
            lazyImages.forEach(function(lazyImage) {
            lazyImageObserver.observe(lazyImage);
            });
        } else {

            lazyImages.forEach(function(lazyImage) {
            lazyImage.src = lazyImage.dataset.src;
            lazyImage.classList.add("imgload");
            lazyImage.parentElement.classList.add("bi_ll_load");
                
            if( lazyImage.dataset.bgsrc )
                lazyImage.style.backgroundImage = "url('"+lazyImage.dataset.bgsrc+"')";
                
            });
        }
    }
    pxloadimage();
    $('#menu-mobile').show();
    var width_body = $("body").width();

    $('.menu-open').on('click', function() {
        $('body').toggleClass('toggle-nav');
        if ($(this).find('i').hasClass('fa-bars')) {
            $(this).find('i').attr('class', 'fa fa-times');
        } else {
            $(this).find('i').attr('class', 'fa fa-bars');
        }
    });
    $('#menu-mobile .menu-item-has-children > a').after('<i class="fa fa-chevron-down"></i>');
    $(document).on('click', '#menu-mobile .menu-item-has-children > a, #menu-mobile .menu-item-has-children > i', function(e) {
        e.preventDefault();
        $(this).parent().find('.sub-menu:eq(0)').toggle();
        if ($(this).parent().find('i:eq(0)').hasClass('fa-chevron-up')) {
            $(this).parent().find('i:eq(0)').attr('class', 'fa fa-chevron-down');
        } else {
            $(this).parent().find('i:eq(0)').attr('class', 'fa fa-chevron-up');
        }
    });

    if (text_ === true) {
        var clicks = 0;
        $(document).on('click', '.app-s .readdescripcion', function(e) {
            e.preventDefault();
            var height_content = $('.entry-limit').outerHeight();
            var height_content_ = Math.ceil($(".app-s #descripcion .entry").height());
            if (clicks == 0 && height_content_ == 160) {

                var letss = $(window).scrollTop();

                $(".app-s #descripcion .entry").css({
                    'height': height_content
                }).removeClass('limit');

                $('html, body').animate({
                    scrollTop: letss
                }, 0);

                $(this).text(text_leer_menos);
                clicks = 1;
            } else {

                var topdescripcion = $('#descripcion').offset();
                $('html, body').animate({
                    scrollTop: (topdescripcion.top - 70)
                }, 0);
                $(".app-s #descripcion .entry").css({
                    'height': '160px'
                }).addClass('limit');
                $(this).text(text_leer_mas);
                clicks = 0;
                var cmda = 2625;
            }
        });
    }

    $(document).on('mouseover', '.box-rating:not(.voted):not(.movil) .ratings-click .rating-click', function() {
        $(this).parent().parent().find(".stars").addClass('hover');
        var number = $(this).data('count');
        for (var i = 1; i <= number; i++) {
            $(".ratings-click .rating-click.r" + i).addClass('active');
        }
    });
    $(document).on('mouseout', '.box-rating:not(.voted):not(.movil) .ratings-click .rating-click', function() {
        $(this).parent().parent().find(".stars").removeClass('hover');
        for (var i = 1; i <= 5; i++) {
            $(".ratings-click .rating-click.r" + i).removeClass('active');
        }
    });

    $(document).on('click', '.box-rating:not(.voted):not(.movil) .ratings-click .rating-click, .ratingBoxMovil .box-rating button', function() {
        var number = $(this).data('count');
        $(".box-rating:not(.voted)").append('<div class="rating-loading"></div>');
		var p_id = $('.box-rating:not(.voted)').data('post-id');
        var request = $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'post_rating',
                post_id: p_id,
                rating_count: number,
            }
        });

        request.done(function(response, textStatus, jqXHR) {
            var datos = $.parseJSON(response);
            var width = datos['average'] * 10 * 2;
            $(".box-rating:not(.voted) .rating-average b").text(datos['average']);
            $(".box-rating:not(.voted) .rating .stars").css('width', width + '%');
            $(".box-rating:not(.voted) .rating-text span").text(datos['users']);
            $(".box-rating").addClass('voted');
            $(".rating-loading").remove();
            $('.ratingBoxMovil').remove();
			localStorage.setItem('px_rating-'+p_id, number);
        });
        request.fail(function(jqXHR, textStatus, errorThrown) {
            console.error(
                "The following error occurred: " +
                textStatus, errorThrown
            );
        });
    });


    $(document).on('click', '.app-s .box-rating.movil:not(.voted)', function() {
        var content = $(this).get(0).outerHTML;
        $('.wrapper-page').after('<div class="ratingBoxMovil">' + content + '</div>');
    });
    $(document).on('click', '.ratingBoxMovil .box-rating .ratings-click .rating-click', function() {
        $('.ratingBoxMovil .box-rating button').remove();
        $(this).parent().parent().find(".stars").addClass('hover');
        var number = $(this).data('count');
        $(".ratingBoxMovil .box-rating .ratings-click .rating-click").removeClass('active');
        for (var i = 1; i <= number; i++) {
            $(".ratingBoxMovil .box-rating .ratings-click .rating-click.r" + i).addClass('active');
        }
        var count = $(this).data('count');
        $('.ratingBoxMovil .box-rating .rating-text').after('<button data-count="' + count + '">' + text_votar + '</button>');
    });
    $(document).on('click', '.ratingBoxMovil', function(e) {
        if ($(e.target).attr('class') == 'ratingBoxMovil') {
            $('.ratingBoxMovil').remove();
        }
    });

    /*SEARCH*/
    $("#searchBox input[type=text]").on('click', function() {
        var text_input = $(this).val();
        if (text_input.length == 0) {
            $("#searchBox ul").html('');
        }
    });

    function delay(callback, ms) {
        var timer = 0;
        return function() {
            var context = this,
                args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function() {
                callback.apply(context, args);
            }, ms || 0);
        };
    }

    var request;
    $("#searchBox input[type=text]").on('keyup', delay(function() {
        var text_input = $(this).val();
        $("#searchBox ul").show();
        if (text_input.length >= 3) {
            $("#searchBox form").addClass('wait');
            request = $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'ajax_searchbox',
                    searchtext: text_input,
                }
            });
            request.done(function(response, textStatus, jqXHR) {
                var datos = $.parseJSON(response);
                $("#searchBox ul").html(datos);
                $("#searchBox form").removeClass('wait');
                pxloadimage();
            });
            request.fail(function(jqXHR, textStatus, errorThrown) {
                console.error("The following error occurred: " + textStatus, errorThrown);
                $("#searchBox form").removeClass('wait');
            });
        } else {
            $("#searchBox ul").html('');
        }
    }, 500));
    $("body").on('click', function(e) {
        if (e.target.id != 'sbinput') {
            $("#searchBox ul").hide();
            return;
        } else {
            $("#searchBox ul").show();
        }
    });

    $('.botones_sociales li a').on('click', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (!url) {
            return;
        }
        var ancho = $(this).data('width');
        var alto = $(this).data('height');
        var posicion_x = (screen.width / 2) - (ancho / 2);
        var posicion_y = (screen.height / 2) - (alto / 2);
        window.open(url, "social", "width=" + ancho + ",height=" + alto + ",menubar=0,toolbar=0,directories=0,scrollbars=0,resizable=0,left=" + posicion_x + ",top=" + posicion_y + "");
    });

    $(document).on('click', '.downloadAPK', function(e) {
        if ($(this).attr('href').indexOf('#download') !== -1) {
            e.preventDefault();
            var topdownload = $('#download').offset();
            $('html, body').animate({
                scrollTop: (topdownload.top - 100)
            }, 500);
        }
    });

    if ($('.box.imagenes .px-carousel-item').length)
        var countitem = $('.box.imagenes .px-carousel-item').length;

    $(document).on('click', '.box.imagenes .px-carousel-container .px-carousel-item', function() {
        if ($(window) < 768) {
            $('html').addClass('nofixed');
        }
        $('.px-carousel-container').css({
            'overflow': 'hidden'
        });
        var position = $(this).index();
        var title = $('#slideimages').data('title');
        $('.wrapper-page').after('<div class="imageBox" style="display:none"><div class="px-carousel-container"></div></div>');
        var items = $('.box.imagenes .px-carousel-container').get(0).outerHTML;
        $(items).find('.px-carousel-item').each(function(index, element) {
            var number = index;
            var elemento = $(element);
            elemento = elemento.find('img').attr('src', $(elemento).find('img').data('big-src'));
            elemento = elemento.parent().html();
            $('.imageBox .px-carousel-container').append('<div class="item" style="display:none;">' + elemento + '<span>' + title + ' ' + (number + 1) + ' ' + text_de + ' ' + countitem + '</span></div>');
        });
        $('.imageBox .item:eq(' + position + ')').show().addClass('active');
        $('.imageBox').prepend('<span class="close">&times;</span>');
        $('.imageBox').prepend('<span class="bn before"><i class="far fa-chevron-left"></i></span>');
        $('.imageBox').append('<span class="bn next"><i class="far fa-chevron-right"></i></span>');
        if ((position + 1) == countitem) {
            $('.imageBox').find('.bn.next').addClass('disabled');
        } else if ((position + 1) == 1) {
            $('.imageBox').find('.bn.before').addClass('disabled');
        }
        $('.imageBox').fadeIn();
        
        document.querySelector('.imageBox .px-carousel-container').addEventListener("touchstart", startTouch_, {
            passive: true
        });
        document.querySelector('.imageBox .px-carousel-container').addEventListener("touchend", moveTouch_, {
            passive: true
        });

        var initialX;

        function startTouch_(e) {
            initialX = e.touches[0].clientX;
        };

        function moveTouch_(e) {
            var touches = e.changedTouches;

            var currentX = touches[0].clientX;

            var diffX = initialX - currentX;

            if( diffX < 0 ) {
                $('.imageBox .bn.before:not(.disabled)').stop().click();
            } else {
                $('.imageBox .bn.next:not(.disabled)').stop().click();
            }
        }
    });
    $(document).on('click', '.imageBox .bn.next:not(.disabled)', function() {
        imageBox($(this), 'next');
    });
    $(document).on('click', '.imageBox .bn.before:not(.disabled)', function() {
        imageBox($(this), 'before');
    });
    $(document).on('click', '.imageBox .close', function() {
        $('html').removeClass('nofixed');
        $('.imageBox').fadeOut(500, function() {
            $(this).remove();
        });
    });
    $(document).on('click', '.imageBox .px-carousel-container, .imageBox .item', function(e) {

        if ($(e.target).attr('class') == 'item active' || $(e.target).attr('class') == 'px-carousel-container') {
            $('.imageBox .close').click();
        }
    });

    function imageBox($this, b_class) {
        if (b_class == "next") {
            if ($this.parent().find('.item.active').index() + 1 == (countitem - 1)) {
                $this.parent().find('.bn.next').addClass('disabled');
            }
            $this.parent().find('.item.active').fadeOut().removeClass('active').next().fadeIn().addClass('active');
            $this.parent().find('.bn.before').removeClass('disabled');
            $this.parent().find('img').addClass("imgload");
        } else if (b_class == "before") {
            if (($this.parent().find('.item.active').prev().index()) == 0) {
                $this.parent().find('.bn.before').addClass('disabled');
            }
            $this.parent().find('.item.active').fadeOut().removeClass('active').prev().fadeIn().addClass('active');
            $this.parent().find('.bn.next').removeClass('disabled');
        }
    }


    $('.link-report').on('click', function() {
        $('body').toggleClass('fixed');
        $('#box-report').fadeIn();
    });
    $('.close-report').on('click', function() {
        $('body').toggleClass('fixed');
        $('#box-report').fadeOut();
    });
    $(document).on('keyup', function(e) {
        if (e.key == "Escape") {
            $('#box-report').fadeOut();
            $('.imageBox .close').click();
            $('body.toggle-nav').removeClass('toggle-nav');
            $('.menu-open i').attr('class', 'fa fa-bars');
        }
        if (e.key == "ArrowRight") {
            imageBox($('.imageBox .bn.next:not(.disabled)'), 'next');
        }
        if (e.key == "ArrowLeft") {
            imageBox($('.imageBox .bn.before:not(.disabled)'), 'before');
        }
    });
    $(document).on('click', '#box-report', function(e) {
        if ($(e.target).attr('id') == 'box-report') {
            $('#box-report').fadeOut();
        }
    });

    if (typeof recaptcha_site !== 'undefined') {
        $(document).on('click', '.link-report', function() {
            var head = document.getElementsByTagName('head')[0];
            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = 'https://www.google.com/recaptcha/api.js?render=' + recaptcha_site;
            head.appendChild(script);
        });

        $(document).on('submit', '#box-report form', function(e) {
            e.preventDefault();
            if (typeof grecaptcha !== 'undefined') {
                grecaptcha.ready(function() {
                    try {
                        grecaptcha.execute(recaptcha_site, {
                                action: 'recaptcha_reports'
                            })
                            .then(function(token) {
                                $('#box-report form').append('<input type="hidden" name="token" value="' + token + '">');
                                $('#box-report form').append('<input type="hidden" name="action" value="recaptcha_reports">');
                                send_box_report();
                            })
                    } catch (error) {
                        alert(error);
                    }
                });
            } else {
                alert('Error reCaptcha');
            }
        });
    } else {
        $(document).on('submit', '#box-report form', function(e) {
            e.preventDefault();
            send_box_report();
        });
    }

    function send_box_report() {
        var serialized = $('#box-report form').find('input, textarea').serialize();
        $('#box-report .box-content form').after('<div class="loading"></div>');
        $('#box-report .box-content form').remove();
        var request = $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'app_report',
                serialized: serialized,
            }
        });
        request.done(function(response, textStatus, jqXHR) {
            if (response == 1) {
                $('#box-report .loading').after('<p style="text-align:center;">' + text_reporte_gracias + '</p>');
                $('#box-report .loading').remove();
            } else {
                alert("Error");
                location.reload();
            }
        });
        request.fail(function(jqXHR, textStatus, errorThrown) {
            console.error(
                "The following error occurred: " +
                textStatus, errorThrown
            );
        });
    }


    $('.iframeBoxVideo').on('click', function() {
        var id_video = $(this).data('id');
        var iframe = '<iframe width="730" height="360" src="https://www.youtube.com/embed/' + id_video + '" style="border:0; overflow:hidden;" allowfullscreen></iframe>';
        $(this).html(iframe);
    });

    var all_divs = $('.px-carousel-container .px-carousel-item').length;

    var i = 0,
        n = 0;
    let scrollleft_before = 0;

    let margin = 0;
    if (is_rtl()) {
        margin = parseInt($('.px-carousel-item').css('margin-left'));
    } else {
        margin = parseInt($('.px-carousel-item').css('margin-right'));
    }
    var position_items = [0];

    function width_total() {
        var sumando_ancho = 0;
        for (var pi = 0; pi < all_divs; pi++) {
            if (pi + 1 == all_divs) {
                sumando_ancho += parseFloat($('.px-carousel-container .px-carousel-item')[pi].getBoundingClientRect().width.toFixed(2));
            } else {
                sumando_ancho += parseFloat($('.px-carousel-container .px-carousel-item')[pi].getBoundingClientRect().width.toFixed(2)) + margin;
            }
        }
        //Colocar el ancho total		
        var carousel_wrapper_width = $('.px-carousel-wrapper').outerWidth();
        $('.px-carousel-container').css({
            'width': sumando_ancho + "px"
        });
        $('#slideimages .px-carousel-item').css({
            'max-width': carousel_wrapper_width + 'px'
        });

        return sumando_ancho;
    }
    // Min width unicamente en la primera carga:
    $('.px-carousel-container').css({
        'min-width': +$('.px-carousel-wrapper').outerWidth() + 'px'
    });

    function posiciones() {
        // Buscar la posición de cada item
        var position_items = [0];
        var sumando_posicion = 0;
        for (var pi = 0; pi < all_divs; pi++) {
            if (pi > 0) {
                if (is_rtl()) {
                    sumando_posicion += parseFloat($('.px-carousel-container .px-carousel-item')[pi - 1].getBoundingClientRect().width.toFixed(2)) + margin;
                } else {
                    sumando_posicion += parseFloat($('.px-carousel-container .px-carousel-item')[pi - 1].getBoundingClientRect().width.toFixed(2)) + margin;
                }
                position_items.push(sumando_posicion);
            }
        }
        return position_items;
    }

    function getTranslate3d(el) {
        var values = el.style.transform.split(/\w+\(|\);?/);
        if (!values[1] || !values[1].length) {
            return [];
        }
        return parseFloat(values[1].split(/px,\s?/g)[0]);
    }


    if ($('.px-carousel-container').length) {

        document.getElementsByClassName('px-carousel-container')[0].addEventListener("touchstart", startTouch, {
            passive: true
        });
        document.getElementsByClassName('px-carousel-container')[0].addEventListener("touchmove", moveTouch, {
            passive: true
        });

        var initialX = null;
        var initialY = null;

        var rev = -1;

        function startTouch(e) {
            initialX = e.touches[0].clientX;
            initialY = e.touches[0].clientY;
            blu_ = 0;
        };

        var iniciado = 0;
        var trnas = 0;
        var blu_ = 0;

        function moveTouch(e) {
            var sumando_ancho = width_total();
            blu_++;

            if (blu_ == 1) {
                iniciado = e.touches[0].pageX;
                trnas = getTranslate3d(document.getElementsByClassName('px-carousel-container')[0]);
            }
            var currentX = e.touches[0].clientX;
            var currentY = e.touches[0].clientY;

            var diffX = initialX - currentX;
            var diffY = initialY - currentY;

            var translate_new = diffX * rev + trnas;

            if (is_rtl()) {
                if (translate_new > 0 && translate_new < sumando_ancho - $('.px-carousel-wrapper').outerWidth()) {
                    $('.px-carousel-container').css({
                        'transform': 'translate3d(' + translate_new + 'px, 0px, 0px)',
                        'transition': 'none'
                    });
                }
            } else {
                if ((translate_new * rev) > 0 && (translate_new * rev) < (sumando_ancho - $('.px-carousel-wrapper').outerWidth())) {
                    $('.px-carousel-container').css({
                        'transform': 'translate3d(' + translate_new + 'px, 0px, 0px)',
                        'transition': 'none'
                    });
                }
            }
            e.preventDefault();
        };
    }

    $(document).on('click', '.px-carousel-nav .px-next', function() {
        var sumando_ancho = width_total();
        // Si el ancho generado es menor al ancho del contenedor, no hacer nada
        if (sumando_ancho < $('.px-carousel-wrapper').width()) {
            return false;
        }
        $('.px-carousel-container').css({
            'min-width': ''
        }); // reset
        var position_items = posiciones();

        var rev = -1;
        if (is_rtl()) {
            rev = 1;
        }
        var translate = getTranslate3d(document.getElementsByClassName('px-carousel-container')[0]) * rev;

        var now_ = 0;
        $.each(position_items, function(index, value) {
            if (value.toFixed(2) > translate) {
                now_ = value * rev;

                return false;
            }
        });
        var diferencia = ($('.px-carousel-wrapper').outerWidth() - sumando_ancho);

        $('.px-carousel-container').animate({
            example: now_,
        }, {
            step: function(now, fx) {
                switch (fx.prop) {
                    case 'example':
                        $(this).css({
                            'transform': 'translate3d(' + now + 'px, 0px, 0px)',
                            'transition': 'all 0.25s ease 0s'
                        });
                        if (is_rtl()) {
                            if (now >= diferencia * -1) {

                                $('.px-carousel-container').stop();
                                $(this).css({
                                    'transform': 'translate3d(' + ((diferencia * -1)) + 'px, 0px, 0px)',
                                    'transition': 'all 0.25s ease 0s'
                                });
                                break;
                            }
                        } else {
                            if (now <= diferencia) {

                                $('.px-carousel-container').stop();
                                $(this).css({
                                    'transform': 'translate3d(' + (diferencia) + 'px, 0px, 0px)',
                                    'transition': 'all 0.25s ease 0s'
                                });
                                break;
                            }
                        }
                }
            },
            duration: 100,
        });
    });

    $(document).on('click', '.px-carousel-nav .px-prev', function() {
        $('.px-carousel-container').css({
            'min-width': ''
        }); // reset
        var sumando_ancho = width_total();
        var position_items = posiciones().reverse();

        var rev = -1;
        if (is_rtl()) {
            rev = 1;
        }
        var translate = getTranslate3d(document.getElementsByClassName('px-carousel-container')[0]) * rev;

        var now_ = 0;
        $.each(position_items, function(index, value) {
            if (value.toFixed(2) < translate) {
                now_ = value * rev;

                return false;
            }
        });

        var diferencia = ($('.px-carousel-wrapper').outerWidth() - sumando_ancho);

        $('.px-carousel-container').animate({
            example: now_,
        }, {
            step: function(now, fx) {
                switch (fx.prop) {
                    case 'example':
                        $(this).css({
                            'transform': 'translate3d(' + now + 'px, 0px, 0px)',
                            'transition': 'all 0.25s ease 0s'
                        });
                }
            },
            duration: 100,
        });
    });

    function is_rtl() {
        if ($('html[dir=rtl]').length) {
            return true;
        }
    }

    function carousel_px(citem) {
        if (!$('.px-carousel').length) return false;

        let margin;
        if (is_rtl()) {
            margin = parseInt($('.px-carousel-item').css('margin-left'));
        } else {
            margin = parseInt($('.px-carousel-item').css('margin-right'));
        }


        let cd = (($('.px-carousel-wrapper').width()) / citem) - margin + (margin / citem);
        $('#slidehome .px-carousel-item').width(cd.toFixed(2));

        let width_slide = $('.px-carousel-container');
        let scrollleft_slide = Math.round($('.px-carousel-container').scrollLeft());

        $('.px-carousel-container').scrollLeft(scrollleft_slide);
    }
    let detectViewPort = function() {
        $('#slideimages .px-carousel-item').css({
            'max-width': $('.px-carousel-wrapper').outerWidth() + 'px'
        });
        width_total();
        let viewPortWidth = $(window).width();
        if (viewPortWidth <= 550) {
            carousel_px(1);
        } else if (viewPortWidth <= 850) {
            carousel_px(2);
        } else {
            if( $(document).find('body').hasClass('sidg') ) {
                if( $(document).find('body').hasClass('full-width') ) {
                    carousel_px(3);
                } else {
                    carousel_px(2);
                }
            }
            else {
                if( $(document).find('body').hasClass('full-width') && viewPortWidth >= 1100 ) {
                    carousel_px(5);
                } else {
                    carousel_px(3);
                }
            }
        }

    };
    detectViewPort();
    $(window).resize(function() {
        detectViewPort();
    });

    function func_sdl() {
        if( $('.sdl-bar').length ) {
            var timer = parseInt($('.sdl-bar').data('timer'));
            var ls = 1;
            var interval_timer = setInterval(function() {
                if( ls <= timer ) {
                    $('.sdl-bar').addClass('active');
                    $('.sdl-bar div').css('width', (100/timer * ls)+'%');
                    ls++;
                } else {
                    clearInterval(interval_timer);
                    $('.show_download_links').show();
                    $('.sdl-bar').removeClass('active').hide();
                }
            }, 1000);
        }

        if( $('.spinvt').length ) {
            var timer = parseInt($('.show_download_links').data('timer'));
            var interval_timer = setInterval(function() {
                $('.spinvt').addClass('active');
                if (timer == 0) {
                    $('.show_download_links').show();
                    $('.spinvt').remove();
                    clearInterval(interval_timer);
                }
                $('.snt').text(timer--);
            }, 1000);
        }
    }
    if ($(document).find('.show_download_links').length && typeof noptcon === "undefined")
        func_sdl();

    var c = 0;
    if( typeof noptcon !== "undefined" ) {
        $(window).scroll(function() {
            let bdwn = $('#download').offset();
            if ($(document).scrollTop() + $(window).height() >= bdwn.top) {
                if( c != 0 ) return;
                c++;
                func_sdl();
            }
        });
    }

    $(window).scroll(function() {
        if ($(this).scrollTop() > 500) {
            if( $('#px-bottom-menu').length && $(window).width() <= 640 ) {
                $('#backtotop').css({
                    bottom: "79px"
                });
            } else {
                $('#backtotop').css({
                    bottom: "15px"
                });
            }
        } else {
            $('#backtotop').css({
                bottom: "-100px"
            });
        }
    });
    $(document).on('click', '#backtotop', function() {
        $('html, body').animate({
            scrollTop: 0
        }, 500);
        return false;
    });

    $(document).on('submit', '#recaptcha_download_links', function(e) {
        e.preventDefault();
        var serializedData = $(this).serialize();

        var request = $.ajax({
            url: ajaxurl,
            type: "POST",
            data: serializedData
        });

        request.done(function(response, textStatus, jqXHR) {
            $('#recaptcha_download_links').after(response);
            $('#recaptcha_download_links').remove();
            func_sdl();
        });
        request.fail(function(jqXHR, textStatus, errorThrown) {
            console.error(
                "The following error occurred: " +
                textStatus, errorThrown
            );
        });
    });

    if ($('#recaptcha_download_links').length) {
        let rdl = $('#recaptcha_download_links').offset();

        if ($(document).scrollTop() + $(window).height() >= rdl.top) {
            add_recaptcha_js();
        } else {
            setInterval(() => {
                add_recaptcha_js();
            }, 5000);
        }

        function add_recaptcha_js() {
            var head = document.getElementsByTagName('head')[0];
            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = 'https://www.google.com/recaptcha/api.js';
            head.appendChild(script);
        }
    }

    $(document).on('click', '#button_light_dark', function(){
        if( !$(this).hasClass('active') ) {
            localStorage.setItem('px_light_dark_option', 1);
            if( localStorage.getItem('px_light_dark_option') == 1 ) {
                $('#css-dark-theme').removeAttr('media');
                setCookie('px_light_dark_option', 1, 365);
            }
        } else {
            localStorage.setItem('px_light_dark_option', 0);
            setCookie('px_light_dark_option', 0, 365);
            $('#css-dark-theme').attr('media', 'max-width: 1px');
        }
        $(this).toggleClass('active');  
        $('body').toggleClass('theme-dark');
        if( $('#button_light_dark i').hasClass('fa-moon') ) {
            $('#button_light_dark i').addClass('fa-sun'); 
            $('#button_light_dark i').removeClass('fa-moon'); 
        } else {
            $('#button_light_dark i').addClass('fa-moon'); 
            $('#button_light_dark i').removeClass('fa-sun'); 
        }
    }); 

})(jQuery);