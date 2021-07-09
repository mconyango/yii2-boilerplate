/**
 * Ajax search for cgridview search
 * @author Fred <mconyango@gmail.com>
 * @type type
 */

(function($){
    'use strict';

    var SEARCH=function(options){
        var defaultOptions={
            form_id: undefined,
            grid_id: undefined,
            grid_type: 'cgridview',
            search_trigger: 'keypress',
        };

        this.options= $.extend({},defaultOptions,options||{});
    };

    //ajax search
    SEARCH.prototype.search=function(){
        var $this=this
            ,form= $('#' + $this.options.form_id);

        var _search=function(e){
            if ($this.options.grid_type === 'cgridview') {
                $.fn.yiiGridView.update($this.options.grid_id, {
                    data: $(e).serialize()
                });
                return false;
            }else{
                $.fn.yiiListView.update($this.options.grid_id, {
                    data: $(e).serialize()
                });
                return false;
            }
        };

        //event listeners
        form.on('submit.myapp.plugin.ajaxsearch',function(e){
            e.preventDefault();
            _search(this);
        });
        //key press trigger
        if ($this.options.search_trigger === 'keypress') {
            form.find('input[type="text"]').on('keyup.myapp.plugin.ajaxsearch', function () {
                if (!MyApp.utils.empty($(this).data('timer')))
                    clearTimeout($.data(this, 'timer'));
                var wait = setTimeout(function () {
                    form.trigger('submit');
                }, 500);
                $(this).data('timer', wait);
            });
        }
        //blur trigger
        form.find('input[type="text"]').on('blur.myapp.plugin.ajaxsearch', function () {
            form.trigger('submit');
        });
    };

    var PLUGIN=function(options){
        var obj=new SEARCH(options);
        obj.search();
    };

    MyApp.plugin.ajaxSearch=PLUGIN;
}(jQuery));