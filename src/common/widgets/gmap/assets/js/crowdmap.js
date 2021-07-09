/**
 *Contains js functions that call the google map API for using Gmap services on a website
 *@author Fredrick <mconyango@gmail.com>
 *@link https://developers.google.com/maps/documentation/javascript/
 *@date Mon 3rd Dec 2012
 */

/**
 *mother container of all methods and properties
 */
var MyGmapCrowdMap = {
        /**
         *stores all the created markers
         */
        markersArray: [],
        /**
         *stores the map object
         */
        map: null
        ,
        infowindow_contents: []
        ,
        /**
         *infowindow
         */
        infowindow: null
        ,
        options: {
                lat: null,
                lng: null,
                map_wrapper_id: 'map_canvas',
                data: null,
                infowindow_content_template: null,
                mapType: 'ROADMAP',
                zoom: 16,
                panControl: true,
                zoomControl: true,
                scaleControl: true,
                marker_color: 'FF0000',
                lat_field: 'latitude',
                lng_field: 'longitude',
        }
        ,
        init: function(options) {
                'use strict';
                var $this = this
                        , map_options;
                $this.options = $.extend({}, $this.options, options || {});
                //set the map options
                map_options = {
                        zoom: $this.options.zoom,
                        panControl: $this.options.panControl,
                        zoomControl: $this.options.zoomControl,
                        scaleControl: $this.options.scaleControl,
                        mapTypeId: google.maps.MapTypeId[$this.options.mapType]
                };
                //set the center
                if ($this.options.lat && $this.options.lng) {
                        var position = new google.maps.LatLng($this.options.lat, $this.options.lng);
                        map_options.center = position;
                }
                //create the map
                $this.map = new google.maps.Map(document.getElementById($this.options.map_wrapper_id), map_options);
                //populate markers
                $this.populate_markers($this.options.data);
        }
        ,
        place_marker: function(position, index, color_code) {
                var $this = this;
                if (MyUtils.empty(color_code))
                        color_code = $this.options.marker_color;
                if (MyUtils.empty(index))
                        index = 0;

                var icon = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|' + color_code
                        , marker = new google.maps.Marker({
                                position: position,
                                map: $this.map,
                                icon: icon
                        });
                $this.markersArray[index] = marker;

                google.maps.event.addListener(marker, 'click', function() {
                        //set centre
                        $this.map.setCenter(marker.getPosition());
                        $this.show_info_window(marker, index);
                });

                return marker;
        }
        ,
        /**
         * Delete overlays (markers)
         */
        deleteOverlays: function() {
                var $this = this;
                if ($this.markersArray) {
                        for (i in $this.markersArray) {
                                $this.markersArray[i].setMap(null);
                        }
                        $this.markersArray.length = 0;
                }
        }
        ,
        //reverse geocode
        reverse_geocode: function(marker) {
                var latlng = marker.getPosition()
                        , geocoder = new google.maps.Geocoder()
                        , location_name = '';

                geocoder.geocode({latLng: latlng}, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                                if (results[0]) {
                                        location_name = results[0].formatted_address;
                                }
                        }

                });

                return location_name;
        }
        ,
        show_info_window: function(marker, index) {
                var $this = this
                        , content;
                //remove any info window opened
                $this.remove_info_window();
                if (MyUtils.empty($this.infowindow_contents[index]))
                        content = $this.reverse_geocode(marker);
                else
                        content = $this.infowindow_contents[index];

                if (MyUtils.empty(content))
                        return false;

                var options = {
                        content: content,
                        maxWidth: 200,
                        pixelOffset: new google.maps.Size(0, 20)
                };
                $this.infowindow = new google.maps.InfoWindow(options);
                $this.infowindow.open($this.map, marker);
        }
        ,
        remove_info_window: function() {
                var $this = this;
                if ($this.infowindow)
                        $this.infowindow.close();
                return false;
        }
        ,
        populate_markers: function() {
                //delete any overlays
                var $this = this;
                if (MyUtils.empty($this.options.data))
                        return false;
                $this.deleteOverlays();
                $this.infowindow_contents = [];
                $.each($this.options.data, function(i, item) {

                        var template = Handlebars.compile($this.options.infowindow_content_template)
                                , content = template(item)
                                , position = new google.maps.LatLng(item[$this.options.lat_field], item[$this.options.lng_field]);
                        $this.place_marker(position, item.id, item.color_code);
                        $this.infowindow_contents[item.id] = content;
                });
        }
}