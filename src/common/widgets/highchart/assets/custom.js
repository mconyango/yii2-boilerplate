/**
 * Created by mconyango on 7/19/15.
 */
(function ($) {
    'use strict';

    let HIGHCHART = function (graphOptions, filterOptions) {
        this.chart = {};
        this.graphOptions = graphOptions;
        var defaultFilterOptions = {
            containerId: undefined,
            showFilter: false,
            filterFormId: undefined,
            dateRangeFilterId: undefined,
            graphTypeFilterId: undefined,
            dateRangeFrom: undefined,
            dateRangeTo: undefined,
            dateRangeFormat: undefined,
            showSummaryStats: false,
            summaryStatsWrapperId: undefined,
        }

        this.filterOptions = $.extend({}, defaultFilterOptions, filterOptions || {});
        if (this.filterOptions.showFilter) {
            setGraphFilter.call(this);
        }
    };

    let setGraphFilter = function () {
        let $this = this;
        //graph_type events
        $('#' + $this.filterOptions.filterFormId).find('button').on('click.myapp.plugin.highchart', function (event) {
            event.preventDefault();
            $this.reloadGraph();
        });
    };

    let create = function () {
        let $this = this;
        //destroy any existing chart b4 creating another one
        if (!MyApp.utils.empty($this.chart)) {
            $this.chart.destroy();
        }

        $this.chart = new Highcharts.Chart($this.graphOptions);
    }

    HIGHCHART.prototype.create = function () {
        create.call(this);
    };

    HIGHCHART.prototype.reloadGraph = function () {
        let $this = this
            , form = $('#' + $this.filterOptions.filterFormId)
            , url = form.attr('action')
            , data = form.serialize();
        $.ajax({
            type: 'get',
            url: url,
            data: data,
            dataType: 'json',
            success: function (options) {
                if ($this.filterOptions.showSummaryStats) {
                    $('#' + $this.filterOptions.summaryStatsWrapperId).html(options.summaryStats);
                }
                $this.graphOptions = options.graphOptions;
                create.call($this);
            },
            beforeSend: function () {
                MyApp.utils.startBlockUI('Please wait...');
            },
            complete: function () {
                MyApp.utils.stopBlockUI();
            },
            error: function (XHR) {
                if (MyApp.DEBUG_MODE) {
                    //let message = XHR.responseText;
                    //MyApp.utils.showAlertMessage(message, 'error');
                    console.log(XHR);
                }
            }
        });
    };

    let PLUGIN = function (graphOptions, filterOptions) {
        let obj = new HIGHCHART(graphOptions, filterOptions);
        obj.create();
    };

    MyApp.plugin.highCharts = PLUGIN;
}(jQuery));
