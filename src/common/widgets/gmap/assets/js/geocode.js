/**
 * @author Fred <fred@btimillman.com>.
 * Date & Time Created 2017-06-02 3:24 PM
 */
MyApp.gmap = MyApp.gmap || {};

(function ($) {
    'use strict';
    var MAP = function (options) {
        var defaultOptions = {
            latitude: null,
            longitude: null,
            mapWrapperId: 'map_canvas',
            geocodeUrl: null,
            modelClass: null,
            latitudeAttribute: 'latitude',
            longitudeAttribute: 'longitude',
            addressAttribute: 'map_address',
            latitudeInputSelector: null,
            longitudeInputSelector: null,
            addressInputSelector: null,
            addressSearchFieldId: null,
            mapType: 'ROADMAP',
            zoom: 16,
            panControl: true,
            zoomControl: true,
            scaleControl: true,
            markerColor: 'FF0000',
            infowindowContents: []
        };

        this.options = $.extend({}, defaultOptions, options || {});
        this.markersArray = [];
        this.map = null;
        this.infowindow = null;
        this.options.latitudeInputSelector = MyApp.utils.getActiveFormFieldSelector(this.options.modelClass, this.options.latitudeAttribute);
        this.options.longitudeInputSelector = MyApp.utils.getActiveFormFieldSelector(this.options.modelClass, this.options.longitudeAttribute);
        this.options.addressInputSelector = MyApp.utils.getActiveFormFieldSelector(this.options.modelClass, this.options.addressAttribute);
    }

    var placeMarker = function (position, reverse_geocode) {
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

        google.maps.event.addListener(marker, 'click', function () {
            $this.show_info_window(marker);
            showInfoWindow.call($this, marker);
        });

        updateLatLng.call($this, position);

        if (reverse_geocode) {
            reverseGeocode.call($this, marker);
        }
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

    var reverseGeocode = function (marker) {
        var $this = this;
        var latlng = marker.getPosition();
        var geocoder = new google.maps.Geocoder();

        geocoder.geocode({latLng: latlng}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                    if (MyApp.DEBUG_MODE) {
                        console.log(results[0]);
                    }
                    var address = results[0].formatted_address;
                    if (address) {
                        updateAddress.call($this, address);
                    }
                }
            }
        });
    }

    var updateAddress = function (address) {
        var $this = this;
        $($this.options.addressInputSelector).val(address);
    }

    var updateLatLng = function (position) {
        var $this = this;
        $($this.options.latitudeInputSelector).val(position.lat());
        $($this.options.longitudeInputSelector).val(position.lng());
    }

    //object public functions
    MAP.prototype.init = function () {
        var $this = this;
        var mapOptions = {
            zoom: $this.options.zoom,
            panControl: $this.options.panControl,
            zoomControl: $this.options.zoomControl,
            scaleControl: $this.options.scaleControl,
            mapTypeId: google.maps.MapTypeId[$this.options.mapType]
        };

        //set the center
        if ($this.options.latitude && $this.options.longitude) {
            mapOptions.center = new google.maps.LatLng($this.options.latitude, $this.options.longitude);
        }

        //create the map
        $this.map = new google.maps.Map(document.getElementById($this.options.mapWrapperId), mapOptions);

        //place marker
        if (mapOptions.center) {
            placeMarker.call($this, mapOptions.center);
        }

        $this.map.addListener('click', function (event) {
            placeMarker.call($this, event.latLng, true);
        });

        $('#' + $this.options.addressSearchFieldId).on('click', function (event) {
            event.preventDefault();
            $this.geocode();
        });

    }
    MAP.prototype.geocode = function () {
        var $this = this;
        var address = $($this.options.addressInputSelector).val();

        $.ajax({
            type: 'get',
            url: $this.options.geocodeUrl,
            dataType: 'json',
            data: 'address=' + address,
            success: function (response) {
                if (response) {
                    if (response[0]) {
                        var location = response[0].geometry.location;
                        placeMarker.call($this, new google.maps.LatLng(location.lat, location.lng));
                    }

                }
            },
            beforeSend: function () {
                MyApp.utils.startBlockUI('Loading...');
            },
            complete: function () {
                MyApp.utils.stopBlockUI();
            },
            error: function (response) {
                if (MyApp.DEBUG_MODE) {
                    console.log(response);
                }
            }
        });
        return false;
    }

    MyApp.gmap.geocode = function (options) {
        var obj = new MAP(options);
        obj.init();
    }
}(jQuery));