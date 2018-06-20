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

        /**
         * TODO: Disabled left/right pager
         */
        $('a.nav-right-disabled').click(function() { return false; });
        $('a.nav-left-disabled').click(function() { return false; });


        /**
         * Remember active tab
         */
        $('#heading-tabs a,#dropdown-heading-tabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        /**
         * Store the currently selected tab in the hash value and update nav links
         */
        $("ul.nav-pills > li > a,ul.dropdown-menu > li > a").on("shown.bs.tab", function (e) {
            var id = $(e.target).attr("href").substr(1);
            window.location.hash = id;
            var angle_left = $('.fa-angle-left');
            if(typeof angle_left.attr('href') != 'undefined') {
                angle_left.attr('href', angle_left.attr('href').split('#')[0] + '#' + id);
            }
            var angle_right = $('.fa-angle-right');
            if(typeof angle_right.attr('href') != 'undefined') {
                angle_right.attr('href', angle_right.attr('href').split('#')[0] + '#' + id);
            }
            var chapter_sel = $('.btn-chapter-sel');
            if(typeof chapter_sel.attr('href') != 'undefined') {
                chapter_sel.each(function(index){
                    $(this).attr('href', $(this).attr('href').split('#')[0] + '#' + id);
                });
            }
        });

        /**
         * On load of the page: switch to the currently selected tab
         * @type {string}
         */
        var hash = window.location.hash;
        $('#heading-tabs a[href="' + hash + '"]').tab('show');
    });//end document ready

}(this, jQuery, Backbone));