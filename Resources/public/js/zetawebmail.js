(function($, jQuery){
    $.fn.zetaWebmail = function(options) {
        var settings = $.extend({}, $.fn.zetaWebmail.defaultOptions, options);

        return this.each(function() {
            // deactive default behavior
            $(this).find("tbody a.zetaMailViewLink").click(function() {
                var url = settings.url + "?";
                url = url + 'source=' + $(this).data('zeta-webmail-source');
                url = url + '&mailbox=' + $(this).data('zeta-webmail-mailbox');
                url = url + '&mail=' + $(this).data('zeta-webmail-mail');
                $(settings.targetFrame).attr('src', url);
                return false;
            });
        });
    };

    $.fn.zetaWebmail.defaultOptions = {
        targetFrame: "#zetaPreviewFrame",
        url: false
    };
})(jQuery,jQuery);

