/**
 *Module based js functions
 *@author Fred <mconyango@gmail.com>
 */
MyApp.modules = MyApp.modules || {};
MyApp.modules.conf = {};
//numbering format
(function ($) {
    'use strict';

    var NF = function (options) {
        var defaultOptions = {
            form: 'my-modal-form',
            nextNumberSelector: '#numberingformat-next_number',
            minDigitsSelector: '#numberingformat-min_digits',
            prefixSelector: '#numberingformat-prefix',
            suffixSelector: '#numberingformat-suffix',
            previewSelector: '#numberingformat-preview'
        };
        this.options = $.extend({}, defaultOptions, options || {});
    };

    NF.prototype.updatePreview = function () {
        var $this = this;
        var update_preview = function () {
            var next_number = parseInt($($this.options.nextNumberSelector).val())
                , min_digits = parseInt($($this.options.minDigitsSelector).val())
                , prefix = $($this.options.prefixSelector).val()
                , suffix = $($this.options.suffixSelector).val()
                , template = '{{prefix}}{{number}}{{suffix}}';
            var number = MyApp.utils.str_pad(next_number, min_digits, "0", 'STR_PAD_LEFT');
            var preview = template.replace('{{prefix}}', prefix);
            preview = preview.replace('{{number}}', number);
            preview = preview.replace('{{suffix}}', suffix);
            $($this.options.previewSelector).val(preview);
        };

        //onblur
        $('#' + $this.options.form).find('input.update-preview').off('blur.myapp.settings').on('blur.myapp.settings', function () {
            update_preview();
        }).trigger('blur');
    };

    var PLUGIN = function (options) {
        var obj = new NF(options);
        obj.updatePreview();
    };

    MyApp.modules.conf.numberingFormat = PLUGIN;
}(jQuery));

//notification setting
(function ($) {
    'use strict';

    var NOTIF = function (options) {
        var defaultSettings = {
            modelClass: 'notiftypes',
            enableInternalNotificationField: 'enable_internal_notification',
            enableEmailNotificationField: 'enable_email_notification',
            enableSmsNotificationField: 'enable_sms_notification',
            internalTemplateField: 'template',
            emailTemplateIdField: 'email_template_id',
            smsTemplateIdField: 'sms_template_id',
            notifyAllUsersField: 'notify_all_users',
            usersField: 'users',
            rolesField: 'roles',
            emailField: 'email',
            phoneField: 'phone',
        }

        this.options = $.extend({}, defaultSettings, options || {});
    }

    NOTIF.prototype.toggleInternal = function () {
        var $this = this;
        var selector = MyApp.utils.getActiveFormFieldSelector($this.options.modelClass, $this.options.enableInternalNotificationField)
            ,
            internalTemplateSelector = MyApp.utils.getActiveFormFieldContainerSelector($this.options.modelClass, $this.options.internalTemplateField);
        var _toggle = function (e) {
            if ($(e).is(':checked')) {
                $(internalTemplateSelector).show();
            } else {
                $(internalTemplateSelector).hide();
            }
        }

        //on page load
        _toggle(selector);

        //onclick
        $(selector).on('click', function (e) {
            _toggle(this);
        });

    }

    NOTIF.prototype.toggleEmail = function () {
        var $this = this;
        var selector = MyApp.utils.getActiveFormFieldSelector($this.options.modelClass, $this.options.enableEmailNotificationField)
            ,
            emailTemplateSelector = MyApp.utils.getActiveFormFieldContainerSelector($this.options.modelClass, $this.options.emailTemplateIdField)
            ,
            emailSelector = MyApp.utils.getActiveFormFieldContainerSelector($this.options.modelClass, $this.options.emailField);
        var _toggle = function (e) {
            var containers = emailTemplateSelector + ',' + emailSelector;
            if ($(e).is(':checked')) {
                $(containers).show();
            } else {
                $(containers).hide();
            }
        }

        //on page load
        _toggle(selector);

        //onclick
        $(selector).on('click', function (e) {
            _toggle(this);
        });

    }

    NOTIF.prototype.toggleSms = function () {
        var $this = this;
        var selector = MyApp.utils.getActiveFormFieldSelector($this.options.modelClass, $this.options.enableSmsNotificationField)
            ,
            smsTemplateSelector = MyApp.utils.getActiveFormFieldContainerSelector($this.options.modelClass, $this.options.smsTemplateIdField)
            ,
            phoneSelector = MyApp.utils.getActiveFormFieldContainerSelector($this.options.modelClass, $this.options.phoneField);
        var _toggle = function (e) {
            var containers = smsTemplateSelector + ',' + phoneSelector;
            if ($(e).is(':checked')) {
                $(containers).show();
            } else {
                $(containers).hide();
            }
        }

        //on page load
        _toggle(selector);

        //onclick
        $(selector).on('click', function (e) {
            _toggle(this);
        });

    }

    NOTIF.prototype.toggleNotifyAll = function () {
        var $this = this;
        var selector = MyApp.utils.getActiveFormFieldSelector($this.options.modelClass, $this.options.notifyAllUsersField)
            ,
            usersSelector = MyApp.utils.getActiveFormFieldContainerSelector($this.options.modelClass, $this.options.usersField)
            ,
            rolesSelector = MyApp.utils.getActiveFormFieldContainerSelector($this.options.modelClass, $this.options.rolesField);
        var _toggle = function (e) {
            var c = usersSelector + ',' + rolesSelector;
            if ($(e).is(':checked')) {
                $(c).hide();
            } else {
                $(c).show();
            }
        }

        //on page load
        _toggle(selector);

        //onclick
        $(selector).on('click', function (e) {
            _toggle(this);
        });

    }

    var PLUGIN = function (options) {
        var obj = new NOTIF(options);
        obj.toggleInternal();
        obj.toggleEmail();
        obj.toggleNotifyAll();
        obj.toggleSms();
    }

    MyApp.modules.conf.notificationSettings = PLUGIN;

}(jQuery));

//password settings
(function ($) {
    "use strict";
    let FORM = function (options) {
        let defaultSettings = {
            formSelector: '#settings-form',
            usePresetField: '#passwordsettings-usepreset',
            presetField: '#passwordsettings-preset',
            presetFieldWrapper: '.field-passwordsettings-preset'
        };
        this.options = $.extend({}, defaultSettings, options || {});
    }

    FORM.prototype = {
        togglePreset: function () {
            let $this = this,
                selector = $this.options.usePresetField;
            let _toggle = function (e) {
                let isChecked = $(e).is(':checked');
                if (isChecked) {
                    $($this.options.formSelector).find('input[type="number"]').attr('readonly', 'readonly');
                    $($this.options.presetFieldWrapper).show();
                } else {
                    $($this.options.formSelector).find('input[type="number"]').removeAttr('readonly');
                    $($this.options.presetFieldWrapper).hide();
                }
            }

            //on page load
            _toggle(selector);
            //on click
            $(selector).on('click', function (e) {
                _toggle(this);
            })
        }
    }

    MyApp.modules.conf.initPasswordSettings = function (options) {
        let obj = new FORM(options);
        obj.togglePreset();
    }

}(jQuery));