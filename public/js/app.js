/**
 * Hack to prevent scrolling to hash tags from bootstrap tabs
 */
var is_citation = false;
var is_searched = false;
var citationDiv;
var iitDiv;
if (location.hash || location.pathname.match(/\/\d{1,2}/)) {               // do the test straight away
    window.scrollTo(0, 0);         // execute it straight away
    setTimeout(function() {
        if(is_citation === false && is_searched == false) {
            window.scrollTo(0, 0);     // run it a bit later also for browser compatibility
        } else if(is_citation === true) {
            $(window).scrollTop(citationDiv.offset().top);
        } else if (is_searched === true) {
            $(window).scrollTop(iitDiv.offset().top);
        }
    }, 1);
}

//alias the global object
//alias jQuery so we can potentially use other libraries that utilize $
//alias Backbone to save us on some typing
(function(exports, $, bb){

    //document ready
    $(function(){

        /**
         ***************************************
         * Cached Globals
         ***************************************
         */
        var $window, $body, $document;

        $window  = $(window);
        $document = $(document);
        $body   = $('body');

        /**
         * Reset Bootstrap tooltips
         */
        $('.ttip').tooltip('hide');

        /**
         * Reset Bootstrap modals
         */
        $('.modal-trigger').modal('hide');

        /**
         * Reset Bootstrap popovers
         */
        $('[data-toggle="popover"]').popover();
        $body.on('click', function (e) {
            $('[data-toggle="popover"]').each(function () {
                if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                    $(this).popover('hide');
                }
            });
        });

        var one_col_tooltip_options = {
            animation: true,
            container: false,
            //delay: { show: 500, hide: 100 },
            delay: 0,
            html: true,
            placement: 'right',
            selector: false,
            template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            //title: function(e) {
            title: function() {
                var sub = $(this).html();
                return $('#one-col-footnote-' + sub).html();
            },
            trigger: 'hover focus',
            viewport: { selector: 'body', padding: 0 }
        };

        var three_col_tooltip_options = one_col_tooltip_options;
        three_col_tooltip_options.title = function() {
            return $('#three-col-footnote-' + $(this).html()).html();
        };
        var commentary_tooltip_options = one_col_tooltip_options;
        commentary_tooltip_options.title = function() {
            return $('#commentary-footnote-' + $(this).html()).html();
        };

        $("a[id*='one_col_sup_']").tooltip(one_col_tooltip_options);
        $("a[id*='three_col_sup_']").tooltip(three_col_tooltip_options);
        $("a[id*='commentary_sup_']").tooltip(commentary_tooltip_options);

        $("form").submit(function(e) {
            e.preventDefault();//prevent the form from actually submitting
            var search_term = $(this).find("input[name=search-box]").val();
            if (search_term.length) {
                window.location = '/search/' + search_term;
            } else {
                $(this).find("span[name=search-error]").text("Not valid!").show().fadeOut( 1000 );
            }
        });

        $('.heading-nav-left.disabled,.heading-nav-right.disabled').on('click', function(e) {
            e.preventDefault();
            return false;
        });

        window.verse_number = 1;

        /**
         * Populate verse modal
         */
        $('.modal-trigger.verse-number').on('click', function (e) {
            window.verse_number = $(e.target).html();
            populateVerseModal(window.verse_number);
        });

        /**
         * Page verse modal left
         */
        $('#nav-links-light-verse-left').on('click', function (e) {
            e.preventDefault();
            window.verse_number = chapter_verse_order[window.verse_index - 1];
            populateVerseModal(window.verse_number);
        });

        /**
         * Page verse modal right
         */
        $('#nav-links-light-verse-right').on('click', function (e) {
            e.preventDefault();
            window.verse_number = chapter_verse_order[window.verse_index + 1];
            populateVerseModal(window.verse_number);
        });

        function populateVerseModal(verse_number) {
            var chapter_number = $('#chapter-number').html();
            var iit_text = $('#iit_' + verse_number).html();
            var heb_text = $('#heb_' + verse_number).html();
            var commentary_text = $('.commentary_' + verse_number).html();
            var kjv_text = $('#kjv_' + verse_number).html();
            if (chapter_number == 40 && verse_number == 41.7) {
                iit_text = '<p><span class="poetry"><span><sup>c</sup> The artisan encourages the smith,<span class="indent">and he who beats with a hammer</span><span class="indent"><i>urges</i> him who pounds the anvil.</span>They say of the welding, It is good,<span class="indent">though they fasten it with riveting </span><span class="indent">that it may not come loose.</span></span></span></p>';
                heb_text = 'וַיְחַזֵּק חָרָשׁ אֶת־צֹרֵף מַחֲלִיק פַּטִּישׁ אֶת־הוֹלֶם פָּעַם אֹמֵר לַדֶּבֶק טוֹב הוּא וַיְחַזְּקֵהוּ בְמַסְמְרִים לֹא יִמּוֹט ׃';
                commentary_text = '<p>(40:18–19; 41:7*; 40:20) Almost the first thing the nations do on the earth is to corrupt themselves, diverting their attention from the true God to images and idols. Isaiah’s satire on idolaters in this passage shows the futility of creating substitutes for humanity’s Creator. As these false gods are the antithesis of the true God, they are the main reason people become spiritually blind and lose understanding of him (Isaiah 27:9–11; 44:9–20). Such gods can’t save them in Jehovah’s Day of Judgment (Isaiah 45:20; 46:1–8). If the nations themselves are but chaos (vv 15–17), then how much more so the images and idols they invent?</p>';
                kjv_text = 'So the carpenter encouraged the goldsmith, and he that smootheth with the hammer him that smote the anvil, saying, It is ready for the sodering: and he fastened it with nails, that it should not be moved.';
            } else if(chapter_number == 41 && verse_number == 7) {
                commentary_text = '<p>See: 40:19 Verse appears out of sequence in text.</p>';
            } else {
                iit_text = iit_text.replace(/<a\b[^>]*>(\w+)<\/a>/ig, "$1");
                iit_text = iit_text.replace(/<a\b[^>]*>(\D)<\/a>\s?/ig, "$1");
                iit_text = iit_text.replace(/<a\b[^>]*>\d{1,2}<\/a>\s?/ig, "");
                iit_text = iit_text.replace(/<div class="spacer"><\/div>/ig, "<span class=\"spacer\"></span>");
                iit_text = iit_text.replace(/<div class="prose-spacer"><\/div>/ig, "<span class=\"prose-spacer\"></span>");
                iit_text = "<p>" + iit_text + "</p>";
            }
            $('#kjv-modal-verse').html(kjv_text);
            $('#iit-modal-verse').html(iit_text);
            $('#heb-modal-verse').html(heb_text);
            var commentary_modal_verse = $('#commentary-modal-verse');
            commentary_modal_verse.html(commentary_text);
            var subject_verses = commentary_modal_verse.children("div").html();
            commentary_modal_verse.children().next('p').first().prepend(subject_verses + ' ');
            var modal_label = '';
            if(chapter_number == 40 && verse_number == 41.7) {
                modal_label = 'Isaiah ' + verse_number;
            } else {
                modal_label = 'Isaiah ' + chapter_number + ':' + verse_number;
            }
            $('#verse-modal-label').html(modal_label);
            updatePagination(parseFloat(verse_number));
        }

        function updatePagination(verse_number) {
            window.chapter_verse_order = $('#chapter-verse-order').text().split(',');
            window.verse_index = chapter_verse_order.indexOf(verse_number.toString());
            var left_pager = $('#nav-links-light-verse-left');
            var prev_verse = verse_number - 1;
            if(prev_verse >= chapter_verse_order[0]) {
                left_pager.disable(false);
            } else {
                left_pager.disable(true);
            }
            //var next_verse = verse_number + 1;
            var right_pager = $('#nav-links-light-verse-right');
            if(verse_index != chapter_verse_order.length - 1) {
                right_pager.disable(false);
            } else {
                right_pager.disable(true);
            }
        }

        /**
         * Populate keyword modal
         */
        $('.modal-trigger.keyword-modal').on('click', function (e) {
            var target = e.target;
            var keyword_value = target.innerHTML;
            var section = target.name;
            var keyword_id = parseInt(target.id, 10);
            var keyword_color = $("#" + keyword_id + '_' + section + '_keyword_color').html();
            $("#keyword_modal_header").attr('class', 'modal-header ' + keyword_color);
            var keyword_description = $("#" + keyword_id + '_' + section + '_keyword_description').html();
            $("#keywordModalLabel").html(keyword_value);
            $("#keyword_modal_paragraph").html(keyword_description);
        });

        var is_cs_toggled = $.cookie('is_cs_toggled');
        if(is_cs_toggled != undefined) {
            if(is_cs_toggled == 'true') {
                $(".cs-top").toggle();
            }
        } else {
            is_cs_toggled = 'false';
            $.cookie('is_cs_toggled', is_cs_toggled);
        }

        $(".hide-chapter-selection").on('click', function (e) {
            //$(".chapter-selection").slideToggle('fast');
            $(".cs-top").toggle();
            e.preventDefault();
            if(is_cs_toggled != undefined) {
                if(is_cs_toggled == 'true') {
                    is_cs_toggled = 'false';
                } else {
                    is_cs_toggled = 'true';
                }
            } else {
                is_cs_toggled = 'false';
            }
            $.cookie('is_cs_toggled', is_cs_toggled);
        });

        $(".nav-read-isaiah").find("a").on('click', function (e) {
            window.location = $(e.currentTarget).attr("href");
        });

        window.isTabShown = false;
        window.heading_tabs = $("#heading-tabs");

        /**
         * Store the currently selected tab in the hash value and update nav links
         */
        $("ul.nav-pills > li > a,ul.dropdown-menu > li > a[href^='#']").on('click', function (e) {
            if (location.pathname != '/') {
                var id;
                if($(e.target).parent().attr("href") != undefined) {
                    id = $(e.target).parent().attr("href").substr(1);
                } else {
                    if($(e.target).attr("href") != undefined) {
                        id = $(e.target).attr("href").substr(1);
                    } else {
                        alert('heading-chapters nav tab id cannot be found!');
                    }
                }
                if(id === undefined) {
                    id = $(e.target).attr("href").substr(1);
                }
                var hash = "#" + id;
                selectTab(hash);
                setNavHash(hash);
                window.isTabShown = true;
            } else {
                window.location = $(e.currentTarget).attr("href");
            }
            e.preventDefault();
        });

        /**
         * On load of the page: switch to the currently selected tab
         */
        var hash = window.location.hash;

        if(location.pathname.indexOf('/concordance') == -1 && location.pathname.indexOf('/search') == -1) {
            if (location.pathname != '/') {
                if (hash != "") {
                    selectTab(hash);
                    setNavHash(hash);
                } else {
                    hash = "#one_col";
                    selectTab(hash);
                    setNavHash(hash);
                }
            } else {
                /*if (hash != "") {
                    setNavHash(hash);
                } else {
                    setNavHash("#one_col");
                }*/
            }
        } else if(location.pathname.indexOf('/search') > -1) {
            var search_parts = location.pathname.split('/')[2].replace(/%20/, ' ').split(' ');
            $('ol').children().each(function() {
                if(this.innerHTML != undefined) {
                    search_parts.forEach(function(value, index) {
                        var replacement = new RegExp('(.*- <)', 'ig');
                        this.innerHTML = this.innerHTML.replace(replacement, function(match, contents, offset, s) {
                            var replacement = new RegExp('(' + value + ')', 'i');
                            return match.replace(replacement, '<span class="highlight">$1</span>');
                        }, value);
                    }, this);
                }
            });
        }

        function setNavHash(hash) {
            var angle_left_top = $('#nav-left-top.fa-angle-left');
            if(typeof angle_left_top.attr('href') != 'undefined') {
                angle_left_top.attr('href', angle_left_top.attr('href').split('#')[0] + hash);
            }
            var angle_left_bottom = $('#nav-left-bottom.fa-angle-left');
            if(typeof angle_left_bottom.attr('href') != 'undefined') {
                angle_left_bottom.attr('href', angle_left_bottom.attr('href').split('#')[0] + hash);
            }
            var angle_right_top = $('#nav-right-top.fa-angle-right');
            if(typeof angle_right_top.attr('href') != 'undefined') {
                angle_right_top.attr('href', angle_right_top.attr('href').split('#')[0] + hash);
            }
            var angle_right_bottom = $('#nav-right-bottom.fa-angle-right');
            if(typeof angle_right_bottom.attr('href') != 'undefined') {
                angle_right_bottom.attr('href', angle_right_top.attr('href').split('#')[0] + hash);
            }
            var chapter_sel = $('.btn-chapter-sel');
            if(typeof chapter_sel.attr('href') != 'undefined') {
                chapter_sel.each(function(){
                    $(this).attr('href', $(this).attr('href').split('#')[0] + hash);
                });
            }
        }

        function selectTab(hash) {
            var heading_tabs_li = window.heading_tabs.find('li');
            heading_tabs_li.removeClass('active');
            heading_tabs_li.find("a[href$=" + hash + "]").closest("li").addClass("active");
            $('.tab-content > .tab-pane').hide();
            window.location.hash = hash;
            $(hash).show();
        }

        var getQueryStringKey = function(key) {
            return getQueryStringAsObject()[key];
        };

        var getQueryStringAsObject = function() {
            var b, cv, e, k, ma, sk, v, r = {},
                d = function (v) { return decodeURIComponent(v).replace(/\+/g, " "); }, //# d(ecode) the v(alue)
                q = window.location.search.substring(1),
                s = /([^&;=]+)=?([^&;]*)/g //# original regex that does not allow for ; as a delimiter:   /([^&=]+)=?([^&]*)/g
                ;

            //# ma(make array) out of the v(alue)
            ma = function(v) {
                //# If the passed v(alue) hasn't been setup as an object
                if (typeof v != "object") {
                    //# Grab the cv(current value) then setup the v(alue) as an object
                    cv = v;
                    v = {};
                    v.length = 0;

                    //# If there was a cv(current value), .push it into the new v(alue)'s array
                    //#     NOTE: This may or may not be 100% logical to do... but it's better than loosing the original value
                    if (cv) { Array.prototype.push.call(v, cv); }
                }
                return v;
            };

            //# While we still have key-value e(ntries) from the q(uerystring) via the s(earch regex)...
            while (e = s.exec(q)) { //# while((e = s.exec(q)) !== null) {
                //# Collect the open b(racket) location (if any) then set the d(ecoded) v(alue) from the above split key-value e(ntry)
                b = e[1].indexOf("[");
                v = d(e[2]);

                //# As long as this is NOT a hash[]-style key-value e(ntry)
                if (b < 0) { //# b == "-1"
                    //# d(ecode) the simple k(ey)
                    k = d(e[1]);

                    //# If the k(ey) already exists
                    if (r[k]) {
                        //# ma(make array) out of the k(ey) then .push the v(alue) into the k(ey)'s array in the r(eturn value)
                        r[k] = ma(r[k]);
                        Array.prototype.push.call(r[k], v);
                    }
                    //# Else this is a new k(ey), so just add the k(ey)/v(alue) into the r(eturn value)
                    else {
                        r[k] = v;
                    }
                }
                //# Else we've got ourselves a hash[]-style key-value e(ntry)
                else {
                    //# Collect the d(ecoded) k(ey) and the d(ecoded) sk(sub-key) based on the b(racket) locations
                    k = d(e[1].slice(0, b));
                    sk = d(e[1].slice(b + 1, e[1].indexOf("]", b)));

                    //# ma(make array) out of the k(ey)
                    r[k] = ma(r[k]);

                    //# If we have a sk(sub-key), plug the v(alue) into it
                    if (sk) { r[k][sk] = v; }
                    //# Else .push the v(alue) into the k(ey)'s array
                    else { Array.prototype.push.call(r[k], v); }
                }
            }

            //# Return the r(eturn value)
            return r;
        };

        var citationQueryString = getQueryStringKey('citation');
        if(citationQueryString != undefined) {
            var citationLink = $('a[href*=' + citationQueryString + ']');
            if(citationLink != undefined) {
                if(location.pathname.indexOf('/concordance') == -1) {
                    citationDiv = citationLink.parent().closest('div');
                    //citationDiv = citationLink.parent();
                    citationLink.addClass('highlight');
                } else {
                    citationDiv = citationLink.parent();
                    if (citationDiv != undefined) {
                            citationDiv.addClass('highlight');
                    }
                }
                is_citation = true;
            }
        }

        var verseQueryString = getQueryStringKey('verse');
        var searchQueryString = getQueryStringKey('search');
        if(verseQueryString != undefined && searchQueryString != undefined) {
            var verse_number = verseQueryString;
            var search = searchQueryString;
            iitDiv = $('#iit_' + verse_number).parent();
            if(location.pathname.indexOf('/concordance') == -1) {
                if (hash == "#one_col") {
                    iitDiv.children().each(function() {
                        if(this.innerHTML != undefined) {
                            var search_parts = search.split(' ');
                            search_parts.forEach(function(value, index) {
                                var replacement = new RegExp('(' + value + ')', 'ig');
                                this.innerHTML = this.innerHTML.replace(replacement, "<span class='highlight'>$1</span>");
                            }, this);
                        }
                    });
                    $(window).scrollTop(iitDiv.offset().top);
                    is_searched = true;
                }
            }
        }

        $.fn.outerHTML = function(s) {
            return s
                ? this.before(s).remove()
                : jQuery("<p>").append(this.eq(0).clone()).html();
        };

        $.fn.extend({
            disable: function(state) {
                return this.each(function() {
                    var $this = $(this);
                    $this.toggleClass('disabled', state);
                });
            }
        });
    });//end document ready

}(this, jQuery, Backbone));