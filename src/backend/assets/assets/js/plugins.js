/**
 * @author Fred <mconyango@gmail.com>
 * DATE: 7/20/15
 * TIME: 10:47 PM
 */
//defines all custom plugins (or wrappers for external plugins)
MyApp.plugin = {};
//MODAL Handles the modal form submit
(function ($) {
    'use strict';
    let DATA_GRID_ID = null;
    var MODAL = function (options) {
        var defaultOptions = {
            modal_id: 'my_bs_modal',
            notif_id: 'my-modal-notif',
            form_id: 'my-modal-form',
            success_class: 'alert-success',
            error_class: 'alert-danger',
            modalTriggerSelector: '.show_modal_form,[data-show-modal],[data-toggle="modal"]',
            onShown: function (e, modal) {
                var grid_id = $(e).data('grid');
                if (!MyApp.utils.empty(grid_id)) {
                    DATA_GRID_ID = grid_id;
                } else {
                    DATA_GRID_ID = null;
                }
            },
            onHidden: function (e, modal) {
                var refresh = $(e).data('refresh');
                if (!MyApp.utils.empty(refresh)) {
                    MyApp.utils.reload();
                } else {
                    $(this).removeData('bs.modal');
                }
            },
            onShow: function (e, modal) {
                var modal = $('#' + modal.options.modal_id);
                var modal_size = $(e).data('modal-size');
                if (!MyApp.utils.empty(modal_size)) {
                    modal.find('.modal-dialog').addClass('modal-lg');
                }
                var loading = '<div class="row"><div class="col-md-12"><div class="content-loading"><i class="fa fa-spinner fa-5x fa-spin text-warning"></i></div></div></div>';
                modal.find('.modal-content').html(loading);
            },
            onLoaded: function (e, modal) {
                $.fn.modal.Constructor.prototype.enforceFocus = function () {
                };
                $("select.select2").select2({});
                MyApp.plugin.depDropDown({});
            },
        };
        this.options = $.extend({}, defaultOptions, options || {});
    };

    /**
     * show the modal
     */
    MODAL.prototype.show = function () {
        var $this = this;
        var modal_id = $this.options.modal_id;
        var clickHandler = function (e) {
            var modal_size = $(e).data('modal-size');
            if (!MyApp.utils.empty(modal_size)) {
                $('#' + modal_id).find('.modal-dialog').addClass(modal_size);
            }
            var url = $(e).data('href') || $(e).data('url') || $(e).attr('href');
            var modal = $('#' + modal_id);
            modal.off('shown.bs.modal').on('shown.bs.modal', function () {
                $this.options.onShown.call(this, e, $this);
            }).off('hidden.bs.modal').on('hidden.bs.modal', function () {
                $this.options.onHidden.call(this, e, $this);
            }).off('show.bs.modal').on('show.bs.modal', function () {
                $this.options.onShow.call(this, e, $this);
            }).off('loaded.bs.modal').on('loaded.bs.modal', function () {
                $this.options.onLoaded.call(this, e, $this);
            }).modal({
                remote: url,
                refresh: true,
            });
        };

        $(function () {
            $(document.body).off('click.myapp.modal').on('click.myapp.modal', $this.options.modalTriggerSelector, function (e) {
                e.preventDefault();
                clickHandler(this);
            });
        });
    };

    /**
     * submit the modal form
     */
    MODAL.prototype.submitForm = function () {
        var $this = this;

        var submit_form = function (e) {
            let form = $('#' + $this.options.form_id)
                , data = form.serialize()
                , action = form.attr('action')
                , method = form.attr('method') || 'POST'
                , originalButtonHtml = $(e).html()
                , grid_id = DATA_GRID_ID;
            $.ajax({
                type: method,
                url: action,
                data: data,
                dataType: 'json',
                success: function (response) {
                    //console.log(response);
                    if (response.success) {
                        var message = '<i class=\"fa fa-check\"></i> ';
                        message += response.message;
                        MyApp.utils.showAlertMessage(message, 'success', '#' + $this.options.notif_id);
                        if (response.forceRedirect === true) {
                            MyApp.utils.reload(response.redirectUrl, 1000);
                        } else if (MyApp.utils.empty(grid_id)) {
                            if (!MyApp.utils.empty(response.redirectUrl)) {
                                MyApp.utils.reload(response.redirectUrl, 1000);
                            }
                        } else {
                            setTimeout(function () {
                                $('#' + $this.options.modal_id).modal('hide');
                                MyApp.grid.updateGrid(grid_id);
                            }, 1000);
                        }
                    } else {
                        MyApp.utils.display_model_errors(response.message, '#' + $this.options.notif_id, form.data('model'));
                    }
                },
                beforeSend: function () {
                    $(e).attr('disabled', 'disabled').html('Please wait....');
                },
                complete: function () {
                    $(e).html(originalButtonHtml).removeAttr('disabled');
                },
                error: function (XHR) {
                    console.log(XHR.responseText);
                    MyApp.utils.showAlertMessage(XHR.responseText, 'error', '#' + $this.options.notif_id);
                }
            });
        };

        $('#' + $this.options.modal_id).off("click.myapp.modal").on("click.myapp.modal", '#' + $this.options.form_id + ' button[type="submit"]', function (e) {
            e.preventDefault();
            submit_form(this);
        });
    };

    var PLUGIN = function (options) {
        var obj = new MODAL(options);
        obj.show();
        obj.submitForm();
    };

    MyApp.plugin.modal = PLUGIN;
}(jQuery));
//NOTIFICATIONS
(function ($) {
    'use strict';
    var NOTIF = function (options) {
        var defaultDefault = {
            checkDelay: 15000, //15 secs
            selector: '#activity',
            check_notif_url: null,
            mark_as_seen_url: null,
            mark_as_read_url: null,
            mark_all_as_read_id: 'mark_all_notif_as_read',
            notif_item_selector: '.notification-body .notif-item',
            refresh_notif_selector: '#refresh_notif'
        };
        this.options = $.extend({}, defaultDefault, options || {});

        var $this = this;
        if (MyApp.utils.empty($this.options.check_notif_url)) {
            $this.options.check_notif_url = $($this.options.selector).data('check-notif-url');
        }
        if (MyApp.utils.empty($this.options.mark_as_seen_url)) {
            $this.options.mark_as_seen_url = $($this.options.selector).data('mark-as-seen-url');
        }
        if (MyApp.utils.empty($this.options.mark_as_read_url)) {
            $this.options.mark_as_read_url = $($this.options.selector).data('mark-as-read-url');
        }
    };

    var loadUrl = function (url, container, show_loading) {
        var $this = this;
        var show_bubble = function (unseen) {
            var bubble = $('#activity').find('.badge');
            if (parseInt(unseen) > 0) {
                bubble.text(unseen).addClass("bg-color-red bounceIn animated");
                bubble.removeClass('hidden');
            } else {
                bubble.addClass('hidden');
            }
        };

        var update_total_notif = function (total) {
            $('span.total-notif').text('(' + total + ')');
        };

        if (typeof show_loading === 'undefined')
            show_loading = true;

        $.ajax({
            type: "GET",
            url: url,
            dataType: 'json',
            cache: true, // (warning: this will cause a timestamp and will call the request twice)
            beforeSend: function () {
                // cog placed
                if (show_loading) {
                    container.html('<h1 class="text-center" style="margin-top:50px;"><i class="fa fa-spinner fa-spin"></i> Loading...</h1>');
                }
            },
            success: function (data) {
                if (show_loading) {
                    container.css({
                        opacity: '0.0'
                    }).html(data.html).delay(50).animate({
                        opacity: '1.0'
                    }, 300);
                } else {
                    container.html(data.html);
                }
                show_bubble(data.unseen);
                update_total_notif(data.total);

                setTimeout(function () {
                    loadUrl.call($this, url, container, false);
                }, $this.options.checkDelay);
            },
            error: function (xhr, ajaxOptions, thrownError) {
            },
            async: false
        });
    };

    NOTIF.prototype.show = function () {
        var $this = this
            , selector = $this.options.selector;

        var mark_as_seen = function (e) {
            var $elem = $(e);

            if ($elem.find('.badge').hasClass('bg-color-red')) {
                $elem.find('.badge').removeClassPrefix('bg-color-');
                $elem.find('.badge').addClass('hidden');
            }

            if (!$elem.next('.ajax-dropdown').is(':visible')) {
                //$elem.next('.ajax-dropdown').fadeIn(150);
                $elem.addClass('active');
            } else {
                // $elem.next('.ajax-dropdown').fadeOut(150);
                $elem.removeClass('active')
            }
            var url = $(e).data('mark-as-seen-url');
            $.ajax({
                type: "GET",
                url: url,
                dataType: 'json'
            });
        };

        $(selector).on('click.myapp.notif', function (e) {
            e.preventDefault();
            mark_as_seen(this);
        });
    };

    NOTIF.prototype.get = function () {
        var $this = this
            , container = $('.ajax-notifications')
            , url = $($this.options.selector).data('check-notif-url');
        loadUrl.call($this, url, container);
    };

    NOTIF.prototype.markAsRead = function () {
        var $this = this
            , selector = $this.options.notif_item_selector + '.unread';
        var mark_as_read = function (e) {
            var notif_item = $(e)
                , url = notif_item.data('mark-as-read-url')
                , target_url = $(e).find('a').attr('href');

            $.ajax({
                type: "GET",
                url: url,
                dataType: 'json',
                complete: function () {
                    notif_item.removeClass('unread');
                    MyApp.utils.reload(target_url);
                }
            });
        };

        $('#header').on('click.myapp.notif', selector, function (e) {
            e.preventDefault();
            mark_as_read(this);
        });
    };

    NOTIF.prototype.markAllAsRead = function () {
        var $this = this;

        var mark_all_as_read = function (e) {
            var url = $(e).data('href');
            $.ajax({
                type: "GET",
                url: url,
                dataType: 'json',
                success: function (response) {
                    $($this.options.notif_item_selector).removeClass('unread');
                }
            });
        };
        $('#' + $this.options.mark_all_as_read_id).on('click', function (e) {
            e.preventDefault();
            mark_all_as_read(this);
        });
    };

    NOTIF.prototype.refresh = function () {
        var $this = this
            , selector = $this.options.refresh_notif_selector;

        $(selector).on('click.myapp.notif', function () {
            //var btn = $(this);
            //btn.button('loading');
            $this.get();
            //btn.button('reset');
        });
    };
    var PLUGIN = function (options) {
        var obj = new NOTIF(options);
        obj.show();
        obj.get();
        obj.markAsRead();
        obj.markAllAsRead();
        obj.refresh();
    };
    MyApp.plugin.notif = PLUGIN;
}(jQuery));
//TYPEAHEAD
(function ($) {

    'use strict';
    var TypeAhead = function (options) {
        var defaultOptions = {
            selector: 'input.show-typeahead',
        };
        this.options = $.extend({}, defaultOptions, options || {});
    };
    TypeAhead.prototype.init = function () {
        var $this = this
            , e = $($this.options.selector)
            , data = e.data();

        if (MyApp.utils.empty(data.href))
            return false;

        var url = MyApp.utils.addParameterToURL(data.href, 'q', '%QUERY');

        var engine = new Bloodhound({
            datumTokenizer: function (d) {
                return Bloodhound.tokenizers.whitespace(d.value);
            },
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: url,
            },
        });
        engine.initialize();

        e.typeahead(null, {
            source: engine.ttAdapter(),
            minLength: 1,
            displayKey: 'value',
            templates: {
                suggestion: Handlebars.compile('<p><strong>{{value}}</strong></p>')
            }
        });
    };

    var PLUGIN = function (options) {
        var obj = new TypeAhead(options);
        obj.init();
    };

    MyApp.plugin.typeahead = PLUGIN;
}(jQuery));
//EXCEL
(function ($) {
    'use strict';

    var EXCEL = {
        /**
         *
         * @param sheetSelector
         * @param response
         */
        setSheets: function (sheetSelector, response) {
            if (!MyApp.utils.empty(response)) {
                MyApp.utils.populateDropDownList(sheetSelector, response.sheets);
            }

            //trigger change event
            $(sheetSelector).trigger('change');
        }
        ,
        /**
         *
         * @returns {boolean}
         */
        setPreview: function () {
            var $this = this
                , form = $('#' + $this.options.form)
                , url = $this.options.previewUrl;

            if (MyApp.utils.empty(url)) {
                return false;
            }

            var set_preview = function (show_progress) {
                if (typeof show_progress === 'undefined')
                    show_progress = false;
                var form = $('#' + $this.options.form)//refresh the form
                    , data = form.serialize()
                    , placeholder_columns = '#placeholder_columns';
                if (MyApp.utils.empty(url)) {
                    return false;
                }
                $.ajax({
                    url: url,
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (response) {
                        if (response.success) {
                            $(placeholder_columns).html(response.html).removeClass('hidden');
                        } else {
                            $(placeholder_columns).html("").addClass('hidden');
                        }
                    },
                    beforeSend: function () {
                        if (show_progress) {
                            MyApp.utils.startBlockUI('Setting Preview. Please wait...');
                        }
                    },
                    complete: function () {
                        if (show_progress) {
                            MyApp.utils.stopBlockUI();
                        }
                    },
                    error: function (xhr) {
                    }
                });
            };

            //events
            var on_blur_selector = $this.options.excel.startRowSelector + ',' + $this.options.excel.endRowSelector + ',' + $this.options.excel.startColumnSelector + ',' + $this.options.excel.endColumnSelector;
            form.on('change.myapp.excel', 'select.placeholder', function () {
                set_preview();
            });
            form.on('change.myapp.excel', $this.options.excel.sheetSelector, function () {
                set_preview(true);
            });
            form.on('blur.myapp.excel', on_blur_selector, function () {
                set_preview();
            });
        }
        ,
        /**
         * submit the import form
         */
        submit: function () {
            var $this = this;
            var _submit = function (e) {
                var form = $('#' + $this.options.form)
                    , url = form.attr('action')
                    , data = form.serialize();

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: data,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            MyApp.utils.reload(response.redirectUrl);
                        } else {
                            MyApp.utils.display_model_errors(response.message, '', true);
                        }
                    },
                    beforeSend: function () {
                        MyApp.utils.startBlockUI('Please wait...');
                    },
                    complete: function () {
                        MyApp.utils.stopBlockUI();
                    },
                    error: function (XHR) {
                        if (MyApp.DEBUG_MODE) {
                            var message = XHR.responseText;
                            MyApp.utils.showAlertMessage(message, 'error');
                        }
                    }
                });
            };
            $('#' + $this.options.form).find('button[type="submit"]').on('click.myapp.excel', function (e) {
                e.preventDefault();
                _submit(this);
            });
        }
    };
    MyApp.plugin.excel = EXCEL;

    //IMPORT EXCEL
    var IMPORT = function (options) {
        var defaultOptions = {
            form: undefined,
            excel: {
                sheetSelector: undefined,
                startRowSelector: undefined,
                endRowSelector: undefined,
                startColumnSelector: undefined,
                endColumnSelector: undefined,
                skipFirstRowSelector: '#excel-skip-first-row'
            },
            previewUrl: undefined,
        };

        this.options = $.extend(true, {}, defaultOptions, options || {});
    };

    //set preview
    IMPORT.prototype.setPreview = function () {
        var $this = this;
        MyApp.plugin.excel.setPreview.call($this);
    }
    //submit
    IMPORT.prototype.submit = function () {
        var $this = this;
        MyApp.plugin.excel.submit.call($this);
    }

    IMPORT.prototype.skipFirstRow = function () {
        var $this = this;
        var selector = $this.options.excel.skipFirstRowSelector;

        var skip = function (e) {
            var startRowSelector = $this.options.excel.startRowSelector;
            if ($(e).is(':checked')) {
                $(startRowSelector).val(2).trigger('blur.myapp.excel');
            } else {
                $(startRowSelector).val(1).trigger('blur.myapp.excel');
            }
        }

        //click event
        $(selector).on('click.myapp.excel', function (event) {
            skip(this);
        });

        //on page load
        skip(selector);
    }

    var PLUGIN = function (options) {
        var obj = new IMPORT(options);
        obj.setPreview();
        obj.submit();
        obj.skipFirstRow();
    };

    MyApp.plugin.importExcel = PLUGIN;
}(jQuery));
//Dependent dropdown
(function ($) {
    "use strict";
    var DepDrop = function (options) {
        let defaultOptions = {
            selector: "select.parent-depdropdown",
            loadingText: 'Loading ...',
        };
        this.options = $.extend({}, defaultOptions, options || {});
    }

    DepDrop.prototype.init = function () {
        let $this = this,
            selector = $this.options.selector,
            $elem = selector instanceof $ ? selector : $(selector);

        let _depDrop = function (parentE) {
            let $parentE = parentE instanceof $ ? parentE : $(parentE);
            let _getData = function (e) {
                let $elem = e instanceof $ ? e : $(e);
                let url = $elem.data('url'),
                    id = $parentE.val(),
                    selected = $elem.data('selected');
                if (MyApp.utils.empty(url)) {
                    return false;
                }
                url = url.replace(new RegExp('idV', 'gi'), id);
                $.ajax({
                    url: url,
                    type: 'get',
                    dataType: 'json',
                    success: function (data) {
                        $elem.removeAttr('disabled');
                        MyApp.utils.populateDropDownList($elem, data, selected, false);
                    },
                    beforeSend: function (xhr) {
                        let $el = $(e);
                        $el.find('option[selected]').removeAttr('selected');
                        $el.val('').attr('disabled', 'disabled').html('');
                        $el.html('<option id="">' + $this.options.loadingText + '</option>');
                    },
                    complete: function (xhr) {

                    },
                    error: function (xhr) {
                        if (MyApp.DEBUG_MODE) {
                            console.log(xhr);
                        }
                    }
                });
            }

            let childSelectors = $parentE.data('child-selectors');
            if (MyApp.utils.empty(childSelectors)) {
                return false;
            }
            $.each(childSelectors, function (index, childSelector) {
                _getData(childSelector);
            })
        }
        //on change
        $elem.off('change.depdropdown').on('change.depdropdown', function (event) {
            _depDrop(this);
        });

    }

    MyApp.plugin.depDropDown = function (options) {
        let obj = new DepDrop(options);
        obj.init();
    }
}(jQuery));
//grid filter form
(function ($) {
    "use strict";
    let FORM = function (options) {
        let defaultOptions = {
            formId: 'grid-filter-form',
        }
        this.options = $.extend({}, defaultOptions, options || {});
    }

    FORM.prototype = {
        filterSearch: function () {
            let $this = this,
                formSelector = '#' + $this.options.formId;
            let _search = function (e) {
                let grid = $(formSelector).data('grid'),
                    url = $(formSelector).attr('action'),
                    data = $(formSelector).serialize();
                url = url + '?' + data;
                MyApp.grid.updateGrid(grid, {url: url});
            }

            $(formSelector).on('click', 'button[type="submit"]', function (event) {
                event.preventDefault();
                _search(this);
            })
        }
    }

    MyApp.plugin.filterSearch = function (options) {
        let obj = new FORM(options);

        obj.filterSearch();
    }
}(jQuery));
//generate report
(function ($) {
    'use strict';
    let REPORT = function (options) {
        let defaultOptions = {
            buttonSelector: '#generate-report-button',
            wrapperSelector: '#generated-report-wrapper',
            downloadWrapperSelector: '#generated-report-download',
            downloadLinkSelector: '#generated-report-download-link'
        };

        this.options = $.extend({}, defaultOptions, options || {});
    }

    REPORT.prototype.generateReport = function () {
        let $this = this;
        let _submit = function (e) {
            let form = $(e).closest('form')
                , url = form.attr('action')
                , method = form.attr('method');
            $.ajax({
                url: url,
                type: method,
                data: form.serialize(),
                dataType: 'json',
                success: function (data) {
                    //noinspection JSUnresolvedVariable
                    $($this.options.downloadLinkSelector).attr('href', data.downloadLink)
                    $($this.options.wrapperSelector).html(data.html);
                    $($this.options.downloadWrapperSelector).removeClass('hidden');
                },
                beforeSend: function () {
                    let loading = '<div class="row"><div class="col-md-12"><div class="content-loading"><i class="fa fa-spinner fa-5x fa-spin text-warning"></i></div></div></div>';
                    $($this.options.wrapperSelector).html(loading);
                    $($this.options.downloadWrapperSelector).addClass('hidden');
                },
                error: function (xhr) {
                    if (MyApp.DEBUG_MODE) {
                        console.log(xhr);
                    }
                }
            });
        }

        $($this.options.buttonSelector).on('click', function (event) {
            event.preventDefault();
            _submit(this);
        });
    }

    MyApp.plugin.generateReport = function (options) {
        let obj = new REPORT(options);
        obj.generateReport();
    }
}(jQuery));
//Workflow notification
(function ($) {
    'use strict';
    let WFNOTIF = function (options) {
        let defaultDefault = {
            checkDelay: 15000, //15 secs
            selector: '#workflow-task-notif',
            checkNotifUrl: null,
        };
        this.options = $.extend({}, defaultDefault, options || {});

        var $this = this;
        if (MyApp.utils.empty($this.options.checkNotifUrl)) {
            $this.options.checkNotifUrl = $($this.options.selector).data('workflow-notif-url');
        }
    };
    let loadUrl = function (url) {
        let $this = this;
        let showBubble = function (unseen) {
            var bubble = $($this.options.selector).find('.badge');
            if (parseInt(unseen) > 0) {
                bubble.text(unseen).addClass("bg-color-red bounceIn animated");
                bubble.removeClass('hidden');
            } else {
                bubble.addClass('hidden');
            }
        };
        $.ajax({
            type: "GET",
            url: url,
            dataType: 'json',
            cache: true, // (warning: this will cause a timestamp and will call the request twice)
            success: function (data) {
                showBubble(data);
                setTimeout(function () {
                    loadUrl.call($this, url);
                }, $this.options.checkDelay);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.responseText);
            },
            async: false
        });
    };
    WFNOTIF.prototype.get = function () {
        var $this = this
            , url = $this.options.checkNotifUrl;
        loadUrl.call($this, url);
    };
    MyApp.plugin.workflowNotif = function (options) {
        var obj = new WFNOTIF(options);
        obj.get();
    };
}(jQuery));