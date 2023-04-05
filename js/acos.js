(function($) {
    $(document).ready(function() {

        var templateCss = '';
        var customColorRow = $('#acos-row');
        var adminColorRow = $('tr.user-admin-color-wrap');
        customColorRow.insertAfter(adminColorRow);

        var checkbox = $('#enable_acos');
        var isCustomSchemeEnabled = checkbox.prop('checked');

        function applyColorSchemePreview() {
            var colors = [];
            $('.acos-picker').each(function() {
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
            $.get(acos_data.acos_css_template_url, function(data) {
                templateCss = data;
                if($('#enable_acos:checked').length){
                    applyColorSchemePreview();
                }
            });
        }

        $(".acos-picker").wpColorPicker({
            change: applyColorSchemePreview,
        });

        fetchTemplateCss();

        $("body").toggleClass('acos-enabled', isCustomSchemeEnabled);

        checkbox.on('change', function() {
            $("body").toggleClass('acos-enabled', this.checked);
        });
    });
})(jQuery);
