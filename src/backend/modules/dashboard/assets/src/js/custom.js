MyApp.modules = MyApp.modules || {};
(function ($) {
    "use strict";
    var DASHBOARD = function (options) {
        let defaultOptions = {
            filterFormSelector: '#map-filter-form',
            mapWrapperSelector: '#dashboard-map-wrapper'
        };
        this.options = $.extend({}, defaultOptions, options || {});
    }

    DASHBOARD.prototype.loadMap = function () {
        let $this = this;

        let _load = function (e) {
            let form = $($this.options.filterFormSelector),
                url = form.attr('action');
            $.ajax({
                url: url,
                type: 'get',
                dataType: 'html',
                data: form.serialize(),
                success: function (data) {
                    $($this.options.mapWrapperSelector).html(data);
                },
                beforeSend: function (xhr) {
                    $($this.options.mapWrapperSelector).html('<h1 class="text-center text-warning" style="margin-top:50px;"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading...</h1>');
                },
                error: function (xhr) {
                    if (MyApp.DEBUG_MODE) {
                        console.log(xhr);
                    }
                }
            })
        }

        //on page load
        //on click
        $($this.options.filterFormSelector).find('button[type="submit"]').on('click', function (event) {
            event.preventDefault();
            _load(this);
        }).trigger('click');

    }

    MyApp.modules.dashboard = function (options) {
        let obj = new DASHBOARD(options);
        obj.loadMap();
    }

}(jQuery));