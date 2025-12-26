@section('Scripts')
    <script src="{{ asset('dist/js/jquery.min.js') }}"></script>
    <script src="{{ asset('dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('dist/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('dist/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('dist/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('dist/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('dist/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('dist/js/buttons.colVis.min.js') }}"></script>
    <script>
        $(function() {
            $("#example1").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "ordering": false,
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Logika SweetAlert
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
            });
        @endif

        // Logika Ambil Titik Koordinat
        document.getElementById('get-location-btn').addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    document.getElementById('titik_koordinat').value = `${lat},${lon}`;
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Titik koordinat berhasil diambil.',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }, error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal mengambil lokasi. Pastikan izin lokasi diberikan.',
                    });
                    console.error(error);
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Geolocation tidak didukung oleh browser ini.',
                });
            }
        });
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script>
        let map;

        document.getElementById('mapModal').addEventListener('shown.bs.modal', function(event) {
            // Ambil koordinat dari tombol yang diklik
            const button = event.relatedTarget;
            const koordinat = button.getAttribute('data-koordinat');
            const [lat, lon] = koordinat.split(',').map(Number);

            // Inisialisasi atau perbarui peta
            if (map) {
                map.remove();
            }

            map = L.map('modal-map').setView([lat, lon], 17); // Set zoom level 17 untuk detail

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            L.marker([lat, lon]).addTo(map)
                .bindPopup('Lokasi Pelanggan').openPopup();

            setTimeout(() => {
                map.invalidateSize();
            }, 500);
        });

        // Pastikan peta direset saat modal ditutup
        document.getElementById('mapModal').addEventListener('hidden.bs.modal', function() {
            if (map) {
                map.remove();
                map = null;
            }
        });
    </script>
    <script>
        // Listener untuk modal peta
        const mapModal = document.getElementById('mapModal');
        mapModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const koordinat = button.getAttribute('data-koordinat');
            const [lat, lon] = koordinat.split(',');

            // Kode untuk inisialisasi peta (jika ada) di sini
            // ...

            // Menyetel href tombol "Lihat di Gmaps"
            const gmapsButton = document.getElementById('gmaps-button');
            // Format URL untuk membuka Gmaps di aplikasi seluler:
            // Contoh: https://www.google.com/maps/dir/?api=1&destination=latitude,longitude
            const gmapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lon}`;
            gmapsButton.href = gmapsUrl;
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alamatModal = document.getElementById('alamatModal');
            alamatModal.addEventListener('show.bs.modal', function(event) {
                // Mendapatkan tombol yang memicu modal
                const button = event.relatedTarget;
                // Mengambil alamat lengkap dari atribut data
                const fullAlamat = button.getAttribute('data-alamat-lengkap');
                // Menemukan elemen <p> di dalam modal
                const alamatParagraph = alamatModal.querySelector('#full_alamat');
                // Menetapkan teks alamat ke elemen <p>
                alamatParagraph.textContent = fullAlamat;
            });
        });
    </script>
    <script>
        // Listener for the edit modal
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const poId = button.getAttribute('data-po-id');
            const poNama = button.getAttribute('data-po-nama');
            const poPaket = button.getAttribute('data-po-paket');
            const poWa = button.getAttribute('data-po-wa');
            const poEmail = button.getAttribute('data-po-email');
            const poAlamat = button.getAttribute('data-po-alamat');
            const poIdentitas = button.getAttribute('data-po-identitas');
            // Correct the attribute name to match your HTML
            const poKoordinat = button.getAttribute('data-po-koordinat');
            const poFotoKtp = button.getAttribute('data-po-foto-ktp');
            const poFotoRumah = button.getAttribute('data-po-foto-rumah');

            // Set the form action to the correct update route
            const editForm = document.getElementById('editForm');
            editForm.action = `/pre-orders/${poId}`;

            // Populate the form fields
            document.getElementById('edit_nama_lengkap').value = poNama;
            document.getElementById('edit_paket_internet').value = poPaket;
            document.getElementById('edit_no_identitas').value = poIdentitas;
            document.getElementById('edit_no_whatsapp').value = poWa;
            document.getElementById('edit_alamat_email').value = poEmail;
            document.getElementById('edit_alamat_lengkap').value = poAlamat;
            document.getElementById('edit_titik_koordinat').value = poKoordinat;

            // Display existing photos
            const currentKtpDiv = document.getElementById('current_foto_ktp');
            const currentRumahDiv = document.getElementById('current_foto_rumah');

            if (poFotoKtp) {
                currentKtpDiv.innerHTML =
                    `<img src="/storage/${poFotoKtp}" alt="Foto KTP" class="img-thumbnail" width="100">`;
            } else {
                currentKtpDiv.innerHTML = 'Tidak ada foto KTP';
            }

            if (poFotoRumah) {
                currentRumahDiv.innerHTML =
                    `<img src="/storage/${poFotoRumah}" alt="Foto Rumah" class="img-thumbnail" width="100">`;
            } else {
                currentRumahDiv.innerHTML = 'Tidak ada foto Rumah';
            }

            // Add event listener for the location button inside the edit modal
            document.getElementById('edit-get-location-btn').addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(position => {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        document.getElementById('edit_titik_koordinat').value = `${lat},${lon}`;
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Titik koordinat berhasil diambil.',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }, error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Gagal mengambil lokasi. Pastikan izin lokasi diberikan.',
                        });
                        console.error(error);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Geolocation tidak didukung oleh browser ini.',
                    });
                }
            });
        });
        // Logika untuk Modal Hapus Pre-Order
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const poId = button.getAttribute('data-po-id');
            const modalForm = deleteModal.querySelector('#deleteForm');

            // Mengatur action form agar sesuai dengan rute hapus
            modalForm.action = `/pre-orders/${poId}`;
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- Logika untuk Modal Detail (NIK & Email) ---
            const detailModal = document.getElementById('detailModal');

            // Memastikan elemen modal ada
            if (detailModal) {
                detailModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;

                    // 1. Ambil data dari atribut data tombol
                    // Atribut ini diambil dari tombol di kolom Opsi
                    const identitas = button.getAttribute('data-identitas');
                    const emailLengkap = button.getAttribute('data-email-lengkap');

                    // 2. Target elemen di modal
                    // ID harus sesuai dengan yang ada di struktur modal gabungan
                    const identitasParagraph = detailModal.querySelector('#detail_identitas');
                    const emailParagraph = detailModal.querySelector('#detail_email');

                    // 3. Isi konten modal
                    if (identitasParagraph) {
                        identitasParagraph.textContent = identitas;
                    }
                    if (emailParagraph) {
                        emailParagraph.textContent = emailLengkap;
                    }
                });
            }
        });
    </script>
    <script>
        // Logika untuk Modal Lihat KTP
        const viewKtpModal = document.getElementById('viewKtpModal');
        viewKtpModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const ktpUrl = button.getAttribute('data-ktp-url');

            const ktpImage = document.getElementById('ktp_image');
            ktpImage.src = ktpUrl;
        });
    </script>
    <script>
        // Logika untuk Modal Lihat Rumah
        const viewRumahModal = document.getElementById('viewRumahModal');
        viewRumahModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const rumahUrl = button.getAttribute('data-rumah-url');

            const rumahImage = document.getElementById('rumah_image');
            rumahImage.src = rumahUrl;
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const delegateModal = document.getElementById('delegateModal');

            if (delegateModal) {
                delegateModal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    const poId = button.getAttribute('data-po-id');
                    const poNama = button.getAttribute('data-po-nama');

                    const modalForm = delegateModal.querySelector('#delegateForm');
                    const poNameDisplay = delegateModal.querySelector('#delegate_po_name');

                    // Set action form ke rute delegasi
                    modalForm.action = `/pre-orders/${poId}/delegate`;
                    poNameDisplay.textContent = poNama;
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ... (Listener lainnya seperti editModal, delegateModal) ...

            // --- Logika untuk Modal Reset Tugas ---
            const resetModal = document.getElementById('resetModal');

            if (resetModal) {
                resetModal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    const poId = button.getAttribute('data-po-id');
                    const poNama = button.getAttribute('data-po-nama');

                    const modalForm = resetModal.querySelector('#resetForm');
                    const poNameDisplay = resetModal.querySelector('#reset_po_name');

                    // Set action form ke rute reset
                    // PASTIKAN rute ini sesuai: Route::post('pre-orders/{preOrder}/reset-delegation', ...)
                    modalForm.action = `/pre-orders/${poId}/reset-delegation`;
                    poNameDisplay.textContent = poNama;
                });
            }
        });
    </script>
@endsection
