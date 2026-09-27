(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();
    
    
    // Initiate the wowjs
    new WOW().init();
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow').css('display', 'flex');
        } else {
            $('.back-to-top').fadeOut('slow').css('display', 'none');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });

    // Form submissions
    $('[data-api-form]').on('submit', function (event) {
        event.preventDefault();

        var form = this;
        var $form = $(form);
        var $button = $form.find('[type="submit"]').first();
        var originalText = $button.text();
        var originalHtml = $button.html();
        var endpoint = $form.attr('action');
        var statusId = $form.attr('data-api-form') + '-status';
        var $status = $form.find('.form-status').first();

        if (!$status.length) {
            $status = $('<div/>', {
                id: statusId,
                class: 'form-status small mt-3',
                'aria-live': 'polite'
            });
            $form.append($status);
        }

        $form.attr('aria-busy', 'true');
        $button.prop('disabled', true).addClass('is-loading');
        if (originalText) {
            $button.text('Sending...');
        }
        $status.removeClass('text-success text-danger').addClass('text-muted').text('Sending your request...');

        $.ajax({
            url: endpoint,
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        }).done(function (response) {
            if (response && response.success) {
                form.reset();
                $status.removeClass('text-muted text-danger').addClass('text-success').text(response.message);
                $form.find('.form-control').removeClass('is-invalid');
            } else {
                $status.removeClass('text-muted text-success').addClass('text-danger').text(response.message || 'Please check the form and try again.');
            }
        }).fail(function (xhr) {
            var response = xhr.responseJSON || {};
            $status.removeClass('text-muted text-success').addClass('text-danger').text(response.message || 'We could not send this right now. Please email brainmintacademy@gmail.com.');
        }).always(function () {
            $form.removeAttr('aria-busy');
            $button.prop('disabled', false).removeClass('is-loading');
            if (originalText) {
                $button.text(originalText);
            } else {
                $button.html(originalHtml);
            }
        });
    });
    
})(jQuery);

