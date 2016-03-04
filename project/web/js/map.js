(function ($)
{

    $(document).ready(function ()
    {
        var map = L.map('map').setView([48.8593829, 2.347227], 12);

        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        L.geoJson(JSON.parse($('#map').attr('data-geojson'))).addTo(map);

    });

})(jQuery);