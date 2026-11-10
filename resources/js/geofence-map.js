
document.addEventListener('alpine:init', () => {

    Alpine.data('geofenceMap', (state) => ({
        map: null,
        marker: null,
        circle: null,
        polyline: null,

        // Koordinat & Radius (Gunakan default kantor jika admin, atau data dari backend)
        lat: state.lat ?? state.latCircle,
        lng: state.lng ?? state.lngCircle,
        radius: state.radius || 0,

        // Mode deteksi: default ke 'admin' jika tidak didefinisikan oleh Filament
        mode: state.config.mode ?? 'default',

        permanentCircle: state.permanentCircle,
        latCircle: state.latCircle ?? -7.258137,
        lngCircle: state.lngCircle ?? 112.749306,
        radCircle: state.radCircle ?? 0,
        init() {

            // console.log(state.statePath);

            let currentLat = this.lat;
            let currentLng = this.lng;

            if (!currentLat || currentLat === "") {
                currentLat = (this.latCircle && this.latCircle !== "") ? this.latCircle : -7.258137;
            }
            if (!currentLng || currentLng === "") {
                currentLng = (this.lngCircle && this.lngCircle !== "") ? this.lngCircle : 112.749306;
            }

            // 1. Inisialisasi Dasar Peta (Sama untuk semua mode)
            this.map = L.map(this.$refs.map).setView([currentLat, currentLng], state.config.zoom ?? 16);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(this.map);

            // Fix bug icon Leaflet
            delete L.Icon.Default.prototype._getIconUrl;
            L.Icon.Default.mergeOptions({
                iconUrl: '/storage/images/leaflet/marker-icon.png',
                iconRetinaUrl: '/storage/images/leaflet/marker-icon-2x.png',
                shadowUrl: '/storage/images/leaflet/marker-shadow.png',
            });

            // 2. Render Element Awal
            this.renderElements(currentLat, currentLng);

            // 3. PERKONDISIAN BERDASARKAN MODE
            if (this.mode === 'admin') {
                this.initAdminMode();
            } else if (this.mode === 'attendance') {
                this.initAttendanceMode();
            }

            // Watcher radius tetap aktif (berguna di panel admin saat geser slider radius)
            this.$watch('radius', value => {

                // Pastikan nilai radius diubah ke integer/float dan tidak boleh 0 atau minus
                let safeRadius = parseFloat(value) || 50;
                if (this.circle && !this.permanentCircle) {
                    this.circle.setRadius(safeRadius);
                }

                // if (this.circle) this.circle.setRadius(value);
            });

            // this.$watch(
            //     () => this.$wire.data,
            //     value => console.log(value)
            // );

        },


        updateGps(lat, lng, distance, accuracy) {
            // console.log(state.statePath);
            const data = {
                latitude: lat,
                longitude: lng,
                distance: distance,
                accuracy: accuracy,
            };
            this.$wire.set(state.statePath, data);
        },

        // --- Logika Khusus Mode Admin (Form Create/Edit Branch) ---
        initAdminMode() {
            // User bebas klik di peta untuk menentukan koordinat kantor baru
            this.map.on('click', (e) => {
                this.updatePosition(e.latlng.lat, e.latlng.lng);
            });
        },

        // --- Logika Khusus Mode Attendance (Karyawan Absen) ---
        initAttendanceMode() {
            // Jalankan pelacakan GPS otomatis milik browser/device karyawan
            this.map.locate({
                // setView: true,
                maxZoom: 17,
                enableHighAccuracy: true
            });

            this.map.on('locationfound', (e) => {

                // Peringatan jika akurasi jelek (lebih dari 150 meter)
                if (e.accuracy > 150) {
                    alert(`Akurasi GPS rendah (${Math.round(e.accuracy)} meter). Mohon pindah ke area terbuka agar sinyal GPS lebih kuat dan akurat.`);
                }

                // Untuk menghitung jarak, titik kantor (office) diambil dari latCircle/lngCircle
                const office = [this.latCircle, this.lngCircle];
                const user = [e.latlng.lat, e.latlng.lng];

                let distance = this.renderPolylineWithDistance([office, user]);

                this.updateGps(user[0], user[1], distance, e.accuracy);
                // console.log(state.statePath);

                this.map.fitBounds(this.polyline.getBounds(), {
                    padding: [50, 50]
                });

                this.updatePosition(user[0], user[1], e.accuracy);


                if (this.circle && !this.permanentCircle) {
                    this.circle.setRadius(e.accuracy);
                }
            });

            this.map.on('locationerror', (e) => {
                alert("Gagal mendeteksi lokasi otomatis. Mohon aktifkan GPS perangkat Anda dan beri izin akses browser.");
                console.error(e.message);
            });
        },

        renderPolylineWithDistance(points = []) {

            // Hapus garis lama jika ada
            if (this.polyline) {
                this.map.removeLayer(this.polyline);
            }

            this.polyline = L.polyline(points, {
                dashArray: '5, 10',
                color: 'red',
                weight: 3,
                opacity: 0.7,
            }).addTo(this.map);

            const pointslatlng = points.map(
                ([lat, lng]) => L.latLng(lat, lng)
            );

            let totalDistance = 0;

            for (let i = 0; i < pointslatlng.length - 1; i++) {
                totalDistance += pointslatlng[i].distanceTo(pointslatlng[i + 1]);
            }

            return Number((totalDistance).toFixed(3));

        },

        // --- Helper Functions (Shared) ---
        renderElements(lat, lng) {
            // 1. Handle Marker (Selalu mengikuti posisi marker terbaru)
            if (!this.marker) {
                this.marker = L.marker([lat, lng]).addTo(this.map);
            } else {
                this.marker.setLatLng([lat, lng]);
            }
            // 2. Tentukan Koordinat & Radius Target Lingkaran
            // Jika permanentCircle true, kunci ke koordinat lingkaran statis. Jika false, ikuti marker.
            const targetLat = this.permanentCircle ? this.latCircle : lat;
            const targetLng = this.permanentCircle ? this.lngCircle : lng;
            const targetRadius = this.permanentCircle ? this.radCircle : this.radius;

            // 3. Handle Lingkaran (Clean & DRY)
            if (!this.circle) {

                // console.log(this.permanentCircle);

                this.circle = L.circle([targetLat, targetLng], { radius: targetRadius }).addTo(this.map);
            } else {
                this.circle.setLatLng([targetLat, targetLng]);
                this.circle.setRadius(targetRadius);
            }
        },

        updatePosition(lat, lng, accuracy = null) {
            // Update nilai entangle agar TextInput di Filament Form terisi
            this.lat = lat;
            this.lng = lng;

            this.renderElements(lat, lng);

            // Sinkronisasi balik ke properti Form Filament PHP
            if (this.state) {
                // this.state.lat = lat;
                // this.state.lng = lng;

                if (accuracy !== null && 'accuracy' in this.state) {
                    this.state.accuracy = Math.round(accuracy);
                }
            }
        }
    }));
});
