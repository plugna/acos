jQuery(document).ready(function ($) {
    var templateCss = '';

    function applyColorSchemePreview() {
        var colors = [];
        $('.custom-admin-color-scheme-picker').each(function() {
            colors.push($(this).wpColorPicker('color'));
        });
//console.log('colors', colors);
        if (templateCss) {
            var css = templateCss;

            for (var i = 0; i < colors.length; i++) {
                css = css.replace(new RegExp('\\$' + (i + 1), 'g'), colors[i]);
            }

            $('#custom-color-scheme-preview').remove();
            $('head').append('<style id="custom-color-scheme-preview">' + css + '</style>');
        }
    }

    function fetchTemplateCss() {
        $.get(custom_admin_color_scheme_data.plugin_url + '/dynamic-css.php?dynamic-css=custom-admin-color-scheme&template=true', function(data) {
            templateCss = data;
            applyColorSchemePreview();
        });
    }

    $('.custom-admin-color-scheme-picker').wpColorPicker({
        change: applyColorSchemePreview,
    });

    fetchTemplateCss();
    // Initialize color picker for each input
    // $('.custom-admin-color-scheme-picker').wpColorPicker({
    //     change: function(event, ui) {
    //         var colors = [];
    //         $('.custom-admin-color-scheme-picker').each(function() {
    //             colors.push($(this).wpColorPicker('color'));
    //         });
    //
    //         $.ajax({
    //             url: custom_admin_color_scheme_data.ajax_url,
    //             type: 'POST',
    //             data: {
    //                 action: 'get_custom_color_scheme_css',
    //                 security: custom_admin_color_scheme_data.security,
    //                 colors: JSON.stringify(colors),
    //             },
    //             success: function(response) {
    //                 if (response.success) {
    //                     $('#custom-color-scheme-preview').remove();
    //                     $('head').append('<style id="custom-color-scheme-preview">' + response.data + '</style>');
    //                 } else {
    //                     console.error('Error fetching custom color scheme CSS:', response.data);
    //                 }
    //             },
    //             error: function(xhr, status, error) {
    //                 console.error('AJAX error:', error);
    //             },
    //         });
    //     },
    // });

    // Save the color scheme
    $('#save_custom_admin_color_scheme').on('click', function (e) {
        e.preventDefault();

        var colorScheme = JSON.stringify([
            $('#color-1').val(),
            $('#color-2').val(),
            $('#color-3').val(),
            $('#color-4').val(),
            $('#color-5').val(),
            $('#color-6').val(),
            $('#color-7').val(),
        ]);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_custom_admin_color_scheme',
                custom_admin_color_scheme: colorScheme,
                security: custom_admin_color_scheme_data.security,
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function () {
                alert('Error: Unable to save the color scheme.');
            },
        });
    });
});
