<?php

    global $wpdb;

    $layout = "<div id='map' style='margin-bottom:20px;'></div>";

    $settings_tbl = $wpdb->prefix.'pukpun_settings';
    $result = $wpdb->get_row("SELECT * FROM $settings_tbl WHERE key_name = 'query_api'");
    $queryUrl = $result->key_value;

    $loggedInUser = get_current_user_id();

    $billingAddress1 = get_user_meta($loggedInUser, 'billing_address_1', true); // address
    $billingAddress2 = get_user_meta($loggedInUser, 'billing_address_2', true); // Tambol
    $billingCity = get_user_meta($loggedInUser, 'billing_city', true); // Amphur
    $billingProvince = get_user_meta($loggedInUser, 'billing_state', true);
    $billingPostcode = get_user_meta($loggedInUser, 'billing_postcode', true);

    $user_firstName = get_user_meta($loggedInUser, 'first_name', true);
    $user_lastName = get_user_meta($loggedInUser, 'last_name', true);
    $user_telephone = get_user_meta($loggedInUser, 'billing_phone', true);

?>

<script>
    var provinceCode = "<?= $billingProvince; ?>";
    var billingPostcode = "<?= $billingPostcode; ?>";
    var user_firstName = "<?= $user_firstName; ?>";
    var user_lastName = "<?= $user_lastName; ?>";
    var user_telephone = "<?= $user_telephone; ?>";

    var getPosition = function (options) {
        return new Promise(function (resolve, reject) {
            navigator.geolocation.getCurrentPosition(resolve, reject, options);
        });
    }

    const getNearestHub = () => {
        // console.log('Selecting Nearest Hub');
        getPosition().then((position) => {
            let mLatlng = {
                lat : position.coords.latitude,
                lng : position.coords.longitude
            };
            localStorage.setItem("device_latlng", JSON.stringify(mLatlng));
        }).catch((err) => {
            console.error(err.message);
            alert('Please check location permission and try again.');
        });
    }

</script>

<script type="module">

    import * as pukpunRoot from "<?php echo get_stylesheet_directory_uri() . '/assets/js/pukpun.js' ?>";

    import * as provinceLatLong from "<?php echo get_stylesheet_directory_uri() . '/assets/js/province.latlong.js' ?>";

    var unPrecariousHubs = [];
    var precariousHubs = [];
    var totalHubs = [];

    jQuery.ajax({
        url: "<?= $queryUrl ?>",
        type: "GET",
        async: false,
        success: function (response){
            precariousHubs = pukpunRoot.getParsedPrecariousData(response,true);
            unPrecariousHubs = pukpunRoot.getParsedPrecariousData(response,false);
            totalHubs = pukpunRoot.getAllParsedHubData(response);
            localStorage.setItem("unPrecariousHubs", JSON.stringify(unPrecariousHubs));
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });

    var getPosition = function (options) {
        return new Promise(function (resolve, reject) {
            navigator.geolocation.getCurrentPosition(resolve, reject, options);
        });
    }
    
    const selectSingleRadio = (radioID) => {
        var $container = jQuery("#shipping_method");
        $container.find('input[type="radio"]').each(function(i,obj){
            if(obj.id == radioID){
                jQuery(this).prop('checked', true);
            }else{
                jQuery(this).prop('checked', false);
            }
        });
    }

    const getShippingCostForCity = (shipping_city) => {
        let shipping_cost = 0;
        if (shipping_city == "TH-10") {
            shipping_cost = 50;
        } else { // Outside BKK
            shipping_cost = 70;
        }
        return shipping_cost;
    }

    const manageShippingSelection = () => {
        let selectedRadio = jQuery("input[name='shipping_method[0]']:checked").val();
        let radioNames = pukpunRoot.getRadioSelection(2); // 2 getName
        if(selectedRadio == radioNames.bicycle){
            jQuery('#store_pickup_field').fadeOut();
            jQuery('#map').fadeIn();
            jQuery('.woocommerce-billing-fields__field-wrapper').show();
        }
        if(selectedRadio == radioNames.postal){
            jQuery('#map').fadeOut();
            jQuery('#store_pickup_field').fadeOut();
            jQuery('.woocommerce-billing-fields__field-wrapper').show();
        }
        if(selectedRadio == radioNames.local_pickup){
            jQuery('#map').fadeOut();
            jQuery('.woocommerce-billing-fields__field-wrapper').hide();
            jQuery('#store_pickup_field').fadeIn();
        }
    }

    const wholeMap = (picker,initialData,skipCheck = false) => {
        var loadInitialData = false;
        let loadTime = 1;
        let loadMapTime = 1;
        if(initialData != null){
            var userInitialData = JSON.parse(JSON.stringify(initialData));
            loadInitialData = true;
        }

        var input = document.getElementById('billing_address_1'); //searchInput

        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', picker.map);

        var infowindow = new google.maps.InfoWindow();
        var marker = new google.maps.Marker({
            map: picker.map,
        });

        let unPrecariousRoadLines = unPrecariousHubs.reduce((found, eachHub) => {
            eachHub.data.map(newEntry => {
                let unPrecariousData = JSON.parse('{"data":['+newEntry.location_data.replace(/(lat|lng)/g, '"$1"')+']}');
                found.push(unPrecariousData.data);
            });
            return found;
        }, []);

        let precariousRoadLines = precariousHubs.reduce((found, eachPrecariousHub) => {
            let precariousData = JSON.parse('{"data":['+eachPrecariousHub.data.location_data.replace(/(lat|lng)/g, '"$1"')+']}');
            found.push(precariousData.data);
            return found;
        }, []);

        var precariousPolygon = new google.maps.Polygon({
            paths: precariousRoadLines,
            strokeColor: "#ffffff00",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#ffffff00",
            fillOpacity: 0.35
        });

        var unPrecariousPolygon = new google.maps.Polygon({
            paths: unPrecariousRoadLines,
            strokeColor: "#ffffff00",
            strokeOpacity: 1,
            strokeWeight: 3,
            fillColor: "#ffffff00",
            fillOpacity: 0.5
        });

        precariousPolygon.setMap(picker.map);
        unPrecariousPolygon.setMap(picker.map);

        var switcher = document.createElement("div");
        switcher.setAttribute("class", "switcher");

        var mSwitch = document.createElement("label");
        mSwitch.setAttribute("class", "switch");

        var inputer = document.createElement("INPUT");
        inputer.setAttribute("type", "checkbox");
        inputer.checked = false;

        var spaner = document.createElement("SPAN");
        spaner.classList.add("slider", "round");

        mSwitch.appendChild(inputer); 
        mSwitch.appendChild(spaner); 
        switcher.appendChild(mSwitch);

        inputer.addEventListener('change', (event) => {
            if(event.target.checked){
                precariousPolygon.setOptions({
                    strokeColor: "#FF0000",
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: "#FF0000",
                    fillOpacity: 0.35
                });
                unPrecariousPolygon.setOptions({
                    strokeColor: "#000000",
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: "#000000",
                    fillOpacity: 0.35
                });
            }else{
                precariousPolygon.setOptions({
                    strokeColor: "#ffffff00",
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: "#ffffff00",
                    fillOpacity: 0.35
                });
                unPrecariousPolygon.setOptions({
                    strokeColor: "#ffffff00",
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: "#ffffff00",
                    fillOpacity: 0.35
                });
            }
        });

        picker.map.controls[google.maps.ControlPosition.TOP_CENTER].push(switcher);

        autocomplete.addListener('place_changed', function(){
            infowindow.close();
            marker.setVisible(false);
            var place = autocomplete.getPlace();
            if (!place.geometry) {
                window.alert("Autocomplete's returned place contains no geometry");
                return;
            }

            // If the place has a geometry, then present it on a map.
            if (place.geometry.viewport) {
                picker.map.fitBounds(place.geometry.viewport);
            } else {
                picker.map.setCenter(place.geometry.location);
                picker.map.setZoom(17);
            }

            marker.setIcon(({
                url: place.icon,
                size: new google.maps.Size(71, 71),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(17, 34),
                scaledSize: new google.maps.Size(20, 32)
            }));

            marker.setPosition(place.geometry.location);

            marker.setVisible(true);

            var address = '';
            if (place.address_components) {
                address = [
                (place.address_components[0] && place.address_components[0].short_name || ''),
                (place.address_components[1] && place.address_components[1].short_name || ''),
                (place.address_components[2] && place.address_components[2].short_name || '')
                ].join(' ');
            }

            infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
            infowindow.open(map, marker);
            
        });

        google.maps.event.addListener(picker.map, 'idle', function(){
            var location = picker.getMarkerPosition();
            let point2 = [location.lat, location.lng];
            var latlng = new google.maps.LatLng(location.lat, location.lng)
            var geocoder = (geocoder = new google.maps.Geocoder())
            geocoder.geocode({ latLng: latlng }, function(results, status){
                if (status == google.maps.GeocoderStatus.OK) {

                    if(loadTime <= 2){
                        jQuery('input[name=billing_first_name]').val(user_firstName);
                        jQuery('input[name=billing_last_name]').val(user_lastName);
                        jQuery('input[name=billing_phone]').val(user_telephone);

                        jQuery('input[name=shipping_first_name]').val(user_firstName);
                        jQuery('input[name=shipping_last_name]').val(user_lastName);
                        jQuery('input[name=shipping_phone]').val(user_telephone);
                    }

                    let selectedRadio = jQuery("input[name='shipping_method[0]']:checked").val();
                    let radioNames = pukpunRoot.getRadioSelection(2); // 2 getName
                    if (results[1] && loadTime > 2 && selectedRadio == radioNames.bicycle){
                        let tempAddress = pukpunRoot.placeToAddress(results[1]);

                        if(tempAddress.route != undefined && tempAddress.streetNumber != undefined){
                            jQuery('input[name=billing_address_1]').val(tempAddress.streetNumber+' '+tempAddress.route);
                        }else{
                            if(tempAddress.streetNumber != undefined && tempAddress.route == undefined){
                                jQuery('input[name=billing_address_1]').val(tempAddress.streetNumber);
                            }else if(tempAddress.route != undefined && tempAddress.streetNumber == undefined){
                                jQuery('input[name=billing_address_1]').val(tempAddress.route);
                            }else if(tempAddress.streetNumber == undefined && tempAddress.route == undefined && tempAddress.placeName != undefined){
                                jQuery('input[name=billing_address_1]').val(tempAddress.placeName);
                            }else{
                                jQuery('input[name=billing_address_1]').val('');
                            }
                        }

                        if(tempAddress.tambol != undefined){
                            jQuery('input[name=billing_address_2]').val(tempAddress.tambol);
                        }else{
                            jQuery('input[name=billing_address_2]').val('');
                        }

                        if(tempAddress.amphur != undefined){
                            jQuery('input[name=billing_city]').val(tempAddress.amphur);
                        }else{
                            jQuery('input[name=billing_city]').val('');
                        }

                        if(tempAddress.province != undefined){
                            let provinceCode = jQuery("#billing_state option:contains("+tempAddress.province+")").attr('value');
                            jQuery('#billing_state').val(provinceCode).trigger('change');
                        }

                        if(tempAddress.zip != undefined){
                            jQuery('input[name=billing_postcode]').val(tempAddress.zip);
                        }else{
                            jQuery('input[name=billing_postcode]').val('');
                        }

                        jQuery('input[name=selected_latlong]').val(location.lat+','+location.lng);

                        jQuery('input[name=formatted_address]').val(results[1].formatted_address);
                        
                    }
                    loadTime++;
                }
            }); 
            const getIndex = (arr, find) => {
                let result = []
                arr.forEach((e, idx)=>{
                    if(e !== find){
                        result.push(idx)
                    }
                })
                return result
            }

            const swapArrayElements = (arr, indexA, indexB) => {
                let temp = arr[indexA];
                arr[indexA] = arr[indexB];
                arr[indexB] = temp;
                return arr
                };
            
            let foundHub = totalHubs.reduce((prevHub,nextHub) => {
                let store = getIndex(nextHub.data.map(e=>e.location_name), '-')
                
                store.forEach(idx=>{
                    nextHub.data = swapArrayElements(nextHub.data, idx, nextHub.data.length - 1)
                })

                let eachLocation = nextHub.data.reduce((prev, next) => {

                    let polygonData = JSON.parse('['+next.location_data.replace(/(lat|lng)/g, '"$1"')+']');
                    const polyGon = polygonData.map(location => {
                        return [location.lat,location.lng];
                    });

                    let isLocationFound = pukpunRoot.checkGeoFence(point2,polyGon);

                    if(isLocationFound){
                        let founded = {
                            hub_name : nextHub.name,
                            lat_long : nextHub.latlong,
                            found_location : next
                        };
                        prev = founded;
                    }
                    return prev
                }, false);
                if(eachLocation){
                    prevHub =  eachLocation;
                }
                return prevHub
            },null);

            var totalShippingCost = 0;

            if(foundHub != null){

                console.log('foundHub :',foundHub);

                localStorage.setItem("foundHub", foundHub.hub_name);

                jQuery('input[name=selected_hub]').val(foundHub.found_location.location_name+'-'+foundHub.hub_name);
                
                let radioID = pukpunRoot.getRadioSelection(1);
                selectSingleRadio(radioID.bicycle);

                let foundLat = foundHub.lat_long.split(',');
                
                let distance = pukpunRoot.calculateDistance(foundLat[0],foundLat[1],location.lat,location.lng,'K');

                jQuery.ajax({
                    type: "POST",
                    url:  "<?php echo admin_url('shipping-cost-variables.php'); ?>",
                    data:{calculateDistance: distance},
                    async:false,
                    success: function (result) {
                        let groupedResult = JSON.parse(result);
                        groupedResult.forEach((eachResult,index) => {
                            console.log(eachResult);
                            let {n,l,a,t,isFreeShippingCost} = eachResult;
                            if(isFreeShippingCost != 1){
                                if(t == 5){
                                    let result = pukpunRoot.Program5(n,l,a,t);
                                    totalShippingCost += result;
                                }else if(t == 21){
                                    let result = pukpunRoot.Program21(a,l,n,t);
                                    totalShippingCost += result;
                                }
                            }
                        });
                        localStorage.setItem("foundHubPrice", totalShippingCost);
                    }
                });

                jQuery('#store_pickup_field').fadeOut();

                let dropper = jQuery('#billing_state').val();

                jQuery('#billing_state').val('').trigger('change');

                setTimeout(function(){
                    jQuery('#billing_state').val(dropper).trigger('change');
                },1000);

                if(foundHub.found_location.isPrecarious == '1'){
                    console.log('Precarious Found');
                    alert('Precarious Found');
                    document.getElementById("is_precarious").value = 1;
                }

            }else{

                localStorage.setItem("foundHub",'');
                localStorage.setItem("foundHubPrice", 0);
                console.log('Hub : Not found');
                let tempRadios = pukpunRoot.getRadioSelection(1);
                document.getElementById(tempRadios.bicycle).checked = true; // De-Select Bicycle
                document.getElementById(tempRadios.postal).checked = false; // Select postal
                document.getElementById(tempRadios.local_pickup).checked = false; // De-Select Self Pickup
                
                manageShippingSelection();
                loadMapTime++;

                if(initialData != null){
                    console.log('Setting Initial Data not null');
                    jQuery('input[name=billing_address_1]').val(userInitialData.billingAddress1);
                    jQuery('input[name=billing_address_2]').val(userInitialData.billingAddress2);
                    jQuery('input[name=billing_city]').val(userInitialData.billingCity);
                    jQuery('#billing_state').val(userInitialData.billingProvince).trigger('change');
                    jQuery('input[name=billing_postcode]').val(userInitialData.billingPostcode);
                }

                totalShippingCost = 0;
                
            }

            jQuery.ajax({
                type: "POST",
                url: wc_checkout_params.ajax_url,
                data: {
                    'action': 'get_and_set_shipping_rate',
                    'fees': totalShippingCost
                },
                async: false,
                success: function (response) {
                    // console.log('Set Cost = ',response);
                    jQuery(document.body).trigger("update_checkout");
                }
            });

            return false;
        });

    }

    const setInitialAddress = (initialData) => {
        jQuery('input[name=billing_address_1]').val(initialData.billingAddress1);
        jQuery('input[name=billing_address_2]').val(initialData.billingAddress2);
        jQuery('input[name=billing_city]').val(initialData.billingCity);
        jQuery('#billing_state').val(initialData.billingProvince).trigger('change');
        jQuery('input[name=billing_postcode]').val(initialData.billingPostcode);
    }
    
    let uberMapStyle = [{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#d6e2e6"}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#cfd4d5"}]},{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#7492a8"}]},{"featureType":"administrative.neighborhood","elementType":"labels.text.fill","stylers":[{"lightness":25}]},{"featureType":"landscape.man_made","elementType":"geometry.fill","stylers":[{"color":"#dde2e3"}]},{"featureType":"landscape.man_made","elementType":"geometry.stroke","stylers":[{"color":"#cfd4d5"}]},{"featureType":"landscape.natural","elementType":"geometry.fill","stylers":[{"color":"#dde2e3"}]},{"featureType":"landscape.natural","elementType":"labels.text.fill","stylers":[{"color":"#7492a8"}]},{"featureType":"landscape.natural.terrain","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"color":"#dde2e3"}]},{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"color":"#588ca4"}]},{"featureType":"poi","elementType":"labels.icon","stylers":[{"saturation":-100}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#a9de83"}]},{"featureType":"poi.park","elementType":"geometry.stroke","stylers":[{"color":"#bae6a1"}]},{"featureType":"poi.sports_complex","elementType":"geometry.fill","stylers":[{"color":"#c6e8b3"}]},{"featureType":"poi.sports_complex","elementType":"geometry.stroke","stylers":[{"color":"#bae6a1"}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"color":"#41626b"}]},{"featureType":"road","elementType":"labels.icon","stylers":[{"saturation":-45},{"lightness":10},{"visibility":"on"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#c1d1d6"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#a6b5bb"}]},{"featureType":"road.highway","elementType":"labels.icon","stylers":[{"visibility":"on"}]},{"featureType":"road.highway.controlled_access","elementType":"geometry.fill","stylers":[{"color":"#9fb6bd"}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"road.local","elementType":"geometry.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"transit","elementType":"labels.icon","stylers":[{"saturation":-70}]},{"featureType":"transit.line","elementType":"geometry.fill","stylers":[{"color":"#b4cbd4"}]},{"featureType":"transit.line","elementType":"labels.text.fill","stylers":[{"color":"#588ca4"}]},{"featureType":"transit.station","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"transit.station","elementType":"labels.text.fill","stylers":[{"color":"#008cb5"},{"visibility":"on"}]},{"featureType":"transit.station.airport","elementType":"geometry.fill","stylers":[{"saturation":-100},{"lightness":-5}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#a6cbe3"}]}]
    
    let mapOptions = {
        zoom: 13,
        mapTypeId: google.maps.MapTypeId.TERRAIN,
        styles:uberMapStyle,
        // center: {
        //     lat: 41.5224,
        //     lng: 2.1455
        // }
    };

    if(unPrecariousHubs.length > 0){

        getNearestHub();

        let userlatlng = JSON.parse(localStorage.getItem("device_latlng"));
        let hubDistance = [];
        unPrecariousHubs.forEach((eachHub) => {
            let latlng = eachHub.latlong.split(',');
            let eachDistance = pukpunRoot.calculateDistance(userlatlng.lat,userlatlng.lng,latlng[0],latlng[1],'K');
            hubDistance.push({hubName:eachHub.name, hubDistance:eachDistance});
        });
        // console.log('hubwithDistance',hubDistance);
        let nearestHub = hubDistance.reduce(function(res, obj) {
            return (obj.hubDistance < res.hubDistance) ? obj : res;
        });
        console.log('nearestHub',nearestHub);
        jQuery("#store_pickup").val(nearestHub.hubName);

        if(provinceCode == ''){ // New User
            getPosition().then((position) => {
                let mLatlng = {
                    lat : position.coords.latitude,
                    lng : position.coords.longitude
                };

                let newUserLocation = [mLatlng.lat, mLatlng.lng];
                let isLocationSupported = unPrecariousHubs.reduce((prevHub,nextHub) => {
                    let eachLocation = nextHub.locations.reduce((prev, next) => {
                        const polyGon = next.data.data.map(location => {
                            return [location.lat,location.lng];
                        });
                        let isLocationFound = pukpunRoot.checkGeoFence(newUserLocation,polyGon);
                        if(isLocationFound){
                            let founded = {
                                hub_name : nextHub.name,
                                lat_long : nextHub.latlong,
                                found_location : next
                            };
                            prev = founded;
                        }
                        return prev
                    }, false);
                    if(eachLocation){
                        prevHub =  eachLocation;
                    }
                    return prevHub
                },null);
                if(isLocationSupported == null){ // Not Supported
                    console.log('No Location Supported');
                    setInitialAddress({
                        billingAddress1:'',
                        billingAddress2:'',
                        billingCity:'',
                        billingProvince:'TH-10',
                        billingPostcode:''
                    });
                    let picker = new locationPicker('map',{mLatlng},mapOptions);
                    let skipCheck = true;
                    wholeMap(picker,null,skipCheck);
                }else{
                    console.log('Location Supported');
                    let picker = new locationPicker('map',{mLatlng},mapOptions);
                    wholeMap(picker,null);
                }

            });
            // .catch((err) => {console.error('err : '+err.message);});
        }else{
            if(provinceCode == 'TH-10'){ // Inside BKK
                let mPicker = new locationPicker('map',{lat:13.7563309, lng:100.5017651},mapOptions);
                wholeMap(mPicker,null);
            }else{
                // console.log('Searching LatLong From Province');
                let provinceName = jQuery('#billing_state option[value="'+provinceCode+'"]').text();
                let provinceData = provinceLatLong.searchProvinceLatLng(provinceName);
                let picker = new locationPicker('map',{lat:provinceData.lat,lng:provinceData.lng},mapOptions);
                wholeMap(picker,null,true);
            }
            setInitialAddress({
                billingAddress1:'<?= $billingAddress1 ?>',
                billingAddress2:'<?= $billingAddress2 ?>',
                billingCity:'<?= $billingCity ?>',
                billingProvince:'<?= $billingProvince ?>',
                billingPostcode:'<?= $billingPostcode ?>'
            });
        }

    }else{
        jQuery("#map").remove();
        let radioID = pukpunRoot.getRadioSelection(1);
        document.getElementById(radioID.bicycle).checked = false;
        document.getElementById(radioID.postal).checked = false;
        document.getElementById(radioID.local_pickup).checked = false;
        document.getElementById(radioID.bicycle).parentElement.remove();
    }

</script>

<?= $layout; ?>