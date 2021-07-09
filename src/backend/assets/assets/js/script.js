/**
 * Created by mconyango on 7/17/15.
 */
//document.ready bootstraping
(function ($) {
    'use strict';
    //shorthand for $( document ).ready....
    $(function () {
        var init = {
            theme_setup: function () {
                pageSetUp();
                //settings
                $('#sadmin-setting').click(function () {
                    $('#ribbon .sadmin-options').toggleClass('activate');
                });
            },
            updateGridView: function () {
                var updateGrid = function (e) {
                    var url = $(e).data('href')
                        , confirm_msg = $(e).data('confirm-message')
                        , dataType = $(e).data('data-type')
                        , pjax_id = $(e).data('grid');

                    if (MyApp.utils.empty(dataType)) {
                        dataType = 'html';
                    }

                    var ajax = function () {
                        $.ajax({
                            type: 'POST',
                            url: url,
                            dataType: dataType,
                            success: function (data) {
                                MyApp.grid.updateGrid(pjax_id);
                                if (!MyApp.utils.empty(data) && data.message) {
                                    MyApp.utils.showAlertMessage(data.message, 'success');
                                }
                            }
                            ,
                            beforeSend: function () {
                                MyApp.utils.startBlockUI('Please wait...');
                            }
                            ,
                            complete: function () {
                                MyApp.utils.stopBlockUI();
                            }
                            ,
                            error: function (XHR) {
                                var message = XHR.responseText;
                                MyApp.utils.showAlertMessage(message, 'error');
                                return false;
                            }
                        });
                    }

                    if (MyApp.utils.empty(confirm_msg)) {
                        ajax();
                    } else {
                        BootstrapDialog.confirm(confirm_msg, function (result) {
                            if (result) {
                                ajax();
                            }
                        });
                    }
                };
                $('body').on('click', 'a.grid-update', function (e) {
                    e.preventDefault();
                    updateGrid(this);
                });
            }
            ,
            activateTabs: function () {
                var path = window.location.pathname;
                path = path.replace(/\/$/, "");
                path = decodeURIComponent(path);
                var checkLink = function (e) {
                    var href = $(e).attr('href');
                    if (href.substring(0, path.length) === path) {
                        return true;
                    } else {
                        return false;
                    }
                };
                //activate tabs
                $('ul.my-nav li>a').each(function () {
                    if (checkLink(this)) {
                        $(this).parent().addClass('active');
                    }
                });
                //activate list-group links
                $('div.my-list-group>a').each(function () {
                    if (checkLink(this)) {
                        $(this).addClass('active');
                    }
                });
            }
            ,
            enableLinkableRow: function () {
                var selector = 'table tr.linkable > td:not(.skip-export ,.grid-actions)';
                $(document.body).on('click.tr.linkable', selector, function () {
                    var url = $(this).parent('tr').data('href');
                    if (!MyApp.utils.empty(url)) {
                        MyApp.utils.reload(url);
                    }
                });
            }
            ,
            showDatePicker: function () {
                $(document.body).on('focusin.datepicker', "input[type='text'].show-datepicker,.show-datepicker input[type='text']", function () {
                    let dateFormat = $(this).data('date-format') || 'yy-mm-dd',
                        miDate = $(this).data('min-date') || null,
                        maxDate = $(this).data('max-date') || null,
                        changeYear = $(this).data('change-year') || true,
                        changeMonth = $(this).data('change-year') || true;
                    $(this).datepicker({
                        dateFormat: dateFormat,
                        prevText: '<i class="fa fa-chevron-left"></i>',
                        nextText: '<i class="fa fa-chevron-right"></i>',
                        minDate: miDate,
                        maxDate: maxDate,
                        changeYear: changeYear,
                        changeMonth: changeMonth,
                        yearRange: "-120:+120"
                    });
                    $(this).attr("autocomplete", "off");
                });
            }
            ,
            showTimePicker: function () {
                $(document.body).on('focusin.timepicker', "input[type='text'].show-timepicker", function () {
                    $(this).timepicker();
                });
            }
            ,
            collapsePanel: function () {
                $('.collapse').on('shown.bs.collapse', function () {
                    $(this).parent().find(".glyphicon-chevron-right").removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
                }).on('hidden.bs.collapse', function () {
                    $(this).parent().find(".glyphicon-chevron-down").removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-right");
                });
            },
            disableCopyPaste: function () {
                $("input.disable-copy-paste").each(function (i, obj) {
                    obj.onpaste = function (e) {
                        e.preventDefault();
                    }
                });
            },
            simpleAjaxPost: function () {
                let selector = 'a.simple-ajax-post,button.simple-ajax-post';
                let ajaxPost = function (e) {
                    let url = $(e).data('href');
                    $.ajax({
                        url: url,
                        type: 'post',
                        dataType: 'json',
                        success: function (data) {
                            if (data.success) {
                                MyApp.utils.reload(null, 0);
                            }
                        },
                        error: function (xhr) {
                            if (MyApp.DEBUG_MODE) {
                                console.log(xhr);
                            }
                        }
                    })
                };

                //event
                $(document.body).on('click.myapp', selector, function (e) {
                    e.preventDefault();
                    ajaxPost(this);
                })
            },
            initPlugins: function () {
                //modal form
                MyApp.plugin.modal({});
                //notifications
                MyApp.plugin.notif({});
                //workflow notifications
                MyApp.plugin.workflowNotif({});
                //dependent dropdowns
                MyApp.plugin.depDropDown({});
                //grid filter form
                MyApp.plugin.filterSearch({});
                //generate report
                MyApp.plugin.generateReport({});
                //timeago
                //$("time.timeago").timeago();
                //select2
                $("select.select2").select2();
                //show tooltip
                $('#content').tooltip({
                    selector: '.show-tooltip',
                });
                //show popover
                $(document.body).popover({
                    selector: '.show-popover,[data-toggle="popover"]',
                    html: true,
                    trigger: 'hover focus',
                    container: 'body'
                });
                //$("form input:text:not(.no-autofocus), form textarea").first('').focus();
                //disable inspect element
                document.addEventListener('contextmenu', function (e) {
                    // e.preventDefault();
                });
            }
        };
        var key;
        //inherit the properties in parent
        for (key in init) {
            MyApp.utils.executeMethodByName(key, init);
        }


        // scroll up
        $.scrollUp();

        // dynamic confirmation message
        $("a[data-prompt-confirmation]").on('click', function (e) {
            var element = $(this);
            e.preventDefault();
            var message = element.data('confirm-message') || "Are you sure?";
            if (confirm(message)) {
                window.location.href = element.prop('href');
            }
        });
    });

})(jQuery);
