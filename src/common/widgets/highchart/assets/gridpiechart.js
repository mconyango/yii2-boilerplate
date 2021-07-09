/**
 * Created by mconyango on 7/20/15.
 */
(function ($) {
    'use strict';
    var PIE = function (container, series, graphOptions) {
        var DEFAULT_GRAPH_OPTIONS = {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null, //null,
                plotShadow: false,
                backgroundColor: 'transparent',
                spacingTop: 3,
            },
            credits: {
                enabled: false,
            },
            title: {
                text: null,
            },
            tooltip: {
                pointFormat: '<b>{point.percentage:.0f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    size: 80,
                    dataLabels: {
                        enabled: false,
                    }
                }
            },
        };
        this.container = container;
        this.series = series;
        this.graphOptions = $.extend(true, {}, DEFAULT_GRAPH_OPTIONS, graphOptions || {});
    };

    PIE.prototype.create = function () {
        var $this = this;
        var options = $.extend(true, {}, $this.graphOptions, {series: $this.series});
        $('#' + $this.container).highcharts(options);
    };

    var PLUGIN = function (container, series, graphOptions) {
        var obj = new PIE(container, series, graphOptions);
        obj.create();
    };
    MyApp.plugin.gridPieChart = PLUGIN;
}(jQuery));