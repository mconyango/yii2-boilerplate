/**
 * Smart DropDown
 * @author Fred <mconyango@gmail.com>
 */

(function ($) {
    'use strict';
    var SELECT = function (options) {
        var defaultOptions = {
            selectId: undefined,
            linkWrapperId: undefined,
            inputWrapperId: undefined,
            inputId: undefined
        };

        this.options = $.extend({}, defaultOptions, options || {});
    };

    SELECT.prototype.run = function () {
        var $this = this
                , selector = '#' + $this.options.linkWrapperId + ' a'
                , field_wrapper = $('#' + $this.options.inputWrapperId)
                , field = $('#' + $this.options.inputId);

        var toggle_field = function () {
            field_wrapper.toggle();
        };

        var submit_form = function (e) {
            if (MyApp.utils.empty($(e).val()))
                return false;

            var url = $(selector).data('href')
                    , form = $('#' + $this.options.selectId).closest('form')
                    , data = form.serializeArray()
                , error_wrapper_selector = '#' + $this.options.inputWrapperId + ' .smart-select-error'
                    , error_wrapper = $(error_wrapper_selector);
            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        // error_wrapper.html(response.message).hide();
                        error_wrapper.hide();
                        MyApp.utils.populateDropDownList('#' + $this.options.selectId, response.data, response.selected);
                        field.val('');
                        field_wrapper.hide();
                    }
                    else {
                        MyApp.utils.display_model_errors(response.message, error_wrapper_selector);
                        error_wrapper.show();
                    }
                },
                beforeSend: function () {
                    $('#' + $this.options.linkWrapperId + ' i.fa-spin').show();
                    field.attr('readonly', 'readonly');
                },
                complete: function () {
                    $('#' + $this.options.linkWrapperId + ' i.fa-spin').hide();
                    field.removeAttr('readonly');
                },
                error: function (XHR) {
                    if (MyApp.DEBUG_MODE) {
                        MyApp.utils.showAlertMessage(XHR.responseText, 'error', error_wrapper_selector);
                        error_wrapper.show();
                    }
                }
            });
        };

        //click event
        $(selector).off('click.myapp.plugin.smartdropdown').on('click.myapp.plugin.smartdropdown', function (e) {
            e.preventDefault();
            toggle_field();
        });
        //on blur
        field.off('blur.myapp.plugin.smartdropdown').on('blur.myapp.plugin.smartdropdown', function () {
            submit_form(this);
        });
    };

    var PLUGIN = function (options) {
        var obj = new SELECT(options);
        obj.run();
    };

    MyApp.plugin.smartDropDown = PLUGIN;
}(jQuery));