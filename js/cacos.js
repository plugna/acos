jQuery(document).ready(function ($) {
    var templateCss = '';

    function applyColorSchemePreview() {
        var colors = [];
        $('.cacos-picker').each(function() {
            colors.push($(this).wpColorPicker('color'));
        });

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
        $.get(cacos_data.plugin_url + '/dynamic-css.php?&template=true&v=1', function(data) {
            templateCss = data;
        });
    }

    $('.cacos-picker').wpColorPicker({
        change: applyColorSchemePreview,
    });

    fetchTemplateCss();

    if($('#enable_cacos:checked').length){
        applyColorSchemePreview();
    }
});
