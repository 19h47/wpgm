var map_styles = [
    {
        "featureType": "all",
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "saturation": 36
            },
            {
                "color": "#333333"
            },
            {
                "lightness": 40
            }
        ]
    },
    {
        "featureType": "all",
        "elementType": "labels.text.stroke",
        "stylers": [
            {
                "visibility": "on"
            },
            {
                "color": "#ffffff"
            },
            {
                "lightness": 16
            }
        ]
    },
    {
        "featureType": "all",
        "elementType": "labels.icon",
        "stylers": [
            {
                "visibility": "off"
            }
        ]
    },
    {
        "featureType": "administrative",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "color": "#fefefe"
            },
            {
                "lightness": 20
            }
        ]
    },
    {
        "featureType": "administrative",
        "elementType": "geometry.stroke",
        "stylers": [
            {
                "color": "#fefefe"
            },
            {
                "lightness": 17
            },
            {
                "weight": 1.2
            }
        ]
    },
    {
        "featureType": "landscape",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#f5f5f5"
            },
            {
                "lightness": 20
            }
        ]
    },
    {
        "featureType": "poi",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#f5f5f5"
            },
            {
                "lightness": 21
            }
        ]
    },
    {
        "featureType": "poi.park",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#dedede"
            },
            {
                "lightness": 21
            }
        ]
    },
    {
        "featureType": "road.highway",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "color": "#ffffff"
            },
            {
                "lightness": 17
            }
        ]
    },
    {
        "featureType": "road.highway",
        "elementType": "geometry.stroke",
        "stylers": [
            {
                "color": "#ffffff"
            },
            {
                "lightness": 29
            },
            {
                "weight": 0.2
            }
        ]
    },
    {
        "featureType": "road.arterial",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#ffffff"
            },
            {
                "lightness": 18
            }
        ]
    },
    {
        "featureType": "road.local",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#ffffff"
            },
            {
                "lightness": 16
            }
        ]
    },
    {
        "featureType": "transit",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#f2f2f2"
            },
            {
                "lightness": 19
            }
        ]
    },
    {
        "featureType": "water",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#e9e9e9"
            },
            {
                "lightness": 17
            }
        ]
    }
];


var WPGM = function (id) {
    if (!(this instanceof WPGM)) {
        return new WPGM();
    }

    var wrapper = document.getElementsByClassName('js-map')[0];

	if (!wrapper) {
		return;
	}

    var el = wrapper.getElementsByClassName('map_canvas')[0];

    
    this.id = false;
    if(id) {
        this.id = id;
    }

	this.inputCheckbox = wrapper.getElementsByClassName('js-filter');

	this.options = {
		center: {
            lat: 48.8534100, 
            lng: 2.3488000
        },
        zoomControl: true,
		mapTypeControl: false,
		scaleControl: false,
		streetViewControl: false,
		rotateControl: false,
        scrollwheel: false,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: map_styles, 
        zoom: 12
	};

	this.map = new google.maps.Map(el, this.options);
	this.infoWindows = [];
	this.markers = [];
	var marker;
	var bounds;

	// Add control
	var controlSelectCategory = wrapper.getElementsByClassName('js-control-event-category')[0];
	
    $(el).append(controlSelectCategory);

	this.ajax();
	this.filter_markers(this.inputCheckbox); 
}


WPGM.prototype = {
    
    /**
     * WPGM.ajax methode
     */
    ajax: function() {
    	var _this = this;

        var id = wpgm_ajax.id;

        if(this.id) {
            id = this.id;

            console.log(this.id);
        }

        // console.log(wpgm_ajax.id);

    	$.ajax({
    		type: 'post',
    		dataType: 'json',
    		method : 'POST',
    		url: wpgm_ajax.ajax_url,
    		data: {
    			'action': 'wpgm_get_markers', 
    			'city': wpgm_ajax.city,
                'category': wpgm_ajax.category,
                'id': id
    			},
    			success: function(response) {

    				_this.success(response);
    			},
    			error: function(xhr){
            	   console.log(xhr);
    			}
    	});
    },


    /**
     * WPGM.success
     */
    success: function(response) {
    	var _this = this;

    	$.each(response.markers, function(i, marker) {
    		_this.add_marker(marker);	
    	});

    	_this.centerMap();
    },


    /**
     * WPGM.filter_markers methode
     */
    filter_markers: function (e) {
    	var _this = this;

    	$(e).on('change', function() {
    		var filters = [];

            // On select, close all info windows  
            _this.closeAllInfoWindows();

            // Empty array

    		$(e).each(function(i, element) {
                if($(element).val() == '0' ) {
                    return true;
                }

    			// Push all value of options in filters array
    			filters.push($(element).val());

            });

            // console.log(filters);

    		// If no option is select
    		// (option none value is egal to nothing)
    		if (filters.reduce(function (a, b) { return a + b }, 0) === 0 ) {

    			// Empty array
    			var filters = [];

    			for (var i = 0; i < _this.markers.length; i++) {
    				
    				// Set all markers visible
    			    _this.markers[i].setVisible(true);
    			}

    			// And return
    			return;
    		}

            // For each marker in markers   

            for (var i = 0; i < _this.markers.length; i++) {

                marker = _this.markers[i];
                var arrayComparison = [];

                // First hide the marker
                marker.setVisible(false);

                // Loop throught all filter in marker
                for (var y = 0; y < marker.filters.length; y++) {
                    
                    // console.log(marker.filters[y]);

                    // For each index in filters
                    for (var u = 0; u < filters.length; u++) {
                        // console.log(filters[u]);
                        
                        // if marker filter is egal to filter
                        if (marker.filters[y] == filters[u]) {

                            // If marker filter is not in array comparison
                            if ($.inArray(marker.filters[y], arrayComparison)) {

                                arrayComparison.push(marker.filters[y]);
                            }
                        }
                    }

                }

                // If array comparison have same length that filters length
                if (arrayComparison.length === filters.length) {
                    // Set marker to visible
                    marker.setVisible(true);
                }
    		}
    	});
    },


    /**
     * WPGM.closeAllInfoWindows methode
     */
    closeAllInfoWindows: function(infowindows) {
        var _this = this;
    	// For each infowindows in array
    	for (var i=0;i<_this.infoWindows.length;i++) {
    		// Close it
    	    _this.infoWindows[i].close();
      	}
    },

    /**
     * WPGM.add_marker methode
     *
     *  This function will add a marker to the selected Google Map
     */
    add_marker: function(marker) {

    	// var
    	var _this = this;
    	var latlng = new google.maps.LatLng(marker.coordinates.latitude, marker.coordinates.longitude);

    	// create marker 
    	var marker = new google.maps.Marker({
    		title: marker.title,
    		position: latlng,
    		animation: google.maps.Animation.DROP,
    		map: this.map,
    		address: marker.address,
    		category: marker.category,
            filters: marker.filters,
    		date: marker.date,
    		permalink: marker.permalink,
    		thumbnail: marker.thumbnail,
    		icon: '/wp-content/themes/les48h/img/svg/marker-' + marker.category[0] + '.svg'
    	});
    	
    	// Push this marker to array
    	this.markers.push(marker);

    	var contentString = 
            '<div class="l-info-marker"><a href="' 
            + marker.permalink + 
            '"><div class="l-info-marker__thumbnail" style="background-image: url(\'' 
            + marker.thumbnail + 
            '\');"></div>' 
            + marker.date.join('') + 
            '<p class="l-info-marker__title">' 
            + marker.title + 
            '</p><p class="Map-pin--blue color-blue-dark-grayish font-size-12 font-semibold uppercase no-margin">' 
            + marker.address + 
            '</p></a></div>';

    	// create info window
    	var infowindow = new google.maps.InfoWindow({
    		content: contentString,

    	});

    	// Push this infowindow to array
    	this.infoWindows.push(infowindow); 

    	// show info window when marker is hover
    	google.maps.event.addListener(marker, 'mouseover', function() {
    		
    		// Close all close infoWindows
    		_this.closeAllInfoWindows(this.infowindow);

    		infowindow.open(this.map, marker);
    		this.map.panTo(this.getPosition());

    	});
    },


    /**
     *  WPGM.centerMap methode
     *
     *  This function will center the map, showing all markers attached to this map
     *
     *  @type function
     */
    centerMap: function() {
        var _this = this;
    	// vars
    	bounds = new google.maps.LatLngBounds();

    	// loop through all markers and create bounds
    	$.each(this.markers, function(i, marker){

    		var latlng = new google.maps.LatLng(marker.position.lat(), marker.position.lng());

    		bounds.extend(latlng);
    	});

    	// Sets the viewport to contain the given bounds.
    	for (var i = 0; i < this.markers.length; i++) {

    		if( _this.markers.length === 1 ) {
    			// set center of map
    		    _this.map.setCenter( bounds.getCenter() );
    		    _this.map.setZoom( 16 );

    		    return;
    		}

    		// fit to bounds
    		_this.map.fitBounds( bounds );
    	}
    }
};

var city_map = new WPGM();