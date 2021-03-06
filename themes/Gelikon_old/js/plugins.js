/* jQuery Form Styler v1.3.8.2 | (c) Dimox | https://github.com/Dimox/jQueryFormStyler */
jQuery(function (d) {
    d.fn.styler = function (p) {
        p = d.extend({idSuffix: "-styler", browseText: "\u041e\u0431\u0437\u043e\u0440...", selectVisibleOptions: 0, singleSelectzIndex: "100", selectSmartPositioning: !0}, p);
        return this.each(function () {
            var a = d(this), q = "", s = "", u = "", t = "";
            void 0 !== a.attr("id") && "" != a.attr("id") && (q = ' id="' + a.attr("id") + p.idSuffix + '"');
            void 0 !== a.attr("class") && "" != a.attr("class") && (s = " " + a.attr("class"));
            void 0 !== a.attr("title") && "" != a.attr("title") && (u = ' title="' + a.attr("title") + '"');
            var v = a.data(),
                e;
            for (e in v)"" != v[e] && (t += " data-" + e + '="' + v[e] + '"');
            q += t;
            a.is(":checkbox") ? a.css({position: "absolute", left: -9999}).each(function () {
                if (1 > a.next("div.jq-checkbox").length) {
                    var b = d("<div" + q + ' class="jq-checkbox' + s + '"' + u + ' style="display: inline-block"><div></div></div>');
                    a.after(b);
                    a.is(":checked") && b.addClass("checked");
                    a.is(":disabled") && b.addClass("disabled");
                    b.click(function () {
                        b.is(".disabled") || (a.is(":checked") ? (a.prop("checked", !1), b.removeClass("checked")) : (a.prop("checked", !0), b.addClass("checked")),
                            a.change());
                        return!1
                    });
                    a.parent("label").add('label[for="' + a.attr("id") + '"]').click(function (a) {
                        b.click();
                        a.preventDefault()
                    });
                    a.change(function () {
                        a.is(":checked") ? b.addClass("checked") : b.removeClass("checked")
                    }).keydown(function (d) {
                        !a.parent("label").length || 13 != d.which && 32 != d.which || b.click()
                    }).focus(function () {
                        b.is(".disabled") || b.addClass("focused")
                    }).blur(function () {
                        b.removeClass("focused")
                    });
                    a.on("refresh", function () {
                        a.is(":checked") ? b.addClass("checked") : b.removeClass("checked");
                        a.is(":disabled") ?
                            b.addClass("disabled") : b.removeClass("disabled")
                    })
                }
            }) : a.is(":radio") ? a.css({position: "absolute", left: -9999}).each(function () {
                if (1 > a.next("div.jq-radio").length) {
                    var b = d("<div" + q + ' class="jq-radio' + s + '"' + u + ' style="display: inline-block"><div></div></div>');
                    a.after(b);
                    a.is(":checked") && b.addClass("checked");
                    a.is(":disabled") && b.addClass("disabled");
                    b.click(function () {
                        b.is(".disabled") || (b.closest("form").find('input[name="' + a.attr("name") + '"]').prop("checked", !1).next().removeClass("checked"), a.prop("checked",
                            !0).next().addClass("checked"), a.change());
                        return!1
                    });
                    a.parent("label").add('label[for="' + a.attr("id") + '"]').click(function (a) {
                        b.click();
                        a.preventDefault()
                    });
                    a.change(function () {
                        d('input[name="' + a.attr("name") + '"]').next().removeClass("checked");
                        a.next().addClass("checked")
                    }).focus(function () {
                        b.is(".disabled") || b.addClass("focused")
                    }).blur(function () {
                        b.removeClass("focused")
                    });
                    a.on("refresh", function () {
                        a.is(":checked") ? (d('input[name="' + a.attr("name") + '"]').next().removeClass("checked"), b.addClass("checked")) :
                            b.removeClass("checked");
                        a.is(":disabled") ? b.addClass("disabled") : b.removeClass("disabled")
                    })
                }
            }) : a.is(":file") ? a.css({position: "absolute", top: "-50%", right: "-50%", fontSize: "200px", opacity: 0}).each(function () {
                if (1 > a.parent("div.jq-file").length) {
                    var b = d("<div" + q + ' class="jq-file' + s + '" style="display: inline-block; position: relative; overflow: hidden"></div>'), e = d('<div class="jq-file__name" style="float: left; white-space: nowrap"></div>').appendTo(b);
                    d('<div class="jq-file__browse" style="float: left">' +
                        p.browseText + "</div>").appendTo(b);
                    a.after(b);
                    b.append(a);
                    a.is(":disabled") && b.addClass("disabled");
                    a.change(function () {
                        e.text(a.val().replace(/.+[\\\/]/, ""))
                    }).focus(function () {
                        b.addClass("focused")
                    }).blur(function () {
                        b.removeClass("focused")
                    }).click(function () {
                        b.removeClass("focused")
                    }).on("refresh", function () {
                        a.is(":disabled") ? b.addClass("disabled") : b.removeClass("disabled")
                    })
                }
            }) : a.is("select") && a.each(function () {
                if (1 > a.next("div.jqselect").length) {
                    var b = function () {
                        function b(a) {
                            a.unbind("mousewheel DOMMouseScroll").bind("mousewheel DOMMouseScroll",
                                function (a) {
                                    var b = null;
                                    "mousewheel" == a.type ? b = -1 * a.originalEvent.wheelDelta : "DOMMouseScroll" == a.type && (b = 40 * a.originalEvent.detail);
                                    b && (a.preventDefault(), d(this).scrollTop(b + d(this).scrollTop()))
                                })
                        }

                        function t() {
                            e = 0;
                            for (len = g.length; e < len; e++) {
                                var a = "", b = "", d = "", n = "", c = "";
                                g.eq(e).prop("selected") && (b = "selected sel");
                                g.eq(e).is(":disabled") && (b = "disabled");
                                g.eq(e).is(":selected:disabled") && (b = "selected sel disabled");
                                void 0 !== g.eq(e).attr("class") && (n = " " + g.eq(e).attr("class"), d = ' data-class="' + g.eq(e).attr("class") +
                                    '"');
                                a = "<li" + d + ' class="' + b + n + '">' + g.eq(e).text() + "</li>";
                                g.eq(e).parent().is("optgroup") && (void 0 !== g.eq(e).parent().attr("class") && (c = " " + g.eq(e).parent().attr("class")), a = "<li" + d + ' class="' + b + n + " option" + c + '">' + g.eq(e).text() + "</li>", g.eq(e).is(":first-child") && (a = '<li class="optgroup' + c + '">' + g.eq(e).parent().attr("label") + "</li>" + a));
                                x += a
                            }
                        }

                        function v() {
                            var f = d("<div" + q + ' class="jq-selectbox jqselect' + s + '" style="display: inline-block; position: relative; z-index:' + p.singleSelectzIndex + '"><div class="jq-selectbox__select"' +
                                u + '><div class="jq-selectbox__select-text"></div><div class="jq-selectbox__trigger"><div class="jq-selectbox__trigger-arrow"></div></div></div></div>');
                            a.after(f).css({position: "absolute", left: -9999});
                            var e = d("div.jq-selectbox__select", f), h = d("div.jq-selectbox__select-text", f), n = g.filter(":selected");
                            n.length ? h.text(n.text()) : h.text(g.first().text());
                            t();
                            var c = d('<div class="jq-selectbox__dropdown" style="position: absolute; overflow: auto; overflow-x: hidden"><ul style="list-style: none">' + x + "</ul></div>");
                            f.append(c);
                            var k = d("li", c), m = 0;
                            k.each(function () {
                                d(this).css({display: "inline-block", "white-space": "nowrap"});
                                d(this).width() > m && (m = d(this).width());
                                d(this).css({display: "block", "white-space": "normal"})
                            });
                            var l = a.clone().css("width", "auto").insertAfter(a), B = l.width();
                            l.remove();
                            B == a.width() ? e.width(m) : f.width(a.outerWidth());
                            m > c.width() && c.width(m + c.width() - k.width());
                            l = k.filter(".selected");
                            1 > l.length && k.first().addClass("selected sel");
                            var y = f.outerHeight();
                            "auto" == c.css("left") && c.css({left: 0});
                            "auto" == c.css("top") && c.css({top: y});
                            var r = k.outerHeight(), z = c.css("top");
                            c.hide();
                            l.length && (g.first().text() != n.text() && f.addClass("changed"), f.data("class", l.data("class")), f.addClass(l.data("class")));
                            if (a.is(":disabled"))return f.addClass("disabled"), !1;
                            e.click(function () {
                                a.focus();
                                if (p.selectSmartPositioning) {
                                    var g = d(window), e = f.offset().top, m = g.height() - y - (e - g.scrollTop()), l = p.selectVisibleOptions, h = 6 * r, w = r * l;
                                    0 < l && 6 > l && (h = w);
                                    0 > m || m < h ? (c.height("auto").css({top: "auto", bottom: z}), c.outerHeight() >
                                        e - g.scrollTop() - 20 && (c.height(Math.floor((e - g.scrollTop() - 20) / r) * r), 0 < l && 6 > l ? c.height() > h && c.height(h) : 6 < l && c.height() > w && c.height(w))) : m > h && (c.height("auto").css({bottom: "auto", top: z}), c.outerHeight() > m - 20 && (c.height(Math.floor((m - 20) / r) * r), 0 < l && 6 > l ? c.height() > h && c.height(h) : 6 < l && c.height() > w && c.height(w)))
                                }
                                d("div.jqselect").css({zIndex: p.singleSelectzIndex - 1}).removeClass("opened focused");
                                f.css({zIndex: p.singleSelectzIndex});
                                c.is(":hidden") ? (d("div.jq-selectbox__dropdown:visible").hide(), c.show(),
                                    f.addClass("opened")) : (c.hide(), f.removeClass("opened"));
                                k.filter(".selected").length && c.scrollTop(c.scrollTop() + k.filter(".selected").position().top - c.innerHeight() / 2 + r / 2);
                                b(c);
                                return!1
                            });
                            k.hover(function () {
                                d(this).siblings().removeClass("selected")
                            });
                            var A = k.filter(".selected").text();
                            k.filter(".selected").text();
                            k.filter(":not(.disabled):not(.optgroup)").click(function () {
                                var b = d(this), l = b.text();
                                if (A != l) {
                                    var m = b.index();
                                    b.is(".option") && (m -= b.prevAll(".optgroup").length);
                                    b.addClass("selected sel").siblings().removeClass("selected sel");
                                    g.prop("selected", !1).eq(m).prop("selected", !0);
                                    A = l;
                                    h.text(l);
                                    g.first().text() != l ? f.addClass("changed") : f.removeClass("changed");
                                    f.data("class") && f.removeClass(f.data("class"));
                                    f.data("class", b.data("class"));
                                    f.addClass(b.data("class"));
                                    a.change()
                                }
                                c.hide();
                                f.removeClass("opened")
                            });
                            c.mouseout(function () {
                                d("li.sel", c).addClass("selected")
                            });
                            a.change(function () {
                                h.text(g.filter(":selected").text());
                                k.removeClass("selected sel").not(".optgroup").eq(a[0].selectedIndex).addClass("selected sel")
                            }).focus(function () {
                                f.addClass("focused")
                            }).blur(function () {
                                f.removeClass("focused")
                            }).bind("keydown keyup",
                                function (b) {
                                    h.text(g.filter(":selected").text());
                                    k.removeClass("selected sel").not(".optgroup").eq(a[0].selectedIndex).addClass("selected sel");
                                    38 != b.which && 37 != b.which && 33 != b.which || c.scrollTop(c.scrollTop() + k.filter(".selected").position().top);
                                    40 != b.which && 39 != b.which && 34 != b.which || c.scrollTop(c.scrollTop() + k.filter(".selected").position().top - c.innerHeight() + r);
                                    13 == b.which && c.hide()
                                });
                            d(document).on("click", function (a) {
                                d(a.target).parents().hasClass("jq-selectbox") || "OPTION" == a.target.nodeName ||
                                (c.hide().find("li.sel").addClass("selected"), f.removeClass("focused opened"))
                            })
                        }

                        function C() {
                            var f = d("<div" + q + ' class="jq-select-multiple jqselect' + s + '"' + u + ' style="display: inline-block"></div>');
                            a.after(f).css({position: "absolute", left: -9999});
                            t();
                            f.append('<ul style="position: relative">' + x + "</ul>");
                            var e = d("ul", f), h = d("li", f).attr("unselectable", "on").css({"-webkit-user-select": "none", "-moz-user-select": "none", "-ms-user-select": "none", "-o-user-select": "none", "user-select": "none"}), n = a.attr("size"),
                                c = e.outerHeight(), k = h.outerHeight();
                            void 0 !== n && 0 < n ? e.css({height: k * n}) : e.css({height: 4 * k});
                            c > f.height() && (e.css("overflowY", "scroll"), b(e), h.filter(".selected").length && e.scrollTop(e.scrollTop() + h.filter(".selected").position().top));
                            a.is(":disabled") ? (f.addClass("disabled"), g.each(function () {
                                d(this).is(":selected") && h.eq(d(this).index()).addClass("selected")
                            })) : (h.filter(":not(.disabled):not(.optgroup)").click(function (b) {
                                a.focus();
                                f.removeClass("focused");
                                var c = d(this);
                                b.ctrlKey || c.addClass("selected");
                                b.shiftKey || c.addClass("first");
                                b.ctrlKey || b.shiftKey || c.siblings().removeClass("selected first");
                                b.ctrlKey && (c.is(".selected") ? c.removeClass("selected first") : c.addClass("selected first"), c.siblings().removeClass("first"));
                                if (b.shiftKey) {
                                    var e = !1, k = !1;
                                    c.siblings().removeClass("selected").siblings(".first").addClass("selected");
                                    c.prevAll().each(function () {
                                        d(this).is(".first") && (e = !0)
                                    });
                                    c.nextAll().each(function () {
                                        d(this).is(".first") && (k = !0)
                                    });
                                    e && c.prevAll().each(function () {
                                        if (d(this).is(".selected"))return!1;
                                        d(this).not(".disabled, .optgroup").addClass("selected")
                                    });
                                    k && c.nextAll().each(function () {
                                        if (d(this).is(".selected"))return!1;
                                        d(this).not(".disabled, .optgroup").addClass("selected")
                                    });
                                    1 == h.filter(".selected").length && c.addClass("first")
                                }
                                g.prop("selected", !1);
                                h.filter(".selected").each(function () {
                                    var a = d(this), b = a.index();
                                    a.is(".option") && (b -= a.prevAll(".optgroup").length);
                                    g.eq(b).prop("selected", !0)
                                });
                                a.change()
                            }), g.each(function (a) {
                                d(this).data("optionIndex", a)
                            }), a.change(function () {
                                h.removeClass("selected");
                                var a = [];
                                g.filter(":selected").each(function () {
                                    a.push(d(this).data("optionIndex"))
                                });
                                h.not(".optgroup").filter(function (b) {
                                    return-1 < d.inArray(b, a)
                                }).addClass("selected")
                            }).focus(function () {
                                f.addClass("focused")
                            }).blur(function () {
                                f.removeClass("focused")
                            }), c > f.height() && a.keydown(function (a) {
                                38 != a.which && 37 != a.which && 33 != a.which || e.scrollTop(e.scrollTop() + h.filter(".selected").position().top - k);
                                40 != a.which && 39 != a.which && 34 != a.which || e.scrollTop(e.scrollTop() + h.filter(".selected:last").position().top -
                                    e.innerHeight() + 2 * k)
                            }))
                        }

                        var g = d("option", a), x = "";
                        a.is("[multiple]") ? C() : v()
                    };
                    b();
                    a.on("refresh", function () {
                        a.next().remove();
                        b()
                    })
                }
            })
        })
    }
})(jQuery);

/*! jQuery UI - v1.9.2 - 2014-09-09
 * http://jqueryui.com
 * Includes: jquery.ui.core.js, jquery.ui.widget.js, jquery.ui.position.js, jquery.ui.autocomplete.js, jquery.ui.menu.js
 * Copyright 2014 jQuery Foundation and other contributors; Licensed MIT */

(function (e, t) {
    function i(t, i) {
        var n, a, o, r = t.nodeName.toLowerCase();
        return"area" === r ? (n = t.parentNode, a = n.name, t.href && a && "map" === n.nodeName.toLowerCase() ? (o = e("img[usemap=#" + a + "]")[0], !!o && s(o)) : !1) : (/input|select|textarea|button|object/.test(r) ? !t.disabled : "a" === r ? t.href || i : i) && s(t)
    }

    function s(t) {
        return e.expr.filters.visible(t) && !e(t).parents().andSelf().filter(function () {
            return"hidden" === e.css(this, "visibility")
        }).length
    }

    var n = 0, a = /^ui-id-\d+$/;
    e.ui = e.ui || {}, e.ui.version || (e.extend(e.ui, {version: "1.9.2", keyCode: {BACKSPACE: 8, COMMA: 188, DELETE: 46, DOWN: 40, END: 35, ENTER: 13, ESCAPE: 27, HOME: 36, LEFT: 37, NUMPAD_ADD: 107, NUMPAD_DECIMAL: 110, NUMPAD_DIVIDE: 111, NUMPAD_ENTER: 108, NUMPAD_MULTIPLY: 106, NUMPAD_SUBTRACT: 109, PAGE_DOWN: 34, PAGE_UP: 33, PERIOD: 190, RIGHT: 39, SPACE: 32, TAB: 9, UP: 38}}), e.fn.extend({_focus: e.fn.focus, focus: function (t, i) {
        return"number" == typeof t ? this.each(function () {
            var s = this;
            setTimeout(function () {
                e(s).focus(), i && i.call(s)
            }, t)
        }) : this._focus.apply(this, arguments)
    }, scrollParent: function () {
        var t;
        return t = e.ui.ie && /(static|relative)/.test(this.css("position")) || /absolute/.test(this.css("position")) ? this.parents().filter(function () {
            return/(relative|absolute|fixed)/.test(e.css(this, "position")) && /(auto|scroll)/.test(e.css(this, "overflow") + e.css(this, "overflow-y") + e.css(this, "overflow-x"))
        }).eq(0) : this.parents().filter(function () {
            return/(auto|scroll)/.test(e.css(this, "overflow") + e.css(this, "overflow-y") + e.css(this, "overflow-x"))
        }).eq(0), /fixed/.test(this.css("position")) || !t.length ? e(document) : t
    }, zIndex: function (i) {
        if (i !== t)return this.css("zIndex", i);
        if (this.length)for (var s, n, a = e(this[0]); a.length && a[0] !== document;) {
            if (s = a.css("position"), ("absolute" === s || "relative" === s || "fixed" === s) && (n = parseInt(a.css("zIndex"), 10), !isNaN(n) && 0 !== n))return n;
            a = a.parent()
        }
        return 0
    }, uniqueId: function () {
        return this.each(function () {
            this.id || (this.id = "ui-id-" + ++n)
        })
    }, removeUniqueId: function () {
        return this.each(function () {
            a.test(this.id) && e(this).removeAttr("id")
        })
    }}), e.extend(e.expr[":"], {data: e.expr.createPseudo ? e.expr.createPseudo(function (t) {
        return function (i) {
            return!!e.data(i, t)
        }
    }) : function (t, i, s) {
        return!!e.data(t, s[3])
    }, focusable: function (t) {
        return i(t, !isNaN(e.attr(t, "tabindex")))
    }, tabbable: function (t) {
        var s = e.attr(t, "tabindex"), n = isNaN(s);
        return(n || s >= 0) && i(t, !n)
    }}), e(function () {
        var t = document.body, i = t.appendChild(i = document.createElement("div"));
        i.offsetHeight, e.extend(i.style, {minHeight: "100px", height: "auto", padding: 0, borderWidth: 0}), e.support.minHeight = 100 === i.offsetHeight, e.support.selectstart = "onselectstart"in i, t.removeChild(i).style.display = "none"
    }), e("<a>").outerWidth(1).jquery || e.each(["Width", "Height"], function (i, s) {
        function n(t, i, s, n) {
            return e.each(a, function () {
                i -= parseFloat(e.css(t, "padding" + this)) || 0, s && (i -= parseFloat(e.css(t, "border" + this + "Width")) || 0), n && (i -= parseFloat(e.css(t, "margin" + this)) || 0)
            }), i
        }

        var a = "Width" === s ? ["Left", "Right"] : ["Top", "Bottom"], o = s.toLowerCase(), r = {innerWidth: e.fn.innerWidth, innerHeight: e.fn.innerHeight, outerWidth: e.fn.outerWidth, outerHeight: e.fn.outerHeight};
        e.fn["inner" + s] = function (i) {
            return i === t ? r["inner" + s].call(this) : this.each(function () {
                e(this).css(o, n(this, i) + "px")
            })
        }, e.fn["outer" + s] = function (t, i) {
            return"number" != typeof t ? r["outer" + s].call(this, t) : this.each(function () {
                e(this).css(o, n(this, t, !0, i) + "px")
            })
        }
    }), e("<a>").data("a-b", "a").removeData("a-b").data("a-b") && (e.fn.removeData = function (t) {
        return function (i) {
            return arguments.length ? t.call(this, e.camelCase(i)) : t.call(this)
        }
    }(e.fn.removeData)), function () {
        var t = /msie ([\w.]+)/.exec(navigator.userAgent.toLowerCase()) || [];
        e.ui.ie = t.length ? !0 : !1, e.ui.ie6 = 6 === parseFloat(t[1], 10)
    }(), e.fn.extend({disableSelection: function () {
        return this.bind((e.support.selectstart ? "selectstart" : "mousedown") + ".ui-disableSelection", function (e) {
            e.preventDefault()
        })
    }, enableSelection: function () {
        return this.unbind(".ui-disableSelection")
    }}), e.extend(e.ui, {plugin: {add: function (t, i, s) {
        var n, a = e.ui[t].prototype;
        for (n in s)a.plugins[n] = a.plugins[n] || [], a.plugins[n].push([i, s[n]])
    }, call: function (e, t, i) {
        var s, n = e.plugins[t];
        if (n && e.element[0].parentNode && 11 !== e.element[0].parentNode.nodeType)for (s = 0; n.length > s; s++)e.options[n[s][0]] && n[s][1].apply(e.element, i)
    }}, contains: e.contains, hasScroll: function (t, i) {
        if ("hidden" === e(t).css("overflow"))return!1;
        var s = i && "left" === i ? "scrollLeft" : "scrollTop", n = !1;
        return t[s] > 0 ? !0 : (t[s] = 1, n = t[s] > 0, t[s] = 0, n)
    }, isOverAxis: function (e, t, i) {
        return e > t && t + i > e
    }, isOver: function (t, i, s, n, a, o) {
        return e.ui.isOverAxis(t, s, a) && e.ui.isOverAxis(i, n, o)
    }}))
})(jQuery);
(function (e, t) {
    var i = 0, s = Array.prototype.slice, n = e.cleanData;
    e.cleanData = function (t) {
        for (var i, s = 0; null != (i = t[s]); s++)try {
            e(i).triggerHandler("remove")
        } catch (a) {
        }
        n(t)
    }, e.widget = function (i, s, n) {
        var a, o, r, h, l = i.split(".")[0];
        i = i.split(".")[1], a = l + "-" + i, n || (n = s, s = e.Widget), e.expr[":"][a.toLowerCase()] = function (t) {
            return!!e.data(t, a)
        }, e[l] = e[l] || {}, o = e[l][i], r = e[l][i] = function (e, i) {
            return this._createWidget ? (arguments.length && this._createWidget(e, i), t) : new r(e, i)
        }, e.extend(r, o, {version: n.version, _proto: e.extend({}, n), _childConstructors: []}), h = new s, h.options = e.widget.extend({}, h.options), e.each(n, function (t, i) {
            e.isFunction(i) && (n[t] = function () {
                var e = function () {
                    return s.prototype[t].apply(this, arguments)
                }, n = function (e) {
                    return s.prototype[t].apply(this, e)
                };
                return function () {
                    var t, s = this._super, a = this._superApply;
                    return this._super = e, this._superApply = n, t = i.apply(this, arguments), this._super = s, this._superApply = a, t
                }
            }())
        }), r.prototype = e.widget.extend(h, {widgetEventPrefix: o ? h.widgetEventPrefix : i}, n, {constructor: r, namespace: l, widgetName: i, widgetBaseClass: a, widgetFullName: a}), o ? (e.each(o._childConstructors, function (t, i) {
            var s = i.prototype;
            e.widget(s.namespace + "." + s.widgetName, r, i._proto)
        }), delete o._childConstructors) : s._childConstructors.push(r), e.widget.bridge(i, r)
    }, e.widget.extend = function (i) {
        for (var n, a, o = s.call(arguments, 1), r = 0, h = o.length; h > r; r++)for (n in o[r])a = o[r][n], o[r].hasOwnProperty(n) && a !== t && (i[n] = e.isPlainObject(a) ? e.isPlainObject(i[n]) ? e.widget.extend({}, i[n], a) : e.widget.extend({}, a) : a);
        return i
    }, e.widget.bridge = function (i, n) {
        var a = n.prototype.widgetFullName || i;
        e.fn[i] = function (o) {
            var r = "string" == typeof o, h = s.call(arguments, 1), l = this;
            return o = !r && h.length ? e.widget.extend.apply(null, [o].concat(h)) : o, r ? this.each(function () {
                var s, n = e.data(this, a);
                return n ? e.isFunction(n[o]) && "_" !== o.charAt(0) ? (s = n[o].apply(n, h), s !== n && s !== t ? (l = s && s.jquery ? l.pushStack(s.get()) : s, !1) : t) : e.error("no such method '" + o + "' for " + i + " widget instance") : e.error("cannot call methods on " + i + " prior to initialization; " + "attempted to call method '" + o + "'")
            }) : this.each(function () {
                var t = e.data(this, a);
                t ? t.option(o || {})._init() : e.data(this, a, new n(o, this))
            }), l
        }
    }, e.Widget = function () {
    }, e.Widget._childConstructors = [], e.Widget.prototype = {widgetName: "widget", widgetEventPrefix: "", defaultElement: "<div>", options: {disabled: !1, create: null}, _createWidget: function (t, s) {
        s = e(s || this.defaultElement || this)[0], this.element = e(s), this.uuid = i++, this.eventNamespace = "." + this.widgetName + this.uuid, this.options = e.widget.extend({}, this.options, this._getCreateOptions(), t), this.bindings = e(), this.hoverable = e(), this.focusable = e(), s !== this && (e.data(s, this.widgetName, this), e.data(s, this.widgetFullName, this), this._on(!0, this.element, {remove: function (e) {
            e.target === s && this.destroy()
        }}), this.document = e(s.style ? s.ownerDocument : s.document || s), this.window = e(this.document[0].defaultView || this.document[0].parentWindow)), this._create(), this._trigger("create", null, this._getCreateEventData()), this._init()
    }, _getCreateOptions: e.noop, _getCreateEventData: e.noop, _create: e.noop, _init: e.noop, destroy: function () {
        this._destroy(), this.element.unbind(this.eventNamespace).removeData(this.widgetName).removeData(this.widgetFullName).removeData(e.camelCase(this.widgetFullName)), this.widget().unbind(this.eventNamespace).removeAttr("aria-disabled").removeClass(this.widgetFullName + "-disabled " + "ui-state-disabled"), this.bindings.unbind(this.eventNamespace), this.hoverable.removeClass("ui-state-hover"), this.focusable.removeClass("ui-state-focus")
    }, _destroy: e.noop, widget: function () {
        return this.element
    }, option: function (i, s) {
        var n, a, o, r = i;
        if (0 === arguments.length)return e.widget.extend({}, this.options);
        if ("string" == typeof i)if (r = {}, n = i.split("."), i = n.shift(), n.length) {
            for (a = r[i] = e.widget.extend({}, this.options[i]), o = 0; n.length - 1 > o; o++)a[n[o]] = a[n[o]] || {}, a = a[n[o]];
            if (i = n.pop(), s === t)return a[i] === t ? null : a[i];
            a[i] = s
        } else {
            if (s === t)return this.options[i] === t ? null : this.options[i];
            r[i] = s
        }
        return this._setOptions(r), this
    }, _setOptions: function (e) {
        var t;
        for (t in e)this._setOption(t, e[t]);
        return this
    }, _setOption: function (e, t) {
        return this.options[e] = t, "disabled" === e && (this.widget().toggleClass(this.widgetFullName + "-disabled ui-state-disabled", !!t).attr("aria-disabled", t), this.hoverable.removeClass("ui-state-hover"), this.focusable.removeClass("ui-state-focus")), this
    }, enable: function () {
        return this._setOption("disabled", !1)
    }, disable: function () {
        return this._setOption("disabled", !0)
    }, _on: function (i, s, n) {
        var a, o = this;
        "boolean" != typeof i && (n = s, s = i, i = !1), n ? (s = a = e(s), this.bindings = this.bindings.add(s)) : (n = s, s = this.element, a = this.widget()), e.each(n, function (n, r) {
            function h() {
                return i || o.options.disabled !== !0 && !e(this).hasClass("ui-state-disabled") ? ("string" == typeof r ? o[r] : r).apply(o, arguments) : t
            }

            "string" != typeof r && (h.guid = r.guid = r.guid || h.guid || e.guid++);
            var l = n.match(/^(\w+)\s*(.*)$/), u = l[1] + o.eventNamespace, c = l[2];
            c ? a.delegate(c, u, h) : s.bind(u, h)
        })
    }, _off: function (e, t) {
        t = (t || "").split(" ").join(this.eventNamespace + " ") + this.eventNamespace, e.unbind(t).undelegate(t)
    }, _delay: function (e, t) {
        function i() {
            return("string" == typeof e ? s[e] : e).apply(s, arguments)
        }

        var s = this;
        return setTimeout(i, t || 0)
    }, _hoverable: function (t) {
        this.hoverable = this.hoverable.add(t), this._on(t, {mouseenter: function (t) {
            e(t.currentTarget).addClass("ui-state-hover")
        }, mouseleave: function (t) {
            e(t.currentTarget).removeClass("ui-state-hover")
        }})
    }, _focusable: function (t) {
        this.focusable = this.focusable.add(t), this._on(t, {focusin: function (t) {
            e(t.currentTarget).addClass("ui-state-focus")
        }, focusout: function (t) {
            e(t.currentTarget).removeClass("ui-state-focus")
        }})
    }, _trigger: function (t, i, s) {
        var n, a, o = this.options[t];
        if (s = s || {}, i = e.Event(i), i.type = (t === this.widgetEventPrefix ? t : this.widgetEventPrefix + t).toLowerCase(), i.target = this.element[0], a = i.originalEvent)for (n in a)n in i || (i[n] = a[n]);
        return this.element.trigger(i, s), !(e.isFunction(o) && o.apply(this.element[0], [i].concat(s)) === !1 || i.isDefaultPrevented())
    }}, e.each({show: "fadeIn", hide: "fadeOut"}, function (t, i) {
        e.Widget.prototype["_" + t] = function (s, n, a) {
            "string" == typeof n && (n = {effect: n});
            var o, r = n ? n === !0 || "number" == typeof n ? i : n.effect || i : t;
            n = n || {}, "number" == typeof n && (n = {duration: n}), o = !e.isEmptyObject(n), n.complete = a, n.delay && s.delay(n.delay), o && e.effects && (e.effects.effect[r] || e.uiBackCompat !== !1 && e.effects[r]) ? s[t](n) : r !== t && s[r] ? s[r](n.duration, n.easing, a) : s.queue(function (i) {
                e(this)[t](), a && a.call(s[0]), i()
            })
        }
    }), e.uiBackCompat !== !1 && (e.Widget.prototype._getCreateOptions = function () {
        return e.metadata && e.metadata.get(this.element[0])[this.widgetName]
    })
})(jQuery);
(function (e, t) {
    function i(e, t, i) {
        return[parseInt(e[0], 10) * (d.test(e[0]) ? t / 100 : 1), parseInt(e[1], 10) * (d.test(e[1]) ? i / 100 : 1)]
    }

    function s(t, i) {
        return parseInt(e.css(t, i), 10) || 0
    }

    e.ui = e.ui || {};
    var n, a = Math.max, o = Math.abs, r = Math.round, h = /left|center|right/, l = /top|center|bottom/, u = /[\+\-]\d+%?/, c = /^\w+/, d = /%$/, p = e.fn.position;
    e.position = {scrollbarWidth: function () {
        if (n !== t)return n;
        var i, s, a = e("<div style='display:block;width:50px;height:50px;overflow:hidden;'><div style='height:100px;width:auto;'></div></div>"), o = a.children()[0];
        return e("body").append(a), i = o.offsetWidth, a.css("overflow", "scroll"), s = o.offsetWidth, i === s && (s = a[0].clientWidth), a.remove(), n = i - s
    }, getScrollInfo: function (t) {
        var i = t.isWindow ? "" : t.element.css("overflow-x"), s = t.isWindow ? "" : t.element.css("overflow-y"), n = "scroll" === i || "auto" === i && t.width < t.element[0].scrollWidth, a = "scroll" === s || "auto" === s && t.height < t.element[0].scrollHeight;
        return{width: n ? e.position.scrollbarWidth() : 0, height: a ? e.position.scrollbarWidth() : 0}
    }, getWithinInfo: function (t) {
        var i = e(t || window), s = e.isWindow(i[0]);
        return{element: i, isWindow: s, offset: i.offset() || {left: 0, top: 0}, scrollLeft: i.scrollLeft(), scrollTop: i.scrollTop(), width: s ? i.width() : i.outerWidth(), height: s ? i.height() : i.outerHeight()}
    }}, e.fn.position = function (t) {
        if (!t || !t.of)return p.apply(this, arguments);
        t = e.extend({}, t);
        var n, d, f, m, g, v = e(t.of), y = e.position.getWithinInfo(t.within), b = e.position.getScrollInfo(y), _ = v[0], x = (t.collision || "flip").split(" "), w = {};
        return 9 === _.nodeType ? (d = v.width(), f = v.height(), m = {top: 0, left: 0}) : e.isWindow(_) ? (d = v.width(), f = v.height(), m = {top: v.scrollTop(), left: v.scrollLeft()}) : _.preventDefault ? (t.at = "left top", d = f = 0, m = {top: _.pageY, left: _.pageX}) : (d = v.outerWidth(), f = v.outerHeight(), m = v.offset()), g = e.extend({}, m), e.each(["my", "at"], function () {
            var e, i, s = (t[this] || "").split(" ");
            1 === s.length && (s = h.test(s[0]) ? s.concat(["center"]) : l.test(s[0]) ? ["center"].concat(s) : ["center", "center"]), s[0] = h.test(s[0]) ? s[0] : "center", s[1] = l.test(s[1]) ? s[1] : "center", e = u.exec(s[0]), i = u.exec(s[1]), w[this] = [e ? e[0] : 0, i ? i[0] : 0], t[this] = [c.exec(s[0])[0], c.exec(s[1])[0]]
        }), 1 === x.length && (x[1] = x[0]), "right" === t.at[0] ? g.left += d : "center" === t.at[0] && (g.left += d / 2), "bottom" === t.at[1] ? g.top += f : "center" === t.at[1] && (g.top += f / 2), n = i(w.at, d, f), g.left += n[0], g.top += n[1], this.each(function () {
            var h, l, u = e(this), c = u.outerWidth(), p = u.outerHeight(), _ = s(this, "marginLeft"), k = s(this, "marginTop"), D = c + _ + s(this, "marginRight") + b.width, T = p + k + s(this, "marginBottom") + b.height, S = e.extend({}, g), M = i(w.my, u.outerWidth(), u.outerHeight());
            "right" === t.my[0] ? S.left -= c : "center" === t.my[0] && (S.left -= c / 2), "bottom" === t.my[1] ? S.top -= p : "center" === t.my[1] && (S.top -= p / 2), S.left += M[0], S.top += M[1], e.support.offsetFractions || (S.left = r(S.left), S.top = r(S.top)), h = {marginLeft: _, marginTop: k}, e.each(["left", "top"], function (i, s) {
                e.ui.position[x[i]] && e.ui.position[x[i]][s](S, {targetWidth: d, targetHeight: f, elemWidth: c, elemHeight: p, collisionPosition: h, collisionWidth: D, collisionHeight: T, offset: [n[0] + M[0], n[1] + M[1]], my: t.my, at: t.at, within: y, elem: u})
            }), e.fn.bgiframe && u.bgiframe(), t.using && (l = function (e) {
                var i = m.left - S.left, s = i + d - c, n = m.top - S.top, r = n + f - p, h = {target: {element: v, left: m.left, top: m.top, width: d, height: f}, element: {element: u, left: S.left, top: S.top, width: c, height: p}, horizontal: 0 > s ? "left" : i > 0 ? "right" : "center", vertical: 0 > r ? "top" : n > 0 ? "bottom" : "middle"};
                c > d && d > o(i + s) && (h.horizontal = "center"), p > f && f > o(n + r) && (h.vertical = "middle"), h.important = a(o(i), o(s)) > a(o(n), o(r)) ? "horizontal" : "vertical", t.using.call(this, e, h)
            }), u.offset(e.extend(S, {using: l}))
        })
    }, e.ui.position = {fit: {left: function (e, t) {
        var i, s = t.within, n = s.isWindow ? s.scrollLeft : s.offset.left, o = s.width, r = e.left - t.collisionPosition.marginLeft, h = n - r, l = r + t.collisionWidth - o - n;
        t.collisionWidth > o ? h > 0 && 0 >= l ? (i = e.left + h + t.collisionWidth - o - n, e.left += h - i) : e.left = l > 0 && 0 >= h ? n : h > l ? n + o - t.collisionWidth : n : h > 0 ? e.left += h : l > 0 ? e.left -= l : e.left = a(e.left - r, e.left)
    }, top: function (e, t) {
        var i, s = t.within, n = s.isWindow ? s.scrollTop : s.offset.top, o = t.within.height, r = e.top - t.collisionPosition.marginTop, h = n - r, l = r + t.collisionHeight - o - n;
        t.collisionHeight > o ? h > 0 && 0 >= l ? (i = e.top + h + t.collisionHeight - o - n, e.top += h - i) : e.top = l > 0 && 0 >= h ? n : h > l ? n + o - t.collisionHeight : n : h > 0 ? e.top += h : l > 0 ? e.top -= l : e.top = a(e.top - r, e.top)
    }}, flip: {left: function (e, t) {
        var i, s, n = t.within, a = n.offset.left + n.scrollLeft, r = n.width, h = n.isWindow ? n.scrollLeft : n.offset.left, l = e.left - t.collisionPosition.marginLeft, u = l - h, c = l + t.collisionWidth - r - h, d = "left" === t.my[0] ? -t.elemWidth : "right" === t.my[0] ? t.elemWidth : 0, p = "left" === t.at[0] ? t.targetWidth : "right" === t.at[0] ? -t.targetWidth : 0, f = -2 * t.offset[0];
        0 > u ? (i = e.left + d + p + f + t.collisionWidth - r - a, (0 > i || o(u) > i) && (e.left += d + p + f)) : c > 0 && (s = e.left - t.collisionPosition.marginLeft + d + p + f - h, (s > 0 || c > o(s)) && (e.left += d + p + f))
    }, top: function (e, t) {
        var i, s, n = t.within, a = n.offset.top + n.scrollTop, r = n.height, h = n.isWindow ? n.scrollTop : n.offset.top, l = e.top - t.collisionPosition.marginTop, u = l - h, c = l + t.collisionHeight - r - h, d = "top" === t.my[1], p = d ? -t.elemHeight : "bottom" === t.my[1] ? t.elemHeight : 0, f = "top" === t.at[1] ? t.targetHeight : "bottom" === t.at[1] ? -t.targetHeight : 0, m = -2 * t.offset[1];
        0 > u ? (s = e.top + p + f + m + t.collisionHeight - r - a, e.top + p + f + m > u && (0 > s || o(u) > s) && (e.top += p + f + m)) : c > 0 && (i = e.top - t.collisionPosition.marginTop + p + f + m - h, e.top + p + f + m > c && (i > 0 || c > o(i)) && (e.top += p + f + m))
    }}, flipfit: {left: function () {
        e.ui.position.flip.left.apply(this, arguments), e.ui.position.fit.left.apply(this, arguments)
    }, top: function () {
        e.ui.position.flip.top.apply(this, arguments), e.ui.position.fit.top.apply(this, arguments)
    }}}, function () {
        var t, i, s, n, a, o = document.getElementsByTagName("body")[0], r = document.createElement("div");
        t = document.createElement(o ? "div" : "body"), s = {visibility: "hidden", width: 0, height: 0, border: 0, margin: 0, background: "none"}, o && e.extend(s, {position: "absolute", left: "-1000px", top: "-1000px"});
        for (a in s)t.style[a] = s[a];
        t.appendChild(r), i = o || document.documentElement, i.insertBefore(t, i.firstChild), r.style.cssText = "position: absolute; left: 10.7432222px;", n = e(r).offset().left, e.support.offsetFractions = n > 10 && 11 > n, t.innerHTML = "", i.removeChild(t)
    }(), e.uiBackCompat !== !1 && function (e) {
        var i = e.fn.position;
        e.fn.position = function (s) {
            if (!s || !s.offset)return i.call(this, s);
            var n = s.offset.split(" "), a = s.at.split(" ");
            return 1 === n.length && (n[1] = n[0]), /^\d/.test(n[0]) && (n[0] = "+" + n[0]), /^\d/.test(n[1]) && (n[1] = "+" + n[1]), 1 === a.length && (/left|center|right/.test(a[0]) ? a[1] = "center" : (a[1] = a[0], a[0] = "center")), i.call(this, e.extend(s, {at: a[0] + n[0] + " " + a[1] + n[1], offset: t}))
        }
    }(jQuery)
})(jQuery);
(function (e) {
    var t = 0;
    e.widget("ui.autocomplete", {version: "1.9.2", defaultElement: "<input>", options: {appendTo: "body", autoFocus: !1, delay: 300, minLength: 1, position: {my: "left top", at: "left bottom", collision: "none"}, source: null, change: null, close: null, focus: null, open: null, response: null, search: null, select: null}, pending: 0, _create: function () {
        var t, i, s;
        this.isMultiLine = this._isMultiLine(), this.valueMethod = this.element[this.element.is("input,textarea") ? "val" : "text"], this.isNewMenu = !0, this.element.addClass("ui-autocomplete-input").attr("autocomplete", "off"), this._on(this.element, {keydown: function (n) {
            if (this.element.prop("readOnly"))return t = !0, s = !0, i = !0, undefined;
            t = !1, s = !1, i = !1;
            var a = e.ui.keyCode;
            switch (n.keyCode) {
                case a.PAGE_UP:
                    t = !0, this._move("previousPage", n);
                    break;
                case a.PAGE_DOWN:
                    t = !0, this._move("nextPage", n);
                    break;
                case a.UP:
                    t = !0, this._keyEvent("previous", n);
                    break;
                case a.DOWN:
                    t = !0, this._keyEvent("next", n);
                    break;
                case a.ENTER:
                case a.NUMPAD_ENTER:
                    this.menu.active && (t = !0, n.preventDefault(), this.menu.select(n));
                    break;
                case a.TAB:
                    this.menu.active && this.menu.select(n);
                    break;
                case a.ESCAPE:
                    this.menu.element.is(":visible") && (this._value(this.term), this.close(n), n.preventDefault());
                    break;
                default:
                    i = !0, this._searchTimeout(n)
            }
        }, keypress: function (s) {
            if (t)return t = !1, s.preventDefault(), undefined;
            if (!i) {
                var n = e.ui.keyCode;
                switch (s.keyCode) {
                    case n.PAGE_UP:
                        this._move("previousPage", s);
                        break;
                    case n.PAGE_DOWN:
                        this._move("nextPage", s);
                        break;
                    case n.UP:
                        this._keyEvent("previous", s);
                        break;
                    case n.DOWN:
                        this._keyEvent("next", s)
                }
            }
        }, input: function (e) {
            return s ? (s = !1, e.preventDefault(), undefined) : (this._searchTimeout(e), undefined)
        }, focus: function () {
            this.selectedItem = null, this.previous = this._value()
        }, blur: function (e) {
            return this.cancelBlur ? (delete this.cancelBlur, undefined) : (clearTimeout(this.searching), this.close(e), this._change(e), undefined)
        }}), this._initSource(), this.menu = e("<ul>").addClass("ui-autocomplete").appendTo(this.document.find(this.options.appendTo || "body")[0]).menu({input: e(), role: null}).zIndex(this.element.zIndex() + 1).hide().data("menu"), this._on(this.menu.element, {mousedown: function (t) {
            t.preventDefault(), this.cancelBlur = !0, this._delay(function () {
                delete this.cancelBlur
            });
            var i = this.menu.element[0];
            e(t.target).closest(".ui-menu-item").length || this._delay(function () {
                var t = this;
                this.document.one("mousedown", function (s) {
                    s.target === t.element[0] || s.target === i || e.contains(i, s.target) || t.close()
                })
            })
        }, menufocus: function (t, i) {
            if (this.isNewMenu && (this.isNewMenu = !1, t.originalEvent && /^mouse/.test(t.originalEvent.type)))return this.menu.blur(), this.document.one("mousemove", function () {
                e(t.target).trigger(t.originalEvent)
            }), undefined;
            var s = i.item.data("ui-autocomplete-item") || i.item.data("item.autocomplete");
            !1 !== this._trigger("focus", t, {item: s}) ? t.originalEvent && /^key/.test(t.originalEvent.type) && this._value(s.value) : this.liveRegion.text(s.value)
        }, menuselect: function (e, t) {
            var i = t.item.data("ui-autocomplete-item") || t.item.data("item.autocomplete"), s = this.previous;
            this.element[0] !== this.document[0].activeElement && (this.element.focus(), this.previous = s, this._delay(function () {
                this.previous = s, this.selectedItem = i
            })), !1 !== this._trigger("select", e, {item: i}) && this._value(i.value), this.term = this._value(), this.close(e), this.selectedItem = i
        }}), this.liveRegion = e("<span>", {role: "status", "aria-live": "polite"}).addClass("ui-helper-hidden-accessible").insertAfter(this.element), e.fn.bgiframe && this.menu.element.bgiframe(), this._on(this.window, {beforeunload: function () {
            this.element.removeAttr("autocomplete")
        }})
    }, _destroy: function () {
        clearTimeout(this.searching), this.element.removeClass("ui-autocomplete-input").removeAttr("autocomplete"), this.menu.element.remove(), this.liveRegion.remove()
    }, _setOption: function (e, t) {
        this._super(e, t), "source" === e && this._initSource(), "appendTo" === e && this.menu.element.appendTo(this.document.find(t || "body")[0]), "disabled" === e && t && this.xhr && this.xhr.abort()
    }, _isMultiLine: function () {
        return this.element.is("textarea") ? !0 : this.element.is("input") ? !1 : this.element.prop("isContentEditable")
    }, _initSource: function () {
        var t, i, s = this;
        e.isArray(this.options.source) ? (t = this.options.source, this.source = function (i, s) {
            s(e.ui.autocomplete.filter(t, i.term))
        }) : "string" == typeof this.options.source ? (i = this.options.source, this.source = function (t, n) {
            s.xhr && s.xhr.abort(), s.xhr = e.ajax({url: i, data: t, dataType: "json", success: function (e) {
                n(e)
            }, error: function () {
                n([])
            }})
        }) : this.source = this.options.source
    }, _searchTimeout: function (e) {
        clearTimeout(this.searching), this.searching = this._delay(function () {
            this.term !== this._value() && (this.selectedItem = null, this.search(null, e))
        }, this.options.delay)
    }, search: function (e, t) {
        return e = null != e ? e : this._value(), this.term = this._value(), e.length < this.options.minLength ? this.close(t) : this._trigger("search", t) !== !1 ? this._search(e) : undefined
    }, _search: function (e) {
        this.pending++, this.element.addClass("ui-autocomplete-loading"), this.cancelSearch = !1, this.source({term: e}, this._response())
    }, _response: function () {
        var e = this, i = ++t;
        return function (s) {
            i === t && e.__response(s), e.pending--, e.pending || e.element.removeClass("ui-autocomplete-loading")
        }
    }, __response: function (e) {
        e && (e = this._normalize(e)), this._trigger("response", null, {content: e}), !this.options.disabled && e && e.length && !this.cancelSearch ? (this._suggest(e), this._trigger("open")) : this._close()
    }, close: function (e) {
        this.cancelSearch = !0, this._close(e)
    }, _close: function (e) {
        this.menu.element.is(":visible") && (this.menu.element.hide(), this.menu.blur(), this.isNewMenu = !0, this._trigger("close", e))
    }, _change: function (e) {
        this.previous !== this._value() && this._trigger("change", e, {item: this.selectedItem})
    }, _normalize: function (t) {
        return t.length && t[0].label && t[0].value ? t : e.map(t, function (t) {
            return"string" == typeof t ? {label: t, value: t} : e.extend({label: t.label || t.value, value: t.value || t.label}, t)
        })
    }, _suggest: function (t) {
        var i = this.menu.element.empty().zIndex(this.element.zIndex() + 1);
        this._renderMenu(i, t), this.menu.refresh(), i.show(), this._resizeMenu(), i.position(e.extend({of: this.element}, this.options.position)), this.options.autoFocus && this.menu.next()
    }, _resizeMenu: function () {
        var e = this.menu.element;
        e.outerWidth(Math.max(e.width("").outerWidth() + 1, this.element.outerWidth()))
    }, _renderMenu: function (t, i) {
        var s = this;
        e.each(i, function (e, i) {
            s._renderItemData(t, i)
        })
    }, _renderItemData: function (e, t) {
        return this._renderItem(e, t).data("ui-autocomplete-item", t)
    }, _renderItem: function (t, i) {
        return e("<li>").append(e("<a>").text(i.label)).appendTo(t)
    }, _move: function (e, t) {
        return this.menu.element.is(":visible") ? this.menu.isFirstItem() && /^previous/.test(e) || this.menu.isLastItem() && /^next/.test(e) ? (this._value(this.term), this.menu.blur(), undefined) : (this.menu[e](t), undefined) : (this.search(null, t), undefined)
    }, widget: function () {
        return this.menu.element
    }, _value: function () {
        return this.valueMethod.apply(this.element, arguments)
    }, _keyEvent: function (e, t) {
        (!this.isMultiLine || this.menu.element.is(":visible")) && (this._move(e, t), t.preventDefault())
    }}), e.extend(e.ui.autocomplete, {escapeRegex: function (e) {
        return e.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&")
    }, filter: function (t, i) {
        var s = RegExp(e.ui.autocomplete.escapeRegex(i), "i");
        return e.grep(t, function (e) {
            return s.test(e.label || e.value || e)
        })
    }}), e.widget("ui.autocomplete", e.ui.autocomplete, {options: {messages: {noResults: "No search results.", results: function (e) {
        return e + (e > 1 ? " results are" : " result is") + " available, use up and down arrow keys to navigate."
    }}}, __response: function (e) {
        var t;
        this._superApply(arguments), this.options.disabled || this.cancelSearch || (t = e && e.length ? this.options.messages.results(e.length) : this.options.messages.noResults, this.liveRegion.text(t))
    }})
})(jQuery);
(function (e) {
    var t = !1;
    e.widget("ui.menu", {version: "1.9.2", defaultElement: "<ul>", delay: 300, options: {icons: {submenu: "ui-icon-carat-1-e"}, menus: "ul", position: {my: "left top", at: "right top"}, role: "menu", blur: null, focus: null, select: null}, _create: function () {
        this.activeMenu = this.element, this.element.uniqueId().addClass("ui-menu ui-widget ui-widget-content ui-corner-all").toggleClass("ui-menu-icons", !!this.element.find(".ui-icon").length).attr({role: this.options.role, tabIndex: 0}).bind("click" + this.eventNamespace, e.proxy(function (e) {
            this.options.disabled && e.preventDefault()
        }, this)), this.options.disabled && this.element.addClass("ui-state-disabled").attr("aria-disabled", "true"), this._on({"mousedown .ui-menu-item > a": function (e) {
            e.preventDefault()
        }, "click .ui-state-disabled > a": function (e) {
            e.preventDefault()
        }, "click .ui-menu-item:has(a)": function (i) {
            var s = e(i.target).closest(".ui-menu-item");
            !t && s.not(".ui-state-disabled").length && (t = !0, this.select(i), s.has(".ui-menu").length ? this.expand(i) : this.element.is(":focus") || (this.element.trigger("focus", [!0]), this.active && 1 === this.active.parents(".ui-menu").length && clearTimeout(this.timer)))
        }, "mouseenter .ui-menu-item": function (t) {
            var i = e(t.currentTarget);
            i.siblings().children(".ui-state-active").removeClass("ui-state-active"), this.focus(t, i)
        }, mouseleave: "collapseAll", "mouseleave .ui-menu": "collapseAll", focus: function (e, t) {
            var i = this.active || this.element.children(".ui-menu-item").eq(0);
            t || this.focus(e, i)
        }, blur: function (t) {
            this._delay(function () {
                e.contains(this.element[0], this.document[0].activeElement) || this.collapseAll(t)
            })
        }, keydown: "_keydown"}), this.refresh(), this._on(this.document, {click: function (i) {
            e(i.target).closest(".ui-menu").length || this.collapseAll(i), t = !1
        }})
    }, _destroy: function () {
        this.element.removeAttr("aria-activedescendant").find(".ui-menu").andSelf().removeClass("ui-menu ui-widget ui-widget-content ui-corner-all ui-menu-icons").removeAttr("role").removeAttr("tabIndex").removeAttr("aria-labelledby").removeAttr("aria-expanded").removeAttr("aria-hidden").removeAttr("aria-disabled").removeUniqueId().show(), this.element.find(".ui-menu-item").removeClass("ui-menu-item").removeAttr("role").removeAttr("aria-disabled").children("a").removeUniqueId().removeClass("ui-corner-all ui-state-hover").removeAttr("tabIndex").removeAttr("role").removeAttr("aria-haspopup").children().each(function () {
            var t = e(this);
            t.data("ui-menu-submenu-carat") && t.remove()
        }), this.element.find(".ui-menu-divider").removeClass("ui-menu-divider ui-widget-content")
    }, _keydown: function (t) {
        function i(e) {
            return e.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&")
        }

        var s, n, a, o, r, h = !0;
        switch (t.keyCode) {
            case e.ui.keyCode.PAGE_UP:
                this.previousPage(t);
                break;
            case e.ui.keyCode.PAGE_DOWN:
                this.nextPage(t);
                break;
            case e.ui.keyCode.HOME:
                this._move("first", "first", t);
                break;
            case e.ui.keyCode.END:
                this._move("last", "last", t);
                break;
            case e.ui.keyCode.UP:
                this.previous(t);
                break;
            case e.ui.keyCode.DOWN:
                this.next(t);
                break;
            case e.ui.keyCode.LEFT:
                this.collapse(t);
                break;
            case e.ui.keyCode.RIGHT:
                this.active && !this.active.is(".ui-state-disabled") && this.expand(t);
                break;
            case e.ui.keyCode.ENTER:
            case e.ui.keyCode.SPACE:
                this._activate(t);
                break;
            case e.ui.keyCode.ESCAPE:
                this.collapse(t);
                break;
            default:
                h = !1, n = this.previousFilter || "", a = String.fromCharCode(t.keyCode), o = !1, clearTimeout(this.filterTimer), a === n ? o = !0 : a = n + a, r = RegExp("^" + i(a), "i"), s = this.activeMenu.children(".ui-menu-item").filter(function () {
                    return r.test(e(this).children("a").text())
                }), s = o && -1 !== s.index(this.active.next()) ? this.active.nextAll(".ui-menu-item") : s, s.length || (a = String.fromCharCode(t.keyCode), r = RegExp("^" + i(a), "i"), s = this.activeMenu.children(".ui-menu-item").filter(function () {
                    return r.test(e(this).children("a").text())
                })), s.length ? (this.focus(t, s), s.length > 1 ? (this.previousFilter = a, this.filterTimer = this._delay(function () {
                    delete this.previousFilter
                }, 1e3)) : delete this.previousFilter) : delete this.previousFilter
        }
        h && t.preventDefault()
    }, _activate: function (e) {
        this.active.is(".ui-state-disabled") || (this.active.children("a[aria-haspopup='true']").length ? this.expand(e) : this.select(e))
    }, refresh: function () {
        var t, i = this.options.icons.submenu, s = this.element.find(this.options.menus);
        s.filter(":not(.ui-menu)").addClass("ui-menu ui-widget ui-widget-content ui-corner-all").hide().attr({role: this.options.role, "aria-hidden": "true", "aria-expanded": "false"}).each(function () {
            var t = e(this), s = t.prev("a"), n = e("<span>").addClass("ui-menu-icon ui-icon " + i).data("ui-menu-submenu-carat", !0);
            s.attr("aria-haspopup", "true").prepend(n), t.attr("aria-labelledby", s.attr("id"))
        }), t = s.add(this.element), t.children(":not(.ui-menu-item):has(a)").addClass("ui-menu-item").attr("role", "presentation").children("a").uniqueId().addClass("ui-corner-all").attr({tabIndex: -1, role: this._itemRole()}), t.children(":not(.ui-menu-item)").each(function () {
            var t = e(this);
            /[^\-—–\s]/.test(t.text()) || t.addClass("ui-widget-content ui-menu-divider")
        }), t.children(".ui-state-disabled").attr("aria-disabled", "true"), this.active && !e.contains(this.element[0], this.active[0]) && this.blur()
    }, _itemRole: function () {
        return{menu: "menuitem", listbox: "option"}[this.options.role]
    }, focus: function (e, t) {
        var i, s;
        this.blur(e, e && "focus" === e.type), this._scrollIntoView(t), this.active = t.first(), s = this.active.children("a").addClass("ui-state-focus"), this.options.role && this.element.attr("aria-activedescendant", s.attr("id")), this.active.parent().closest(".ui-menu-item").children("a:first").addClass("ui-state-active"), e && "keydown" === e.type ? this._close() : this.timer = this._delay(function () {
            this._close()
        }, this.delay), i = t.children(".ui-menu"), i.length && /^mouse/.test(e.type) && this._startOpening(i), this.activeMenu = t.parent(), this._trigger("focus", e, {item: t})
    }, _scrollIntoView: function (t) {
        var i, s, n, a, o, r;
        this._hasScroll() && (i = parseFloat(e.css(this.activeMenu[0], "borderTopWidth")) || 0, s = parseFloat(e.css(this.activeMenu[0], "paddingTop")) || 0, n = t.offset().top - this.activeMenu.offset().top - i - s, a = this.activeMenu.scrollTop(), o = this.activeMenu.height(), r = t.height(), 0 > n ? this.activeMenu.scrollTop(a + n) : n + r > o && this.activeMenu.scrollTop(a + n - o + r))
    }, blur: function (e, t) {
        t || clearTimeout(this.timer), this.active && (this.active.children("a").removeClass("ui-state-focus"), this.active = null, this._trigger("blur", e, {item: this.active}))
    }, _startOpening: function (e) {
        clearTimeout(this.timer), "true" === e.attr("aria-hidden") && (this.timer = this._delay(function () {
            this._close(), this._open(e)
        }, this.delay))
    }, _open: function (t) {
        var i = e.extend({of: this.active}, this.options.position);
        clearTimeout(this.timer), this.element.find(".ui-menu").not(t.parents(".ui-menu")).hide().attr("aria-hidden", "true"), t.show().removeAttr("aria-hidden").attr("aria-expanded", "true").position(i)
    }, collapseAll: function (t, i) {
        clearTimeout(this.timer), this.timer = this._delay(function () {
            var s = i ? this.element : e(t && t.target).closest(this.element.find(".ui-menu"));
            s.length || (s = this.element), this._close(s), this.blur(t), this.activeMenu = s
        }, this.delay)
    }, _close: function (e) {
        e || (e = this.active ? this.active.parent() : this.element), e.find(".ui-menu").hide().attr("aria-hidden", "true").attr("aria-expanded", "false").end().find("a.ui-state-active").removeClass("ui-state-active")
    }, collapse: function (e) {
        var t = this.active && this.active.parent().closest(".ui-menu-item", this.element);
        t && t.length && (this._close(), this.focus(e, t))
    }, expand: function (e) {
        var t = this.active && this.active.children(".ui-menu ").children(".ui-menu-item").first();
        t && t.length && (this._open(t.parent()), this._delay(function () {
            this.focus(e, t)
        }))
    }, next: function (e) {
        this._move("next", "first", e)
    }, previous: function (e) {
        this._move("prev", "last", e)
    }, isFirstItem: function () {
        return this.active && !this.active.prevAll(".ui-menu-item").length
    }, isLastItem: function () {
        return this.active && !this.active.nextAll(".ui-menu-item").length
    }, _move: function (e, t, i) {
        var s;
        this.active && (s = "first" === e || "last" === e ? this.active["first" === e ? "prevAll" : "nextAll"](".ui-menu-item").eq(-1) : this.active[e + "All"](".ui-menu-item").eq(0)), s && s.length && this.active || (s = this.activeMenu.children(".ui-menu-item")[t]()), this.focus(i, s)
    }, nextPage: function (t) {
        var i, s, n;
        return this.active ? (this.isLastItem() || (this._hasScroll() ? (s = this.active.offset().top, n = this.element.height(), this.active.nextAll(".ui-menu-item").each(function () {
            return i = e(this), 0 > i.offset().top - s - n
        }), this.focus(t, i)) : this.focus(t, this.activeMenu.children(".ui-menu-item")[this.active ? "last" : "first"]())), undefined) : (this.next(t), undefined)
    }, previousPage: function (t) {
        var i, s, n;
        return this.active ? (this.isFirstItem() || (this._hasScroll() ? (s = this.active.offset().top, n = this.element.height(), this.active.prevAll(".ui-menu-item").each(function () {
            return i = e(this), i.offset().top - s + n > 0
        }), this.focus(t, i)) : this.focus(t, this.activeMenu.children(".ui-menu-item").first())), undefined) : (this.next(t), undefined)
    }, _hasScroll: function () {
        return this.element.outerHeight() < this.element.prop("scrollHeight")
    }, select: function (t) {
        this.active = this.active || e(t.target).closest(".ui-menu-item");
        var i = {item: this.active};
        this.active.has(".ui-menu").length || this.collapseAll(t, !0), this._trigger("select", t, i)
    }})
})(jQuery);



