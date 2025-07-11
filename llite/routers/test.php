
Edit in JSFiddle

    Result
    HTML

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Leaflet Demo</title>
    <script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

    <link href='https://unpkg.com/leaflet@1.3.4/dist/leaflet.css' rel='stylesheet' />
    <link href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" rel='stylesheet' />
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        #map {
            width: 100vw;
            height: 100vh;
        }
    </style>
</head>

<body>
    <div id="map"></div>
    <script>
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'https://raw.githubusercontent.com/akq/Leaflet.DonutCluster/master/src/Leaflet.DonutCluster.js',
            true);


        xhr.onreadystatechange = function () { //Call a function when the state changes.
            if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
                eval(this.responseText);
                var markers = L.DonutCluster({
                    chunkedLoading: true
                }, {
                    key: 'title',
                    arcColorDict: {
                        A: 'red',
                        B: 'blue',
                        C: 'yellow',
                        D: 'black'
                    }
                })

                for (var i = 0; i < points.length; i++) {
                    var a = points[i];
                    var title = a[2];
                    var marker = L.marker(L.latLng(a[0], a[1]), {
                        title: title
                    });


                    marker.bindPopup(title);
                    markers.addLayer(marker);
                }

                map.addLayer(markers);
            }
        }
        xhr.send(null);
        function mockPoints(total) {
            var types = ['A', 'B', 'C', 'D'],
                x = [0, 120],
                y = [30, 60],
                result = [],
                i = 0;
            while (i++ < total) {
                var lat = Math.random() * (y[1] - y[0]) + y[0],
                    long = Math.random() * (x[1] - x[0]) + x[0],
                    type = types[Math.floor(Math.random() * types.length)];
                result.push([lat, long, type])
            }
            return result;
        }
        var points = mockPoints(2000);
        var map = L.map('map').setView([40.0484, 116.286976], 3);
        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png').addTo(map);
    </script>
</body>

</html>

