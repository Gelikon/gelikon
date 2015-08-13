/** положение скрола от верха страницы нужное для открытия мини-шапки */
var miniHeaderOpenHeight = 350;
/**автоматическая прокрутка промо-слайдера (да - true, нет - false) */
var autoPlayPromoSlider = true;
/** скорость переключения слайдов, промо-слайдера (миллисекунды) */
var promoSliderTimer = 4000;
/** автомачиская прокрутка слайдера новостей (да - true, нет - false) */
var autoPlayNewsSlider = false;
/** скорость переключения слайдов, слайдера новостей (миллисекунды) */
var newsSliderTimer = 4000;


$(document).ready(function () {
    /**
     * @param {?} callbacks
     * @return {undefined}
     */
    function initialize(callbacks) {
        callbacks.each(function () {
            /*var $e = $(this);
            var poster = $e.find(".lang-panel");
            var rule = $e.find(".gk-menu-lang");
            var button = rule.find(".btn-lang");*/
            var lang = $('.btn-lang.sel').attr('rel');

            $('.gk-mega-footer .lang-panel-'+lang).css('display','block');
            $('.gk-main-menu .lang-panel-'+lang).css('display','block');

            $(".btn-lang").on("click", function () {
                lang = $(this).attr('rel');
                $('.gk-mega-footer .btn-lang.sel, .gk-main-menu .btn-lang.sel, .gk-lang btn-lang.sel').removeClass('sel');
                $('.gk-mega-footer .btn-lang-'+lang+', .gk-main-menu .btn-lang-'+lang+', .gk-lang btn-lang-'+lang).addClass('sel');
                /*var tabIndex = $(this).index();
                button.removeClass("sel");
                $(this).addClass("sel");*/

                if (lang == 'ru') {
                    $('.btn-lang-ru').html('на русском');
                    $('.btn-lang-de').html('на немецком')
                } else {
                    $('.btn-lang-ru').html('auf russisch');
                    $('.btn-lang-de').html('deutsch')
                }

                
                $('.gk-mega-footer .lang-panel').css('display','none');
                $('.gk-mega-footer .lang-panel-'+lang).css('display','block');
                $('.gk-main-menu .lang-panel').css('display','none');
                $('.gk-main-menu .lang-panel-'+lang).css('display','block');
            });
        });
    }

    /**
     * @param {?} stream
     * @param {boolean} allBindingsAccessor
     * @param {number} frequency
     * @return {undefined}
     */
    function init(stream, allBindingsAccessor, frequency) {
        stream.each(function () {
            /**
             * @return {undefined}
             */
            function handler() {
                if (activeTab == aLength - 1) {
                    /** @type {number} */
                    activeTab = 0;
                } else {
                    activeTab++;
                }
                a.fadeOut().eq(activeTab).fadeIn();
                button.removeClass("sel").eq(activeTab).addClass("sel");
            }

            /**
             * @return {undefined}
             */
            function init() {
                if (activeTab == 0) {
                    /** @type {number} */
                    activeTab = aLength - 1;
                } else {
                    activeTab--;
                }
                a.fadeOut().eq(activeTab).fadeIn();
                button.removeClass("sel").eq(activeTab).addClass("sel");
            }

            var $e = $(this);
            var a = $e.find(".slide");
            var aLength = a.length;
            var component = $e.find(".arrow-left");
            var cancel = $e.find(".arrow-right");
            var li = $e.find(".gk-dots");
            /** @type {number} */
            var activeTab = 0;
            a.hide().eq(0).show();
            /** @type {number} */
            var i = 0;
            for (; i < aLength; i++) {
                li.prepend('<li><a href="#"></a></li>');
            }
            var button = li.find("a");
            button.eq(0).addClass("sel");
            if (allBindingsAccessor) {
                /** @type {number} */
                var id = setInterval(handler, frequency)
            }
            cancel.on("click", function () {
                handler();
                clearInterval(id);
                return false;
            });
            component.on("click", function () {
                init();
                clearInterval(id);
                return false;
            });
            button.on("click", function () {
                button.removeClass("sel");
                clearInterval(id);
                var index = $(this).parent().index();
                activeTab = index;
                a.fadeOut().eq(index).fadeIn();
                $(this).addClass("sel");
                return false;
            });
        });
    }

    $(function () {
        /** @type {Array} */
        var availableTags = ["ActionScript", "AppleScript", "Asp", "BASIC", "C", "C++", "Clojure", "COBOL", "ColdFusion", "Erlang", "Fortran", "Groovy", "Haskell", "Java", "JavaScript", "Lisp", "Perl", "PHP", "Python", "Ruby", "Scala", "Scheme", "\u0420\u043e\u043c\u0430", "1234"];
        var div = $(".gk-panel-header").find(".gk-search-form").find(".hide-panel");
        var appendTo = $(".gk-panel-mini-header");
        var detached = $(".gk-panel-footer").find(".gk-search-form").find(".hide-panel");
        $(".search-input").autocomplete({
            source: availableTags,
            minLength: 2,
            appendTo: div
        });
        $(".search-input2").autocomplete({
            source: availableTags,
            minLength: 2,
            appendTo: appendTo
        });
        $(".search-input3").autocomplete({
            source: availableTags,
            minLength: 1,
            appendTo: detached
        });
    });
    /** @type {string} */
    var later = "";
    later += '<div class="gk-keyboard">';
    later += '    <ul class="row">';
    later += '        <li class="key alt" data-key="192">\u0451</li>';
    later += '        <li class="key alt" data-key="49" data-alt="!" data-main="1">1</li>';
    later += '        <li class="key alt" data-key="50" data-alt=\'"\' data-main="2">2</li>';
    later += '        <li class="key alt" data-key="51" data-alt="\u2116" data-main="3">3</li>';
    later += '        <li class="key alt" data-key="52" data-alt=";" data-main="4">4</li>';
    later += '        <li class="key alt" data-key="53" data-alt="%" data-main="5">5</li>';
    later += '        <li class="key alt" data-key="54" data-alt=":" data-main="6">6</li>';
    later += '        <li class="key alt" data-key="55" data-alt="?" data-main="7">7</li>';
    later += '        <li class="key alt" data-key="56" data-alt="*" data-main="8">8</li>';
    later += '        <li class="key alt" data-key="57" data-alt="(" data-main="9">9</li>';
    later += '        <li class="key alt" data-key="48" data-alt=")" data-main="0">0</li>';
    later += '        <li class="key alt" data-key="189" data-alt="_" data-main="-">-</li>';
    later += '        <li class="key alt" data-key="187" data-alt="+" data-main="=">=</li>';
    later += '        <li class="delete" data-key="8">delete</li>';
    later += "    </ul>";
    later += '    <ul class="row">';
    later += '        <li class="capslock">Caps lock</li>';
    later += '        <li class="key" data-key="81">\u0439</li>';
    later += '        <li class="key" data-key="87">\u0446</li>';
    later += '        <li class="key" data-key="69">\u0443</li>';
    later += '        <li class="key" data-key="82">\u043a</li>';
    later += '        <li class="key" data-key="84">\u0435</li>';
    later += '        <li class="key" data-key="89">\u043d</li>';
    later += '        <li class="key" data-key="85">\u0433</li>';
    later += '        <li class="key" data-key="73">\u0448</li>';
    later += '        <li class="key" data-key="79">\u0449</li>';
    later += '        <li class="key" data-key="80">\u0437</li>';
    later += '        <li class="key" data-key="219">\u0445</li>';
    later += '        <li class="key" data-key="221">\u044a</li>';
    later += "    </ul>";
    later += '    <ul class="row">';
    later += '        <li class="key" data-key="65">\u0444</li>';
    later += '        <li class="key" data-key="83">\u044b</li>';
    later += '        <li class="key" data-key="68">\u0432</li>';
    later += '        <li class="key" data-key="70">\u0430</li>';
    later += '        <li class="key" data-key="71">\u043f</li>';
    later += '        <li class="key" data-key="72">\u0440</li>';
    later += '        <li class="key" data-key="74">\u043e</li>';
    later += '        <li class="key" data-key="75">\u043b</li>';
    later += '        <li class="key" data-key="76">\u0434</li>';
    later += '        <li class="key" data-key="186">\u0436</li>';
    later += '        <li class="key" data-key="222">\u044d</li>';
    later += '        <li class="key alt" data-alt="/" data-main="\'/\'">/</li>';
    later += '        <li class="enter">Enter</li>';
    later += "    </ul>";
    later += '    <ul class="row">';
    later += '        <li class="key">\u044f</li>';
    later += '        <li class="key">\u0447</li>';
    later += '        <li class="key">\u0441</li>';
    later += '        <li class="key">\u043c</li>';
    later += '        <li class="key">\u0438</li>';
    later += '        <li class="key">\u0442</li>';
    later += '        <li class="key">\u044c</li>';
    later += '        <li class="key">\u0431</li>';
    later += '        <li class="key">\u044e</li>';
    later += '        <li class="key alt" data-alt="," data-main=".">.</li>';
    later += "    </ul>";
    later += '    <ul class="row">';
    later += '        <li class="space" data-key="32">space</li>';
    later += "    </ul>";
    later += "</div>";
    $("select, input").styler();
    /** @type {number} */
    var h = 0;
    if ($(document).height() < $(window).height()) {
        h = $(window).height();
    } else {
        h = $(document).height();
    }
    $("body").prepend('<div class="gk-overlay"></div>');
    var slide = $(".gk-overlay");
    slide.height(h);

    $('#SubmitLogin').click(function () {
       $('.create_hidden').remove();
       return true;
    });
    $('#SubmitCreate').click(function () {
       $('.login_hidden').remove();
       return true;
    });
    
    $(".gk-search-form").each(function () {
        /**
         * @return {undefined}
         */
        function toggle() {
            slide.hide();
            ul.slideUp(speed);
            inputsVariables.val("");
            $(".gk-keyboard").remove();
        }

        var set = $(this);
        var all = $(".btn-open", set);
        var ul = $(".hide-panel", set);
        var inputsVariables = $(".gk-search-form").find("input[type=text]");
        /** @type {number} */
        var speed = 300;
        all.on("click", function () {
            inputsVariables.val("");
            $(".gk-keyboard").remove();
            $(".gk-search-form").find(".hide-panel").hide();
            ul.slideDown(speed);
            slide.show();
            return false;
        });
        slide.on("click", function () {
            toggle();
        });
        $(document).keydown(function (event) {
            if (event.which == 27) {
                toggle();
            }
        });
    });
    $(".gk-user-authentication").each(function () {
        /**
         * @return {undefined}
         */
        function hideProposal() {
            slide.hide();
            target.slideUp();
        }

        var elem = $(this);
        var select = $(".btn-open", elem);
        var target = $(".hide-panel", elem);
        var markup = $(".icon-delete", elem);
        select.on("click", function () {
            target.slideDown();
            slide.show();
        });
        slide.on("click", function () {
            hideProposal();
        });
        markup.on("click", function () {
            hideProposal();
        });
        $(document).keydown(function (event) {
            if (event.which == 27) {
                hideProposal();
            }
        });
    });
    (function () {
        $(".gk-main-menu").each(function () {
            var set = $(this);
            var all = $("li", set);

            all.hover(function () {
                var $slide = $(".hide-panel", $(this));
                var section = $slide.find(".line");
                clearTimeout($.data(this, "timer"));
                $slide.stop(true, true).slideDown(200);
                var otherElementRect = $(this).position();
                section.css({
                    "left": otherElementRect.left + 100
                }).width($(this).width() + 25);
            }, function () {
                var mod = $(".hide-panel", $(this));
                $.data(this, "timer", setTimeout($.proxy(function () {
                    mod.stop(true, true).slideUp(200);
                }, this), 100));
            });
        });
    })();
    (function () {
        $(".sub-menu-l1").each(function () {
            var ul = $(this);
            var items = ul.find("li");
            var rule = items.has(".sub-menu-l2");
            rule.find("a").after('<span class="icon icon-plus"></span>');
            var element = $(".icon", ul);
            element.on("click", function () {
                $(this).parent().find(".sub-menu-l2").slideToggle(300);
                $(this).toggleClass("icon-minus");
            });
        });
    })();
    initialize($(".gk-main-menu"));
    //initialize($(".gk-mega-footer"));
    (function () {
        var $e = $(".gk-panel-header");
        var ul = $(".gk-panel-mini-header");
        $(window).scroll(function () {
            if ($(this).scrollTop() > miniHeaderOpenHeight) {
                ul.slideDown();
                $e.find(".hide-panel").hide();
            } else {
                $(".gk-main-menu .hide-panel").hide(1);
                ul.slideUp();
                ul.find(".hide-panel").hide();
                slide.hide();
            }
        });
    })();
    init($(".gk-promo-slider"), autoPlayPromoSlider, promoSliderTimer);
    init($(".gk-news-slider"), autoPlayNewsSlider, newsSliderTimer);
    (function () {
        $(".gk-catalog").find(".gk-item").each(function () {

            var self = $(this);

            self.find('.image').hover(function () {
                $(this).parent().addClass("gk-item-sel");
            });

            self.hover(function () {
            }, function () {
                $(this).removeClass("gk-item-sel");
            })


        });
    })();
    (function () {
        var $e = $(".gk-counter");
        var component = $e.find(".more");
        var cancel = $e.find(".loss");
        component.on("click", function () {
            var context = $(this).parent();
            /** @type {number} */
            var compare = $(".field", context).val() * 1;
            $(".field", context).val(compare + 1);
            return false;
        });
        cancel.on("click", function () {
            var context = $(this).parent();
            /** @type {number} */
            var index = $(".field", context).val() * 1;
            $(".field", context).val(index - 1);
            if (index < 1) {
                $(".field", context).val(0);
            }
            return false;
        });
    })();
    (function () {
        $(".gk-gallery").each(function () {
            var $e = $(this);
            var that = $e.find(".slide");
            var $img = $e.find(".thumb-wrap");
            var component = $e.find(".arrow-left");
            var tabs = $e.find(".arrow-right");
            /** @type {number} */
            var a1 = 0;
            component.addClass("l-disable");
            that.hide().eq(0).show();
            var button = $(".thumb");
            var b4 = button.width() + 15;
            $img.width(b4 * button.length - 15);
            button.eq(0).addClass("sel");

            function next() {
                component.removeClass("l-disable");
                if (a1 == button.length - 5) {
                    /** @type {number} */
                    a1 = button.length - 5;
                } else {
                    a1++;
                }
                if (a1 == button.length - 5) {
                    $(this).addClass("r-disable");
                }
                $img.animate({
                    left: -(a1 * b4)
                });
            }

            that.on('click', function () {
                var d = $(this).index();

                component.removeClass("l-disable");


                if (a1 == button.length - 5) {
                    /** @type {number} */
                    a1 = button.length - 5;
                } else {
                    a1++;
                }
                that.fadeOut().eq(d).next().fadeIn();

                if (d == button.length - 1) {
                    that.fadeOut().eq(0).fadeIn();
                    a1 = 0;
                    d = -1
                }

                $img.animate({
                    left: -(a1 * b4)
                });

                if (d == button.length - 5) {
                    tabs.addClass("r-disable");
                    component.removeClass("l-disable");
                }

                if (d == -1) {
                    component.addClass("l-disable");
                    tabs.removeClass("r-disable");
                }
                button.removeClass("sel").eq(d + 1).addClass("sel")
            });

            tabs.on("click", function () {
                next();
                return false
            });
            component.on("click", function () {
                tabs.removeClass("r-disable");
                if (a1 == 0) {
                    /** @type {number} */
                    a1 = 0;
                } else {
                    a1--;
                }
                if (a1 == 0) {
                    $(this).addClass("l-disable");
                }
                $img.animate({
                    left: -(a1 * b4)
                });
                return false
            });
            button.on("click", function () {
                var activeTab = $(this).index();
                that.fadeOut().eq(activeTab).fadeIn();
                button.removeClass("sel");
                $(this).addClass("sel");
            });


        });
    })();
    (function () {
        $('#processOrder').on("click", function () {
            var id,href = '';
            $(".jq-radio").each(function () {
                if($(this).hasClass('checked')){
                    id = $(this).attr('id').replace('-styler','');
                    href = $('#' + id).val();
                }
            });
            if(!href)
                alert('Выберите способ лоставки');
            else if(href == 'paypal')
                $('#paypal_payment_form').submit();
            else    
                location.href = href;
        });
        
        
        $(".gk-blk-open-radio").each(function () {
            $(".jq-radio").on("click", function () {
                $(".jq-radio").each(function () {
                    $(this).removeClass('checked');
                });
                $(this).addClass('checked');
                $(".jq-radio").closest(".wrap").removeClass("sel");
                var relatedTarget = $(this),
                    delSum = relatedTarget.closest(".wrap").find(".price span").html(),
                    totalSum = $('#value').val();

                if(delSum !== undefined){
                    delSum = delSum.split(' ');
                    delSum = delSum[0].replace(',','.');
                    delSum = parseFloat(delSum);
                }

                if(totalSum !== undefined){
                    totalSum = totalSum.split(' ');
                    totalSum = totalSum[0].replace(',','.');
                    totalSum = parseFloat(totalSum); 
                }

                relatedTarget.closest(".wrap").addClass("sel");

                if (isNaN(delSum)) {
                    $(".delivery-price span").html('бесплатно');
                    delSum = 0;
                }else{
                    $(".delivery-price span").html(delSum + ' ' + 'EUR');
                }
                summ = totalSum+delSum;
                if(!isNaN(summ.toFixed(2)))
                    $(".total-price span").html(summ.toFixed(2) + ' ' + 'EUR');
            });
        });
    })();

    (function () {
        $(".gk-blk-delivery-address").each(function () {
            var $e = $(this);
            var group_content = $e.find(".hide-panel");
            $e.find(".jq-checkbox").on("click", function () {
                if (!$(this).hasClass("checked")) {
                    group_content.slideUp();
                } else {
                    group_content.slideDown();
                }
            });
        });
    })();
    $(".gk-panel-msg").find(".msg").each(function () {
        var $e = $(this);
        var component = $e.find(".icon-delete");
        component.on("click", function () {
            $(this).closest(".msg").remove();
        });
    });
    var button = $(".gk-pay-system").find(".item");
    button.on("click", function () {
        button.removeClass("sel");
        $(this).addClass("sel");
    });
    (function () {
        $(".gk-search-form").each(function () {
            var $e = $(this);
            var component = $e.find(".icon-keyboard");
            component.on("click", function () {
                $(".gk-keyboard").remove();
                $(this).parent().prepend(later);
                var collection = $(".gk-keyboard");
                collection.each(function () {
                    var t = $(this);
                    var component = t.find(".key");
                    var cancel = t.find(".capslock");
                    var ok = t.find(".space");
                    var markup = t.find(".delete");
                    var cursor = t.find(".alt");
                    /** @type {boolean} */
                    var caps = false;
                    var input = t.parent().find("input[type=text]");


                    cancel.on("click", function () {
                        t.toggleClass("caps");
                        $(this).toggleClass("sel");
                        if ($(this).hasClass("sel")) {
                            /** @type {boolean} */
                            caps = true;
                        } else {
                            /** @type {boolean} */
                            caps = false;
                        }
                        cursor.each(function () {
                            var element = $(this);
                            var clone = element.data("main");
                            var alt = element.data("alt");
                            if (caps == true) {
                                element.html(alt);
                            } else {
                                element.html(clone);
                            }
                        });
                        return caps;
                    });
                    ok.on("click", function () {
                        input.focus().val(input.val() + " ").trigger(jQuery.Event("keydown", {
                            keyCode: 32
                        }));
                    });
                    markup.on("click", function () {
                        var headBuffer = input.val();
                        input.val(headBuffer.substr(0, headBuffer.length - 1)).focus();
                    });
                    component.on("click", function () {
                        var letter = $(this).data("key");
                        var semicolonEvent = jQuery.Event("keydown", {
                            keyCode: letter
                        });
                        var part = $(this).html();
                        if (caps == true) {
                            input.focus().val(input.val() + part.toUpperCase()).trigger(semicolonEvent);
                        } else {
                            input.focus().val(input.val() + part.toLowerCase()).trigger(semicolonEvent);
                        }
                    });
                });
            });
        });
    })();


    //(function () {
    //    function getRandomInt(min, max) {
    //        return Math.floor(Math.random() * (max - min + 1)) + min;
    //    }
    //    var num = getRandomInt(1,3);
    //    var link = 'css/main'+num+'.css';
    //    $("head script").eq(0).before($("<link rel='stylesheet' href='"+link+"' type='text/css' media='screen' />"));
    //})();


});
