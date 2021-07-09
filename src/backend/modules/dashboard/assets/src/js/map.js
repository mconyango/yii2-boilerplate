MyApp.plugin = MyApp.plugin || {};
(function ($) {
    "use strict";
    let MAP = function (options) {
        let defaultOptions = {
            mapData: null,
            mapPath: null,
        };
        this.options = $.extend({}, defaultOptions, options || {});
    }

    var baseMapPath = "mapdata/",
        showDataLabels = false,// Switch for data labels enabled/disabled
        mapCount = 0,
        searchText,
        mapOptions = '';

    var getRating = function (country_iso2) {
        let $this = this,
            mapData = JSON.parse($this.options.mapData),
            countryData = mapData[country_iso2];

        if (typeof countryData === 'undefined') {
            return null;
        }

        if (MyApp.utils.empty(countryData.assessmentLine) || !countryData.assessmentLine.rating) {
            return null;
        }

        return parseFloat(countryData.assessmentLine.rating);
    }

    MAP.prototype.run = function () {
        let $this = this,
            mapDropdownSelector = '#mapDropdown',
            chkDataLabelsSelector = '#chkDataLabels';
        // Populate dropdown menus and turn into jQuery UI widgets

        $.each(Highcharts.mapDataIndex, function (mapGroup, maps) {
            if (mapGroup !== "version") {
                mapOptions += '<option class="option-header">' + mapGroup + '</option>';
                $.each(maps, function (desc, path) {
                    mapOptions += '<option value="' + path + '">' + desc + '</option>';
                    mapCount += 1;
                });
            }
        });
        searchText = 'Search ' + mapCount + ' maps';
        mapOptions = '<option value="custom/world.js">' + searchText + '</option>' + mapOptions;
        $(mapDropdownSelector).append(mapOptions).combobox();
        // Change map when item selected in dropdown
        $(mapDropdownSelector).change(function () {
            var $selectedItem = $("option:selected", this),
                mapDesc = $selectedItem.text(),
                mapKey = this.value.slice(0, -3),
                svgPath = baseMapPath + mapKey + '.svg',
                geojsonPath = baseMapPath + mapKey + '.geo.json',
                javascriptPath = baseMapPath + this.value,
                isHeader = $selectedItem.hasClass('option-header');

            // Dim or highlight search box
            if (mapDesc === searchText || isHeader) {
                $('.custom-combobox-input').removeClass('valid');
                location.hash = '';
            } else {
                $('.custom-combobox-input').addClass('valid');
                location.hash = mapKey;
            }

            if (isHeader) {
                return false;
            }

            // Show loading
            if (Highcharts.charts[0]) {
                Highcharts.charts[0].showLoading('<i class="fa fa-spinner fa-spin fa-2x"></i>');
            }

            /**
             * Returns a random number between min (inclusive) and max (exclusive)
             */
            function getRandomArbitrary(min, max) {
                let n = Math.random() * (max - min) + min;
                return Math.round(n * 10) / 10;
            }


            // When the map is loaded or ready from cache...
            function mapReady() {

                var mapGeoJSON = Highcharts.maps[mapKey],
                    data = [],
                    parent,
                    match;

                // Generate non-random data for the map
                $.each(mapGeoJSON.features, function (index, feature) {
                    let iso2 = feature.properties['hc-a2'],
                        rating = getRating.call($this, iso2);
                    data.push({
                        key: feature.properties['hc-key'],
                        value: rating
                    });
                });

                // Show arrows the first time a real map is shown
                if (mapDesc !== searchText) {
                    $('.selector .prev-next').show();
                    $('#sideBox').show();
                }

                // Is there a layer above this?
                match = mapKey.match(/^(countries\/[a-z]{2}\/[a-z]{2})-[a-z0-9]+-all$/);
                if (/^countries\/[a-z]{2}\/[a-z]{2}-all$/.test(mapKey)) { // country
                    parent = {
                        desc: 'World',
                        key: 'custom/world'
                    };
                } else if (match) { // admin1
                    parent = {
                        desc: $('option[value="' + match[1] + '-all.js"]').text(),
                        key: match[1] + '-all'
                    };
                }
                $('#up').html('');
                if (parent) {
                    $('#up').append(
                        $('<a><i class="fa fa-angle-up"></i> ' + parent.desc + '</a>')
                            .attr({
                                title: parent.key
                            })
                            .click(function () {
                                $('#mapDropdown').val(parent.key + '.js').change();
                            })
                    );
                }


                // Instantiate chart
                $("#container").highcharts('Map', {

                    title: {
                        text: null
                    },

                    mapNavigation: {
                        enabled: true
                    },

                    colorAxis: {
                        min: 0,
                        stops: [
                            [0, '#EFEFFF'],
                            [0.5, Highcharts.getOptions().colors[0]],
                            [1, Highcharts.Color(Highcharts.getOptions().colors[0]).brighten(-0.5).get()]
                        ]
                    },

                    legend: {
                        layout: 'vertical',
                        align: 'left',
                        verticalAlign: 'bottom'
                    },

                    series: [{
                        data: data,
                        mapData: mapGeoJSON,
                        joinBy: ['hc-key', 'key'],
                        name: 'INDICATOR: Freedom to establish and choose schools',
                        states: {
                            hover: {
                                color: Highcharts.getOptions().colors[2]
                            }
                        },
                        dataLabels: {
                            enabled: showDataLabels,
                            formatter: function () {
                                return mapKey === 'custom/world' || mapKey === 'countries/us/us-all' ?
                                    (this.point.properties && this.point.properties['hc-a2']) :
                                    this.point.name;
                            }
                        },
                        point: {
                            events: {
                                // On click, look for a detailed map
                                click: function () {
                                    var key = this.key;
                                    $('#mapDropdown option').each(function () {
                                        if (this.value === 'countries/' + key.substr(0, 2) + '/' + key + '-all.js') {
                                            $('#mapDropdown').val(this.value).change();
                                        }
                                    });
                                }
                            }
                        }
                    }, {
                        type: 'mapline',
                        name: "Separators",
                        data: Highcharts.geojson(mapGeoJSON, 'mapline'),
                        nullColor: 'gray',
                        showInLegend: false,
                        enableMouseTracking: false
                    }]
                });

                showDataLabels = $("#chkDataLabels").prop('checked');

            }

            // Check whether the map is already loaded, else load it and
            // then show it async
            if (Highcharts.maps[mapKey]) {
                mapReady();
            } else {
                $.getScript(javascriptPath, mapReady);
            }
        });

        // Toggle data labels - Note: Reloads map with new random data
        $(chkDataLabelsSelector).change(function () {
            showDataLabels = $(chkDataLabelsSelector).prop('checked');
            $(mapDropdownSelector).change();
        });

        // Switch to previous map on button click
        $("#btn-prev-map").click(function () {
            $("#mapDropdown option:selected").prev("option").prop("selected", true).change();
        });

        // Switch to next map on button click
        $("#btn-next-map").click(function () {
            $(mapDropdownSelector).find('option:selected').next("option").prop("selected", true).change();
        });

        // Trigger change event to load map on startup
        if ($this.options.mapPath) {
            $(mapDropdownSelector).val($this.options.mapPath);
        } else if (location.hash) {
            $(mapDropdownSelector).val(location.hash.substr(1) + '.js');
        } else { // for IE9
            $($(mapDropdownSelector + ' option')[0]).attr('selected', 'selected');
        }
        $(mapDropdownSelector).change();
    }

    MyApp.plugin.highmaps = function (options) {
        let obj = new MAP(options);
        obj.run();
    }
}(jQuery));