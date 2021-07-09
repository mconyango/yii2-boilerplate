/**
 * Defines the app global object
 * @author Fred <mconyango@gmail.com>
 * @type object
 */
if (typeof jQuery === 'undefined') {
    throw new Error('MyApp requires jQuery')
}

var MyApp = (function ($) {
    'use strict';
    var _myapp = {};

    _myapp.DEBUG_MODE = true;

    //will contain all modules js logics
    _myapp.modules = {};

    //utils
    var utils = {
        /**
         * reloads a page
         * @param url
         * @param delay delay in milliseconds
         */
        reload: function (url, delay) {
            if (!url || typeof url === 'undefined') {
                url = location.href;
            }
            if (!delay || typeof delay === 'undefined')
                window.location = url;
            else {
                setTimeout(function () {
                    window.location = url;
                }, delay);
            }
        }
        ,
        /**
         * Trigger submit event on a form
         * @param {string} selector The form selector
         * @returns {Boolean}
         */
        triggerSubmit: function (selector) {
            $(selector).trigger('submit');
            return false;
        }
        ,
        /**
         * Populates a select options with a given data in JSON format e.g [{id:"1",name:"Sample1"},{id:"2",name:"Sample2"}]
         * @param selector
         * @param data JSON data
         * @param selected
         * @param change
         */
        populateDropDownList: function (selector, data, selected, change) {
            let options = [];
            let tip = '';
            let $elem = selector instanceof $ ? selector : $(selector);
            $.each(data, function (i, item) {
                if (i === null || i === "") {
                    tip = '<option value="' + i + '">' + item + '</option>';
                } else {
                    options += '<option value="' + i + '">' + item + '</option>';
                }
            });

            if (!MyApp.utils.empty(tip)) {
                options = tip + options;
            }
            $elem.html(options);
            if (!utils.empty(selected)) {
                $elem.val(selected);
            }

            if (typeof change === 'undefined') {
                change = true;
            }

            if (change) {
                $elem.change();
            }
        }
        ,
        /**
         * Formats a number with grouped thousands~
         * Strip all characters but numerical ones.
         * @param {mixed} number
         * @param {integer} decimals
         * @param {string} dec_point
         * @param {string} thousands_sep
         * @returns {@exp;s@call;join}
         */
        number_format: function (number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }
        ,
        /**
         * Adds more params to a url
         * @param url
         * @param key the $_GET key
         * @param value the $_GET value for the key
         * @returns {*}
         */
        addParameterToURL: function (url, key, value) {
            var _url = url;
            _url += (_url.split('?')[1] ? '&' : '?') + key + '=' + value;
            return _url;
        }
        ,
        /**
         * Starts a block UI
         * @param {string} message
         * @returns {void}
         */
        startBlockUI: function (message) {
            if (typeof message === 'undefined') {
                message = 'Please wait ...';
            }
            var content = '<span id="my_block_ui">' + message + '</span>';
            $.blockUI({
                message: content,
                css: {
                    border: 'none', padding: '15px',
                    backgroundColor: '#333C44',
                    '-webkit-border-radius': '3px',
                    '-moz-border-radius': '3px',
                    opacity: 1, color: '#fff',
                },
                overlayCSS: {
                    backgroundColor: '#000',
                    opacity: 0.4,
                    cursor: 'wait',
                    'z-index': 1030,
                },
            });
        }
        ,
        /**
         * Stops a block ui
         * @returns {undefined}
         */
        stopBlockUI: function () {
            $.unblockUI();
        }
        ,
        /**
         * Checks whether a given string is a valid integer
         * @param {string} number
         * @returns {Boolean}
         */
        isInt: function (number) {
            return !!(Math.floor(number) == number && $.isNumeric(number));
        },

        renderAlert: function (type, message) {
            var validTypes = ['success', 'error', 'notice'], html = '';
            if (typeof type === 'undefined' || ($.inArray(type, validTypes) < 0))
                type = validTypes[0];
            if (type === 'success') {
                html += '<div class="alert alert-success alert-block">';
            } else if (type === 'error') {
                html += '<div class="alert alert-danger alert-block">';
            } else {
                html += '<div class="alert alert-warning alert-block">';
            }
            html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
            html += '<p>' + message + '</p>';
            html += '</div>';
            return {html: html, type: type};
        },
        /**
         * Show alert message.
         * @param message
         * @param type
         * @param containerSelector
         */
        showAlertMessage: function (message, type, containerSelector) {
            var __ret = this.renderAlert(type, message);
            var html = __ret.html;
            type = __ret.type;
            if (MyApp.utils.empty(containerSelector)) {
                if (type === 'success') {
                    BootstrapDialog.alert({
                        title: 'SUCCESS',
                        message: message,
                        type: BootstrapDialog.TYPE_SUCCESS,
                        closable: true
                    });
                } else if (type === 'error') {
                    BootstrapDialog.alert({
                        title: 'ERROR',
                        message: message,
                        type: BootstrapDialog.TYPE_DANGER,
                        closable: true
                    });
                } else {
                    BootstrapDialog.alert({
                        title: 'WARNING',
                        message: message,
                        type: BootstrapDialog.TYPE_WARNING,
                        closable: true
                    });
                }
            } else {
                var container;
                if (typeof containerSelector === 'object') {
                    container = containerSelector;
                } else {
                    container = $(containerSelector);
                }
                container.html(html).removeClass('hidden');
            }
        }
        ,
        /**
         *Bootstrap datepicker wrapper
         * @param {type} e
         * @returns {undefined}
         */
        bootstrapDatePicker: function (e) {
            var nowTemp = new Date();
            var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
            $(e).datepicker({
                format: 'yyyy-mm-dd', onRender: function (date) {
                    if ($(e).data('date-disabled') === 'past') {
                        return date.valueOf() < now.valueOf() ? 'disabled' : '';
                    } else if ($(e).data('date-disabled') === 'future') {
                        return date.valueOf() > now.valueOf() ? 'disabled' : '';
                    }
                }
            });
        }
        ,
        /**
         *
         * @returns {Boolean}
         *
         */
        empty: function (mixed_var) {
            // Checks if the argument variable is empty
            // undefined, null, false, number 0, empty string,
            // string "0", objects without properties and empty arrays
            // are considered empty
            //
            // http://kevin.vanzonneveld.net
            // +   original by: Philippe Baumann
            // +      input by: Onno Marsman
            // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // +      input by: LH
            // +   improved by: Onno Marsman
            // +   improved by: Francesco
            // +   improved by: Marc Jansen
            // +      input by: Stoyan Kyosev (http://www.svest.org/)
            // +   improved by: Rafal Kukawski         // *     example 1: empty(null);
            // *     returns 1: true
            // *     example 2: empty(undefined);
            // *     returns 2: true
            // *     example 3: empty([]);
            // *     returns 3: true
            // *     example 4: empty({});
            // *     returns 4: true
            // *     example 5: empty({'aFunc' : function () { alert('humpty'); } });
            // *     returns 5: false
            var undef, key, i, len;
            var emptyValues = [undef, null, false, 0, "", "0"];
            for (i = 0, len = emptyValues.length; i < len; i++) {
                if (mixed_var === emptyValues[i]) {
                    return true;
                }
            }

            if (typeof mixed_var === "object") {
                for (key in mixed_var) {
                    // TODO: should we check for own properties only?
                    //if (mixed_var.hasOwnProperty(key)) {
                    return false;
                    //}
                }
                return true;
            }

            return false;
        },

        /**
         * Strip HTML tags      * @param {string} the_string
         * @returns {undefined}
         */
        strip_tags: function (the_string) {
            return the_string.replace(/(<([^>]+)>)/ig, "");
        },

        /**
         * Cookie management
         * @type type
         */
        myCookie: {
            options: {
                expires: 7, path: '/',
                domain: undefined, //defaults to the domain where the cookie was created,
                secure: false
            },
            /**
             *
             * @param {string} namespace
             * @param {string} key
             * @param {string} value
             * @param options
             * @returns {void}
             */
            set: function (namespace, key, value, options) {
                'use strict';
                options = $.extend({}, this.options, options || {});
                $.cookie(namespace + '_' + key, value, options);
            },
            /**
             * Get stored cookie
             * @param {string} namespace
             * @param {string} key
             * @returns {mixed} value
             */
            get: function (namespace, key) {
                'use strict';
                return $.cookie(namespace + '_' + key);
            },
            remove: function (namespace, key) {
                'use strict';
                $.removeCookie(namespace + '_' + key, this.options);
            }
        },

        /**
         *
         * @param string
         * @param notif_container_selector
         * @param model
         */
        display_model_errors: function (string, notif_container_selector, model) {
            var input_error_class = 'has-error'
                , addErrorClass = function (id) {
                $('#' + id).closest('.form-group').addClass(input_error_class);
            };
            //remove all errors
            $('.' + input_error_class).removeClass(input_error_class);
            var message = '<ul>';
            if(typeof string === 'string' || string instanceof String){
                message += '<li>' + string + '</li>';
            }
            else {
                $.each(string, function (i) {
                    if ($.isArray(string[i])) {
                        $.each(string[i], function (j, msg) {
                            message += '<li>' + msg + '</li>';
                        });
                    }
                    addErrorClass(model + '-' + i);
                });
            }

            message += '</ul>';
            if (!utils.empty(notif_container_selector)) {
                this.showAlertMessage(message, 'error', notif_container_selector);
            } else {
                BootstrapDialog.alert({
                    title: 'WARNING',
                    message: message,
                    type: BootstrapDialog.TYPE_WARNING,
                    closable: true
                });
            }
        }
        ,
        /**
         *hide model errors
         * @param {string} container_selector
         * @returns {undefined}
         */
        hide_model_errors: function (container_selector) {
            if (typeof container_selector === 'undefined')
                container_selector = '#user-flash-messages';
            var container;
            if (typeof container_selector === 'object') {
                container = container_selector;
            } else {
                container = $(container_selector);
            }
            container.html("").addClass('hidden');
            var error_class = 'my-form-error';
            $('.' + error_class).removeClass(error_class);
        }
        ,
        /**
         * Clear all form values
         * @param {string} form_id
         * @returns {undefined}
         */
        clear_form: function (form_id) {
            $(':input', '#' + form_id).not(':button, :submit, :reset').val('').removeAttr('checked').removeAttr('selected').removeClass('my-form-error');
        }
        ,
        getActiveFormFieldSelector: function (modelClass, field) {
            modelClass = modelClass.toLowerCase();
            return '#' + modelClass + '-' + field;
        },
        getActiveFormFieldContainerSelector: function (modelClass, field) {
            modelClass = modelClass.toLowerCase();
            return '.field-' + modelClass + '-' + field;
        },
        /**
         *
         * @param {string} selector
         * @param {integer} speed
         * @returns {undefined}
         */
        scroll_to: function (selector, speed) {
            if (typeof speed === 'undefined') {
                speed = 2000;
            }
            $('html, body').animate({
                scrollTop: $(selector).offset().top
            }, speed);
        }
        ,
        /**
         *
         * @param input
         * @param pad_length
         * @param pad_string
         * @param pad_type
         * @returns {string}
         */
        str_pad: function (input, pad_length, pad_string, pad_type) {
            //  discuss at: http://phpjs.org/functions/str_pad/
            // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // improved by: Michael White (http://getsprink.com)
            //    input by: Marco van Oort
            // bugfixed by: Brett Zamir (http://brett-zamir.me)
            //   example 1: str_pad('Kevin van Zonneveld', 30, '-=', 'STR_PAD_LEFT');
            //   returns 1: '-=-=-=-=-=-Kevin van Zonneveld'
            //   example 2: str_pad('Kevin van Zonneveld', 30, '-', 'STR_PAD_BOTH');
            //   returns 2: '------Kevin van Zonneveld-----'
            var half = '',
                pad_to_go;
            var str_pad_repeater = function (s, len) {
                var collect = '',
                    i;
                while (collect.length < len) {
                    collect += s;
                }
                collect = collect.substr(0, len);
                return collect;
            };
            input += '';
            pad_string = pad_string !== undefined ? pad_string : ' ';
            if (pad_type !== 'STR_PAD_LEFT' && pad_type !== 'STR_PAD_RIGHT' && pad_type !== 'STR_PAD_BOTH') {
                pad_type = 'STR_PAD_RIGHT';
            }
            if ((pad_to_go = pad_length - input.length) > 0) {
                if (pad_type === 'STR_PAD_LEFT') {
                    input = str_pad_repeater(pad_string, pad_to_go) + input;
                } else if (pad_type === 'STR_PAD_RIGHT') {
                    input = input + str_pad_repeater(pad_string, pad_to_go);
                } else if (pad_type === 'STR_PAD_BOTH') {
                    half = str_pad_repeater(pad_string, Math.ceil(pad_to_go / 2));
                    input = half + input + half;
                    input = input.substr(0, pad_length);
                }
            }

            return input;
        },
        stripScripts: function (s) {
            var div = document.createElement('div');
            div.innerHTML = s;
            var scripts = div.getElementsByTagName('script');
            var i = scripts.length;
            while (i--) {
                scripts[i].parentNode.removeChild(scripts[i]);
            }
            return div.innerHTML;
        }
        ,
        /**
         * Print any div contents
         * @param {string} selector
         */
        print_div: function (selector) {
            $(selector).printThis({
                debug: true,
                importCSS: true,
                printContainer: true,
                pageTitle: $('title').html(),
                removeInline: false,
                printDelay: 333, header: null,
                formValues: false
            });
        },
        printElement: function (divId) {
            var printContents = document.getElementById(divId).innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            document.body.innerHTML = originalContents;
        }
        ,
        inherit: function (parent, child) {
            var key;
            //inherit the properties in parent
            for (key in parent) {
                if (parent.hasOwnProperty(key)) {
                    child[key] = parent[key];
                }
            }

            return child;
        },
        /**
         *
         * @param functionName
         * @param context
         * @returns {*}
         */
        executeMethodByName: function (functionName, context) {
            var args = [].slice.call(arguments).splice(2);
            var namespaces = functionName.split(".");
            var func = namespaces.pop();
            for (var i = 0; i < namespaces.length; i++) {
                context = context[namespaces[i]];
            }
            return context[func].apply(this, args);
        }
        ,
        /**
         * Convert form inputs to json object
         */
        serializeObject: function (form) {
            return new FormData(form[0]);
        }
    };
    _myapp.utils = utils;

    //grid
    _myapp.grid = {
        /**
         *
         * @param pjaxContainerId
         * @param options
         */
        updateGrid: function (pjaxContainerId, options) {
            let defaultSettings = {
                container: '#' + pjaxContainerId
            }
            let settings = $.extend({}, defaultSettings, options || {})
            $.pjax.reload(settings);
        },
        /**
         * get selection ids from gridview
         * @param grid_id
         * @returns {*}
         */
        getSelectedIds: function (grid_id) {
            var selectionIds = $.fn.yiiGridView.getSelection(grid_id);
            if (selectionIds.length === 0 || selectionIds === '') {
                return false;
            }
            return selectionIds;
        },
        /**
         * Submit Gridview operation via AJAX
         * @param e
         * @returns {boolean}
         */
        submitGridView: function (e) {
            var grid_id = (!MyApp.utils.empty($(e).data('grid'))) ? $(e).data('grid') : $(e).data('grid_id')
                , url = (!MyApp.utils.empty($(e).data('href'))) ? $(e).data('href') : $(e).data('ajax-url')
                , dataType = (!MyApp.utils.empty($(e).data('data-type'))) ? $(e).data('data-type') : 'html'
                , confirm_msg = $(e).data('confirm')
                ,
                forceSelection = (!MyApp.utils.empty($(e).data('force-selection'))) ? $(e).data('force-selection') : false
                , data = '';

            var selectionIds = this.getSelectedIds(grid_id);
            if (!MyApp.utils.empty(selectionIds)) {
                if (!MyApp.utils.empty(data)) {
                    data += '&';
                }
                data += 'ids=' + selectionIds;
            } else if (forceSelection) {
                this.noSelectionWarning();
                return false;
            }

            var ajaxPost = function () {
                $.fn.yiiGridView.update(grid_id, {
                    type: 'post',
                    url: url,
                    data: data,
                    dataType: dataType,
                    success: function (data) {
                        $.fn.yiiGridView.update(grid_id, {url: location.href});
                        if (dataType === 'json' && !MyApp.utils.empty(data.message)) {
                            if (data.success) {
                                MyApp.utils.showAlertMessage(data.message, 'success');
                            } else {
                                MyApp.utils.showAlertMessage(data.message, 'error');
                            }
                        }
                    },
                    error: function (XHR) {
                        MyApp.utils.showAlertMessage(XHR.responseText, 'error');
                    }
                });
            };

            if (!MyApp.utils.empty(confirm_msg)) {
                BootstrapDialog.confirm(confirm_msg, function (result) {
                    if (result) {
                        ajaxPost();
                    }
                });
            } else {
                ajaxPost();
            }
        },

        gridAction: function (e) {
            var grid_id = $(e).data('grid')
                , url = $(e).data('href')
                ,
                forceSelection = (!MyApp.utils.empty($(e).data('force-selection'))) ? $(e).data('force-selection') : false;

            var selectionIds = this.getSelectedIds(grid_id);
            if (!MyApp.utils.empty(selectionIds)) {
                url = utils.addParameterToURL(url, 'ids', selectionIds);
            } else if (forceSelection) {
                this.noSelectionWarning();
                return false;
            }

            utils.reload(url);
        },

        /**
         *
         * @returns {undefined}
         */
        noSelectionWarning: function () {
            BootstrapDialog.alert({
                title: 'WARNING',
                message: 'Please select some items first.',
                type: BootstrapDialog.TYPE_WARNING,
            });
        }
    };

    return _myapp;
}(jQuery));
