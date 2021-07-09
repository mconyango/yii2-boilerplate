/**
 * @author Fred <fred@btimillman.com>.
 * Date & Time Created 2017-06-13 1:42 PM
 */
var MyApp = MyApp || {};
MyApp.widget = MyApp.widget || {};
(function ($) {
    'use strict';
    var WIDGET = function (options) {
        var defaultOptions = {
            selectors: {
                form: null,
                panel: null,
                itemsTable: null,
                addLineButton: null,
                saveLineItem: '.save-line-item',
                deleteItemButton: '.delete-line-item',
                primaryKeyField: null,
                foreignKeyField: null,
                submit: null,
                notification: null,
                itemTr: 'tr.line-item',
                finishButton: null,
                parentPrimaryKeyField: null
            },
            actionParamNextIndex: null,
            actionParamAppendItem: null,
            actionParamDeleteItem: null,
            actionParamItemId: null,
            actionParamSaveItem: null,
            itemTrIdPrefix: null,
            itemInputErrorClass: 'my-form-error',
            parentModelShortClassName: null,
            events: {
                afterAdd: function (index) {
                },
                beforeSave: function (tr) {
                },
                afterSave: function (tr, response) {
                },
                beforeDelete: function (tr) {
                },
                afterDelete: function (tr, response) {
                },
                beforeFinish: function () {
                },
                afterFinish: function (response) {
                    var $this = this;
                    MyApp.utils.showAlertMessage(response.message, 'success', $this.options.selectors.notification);
                    MyApp.utils.reload(response.redirectUrl, 2000);
                }
            }
        };

        this.options = $.extend(true, {}, defaultOptions, options || {});
    };

    //utils
    var updateItemsCount = function () {
        $(this.options.selectors.panel).find('.panel-title>span').html($(this.options.selectors.form).find(this.options.selectors.itemTr).length + ' Items');
    }, showItemInputError = function (tr, input_class) {
        tr.find('.' + input_class).addClass(this.options.itemInputErrorClass);
        tr.addClass('bg-danger');
    }, hideItemInputError = function (tr) {
        tr.find('.' + this.options.itemInputErrorClass).removeClass(this.options.itemInputErrorClass);
        tr.removeClass('bg-danger');
    }, markItemAsSaved = function (tr) {
        var saved_css_class = 'text-success'
            , unsaved_css_class = 'text-warning';
        tr.find(this.options.selectors.saveLineItem).removeClass(unsaved_css_class).addClass(saved_css_class);

    }, processItemSaveResponse = function (tr, response, noExistingError = true) {
        var $this = this;

        if (response.success) {
            $this.options.events.afterSave.call($this, tr, response);
            hideItemInputError.call($this, tr);
            markItemAsSaved.call($this, tr);
            //set Id
            tr.find($this.options.selectors.primaryKeyField).val(response.id);
            return true;
        }
        else {
            if (noExistingError) {
                hideItemInputError.call($this, tr);
                //show error
                $.each(response.error, function (i) {
                    showItemInputError.call($this, tr, i);
                });
                MyApp.utils.display_model_errors(response.error, false, true);
            }
            return false;
        }
    };
    //add a new row
    WIDGET.prototype.addItem = function () {
        var $this = this
            , formSelector = $this.options.selectors.form
            , selector = $this.options.selectors.addLineButton;

        if (MyApp.utils.empty(selector)) {
            return false;
        }

        let _addLine = function (e) {
            let url = $(e).data('href');
            let getIndex = function (index) {
                if (typeof index === 'undefined') {
                    index = $(formSelector).find($this.options.selectors.itemTr).length + 1;
                }
                if (document.getElementById($this.options.itemTrIdPrefix + '-' + index) === null) {
                    return index;
                } else {
                    index = index + 1;
                    getIndex(index);
                }
            }
            let nextIndexParam = $this.options.actionParamNextIndex
                , appendLineParam = $this.options.actionParamAppendItem
                , index = getIndex();
            $.ajax({
                type: 'POST',
                url: url,
                data: nextIndexParam + '=' + index + '&' + appendLineParam + '=' + 1,
                success: function (html) {
                    $(formSelector).find($this.options.selectors.itemsTable).find('tbody').append(html);
                    $this.options.events.afterAdd.call($this, index);
                    updateItemsCount.call($this);
                }
            });
        };
        //onclick
        $(formSelector).on('click.myapp.lineitem', selector, function (e) {
            e.preventDefault();
            e.stopPropagation();
            _addLine(this);
        });
    };
    //delete item
    WIDGET.prototype.deleteItem = function () {
        var $this = this
            , selector = $this.options.selectors.deleteItemButton;

        var _delete = function (e) {
            var tr = $(e).closest('tr');
            $this.options.events.beforeDelete.call($this, tr);

            var url = $(e).data('href')
                , id = tr.find($this.options.selectors.primaryKeyField).val();

            if (MyApp.utils.empty(id)) {
                tr.remove();
                $this.options.events.afterDelete.call($this, tr);
                updateItemsCount.call($this);
                return false;
            }

            var actionParamDeleteItem = $this.options.actionParamDeleteItem
                , actionParamItemId = $this.options.actionParamItemId;

            $.ajax({
                type: 'POST',
                url: url,
                data: actionParamDeleteItem + '=' + 1 + '&' + actionParamItemId + '=' + id,
                dataType: 'json',
                success: function (response) {
                    $this.options.events.afterDelete.call($this, tr, response);
                    tr.remove();
                    updateItemsCount.call($this);
                }
            });
        };

        //on click
        $($this.options.selectors.itemsTable).on('click.myapp.lineitem', selector, function (e) {
            e.preventDefault();
            var $this = this;
            var confirm_msg = $($this).data('delete-confirm');
            if (MyApp.utils.empty(confirm_msg)) {
                _delete($this);
            } else {
                BootstrapDialog.confirm(confirm_msg, function (result) {
                    if (result) {
                        _delete($this);
                    }
                })
            }
        });
    };
    //save row
    WIDGET.prototype.saveItem = function () {
        var $this = this;

        var _save = function (e) {
            var tr = $(e).closest('tr');
            $this.options.events.beforeSave.call($this, tr);

            let url = $(e).data('href'),
                //data = MyApp.utils.serializeObject(tr),
                data = tr.find('input,select,textarea').serialize(),
                actionParamSaveItem = $this.options.actionParamSaveItem;
            data = actionParamSaveItem + '=' + 1 + '&' + data;
            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                dataType: 'json',
                success: function (responses) {
                    $.each(responses, function (i, response) {
                        processItemSaveResponse.call($this, tr, response);
                    });
                },
                error: function (XHR) {
                    if (MyApp.DEBUG_MODE) {
                        console.log(XHR.responseText);
                        MyApp.utils.showAlertMessage(XHR.responseText, 'error');
                    }
                }
            });
        };
        //on click
        $($this.options.selectors.itemsTable).on('click.myapp.lineitem', $this.options.selectors.saveLineItem, function (e) {
            e.preventDefault();
            _save(this);
        });
    };
    //finish
    WIDGET.prototype.finish = function () {
        var $this = this
            , formSelector = this.options.selectors.form
            , buttonSelector = this.options.selectors.finishButton;

        var _finish = function (e) {
            $this.options.events.beforeFinish.call($this);

            let form = $(formSelector);
            let url = form.attr('action'),
                // data = $(formSelector).serialize(),
                data = MyApp.utils.serializeObject(form);
            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.id) {
                        $($this.options.selectors.parentPrimaryKeyField).val(response.id);
                    }
                    if (response.error) {
                        MyApp.utils.display_model_errors(response.error, false, $this.options.parentModelShortClassName);
                    }
                    if (response.items) {
                        $.each(response.items, function (i, item) {
                            let tr = $('#' + $this.options.itemTrIdPrefix + '-' + i);
                            let success = processItemSaveResponse.call($this, tr, item, !response.error);
                            if (!success) {
                                return false;
                            }
                        });
                    }
                    if (response.success) {
                        $this.options.events.afterFinish.call($this, response);
                    }


                },
                beforeSend: function () {
                    MyApp.utils.startBlockUI();
                },
                complete: function () {
                    MyApp.utils.stopBlockUI();
                },
                error: function (XHR) {
                    MyApp.utils.showAlertMessage(XHR.responseText, 'error');
                    if (MyApp.DEBUG_MODE) {
                        console.log(XHR.responseText);
                    }
                }
            });
        };
        //on click
        $(formSelector).on('click.myapp.lineitem', buttonSelector, function (e) {
            e.preventDefault();
            _finish(this);
        });
    };

    MyApp.widget.initLineItem = function (options) {
        var obj = new WIDGET(options);
        obj.addItem();
        obj.saveItem();
        obj.deleteItem();
        obj.finish();
    };
}(jQuery));