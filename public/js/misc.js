(function($) {
    'use strict';
    $(function() {
        var sidebar = $('.sidebarLinks');

        //Add active class to nav-link based on url dynamically
        //Active class can be hard coded directly in html file also as required

        for (var url = window.location, element = $(".sidebarLinks .nav-item a").filter(function() {

                var url = window.location;
                var urlparts = url.toString().split('/');
                var request = urlparts[urlparts.indexOf('admin') + 1];

                var url1 = this.href;
                var urlparts1 = url1.toString().split('/');
                var request1 = urlparts1[urlparts1.indexOf('admin') + 1];
                if (request1 != undefined && request != undefined)
                    return (request1.includes(request) || request.includes(request1));
                else
                    return this.href == url;
            }).addClass("active").parent().addClass("active");;) {
            if (!element.is("li")) break;
            if (element.parent().parent().hasClass('sidebar-offcanvas')) break;
            element = element.parent().addClass("in").parent().addClass("active");
        }

        //Close other submenu in sidebar on opening any

        sidebar.on('show.bs.collapse', '.collapse', function() {
            sidebar.find('.collapse.show').collapse('hide');
        });


        //Change sidebar and content-wrapper height
        applyStyles();

        function applyStyles() {
            //Applying perfect scrollbar
            if ($('.scroll-container').length) {
                const ScrollContainer = new PerfectScrollbar('.scroll-container');
            }
        }

        //checkbox and radios
        $(".form-check label,.form-radio label").append('<i class="input-helper"></i>');


        $(".purchace-popup .popup-dismiss").on("click", function() {
            $(".purchace-popup").slideToggle();
        });
    });
})(jQuery);