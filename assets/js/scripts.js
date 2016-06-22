jQuery(document).ready(function ($) {

    $('[data-udemy-click-tracking="true"] a, a[data-udemy-click-tracking="true"]').on( "click", function( event ) {

        // Debug
        //event.preventDefault();

        var link = $(this);

        // Check if link is marked as prevent tracking
        if (typeof link.data('udemy-prevent-click-tracking') !== 'undefined')
            return;

        // If clicked link has container attribute take it, otherwise search for the ancestor
        var container = ( $(this).attr('data-udemy-click-tracking') ) ? $(this) : $(this).closest('[data-udemy-click-tracking="true"]');

        var label = false;

        if (typeof container.data('udemy-course-title') !== 'undefined') {
            label = container.data('udemy-course-title');
        }

        if ( !label )
            return;

        var category = 'udemy-link';
        var action = 'click';

        // Google Analytics old
        if (typeof (_gaq) !== "undefined") {
            _gaq.push(['_trackEvent', category, action, label]);
            // Google Analytics new
        } else if (typeof (ga) !== "undefined") {
            ga('send', 'event', category, action, label);
            // Piwik
        } else if (typeof (_paq) !== "undefined") {
            _paq.push(['trackEvent', category, action, label]);
        }
    });

});