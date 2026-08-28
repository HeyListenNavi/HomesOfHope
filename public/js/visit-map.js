document.addEventListener("alpine:init", () => {
    Alpine.data("visitMapData", ({ markersData, iconHome, iconLand }) => {
        let map = null;
        let clusterGroup = null;
        let markerInstances = {};

        return {
            markersData: markersData,
            iconHome: iconHome,
            iconLand: iconLand,

            init() {
                this.loadDependencies().then(() => {
                    this.initMap();
                    this.drawMapPoints();
                });

                if (this.$cleanup) {
                    this.$cleanup(() => {
                        if (map) {
                            map.remove();
                            map = null;
                        }
                    });
                }
            },

            drawMapPoints(newMarkers = null) {
                if (newMarkers) {
                    this.markersData = newMarkers;
                }

                // Remove old layers
                if (clusterGroup) clusterGroup.clearLayers();
                markerInstances = {};

                if (!this.markersData || this.markersData.length === 0) return;

                const bounds = L.latLngBounds();
                let hasValidMarkers = false;
                let markersArray = [];
                const selectedLocations = this.$wire.selectedLocations || [];

                this.markersData.forEach(marker => {
                    if (!marker.lat || !marker.lng) return;

                    const isSelected = selectedLocations.some(loc => loc.family_id === marker.family_id && loc.type === marker.type);
                    const leafletMarker = L.marker([marker.lat, marker.lng], { 
                        icon: this.getIconForMarker(marker, isSelected) 
                    });

                    leafletMarker.bindTooltip(marker.title, {
                        className: 'font-semibold text-sm rounded-lg shadow-sm ring-1 ring-gray-950/5',
                        direction: 'top',
                        offset: [0, -15]
                    });

                    // Blindly tell PHP when a marker is clicked
                    leafletMarker.on('click', () => {
                        this.$wire.toggleLocation(marker.family_id, marker.type);
                    });

                    markerInstances[marker.id] = leafletMarker;
                    markersArray.push(leafletMarker);

                    bounds.extend([marker.lat, marker.lng]);
                    hasValidMarkers = true;
                });

                clusterGroup.addLayers(markersArray);

                if (hasValidMarkers) {
                    map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
                }
            },

            syncSelectedColors() {
                if (!map || !this.markersData) return;
                
                const selectedLocations = this.$wire.selectedLocations || [];
                
                this.markersData.forEach(marker => {
                    const isSelected = selectedLocations.some(loc => loc.family_id === marker.family_id && loc.type === marker.type);
                    
                    const instance = markerInstances[marker.id];
                    if (!instance) return;

                    if (instance._icon) {
                        const container = instance._icon.querySelector('.marker-icon-container');
                        if (container) {
                            if (isSelected) {
                                container.classList.add('selected');
                            } else {
                                container.classList.remove('selected');
                            }
                        }
                        instance.options.icon = this.getIconForMarker(marker, isSelected);
                    } else {
                        instance.setIcon(this.getIconForMarker(marker, isSelected));
                    }
                });
            },

            loadDependencies() {
                return new Promise((resolve) => {
                    if (window.L && window.L.markerClusterGroup) return resolve();

                    const loadScript = (src) => new Promise((res) => {
                        const script = document.createElement('script');
                        script.src = src;
                        script.onload = res;
                        document.head.appendChild(script);
                    });

                    if (!window.L) {
                        loadScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js')
                            .then(() => loadScript('https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js'))
                            .then(resolve);
                    } else {
                        loadScript('https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js').then(resolve);
                    }
                });
            },

            initMap() {
                const container = document.getElementById('map');
                if (container && container._leaflet_id) container._leaflet_id = null;
                if (map) map.remove();
                
                map = L.map('map', { attributionControl: false }).setView([32.5149, -117.0382], 12);

                L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png?key=' + window.cartoBasemapKey, {
                    subdomains: 'abcd',
                    maxZoom: 20
                }).addTo(map);

                clusterGroup = L.markerClusterGroup({
                    showCoverageOnHover: false,
                    iconCreateFunction: function(cluster) {
                        const childCount = cluster.getChildCount();
                        let size = childCount > 50 ? 60 : (childCount > 10 ? 52 : 44);

                        return new L.DivIcon({
                            html: `<div class="custom-cluster-icon"><span>${childCount}</span></div>`,
                            className: 'custom-cluster-marker',
                            iconSize: new L.Point(size, size)
                        });
                    }
                });

                map.addLayer(clusterGroup);
            },

            getIconForMarker(marker, isSelected) {
                const svgContent = marker.type === 'home' ? this.iconHome : this.iconLand;
                const typeClass = marker.type === 'home' ? 'type-home' : 'type-land';

                return L.divIcon({
                    className: 'custom-div-icon',
                    html: `
                        <div class="marker-icon-container ${typeClass} ${isSelected ? 'selected' : ''}">
                            ${svgContent}
                        </div>
                    `,
                    iconSize: [44, 44],
                    iconAnchor: [22, 22],
                });
            }
        };
    });
});
