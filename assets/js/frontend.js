jQuery(document).ready(function($) {

    // Registration
    $('#yem-registration-form').on('submit', function(e) {
        e.preventDefault();
        $('#yem-registration-errors').html('');

        $.ajax({
            type: 'POST',
            url: YUSOCIAL_EM.ajaxurl,
            data: {
                action: 'yem_ajax_register',
                yem_username: $('input[name="yem_username"]').val(),
                yem_email: $('input[name="yem_email"]').val(),
                yem_password: $('input[name="yem_password"]').val(),
                nonce: YUSOCIAL_EM.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.redirect;
                } else {
                    $('#yem-registration-errors').html(response.data);
                }
            }
        });
    });

    // Delete user
    $('.yem-delete-user-btn').on('click', function(e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this user?')) return;

        const button = $(this);
        const userId = button.data('user-id');

        $.ajax({
            url: YUSOCIAL_EM.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'yem_delete_user',
                user_id: userId,
                nonce: YUSOCIAL_EM.nonce
            },
            success: function(response) {
                if (response.success) {
                    // $('#yem-status-message').html('<p style="color:green;">' + response.data.message + '</p>');
                    $('#user-row-' + userId).fadeOut(300, function () {
                        $(this).remove();
                    });
                } else {
                    $('#yem-status-message').html('<p style="color:red;">' + response.data.message + '</p>');
                }
                               
            },
            error: function() {
                $('#user-row-' + userId).fadeOut(300, function () {
                    $(this).remove();
                });
            }
        });
    });
});