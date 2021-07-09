/**
 *Contains js functions that call the google map API for using Gmap services on a website
 *@author Fredrick <mconyango@gmail.com>
 *@link https://developers.google.com/maps/documentation/javascript/
 *@date Sat 27th Feb 2016
 */
MyApp.gmap = MyApp.gmap || {};
(function ($) {
    'use strict';

    //constructor
    var MAP = function (options) {
        var defaultOptions = {
            latitude: null,
            longitude: null,
            mapWrapperId: 'map_canvas',
            infowindowContent: null,
            mapType: 'ROADMAP',
            zoom: 16,
            panControl: true,
            zoomControl: true,
            scaleControl: true,
            markerColor: 'FF0000'
        }

        this.options = $.extend({}, defaultOptions, options || {});
        this.markersArray = [];
        this.map = null;
        this.infowindow = null;
    }

    //utility functions
    var placeMarker = function (position) {
        var $this = this;
        deleteOverlays.call($this);
        var icon = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|' + $this.options.markerColor;
        var marker = new google.maps.Marker({
            position: position,
            map: $this.map,
            icon: icon
        });

        $this.map.setCenter(position);
        $this.markersArray.push(marker);
        showInfoWindow.call($this, marker);
        return marker;
    }

    var deleteOverlays = function () {
        var $this = this;
        if ($this.markersArray) {
            for (var i in $this.markersArray) {
                $this.markersArray[i].setMap(null);
            }
            $this.markersArray.length = 0;
        }
    }

    var showInfoWindow = function (marker) {
        var $this = this
            , content;
        //remove any info window opened
        removeInfoWindow.call($this);
        if (MyApp.utils.empty($this.options.infowindowContent))
            content = reverseGeocode.call($this, marker);
        else
            content = $this.options.infowindowContent;

        if (MyApp.utils.empty(content))
            return false;

        var options = {
            content: content,
            maxWidth: 200,
            pixelOffset: new google.maps.Size(0, 20)
        };
        $this.infowindow = new google.maps.InfoWindow(options);
        $this.infowindow.open($this.map, marker);
    }

    var removeInfoWindow = function () {
        if (this.infowindow)
            this.infowindow.close();
        return false;
    }

    /**
     *
     * @param marker
     * @returns {string}
     */
    var reverseGeocode = function (marker) {
        var latlng = marker.getPosition()
            , geocoder = new google.maps.Geocoder()
            , location_name = '';

        geocoder.geocode({latLng: latlng}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                    location_name = results[0].formatted_address;
                }
            }

        });

        return location_name;
    }

    //object public functions
    MAP.prototype.init = function () {
        var $this = this;
        var map_options = {
            zoom: $this.options.zoom,
            panControl: $this.options.panControl,
            zoomControl: $this.options.zoomControl,
            scaleControl: $this.options.scaleControl,
            mapTypeId: google.maps.MapTypeId[$this.options.mapType]
        };

        //set the center
        if ($this.options.latitude && $this.options.longitude) {
            var position = new google.maps.LatLng($this.options.latitude, $this.options.longitude);
            map_options.center = position;
        }

        //create the map
        $this.map = new google.maps.Map(document.getElementById($this.options.mapWrapperId), map_options);
        //place marker
        if (position)
            placeMarker.call($this, position);
    }

    //expose this plugin
    var PLUGIN = function (options) {
        var obj = new MAP(options);
        obj.init();
    }

    MyApp.gmap.singleView = PLUGIN;
}(jQuery));
