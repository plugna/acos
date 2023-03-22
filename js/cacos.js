(function($) {
    $(document).ready(function() {

        var templateCss = '';
        var customColorRow = $('#cacos-row');
        var adminColorRow = $('tr.user-admin-color-wrap');
        customColorRow.insertAfter(adminColorRow);

        var checkbox = $('#enable_cacos');
        //var colorSchemeTable = $('#cacos-row').closest('.form-table');
        var isCustomSchemeEnabled = checkbox.prop('checked');

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


        if (isCustomSchemeEnabled) {
            //colorSchemeTable.show();
            $('body').addClass('cacos-enabled');
        } else {
            //colorSchemeTable.hide();
            $('body').removeClass('cacos-enabled');
        }

        checkbox.on('change', function() {
            if (this.checked) {
                //colorSchemeTable.show();
                $('body').addClass('cacos-enabled');
            } else {
                //colorSchemeTable.hide();
                $('body').removeClass('cacos-enabled');
            }
        });
    });
})(jQuery);
