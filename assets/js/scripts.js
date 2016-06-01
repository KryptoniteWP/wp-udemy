jQuery( document ).on( 'click', '.edd-envato-customers-form-submit', function(event) {

    event.preventDefault();

    // Set variables
    var purchaseCodeForm = jQuery('#edd_envato_customers_form');
    var purchaseCodeInput = jQuery('#edd_envato_customers_form_purchase_code');
    var purchaseCodeContainer = purchaseCodeInput.parents('p');
    var purchaseCode = purchaseCodeInput.val();
    var purchaseCodeFormResponse = jQuery('#edd_envato_customers_form_response');

    // Clear response from possibly last try
    purchaseCodeFormResponse.empty();

    // Check if purchase code was entered
    if( !purchaseCode ) {
        purchaseCodeContainer.addClass('warning');
        return false;
    }

    if ( purchaseCodeContainer.hasClass('warning') )
        purchaseCodeContainer.removeClass('warning');

    // Collect form actions
    var formActions = new Array();

    if ( purchaseCodeForm.attr('data-envato-verification') )
        formActions.push('verification');

    if ( purchaseCodeForm.attr('data-envato-discount') )
        formActions.push('discount');

    // Start visually progress
    purchaseCodeForm.addClass('loading');

    // Request
    jQuery.ajax({
        url : edd_envato_customers_post.ajax_url,
        type : 'post',
        data : {
            action : 'post_edd_envato_customers_redeem_code',
            purchase_code : purchaseCode,
            form_actions : formActions
        },
        success : function( response ) {

            // Stop visually progress
            purchaseCodeForm.removeClass('loading');

            // Hide form if success
            if ( response.indexOf("success") >= 0 ) {
                jQuery('#edd_envato_customers_form').hide();
            }

            // Print response
            purchaseCodeFormResponse.html( response );
        }

    });
});

/*
 * Checkout
 */
jQuery( document ).on( 'click', '#edd-envato-customers-checkout-link', function(event) {

    event.preventDefault();

    jQuery('#edd-envato-customers-checkout-container').addClass('active');
});