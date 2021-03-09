jQuery(document).ready(function ($) {

    $( document ).on( 'click', '#ufwp-delete-cache-submit', function(event) {
        $('#ufwp_delete_cache').val('1');
    });

    $( document ).on( 'click', '#ufwp-delete-images-cache-submit', function(event) {
        $('#ufwp_delete_images_cache').val('1');
    });

    $( document ).on( 'click', '#ufwp-reset-log-submit', function(event) {
        $('#ufwp_reset_log').val('1');
    });
});