(function($) {
    $(document).ready(function() {

        var templateCss = '';
        var customColorRow = $('#acos-row');
        var adminColorRow = $('tr.user-admin-color-wrap');
        customColorRow.insertAfter(adminColorRow);

        var checkbox = $('#enable_acos');
        //var colorSchemeTable = $('#acos-row').closest('.form-table');
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
            $.get(acos_data.plugin_url + '/dynamic-css.php?&template=true&v=1', function(data) {
                templateCss = data;
            });
        }

        $('.acos-picker').wpColorPicker({
            change: applyColorSchemePreview,
        });

        fetchTemplateCss();

        if($('#enable_acos:checked').length){
            applyColorSchemePreview();
        }


        if (isCustomSchemeEnabled) {
            //colorSchemeTable.show();
            $('body').addClass('acos-enabled');
        } else {
            //colorSchemeTable.hide();
            $('body').removeClass('acos-enabled');
        }

        checkbox.on('change', function() {
            if (this.checked) {
                //colorSchemeTable.show();
                $('body').addClass('acos-enabled');
            } else {
                //colorSchemeTable.hide();
                $('body').removeClass('acos-enabled');
            }
        });
    });
})(jQuery);
