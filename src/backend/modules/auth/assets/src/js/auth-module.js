/*
 *Module based js functions
 *@author Fred <mconyango@gmail.com>
 */

//module definitions
MyApp.modules.auth = {};

//roles
(function ($) {
    "use strict";

    var ROLES = function (options) {
        var defaultOptions = {
            select2Selector: "select.select2",
            selectAllSelector: "button.my-select-all"
        };

        this.options = $.extend({}, defaultOptions, options || {});
    };

    ROLES.prototype.toggleColumnCheckAll = function () {
        let _toggle = function (e) {
            let isChecked = $(e).is(":checked"),
                targetCheckBoxClass = $(e).data('target-class');
            if (isChecked) {
                $('.' + targetCheckBoxClass).prop("checked", true);
            } else {
                $('.' + targetCheckBoxClass).prop("checked", false);
            }
        };

        $(document.body).on("click", 'input.check-all-checkbox', function (e) {
            _toggle(this);
        });
    }

    //toggle
    ROLES.prototype.toggleCheckAll = function () {
        var $this = this;

        var _toggle = function (e) {
            var checkbox = $("input:checkbox.my-roles-checkbox")
                , isChecked = checkbox.is(":checked");
            if (isChecked) {
                checkbox.prop("checked", false);
                $(this).text("Uncheck all");
            } else {
                checkbox.prop("checked", true);
                $(this).text("Check all");
            }
        };

        $($this.options.selectAllSelector).on("click", function (e) {
            e.preventDefault();
            _toggle(this);
        });
    };

    var PLUGIN = function (options) {
        var obj = new ROLES(options);
        obj.toggleCheckAll();
        obj.toggleColumnCheckAll();
    };

    MyApp.modules.auth.roles = PLUGIN;
}(jQuery));
//users
(function ($) {
    "use strict";

    var USER = function (options) {
        var defaultOptions = {
            changeStatusSelector: "a.change-user-status"
        }
        this.options = $.extend({}, defaultOptions, options || {});
    }

    USER.prototype.changeStatus = function () {
        var $this = this
            , selector = $this.options.changeStatusSelector;

        var ajaxPost = function (e) {
            var url = $(e).data("href");
            $.ajax({
                url: url,
                type: "post",
                dataType: "json",
                success: function (data) {
                    if (data.success) {
                        MyApp.utils.reload();
                    }
                },
                error: function (xhr) {
                    if (MyApp.DEBUG_MODE) {
                        console.log(xhr);
                    }
                }
            })
        }

        //event
        $(selector).on("click.myapp", function (e) {
            e.preventDefault();
            ajaxPost(this);
        })
    }

    var PLUGIN = function (options) {
        var obj = new USER(options);
        obj.changeStatus();
    }

    MyApp.modules.auth.user = PLUGIN;
}(jQuery));
//user form filters (country,region,branch)
(function ($) {
    "use strict";

    var FILTER = function (options) {
        var defaultOptions = {
            baseId: undefined,
            filterOnLoad: true,
            countryField: "country_id",
            regionField: "region_id",
            branchField: "branch_id",
        };
        this.options = $.extend({}, defaultOptions, options || {})
    }

    var getSelector = function (field) {
        return "#" + this.options.baseId + "-" + field;
    }
    /**
     *
     * @param selector
     * @param targetSelector
     * @param changeOnLoad
     */
    var filter = function (selector, targetSelector, changeOnLoad) {
        if (changeOnLoad === "undefined") {
            changeOnLoad = false;
        }
        var ajaxPost = function (e) {
            var url = $(e).data("href")
                , value = $(e).val();

            $.ajax({
                url: url,
                type: "post",
                data: "id=" + value,
                dataType: "json",
                success: function (data) {
                    MyApp.utils.populateDropDownList(targetSelector, data);
                    $(targetSelector).change();
                },
                error: function (xhr) {
                    if (MyApp.DEBUG_MODE) {
                        console.log(xhr);
                    }
                }
            })
        }

        //event
        $(selector).on("change", function (e) {
            ajaxPost(this);
        });
        if (changeOnLoad) {
            $(selector).change();
        }
    }

    FILTER.prototype.country = function () {
        var $this = this;
        var selector = getSelector.call($this, $this.options.countryField)
            , targetSelector = getSelector.call($this, $this.options.regionField);
        filter.call($this, selector, targetSelector, $this.options.filterOnLoad);
    }

    FILTER.prototype.region = function () {
        var $this = this;
        var selector = getSelector.call($this, $this.options.regionField)
            , targetSelector = getSelector.call($this, $this.options.branchField);
        filter.call($this, selector, targetSelector);
    }

    var PLUGIN = function (options) {
        var obj = new FILTER(options);
        obj.country();
        obj.region();
    }

    MyApp.modules.auth.filter = PLUGIN;
}(jQuery));
//auto generate password
(function ($) {
    "use strict";
    let FORM = function (options) {
        let defaultOptions = {
            autoGeneratePasswordFieldSelector: '#users-auto_generate_password',
            passwordFieldsWrapper: '#password-fields-wrapper'
        };
        this.options = $.extend({}, defaultOptions, options || {});
    }
    FORM.prototype = {
        toggleFields: function () {
            let $this = this,
                selector = $this.options.autoGeneratePasswordFieldSelector;
            let _toggle = function (e) {
                let isChecked = $(e).is(':checked');
                if (isChecked) {
                    $($this.options.passwordFieldsWrapper).hide();
                } else {
                    $($this.options.passwordFieldsWrapper).show();
                }
            }
            _toggle(selector);
            $(selector).on('click', function (e) {
                _toggle(this);
            })
        }
    }

    MyApp.modules.auth.autoGeneratePassword = function (options) {
        let obj = new FORM(options);
        obj.toggleFields();
    }
}(jQuery));

//toggle organization
(function ($) {
    "use strict";
    let FORM = function (options) {
        let defaultOptions = {
            organizationWrapperSelector: '#organization-id-wrapper',
            levelIdFieldSelector: '#users-level_id',
        }
        this.options = $.extend({}, defaultOptions, options || {});
    }

    FORM.prototype = {
        toggleOrganization: function () {
            let $this = this,
                selector = $this.options.levelIdFieldSelector;
            let _toggle = function (e) {
                let val = $(e).val(),
                    showOrganizationFlags = $(e).data('show-organization');
                if (showOrganizationFlags.includes(parseInt(val))) {
                    $($this.options.organizationWrapperSelector).show();
                } else {
                    $($this.options.organizationWrapperSelector).hide();
                }
            }
            //on page load
            _toggle(selector);
            //on change
            $(selector).on('change', function (event) {
                _toggle(this);
            })
        }
    };

    MyApp.modules.auth.toggleOrganization = function (options) {
        let obj = new FORM(options);
        obj.toggleOrganization();
    }
}(jQuery));

//init user create/update form
(function ($) {
    "use strict";
    MyApp.modules.auth.initUserForm = function (options) {
        MyApp.modules.auth.toggleOrganization(options);
        MyApp.modules.auth.autoGeneratePassword(options);
    }
}(jQuery));