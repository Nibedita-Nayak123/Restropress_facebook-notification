jQuery(function( $ ) {
    $('body').on('click', '.rp-facebook-test-notification', function(e) {
        e.preventDefault();

        // Get the test message from the textarea
        var testMessage = $('.test_facebook_text textarea').val();

        // Clear any previous error classes
        $('.test_facebook_text textarea').removeClass('error');

        if (testMessage === '') {
            // Add an error class to the textarea if it's empty
            $('.test_facebook_text textarea').addClass('error');
        } else {
            // If the test message is not empty, proceed to send the notification

            // Replace 'RECIPIENT_USER_ID_HERE' with the actual recipient's Facebook user ID
            var recipientUserId = '6908975855847891';

            var data = {
                action: 'rp_facebook_test_notification_service', // The PHP action hook
                recipient_user_id: recipientUserId,
                test_message: testMessage
            };

            $.ajax({
                type: 'POST',
                data: data,
                url: rpfacebookAdmin.ajax_url, // Make sure 'rpfacebookAdmin.ajaxurl' is defined in your WordPress environment
                success: function(response) {
                    if (response.success) {
                        // Notification sent successfully
                        Swal({
                            type: 'info',
                            title: rpfacebookAdmin.message_sent,
                            text: rpfacebookAdmin.message_sent_text
                        });
                    } else {
                        // Handle errors
                        Swal({
                            type: 'error',
                            title: rpfacebookAdmin.message_error,
                            text: rpfacebookAdmin.message_error_text
                        });
                    }
                    return false;
                },
                error: function(response) {
                    console.log(response)
                }
            });
        }
    });
});
