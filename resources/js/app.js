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
    Alpine.data('locationPicker', (latModel, lngModel, cityModel = null, colonyModel = null, addressModel = null) => ({
        map: null,
        loadingGps: false,
        searching: false,
        savingAddress: false,
        confirmedSuccess: false,
        successTimer: null,
        searchMessage: '',
        isDragging: false,
        lat: null,
        lng: null,
        searchQuery: '',
        suggestions: [],
        defaultCenter: { lat: 32.4000, lng: -117.0500 }, // Baja California (Tijuana / Rosarito / Ensenada)

        async init() {
            const rawLat = this.$wire.get(latModel);
            const rawLng = this.$wire.get(lngModel);
            this.lat = rawLat ? parseFloat(rawLat) : null;
            this.lng = rawLng ? parseFloat(rawLng) : null;

            this.$watch('$wire.' + latModel, (val) => {
                if (val && parseFloat(val) !== this.lat) {
                    this.lat = parseFloat(val);
                    if (this.map && this.lng) {
                        this.map.panTo({ lat: this.lat, lng: this.lng });
                    }
                }
            });

            await this.initGoogleMap();
        },

        extractDetailsFromComponents(components, formattedAddress, fallbackTitle) {
            let city = '';
            let colony = '';
            let street = '';
            let streetNumber = '';

            if (Array.isArray(components)) {
                for (const comp of components) {
                    const types = comp.types || [];
                    const name = comp.longText || comp.long_name || comp.name || '';

                    if (types.includes('locality') || types.includes('administrative_area_level_2')) {
                        if (name.toLowerCase().includes('rosarito')) {
                            city = 'Rosarito';
                        } else if (name.toLowerCase().includes('tijuana')) {
                            city = 'Tijuana';
                        }
                    }

                    if (types.includes('sublocality_level_1') || types.includes('sublocality') || types.includes('neighborhood') || types.includes('administrative_area_level_3')) {
                        if (!colony) {
                            colony = name;
                        }
                    }

                    if (types.includes('route')) {
                        street = name;
                    }
                    if (types.includes('street_number')) {
                        streetNumber = name;
                    }
                }
            }

            if (!city) {
                if (formattedAddress && formattedAddress.toLowerCase().includes('rosarito')) {
                    city = 'Rosarito';
                } else {
                    city = 'Tijuana';
                }
            }

            if (!colony && fallbackTitle) {
                colony = fallbackTitle;
            }

            const fullAddress = formattedAddress || (street ? `${street} ${streetNumber}`.trim() : (colony ? `${colony}, ${city}` : ''));

            return {
                city,
                colony,
                address: fullAddress
            };
        },

        applyLocationDetails(details) {
            if (!details) return;
            if (cityModel && details.city) {
                this.$wire.set(cityModel, details.city);
            }
            if (colonyModel && details.colony) {
                this.$wire.set(colonyModel, details.colony);
            }
            if (addressModel && details.address) {
                this.$wire.set(addressModel, details.address);
            }
        },

        async confirmAndFillAddress() {
            if (!this.lat || !this.lng) {
                this.searchMessage = 'Por favor selecciona un punto en el mapa primero.';
                setTimeout(() => { this.searchMessage = ''; }, 4000);
                return;
            }

            this.savingAddress = true;
            this.searchMessage = '';

            try {
                if (!window.google || !window.google.maps) {
                    this.savingAddress = false;
                    return;
                }

                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    location: { lat: this.lat, lng: this.lng }
                }, (results, status) => {
                    this.savingAddress = false;
                    if (status === 'OK' && results && results[0]) {
                        const res = results[0];
                        const details = this.extractDetailsFromComponents(
                            res.address_components,
                            res.formatted_address,
                            ''
                        );
                        this.applyLocationDetails(details);

                        this.confirmedSuccess = true;
                        if (this.successTimer) clearTimeout(this.successTimer);
                        this.successTimer = setTimeout(() => {
                            this.confirmedSuccess = false;
                        }, 3500);
                    } else {
                        this.searchMessage = 'No se pudo obtener la dirección exacta automáticamente. Puedes escribirla abajo.';
                        setTimeout(() => { this.searchMessage = ''; }, 5000);
                    }
                });
            } catch (err) {
                this.savingAddress = false;
                console.error('Error al confirmar dirección:', err);
            }
        },

        async initGoogleMap() {
            try {
                if (!window.google || !window.google.maps) {
                    console.error('Google Maps API Loader no está disponible.');
                    return;
                }

                const { Map } = await google.maps.importLibrary("maps");
                await google.maps.importLibrary("places");

                const hasInitialCoords = this.lat && this.lng;
                const center = hasInitialCoords
                    ? { lat: this.lat, lng: this.lng }
                    : this.defaultCenter;

                this.map = new Map(this.$refs.mapContainer, {
                    center: center,
                    zoom: hasInitialCoords ? 17 : 14,
                    mapTypeId: 'hybrid',
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: false,
                    zoomControl: true,
                    zoomControlOptions: {
                        position: google.maps.ControlPosition.RIGHT_CENTER,
                    },
                    gestureHandling: 'greedy',
                });

                if (!hasInitialCoords) {
                    this.updateCoordinates(center.lat, center.lng, false);
                }

                this.map.addListener('dragstart', () => {
                    this.isDragging = true;
                });

                this.map.addListener('dragend', () => {
                    this.isDragging = false;
                });

                this.map.addListener('idle', () => {
                    this.isDragging = false;
                    const newCenter = this.map.getCenter();
                    if (newCenter) {
                        const lat = parseFloat(newCenter.lat().toFixed(7));
                        const lng = parseFloat(newCenter.lng().toFixed(7));
                        this.updateCoordinates(lat, lng, false);
                    }
                });
            } catch (err) {
                console.error('Error inicializando Google Maps:', err);
            }
        },

        updateCoordinates(lat, lng, triggerGeocode = false) {
            this.lat = lat;
            this.lng = lng;
            this.$wire.set(latModel, lat);
            this.$wire.set(lngModel, lng);
        },

        async fetchSuggestions() {
            const query = (this.searchQuery || '').trim();
            if (query.length < 2) {
                this.suggestions = [];
                return;
            }

            try {
                const { AutocompleteSuggestion } = await google.maps.importLibrary("places");
                const tijuanaCenter = new google.maps.LatLng(32.5149, -117.0382);

                const request = {
                    input: query,
                    includedRegionCodes: ['mx'],
                    locationBias: {
                        center: tijuanaCenter,
                        radius: 45000,
                    },
                };

                const response = await AutocompleteSuggestion.fetchAutocompleteSuggestions(request);
                if (response && response.suggestions && response.suggestions.length > 0) {
                    this.suggestions = response.suggestions.slice(0, 8).map(s => {
                        const pred = s.placePrediction;
                        return {
                            title: pred.mainText?.text || pred.text?.text || '',
                            subtitle: pred.secondaryText?.text || '',
                            placePrediction: pred,
                        };
                    });
                } else {
                    this.suggestions = [];
                }
            } catch (err) {
                console.error('Error al obtener sugerencias de Google Places:', err);
                this.suggestions = [];
            }
        },

        async selectSuggestion(item) {
            this.suggestions = [];
            this.searchQuery = item.title + (item.subtitle ? ', ' + item.subtitle : '');

            if (item.placePrediction) {
                try {
                    const place = item.placePrediction.toPlace();
                    await place.fetchFields({
                        fields: ['location', 'displayName', 'formattedAddress', 'addressComponents']
                    });
                    if (place.location && this.map) {
                        this.map.panTo(place.location);
                        this.map.setZoom(17);
                        const lat = parseFloat(place.location.lat().toFixed(7));
                        const lng = parseFloat(place.location.lng().toFixed(7));
                        this.updateCoordinates(lat, lng, false);

                        const details = this.extractDetailsFromComponents(
                            place.addressComponents,
                            place.formattedAddress,
                            item.title
                        );
                        this.applyLocationDetails(details);
                    }
                } catch (err) {
                    console.error('Error al obtener detalles del lugar:', err);
                }
            }
        },

        async performSearch() {
            const query = (this.searchQuery || '').trim();
            if (!query) return;

            if (this.suggestions.length > 0) {
                this.selectSuggestion(this.suggestions[0]);
                return;
            }

            this.searching = true;
            this.searchMessage = '';

            try {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    address: query + ', Tijuana, Baja California',
                    componentRestrictions: { country: 'MX' },
                }, (results, status) => {
                    this.searching = false;
                    if (status === 'OK' && results && results[0]) {
                        const res = results[0];
                        const loc = res.geometry.location;
                        if (this.map) {
                            this.map.panTo(loc);
                            this.map.setZoom(17);
                        }
                        const lat = parseFloat(loc.lat().toFixed(7));
                        const lng = parseFloat(loc.lng().toFixed(7));
                        this.updateCoordinates(lat, lng, false);
                        this.suggestions = [];

                        const details = this.extractDetailsFromComponents(
                            res.address_components,
                            res.formatted_address,
                            query
                        );
                        this.applyLocationDetails(details);
                    } else {
                        this.searchMessage = 'No encontramos esa dirección. Intenta con una referencia cercana o mueve el mapa.';
                        setTimeout(() => { this.searchMessage = ''; }, 6000);
                    }
                });
            } catch (err) {
                this.searching = false;
                console.error('Error en geocodificación:', err);
            }
        },

        getLocation() {
            if (!navigator.geolocation) {
                alert('Tu navegador no soporta geolocalización GPS.');
                return;
            }

            this.loadingGps = true;
            this.searchMessage = '';

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = parseFloat(pos.coords.latitude.toFixed(7));
                    const lng = parseFloat(pos.coords.longitude.toFixed(7));
                    if (this.map) {
                        this.map.panTo({ lat, lng });
                        this.map.setZoom(18);
                    }
                    this.updateCoordinates(lat, lng, false);
                    this.loadingGps = false;
                },
                (err) => {
                    this.loadingGps = false;
                    this.searchMessage = 'No pudimos obtener tu ubicación por GPS. Puedes escribir tu colonia arriba o mover el mapa.';
                    setTimeout(() => { this.searchMessage = ''; }, 6000);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    }));
});