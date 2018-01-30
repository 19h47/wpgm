jQuery(document).ready(function($) {

	var el = document.getElementById('map_canvas');

	if(!el) {
		return;
	}

	var googlemaps = google.maps;
	var map;
	var marker;
	var defaultloc;
	var lat = $('#wpgm_latitude').val();
	var lon = $('#wpgm_longitude').val();

	defaultloc = new googlemaps.LatLng(48.864716, 2.349014);

	// Set up our map canvas with default coordinates for the US
	map = new googlemaps.Map(el, {
		zoom: 4,
		center: defaultloc,
		mapTypeId: googlemaps.MapTypeId.ROADMAP,
	});

	// Set a single marker. Don't assign it a position yet.
	marker = new googlemaps.Marker({
		map: map,
		draggable: true,
	});

	// Set our marker position
	function WPGM_map_markers_set( lat, lon ) {

		marker.setPosition(new googlemaps.LatLng(lat, lon));
		map.setCenter(marker.position);
		map.setZoom(16);

		$('#wpgm_latitude').val(lat);
		$('#wpgm_longitude').val(lon);
	}


	// Set our marker position if we have one already saved
	$('#map_canvas').ready(function(event){
		if (lat && lon) {
			WPGM_map_markers_set(lat, lon);
		}
	});

	// Listen for marker movement and update coordinates
	googlemaps.event.addListener(marker, 'dragend', function(evt){
		$('#wpgm_latitude').val(evt.latLng.lat().toFixed(6));
		$('#wpgm_longitude').val(evt.latLng.lng().toFixed(6));
	});

   // Lookup coordinates from Google
   $('#wpgm_address_search_submit').on('click', function(event) {
		// Stop the default submission from happening
	   	event.preventDefault();

   		adressSearchSubmit();
   });

   adressSearchSubmit();

   function adressSearchSubmit() {
   		// console.log('adressSearchSubmit');

	   	// Grab our form value
	   	var address = $('#wpgm_address').val();

	   	$.ajax({
		   	type : 'post',
		   	dataType : 'json',
		   	url : wpgm_ajax.ajax_url,
		   	data : {
			   'action': 'wpgm_address_search',
			   'address': address
		   	},
		   	success : function(response) {
			   WPGM_map_markers_set(response.latitude, response.longitude);
		   	}
	   	});
   	}


   // Clear our map and reset to original state
   $('#wpgm_address_clear').on('click', function( event ) {

		// Stop the default submission from happening
		event.preventDefault();

		// Clear our form values
		$('#wpgm_address').val('');
		$('#wpgm_latitude').val('');
		$('#wpgm_longitude').val('');

		// Clear our marker and reset our map
		marker.setPosition();
		map.setCenter(defaultloc);
		map.setZoom(4);
	});
});