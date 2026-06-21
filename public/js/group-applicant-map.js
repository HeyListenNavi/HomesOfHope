document.addEventListener("alpine:init", () => {
    Alpine.data("groupApplicantMap", ({ points, unmappable, center, zoom }) => ({
        points: points,
        unmappable: unmappable,
        center: center,
        zoom: zoom,
        map: null,
        markers: [],

        init() {
            if (typeof L === 'undefined') {
                let attempts = 0;
                const checkLeaflet = setInterval(() => {
                    attempts++;
                    if (typeof L !== 'undefined') {
                        clearInterval(checkLeaflet);
                        this.setupMap(this.center, this.zoom, this.points);
                    }
                    if (attempts > 100) clearInterval(checkLeaflet);
                }, 100);
                return;
            }

            this.setupMap(this.center, this.zoom, this.points);
        },

        setupMap(center, zoom, points) {
            this.map = L.map(this.$refs.map).setView(center, zoom);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(this.map);

            const customIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="background-color: #61b346; width: 16px; height: 16px; border: 3px solid white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8],
                popupAnchor: [0, -8]
            });

            points.forEach(point => {
                const popupContent = `
                    <div style="font-family: inherit; min-width: 150px;">
                        <h4 style="margin: 0 0 4px 0; font-weight: bold; font-size: 14px; color: #111827;">${point.name}</h4>
                        <p style="margin: 0 0 10px 0; font-size: 12px; color: #6b7280;">${point.city}</p>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <a href="${point.url}" target="_blank" rel="noopener noreferrer" 
                               style="display: block; background-color: #61b346; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 500; text-align: center;">
                               Ver Perfil
                            </a>
                            ${point.map_url ? `
                                <a href="${point.map_url}" target="_blank" rel="noopener noreferrer"
                                   style="display: block; background-color: #f3f4f6; color: #374151; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 500; text-align: center; border: 1px solid #d1d5db;">
                                   Abrir en Maps
                                </a>
                            ` : ''}
                        </div>
                    </div>
                `;

                const marker = L.marker([point.lat, point.lng], { icon: customIcon })
                    .addTo(this.map)
                    .bindPopup(popupContent, {
                        closeButton: false,
                        className: 'applicant-popup'
                    });
                
                this.markers.push(marker);
            });

            if (points.length > 0) {
                const group = new L.featureGroup(this.markers);
                this.map.fitBounds(group.getBounds().pad(0.1));
            }
        }
    }));
});
