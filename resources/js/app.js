import './bootstrap';

document.addEventListener('alpine:init', () => {
    Alpine.data('datePicker', () => ({
        value: '',
        day: '',
        month: '',
        year: '',
        init() {
            if (this.value) {
                let parts = this.value.split('-');
                if (parts.length === 3) {
                    this.year = parts[0];
                    this.month = parseInt(parts[1]).toString();
                    this.day = parseInt(parts[2]).toString();
                }
            }
            this.$watch('day', val => this.updateValue());
            this.$watch('month', val => this.updateValue());
            this.$watch('year', val => this.updateValue());
            this.$watch('value', val => {
                if (val && val.includes('-')) {
                    let parts = val.split('-');
                    this.year = parts[0];
                    this.month = parseInt(parts[1]).toString();
                    this.day = parseInt(parts[2]).toString();
                }
            });
        },
        updateValue() {
            if (this.year && this.month && this.day) {
                let m = this.month.padStart(2, '0');
                let d = this.day.padStart(2, '0');
                this.value = `${this.year}-${m}-${d}`;
            } else {
                this.value = null;
            }
        },
        months: [
            { val: '1', name: 'Enero' }, { val: '2', name: 'Febrero' }, { val: '3', name: 'Marzo' },
            { val: '4', name: 'Abril' }, { val: '5', name: 'Mayo' }, { val: '6', name: 'Junio' },
            { val: '7', name: 'Julio' }, { val: '8', name: 'Agosto' }, { val: '9', name: 'Septiembre' },
            { val: '10', name: 'Octubre' }, { val: '11', name: 'Noviembre' }, { val: '12', name: 'Diciembre' }
        ],
        days() {
            const y = this.year || 2024;
            const m = this.month || 1;
            const daysInMonth = new Date(y, m, 0).getDate();
            return Array.from({ length: daysInMonth }, (_, i) => i + 1);
        },
        years() {
            let current = new Date().getFullYear();
            return Array.from({ length: 100 }, (_, i) => current - i);
        }
    }));
});

window.addEventListener('scroll-to-top', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

document.addEventListener('alpine:init', () => {
    Alpine.data('locationPicker', (latModel, lngModel) => ({
        map: null,
        marker: null,
        loading: false,
        lat: null,
        lng: null,

        init() {
            this.lat = this.$wire.get(latModel);
            this.lng = this.$wire.get(lngModel);

            this.$watch('$wire.' + latModel, val => {
                this.lat = val;
                if (this.map && this.lat && this.lng) {
                    this.updateMarker(this.lat, this.lng);
                    this.map.flyTo([this.lat, this.lng], 15);
                }
            });

            this.loadDependencies().then(() => {
                this.initMap();
            });
        },

        loadDependencies() {
            return new Promise((resolve) => {
                if (window.L) return resolve();
                const css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(css);
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.onload = resolve;
                document.head.appendChild(script);
            });
        },

        initMap() {
            const center = (this.lat && this.lng) ? [this.lat, this.lng] : [32.5149, -117.0382];
            this.map = L.map(this.$refs.mapContainer, {
                attributionControl: false
            }).setView(center, 13);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(this.map);

            if (this.lat && this.lng) {
                this.updateMarker(this.lat, this.lng);
            }

            this.map.on('click', (e) => {
                const {
                    lat,
                    lng
                } = e.latlng;
                this.updateMarker(lat, lng);
                this.$wire.set(latModel, lat);
                this.$wire.set(lngModel, lng);
            });
        },

        updateMarker(lat, lng) {
            if (!this.marker) {
                const color = latModel === 'land.lat' ? '#61b346' : '#fbbf24';
                const icon = L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div style="display:flex; align-items:center; justify-content:center; background-color:white; border-radius:9999px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); width:44px; height:44px; border:2px solid white; color: ${color};"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg></div>`,
                    iconSize: [44, 44],
                    iconAnchor: [22, 44],
                });
                this.marker = L.marker([lat, lng], {
                    icon
                }).addTo(this.map);
            } else {
                this.marker.setLatLng([lat, lng]);
            }
        },

        getLocation() {
            this.loading = true;
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    this.$wire.set(latModel, lat);
                    this.$wire.set(lngModel, lng);
                    this.updateMarker(lat, lng);
                    this.map.flyTo([lat, lng], 16);
                    this.loading = false;
                },
                (err) => {
                    alert(
                        'No se pudo obtener la ubicación automáticamente. Toca el mapa para colocar el pin manualmente.'
                    );
                    this.loading = false;
                }, {
                enableHighAccuracy: true
            }
            );
        }
    }));
});