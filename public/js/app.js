/**
 * Hack to prevent scrolling to hash tags from bootstrap tabs
 */
if (location.hash) {               // do the test straight away
    window.scrollTo(0, 0);         // execute it straight away
    setTimeout(function() {
        window.scrollTo(0, 0);     // run it a bit later also for browser compatibility
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
        //three_col_tooltip_options.title = function(e) {
        three_col_tooltip_options.title = function() {
            return $('#three-col-footnote-' + $(this).html()).html();
        };

        $("a[id*='one_col_sup_']").tooltip(one_col_tooltip_options);
        $("a[id*='three_col_sup_']").tooltip(three_col_tooltip_options);

        /**
         * Remember active tab
         */
        $('#heading-tabs a,#dropdown-heading-tabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
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

        $('#nav-links-light-verse-left').on('click', function (e) {
            e.preventDefault();
            window.verse_number--;
            populateVerseModal(window.verse_number);
        });

        $('#nav-links-light-verse-right').on('click', function (e) {
            e.preventDefault();
            window.verse_number++;
            populateVerseModal(window.verse_number);
        });

        window.isTabShown = false;
        window.heading_tabs = $("#heading-tabs");

        /**
         * Populate keyword modal
         */
        $('.modal-trigger.keyword-modal').on('click', function (e) {
            var keyword_value = e.target.innerHTML;
            var keyword_verse_number = parseInt(e.target.id, 10);
            var keyword_color = $("#" + keyword_verse_number + '_keyword_color').html();
            $("#keyword_modal_header").attr('class', 'modal-header ' + keyword_color);
            var keyword_description = $("#" + keyword_verse_number + '_keyword_description').html();
            $("#keywordModalLabel").html(keyword_value);
            $("#keyword_modal_paragraph").html(keyword_description);
        });

        /**
         * Store the currently selected tab in the hash value and update nav links
         */
        $("ul.nav-pills > li > a,ul.dropdown-menu > li > a").on("shown.bs.tab", function (e) {
            var id = $(e.target).attr("href").substr(1);
            var hash = "#" + id;
            selectTab(hash);
            setNavHash(hash);
            window.isTabShown = true;
        });

        $("#index-aside").find("a").on('click', function (e) {
            window.location = $(e.currentTarget).attr("href");
        });

        /**
         * On load of the page: switch to the currently selected tab
         */
        var hash = window.location.hash;

        if(location.pathname != '/') {
            if (hash != "") {
                selectTab(hash);
                setNavHash(hash);
            } else {
                hash = "#one_col";
                selectTab(hash);
                setNavHash(hash);
            }
        } else {
            if (hash != "") {
                setNavHash(hash);
            } else {
                setNavHash("#one_col");
            }
        }

        window.heading_tabs.find('li > a').click(function (e) {
            var t = e.target;
            if (t.parentElement.href != undefined) {
                var parentHref = t.parentElement.href;
                var hashStart = parentHref.indexOf("#");
                if(hashStart != -1) {
                    var hash = parentHref.substr(hashStart);
                    selectTab(hash);
                }
                return false;
            } else {
                return true;
            }
        });

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
            location.hash = hash;
            $(hash).show();
        }

        function populateVerseModal(verse_number) {
            var chapter_number = $('#chapter-number').html();
            var kjv_text = $('#kjv_' + verse_number).html();
            var iit_text = $('#iit_' + verse_number).html();
            iit_text = iit_text.replace(/<a\b[^>]*>(.*?)<\/a>\s?/i,"");
            iit_text = "<p>" + iit_text + "</p>";
            var heb_text = $('#heb_' + verse_number).html();
            $('#kjv-modal-verse').html(kjv_text);
            $('#iit-modal-verse').html(iit_text);
            $('#heb-modal-verse').html(heb_text);
            $('#verse-modal-label').html('Isaiah ' + chapter_number + ':' + verse_number);
            updatePagination(verse_number);
        }

        function updatePagination(verse_number) {
            var left_pager = $('#nav-links-light-verse-left');
            var prev_verse = verse_number - 1;
            if(prev_verse >= 1) {
                left_pager.disable(false);
            } else {
                left_pager.disable(true);
            }
            var right_pager = $('#nav-links-light-verse-right');
            var verse_count = $('#verse-count').html();
            var next_verse = verse_number + 1;
            if(next_verse <= verse_count) {
                right_pager.disable(false);
            } else {
                right_pager.disable(true);
            }
        }

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