jQuery(document).ready(function ($) {
    var templateCss = '';
    var $cacosTable = $('#custom_admin_color_scheme_row').closest('.form-table');

    function applyColorSchemePreview() {
        var colors = [];
        $('.custom-admin-color-scheme-picker').each(function() {
            colors.push($(this).wpColorPicker('color'));
        });

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

    // Initialize the "Enable" checkbox
    // var $enableCacos = $('#enable_cacos');
    // var isCacosEnabled = $enableCacos.is(':checked');
    //
    // $cacosTable.toggle(!isCacosEnabled);
    //
    // $('body').toggleClass('cacos-enabled', isCacosEnabled);

    // Show/hide the custom color scheme section based on the "Enable" checkbox
    // $enableCacos.on('change', function() {
    //     var isCacosEnabled = $(this).is(':checked');
    //     $cacosTable.toggle(!isCacosEnabled);
    //     $('body').toggleClass('cacos-enabled', isCacosEnabled);
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
