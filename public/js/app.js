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
        //$('.nav-left-disabled a').on('click', function(e) { e.preventDefault(); });
        //$('.nav-right-disabled a').on('click', function(e) { e.preventDefault(); });

        /**
         * Remember active tab
         */
        $('#heading-tabs a,#dropdown-heading-tabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        var isTabShown = false;

        /**
         * Store the currently selected tab in the hash value and update nav links
         */
        $("ul.nav-pills > li > a,ul.dropdown-menu > li > a").on("shown.bs.tab", function (e) {
            var id = $(e.target).attr("href").substr(1);
            window.location.hash = id;
            setNavHash('#' + id);
            window.isTabShown = true;
        });

        $("#index-aside a").on('click', function (e) {
            var id = $(e.currentTarget).attr("href").substr(1);
            window.location.hash = id;
            setNavHash('#' + id);
        });

        function setNavHash(hash) {
            var angle_left = $('.fa-angle-left');
            if(typeof angle_left.attr('href') != 'undefined') {
                angle_left.attr('href', angle_left.attr('href').split('#')[0] + hash);
            }
            var angle_right = $('.fa-angle-right');
            if(typeof angle_right.attr('href') != 'undefined') {
                angle_right.attr('href', angle_right.attr('href').split('#')[0] + hash);
            }
            var chapter_sel = $('.btn-chapter-sel');
            if(typeof chapter_sel.attr('href') != 'undefined') {
                chapter_sel.each(function(index){
                    $(this).attr('href', $(this).attr('href').split('#')[0] + hash);
                });
            }
        }

        /**
         * On load of the page: switch to the currently selected tab
         */
        var hash = window.location.hash;
        $('#heading-tabs a[href="' + hash + '"]').tab('show');

        if(isTabShown === false){
            if(hash === '') {
                //if(location.pathname != '/') {
                    window.location.hash = '#one_col';
                //}
                setNavHash('#one_col');
            } else {
                setNavHash(hash);
            }
        }

        /*var scrolled = false;

        $(window).scroll(function(){
            scrolled = true;
        });

        if ( window.location.hash && scrolled ) {
            $(window).scrollTop( 0 );
        }*/
    });//end document ready

}(this, jQuery, Backbone));

/*if (location.hash) {               // do the test straight away
    window.scrollTo(0, 0);         // execute it straight away
    setTimeout(function() {
        window.scrollTo(0, 0);     // run it a bit later also for browser compatibility
    }, 1);
}*/