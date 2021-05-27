            var villes = {
                "Paris": { "lat": 48.852969, "lon": 2.349903 },
                "Brest": { "lat": 48.383, "lon": -4.500 },
                "Quimper": { "lat": 48.000, "lon": -4.100 },
                "Bayonne": { "lat": 43.500, "lon": -1.467 },
                "Marseille":{"lat":43.296174,"lon":5.362019953},
                "Lyon":{"lat":45.737814,"lon":4.832011},
                "Toulouse":{"lat":43.604462,"lon":1.444247},
                "Bordeaux":{"lat":44.841225,"lon":-0.580036},
                "Rennes":{"lat":48.111339,"lon":-1.68002},
                "Caen":{"lat":49.181004,"lon":-0.369891},
                "Clermont ferand":{"lat":45.777455,"lon":3.081943},
                "Nice":{"lat":43.700936,"lon":7.268391},
                "Lile":{"lat":50.636565,"lon":3.063528},
                "Nancy":{"lat":48.693722,"lon":6.18341},    
                "Strasbourg":{"lat":48.584614,"lon":7.750713},
                "Nante":{"lat":47.213637,"lon":-1.554136},
                "Lorient":{"lat":47.747734,"lon":-3.366091},
                "Guingamp":{"lat":48.561848,"lon":-3.150201},
                "Metz":{"lat":49.119696,"lon":6.176355},
                "Anger":{"lat":49.061641,"lon":0.458682},
                "Le havre":{"lat":49.493898,"lon":0.107913},
                "Dijon":{"lat":47.321581,"lon":5.04147},
                "Orleans":{"lat":47.902734,"lon":1.908607},
            };
            var tableauMarqueurs = [];

            // On initialise la carte
            var carte = L.map('maCarte').setView([48.852969, 2.349903], 13);
            
            // On charge les "tuiles"
            L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
                // Il est toujours bien de laisser le lien vers la source des données
                attribution: 'données © <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
                minZoom: 1,
                maxZoom: 20
            }).addTo(carte);

            var marqueurs = L.markerClusterGroup();

            // On personnalise le marqueur
            var icone = L.icon({
                iconUrl: "images/marqueur.png",
                iconSize: [50, 50],
                iconAnchor: [25, 50],
                popupAnchor: [0, -50]
            })

            // On parcourt les différentes villes
            for(ville in villes){
                // On crée le marqueur et on lui attribue une popup
                var marqueur = L.marker([villes[ville].lat, villes[ville].lon], {icon: icone}); //.addTo(carte); Inutile lors de l'utilisation des clusters
                marqueur.bindPopup("<p>"+ville+"</p>");
                marqueurs.addLayer(marqueur); // On ajoute le marqueur au groupe

                // On ajoute le marqueur au tableau
                tableauMarqueurs.push(marqueur);
            }
            // On regroupe les marqueurs dans un groupe Leaflet
            var groupe = new L.featureGroup(tableauMarqueurs);

            // On adapte le zoom au groupe
            carte.fitBounds(groupe.getBounds().pad(0.5));

            carte.addLayer(marqueurs);
