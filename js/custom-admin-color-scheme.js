jQuery(document).ready(function ($) {
    var templateCss = '';
    ///var $cacosTable = $('#custom_admin_color_scheme_row').closest('.form-table');

    function applyColorSchemePreview() {
        var colors = [];
        $('.custom-admin-color-scheme-picker').each(function() {
            colors.push($(this).wpColorPicker('color'));
        });

        console.log('colors', colors);

        if (templateCss) {
            var css = templateCss;

            for (var i = 0; i < colors.length; i++) {
                css = css.replace(new RegExp('\\$' + (i + 1), 'g'), colors[i]);
            }

            $('#custom-color-scheme-preview').remove();
            $('body').append('<style id="custom-color-scheme-preview">' + css + '</style>');
        }
    }

    function fetchTemplateCss() {
        $.get(custom_admin_color_scheme_data.plugin_url + '/dynamic-css.php?&template=true&v=1', function(data) {
            templateCss = data;
        });
    }

    $('.custom-admin-color-scheme-picker').wpColorPicker({
        change: applyColorSchemePreview,
    });

    fetchTemplateCss();
    if($('#enable_cacos:checked').length){
        applyColorSchemePreview();
    }
});
