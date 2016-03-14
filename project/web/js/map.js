(function ($)
{

    $(document).ready(function ()
    {
        if($('#map').length) {
            var map = L.map('map').setView([48.8593829, 2.347227], 12);

            L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var geojson = JSON.parse($('#map').attr('data-geojson'));
            var markers = [];
            var hoverTimeout = null;
            L.geoJson(geojson, 
                {
                    onEachFeature: function (feature, layer) {
                        layer.on('mouseover', function(e) {
                            $('.leaflet-marker-icon').css('opacity', '0.5');
                            $(e.target._icon).css('opacity', '1');
                            e.target.setZIndexOffset(1001);
                            if(hoverTimeout) {
                                clearTimeout(hoverTimeout);
                            }
                            hoverTimeout = setTimeout(function(){
                                $('#liste_passage .list-group-item').blur();
                                var element = $('#'+e.target.feature.properties._id);
                                var list = $('#liste_passage');
                                list.scrollTop(0);
                                list.scrollTop(element.position().top - (list.height()/2) + (element.height()));
                                element.focus();
                            }, 400);
                        });

                        layer.on('mouseout', function(e) {
                            if(hoverTimeout) {
                                clearTimeout(hoverTimeout);
                            }
                            e.target.setZIndexOffset(900);
                            $('#'+e.target.feature.properties._id).blur();
                            $('.leaflet-marker-icon').css('opacity', '1');
                        });

                        layer.on('click', function(e) {
                            document.location.href= $('#'+e.target.feature.properties._id).attr('href');
                        });
                    },
                    pointToLayer: function (feature, latlng) {
                        var marker = L.marker(latlng, {icon: L.ExtraMarkers.icon({
                                                    icon: feature.properties.icon,
                                                    markerColor: feature.properties.color,
                                                    iconColor: feature.properties.colorText,
                                                    shape: 'circle',
                                                    prefix: 'mdi',
                                                    svg: true
                                                })});
                        markers[feature.properties._id] = marker;
                        return marker;
                    }
                }
            ).addTo(map);

            $('#liste_passage .list-group-item').hover(function() {
                var marker = markers[$(this).attr('id')];
                $('.leaflet-marker-icon').css('opacity', '0.5');
                $(marker._icon).css('opacity', '1');
                marker.setZIndexOffset(1001);
            }, function() {
                var marker = markers[$(this).attr('id')];
                marker.setZIndexOffset(900);
                $('.leaflet-marker-icon').css('opacity', '1');
            });
        }

    });

})(jQuery);