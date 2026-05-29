@section('Scripts')
    <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // --- Custom Modal System ---
        function openCustomModal(modalId, triggerEl = null) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            // Handle Data Population if triggerEl is provided
            if (triggerEl) {
                const $btn = $(triggerEl);
                
                if (modalId === 'editModal') {
                    const poId = $btn.data('po-id');
                    $('#editForm').attr('action', `/pre-orders/${poId}`);
                    $('#edit_nama_lengkap').val($btn.data('po-nama'));
                    $('#edit_paket_internet').val($btn.data('po-paket'));
                    $('#edit_no_identitas').val($btn.data('po-identitas'));
                    $('#edit_no_whatsapp').val($btn.data('po-wa'));
                    $('#edit_alamat_email').val($btn.data('po-email'));
                    $('#edit_alamat_lengkap').val($btn.data('po-alamat'));
                    $('#edit_titik_koordinat').val($btn.data('po-koordinat'));
                    
                    // Reset tabs to first
                    $(`#${modalId} .modal-tab`).first().trigger('click');
                }

                if (modalId === 'detailModal') {
                    $('#detail_nama_lengkap').text($btn.data('nama'));
                    $('#detail_identitas').text($btn.data('identitas'));
                    $('#detail_email').text($btn.data('email-lengkap'));
                    $('#detail_alamat').text($btn.data('alamat-lengkap'));
                    $('#detail_paket').text($btn.data('paket'));
                    $('#detail_koordinat').text($btn.data('koordinat') || 'N/A');

                    // Handle Photo Previews
                    const fotoKtp = $btn.data('foto-ktp');
                    const fotoRumah = $btn.data('foto-rumah');

                    if (fotoKtp) {
                        $('#detail_foto_ktp').attr('src', fotoKtp).removeClass('hidden');
                        $('#no_ktp_placeholder').addClass('hidden');
                    } else {
                        $('#detail_foto_ktp').addClass('hidden');
                        $('#no_ktp_placeholder').removeClass('hidden');
                    }

                    if (fotoRumah) {
                        $('#detail_foto_rumah').attr('src', fotoRumah).removeClass('hidden');
                        $('#no_rumah_placeholder').addClass('hidden');
                    } else {
                        $('#detail_foto_rumah').addClass('hidden');
                        $('#no_rumah_placeholder').removeClass('hidden');
                    }
                }

                if (modalId === 'deleteModal') {
                    $('#deleteForm').attr('action', `/pre-orders/${$btn.data('po-id')}`);
                }

                if (modalId === 'delegateModal') {
                    $('#delegateForm').attr('action', `/pre-orders/${$btn.data('po-id')}/delegate`);
                    $('#delegate_po_name').text($btn.data('po-nama'));
                }

                if (modalId === 'resetModal') {
                    $('#resetForm').attr('action', `/pre-orders/${$btn.data('po-id')}/reset-delegation`);
                    $('#reset_po_name').text($btn.data('po-nama'));
                }

                if (modalId === 'unspekModal') {
                    $('#unspekForm').attr('action', `/pre-orders/${$btn.data('po-id')}/unspek`);
                    $('#unspek_po_name').text($btn.data('po-nama'));
                }

                if (modalId === 'mapModal') {
                    modal.classList.add('open');
                    document.body.style.overflow = 'hidden';
                    const koordinat = $btn.data('koordinat');
                    if (koordinat) {
                        initModalMap(koordinat);
                    }
                    return; // Avoid double open below
                }
            }

            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeCustomModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('open');
                document.body.style.overflow = '';
            }
        }

        // --- Map Logic ---
        let map;
        function initModalMap(koordinat) {
            const [lat, lon] = koordinat.split(',').map(Number);
            
            // Wait for modal transition
            setTimeout(() => {
                const mapContainer = document.getElementById('modal-map');
                if (!mapContainer) return;

                if (map) {
                    map.remove();
                    map = null;
                }

                map = L.map('modal-map').setView([lat, lon], 17);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                // Fix for missing marker icon in builds
                const DefaultIcon = L.icon({
                    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });
                L.Marker.prototype.options.icon = DefaultIcon;

                L.marker([lat, lon]).addTo(map)
                    .bindPopup('<div class="text-center font-bold">Lokasi Pemasangan</div>')
                    .openPopup();
                
                $('#gmaps-button').attr('href', `https://www.google.com/maps/dir/?api=1&destination=${lat},${lon}`);
                
                // Repeated invalidation to handle complex CSS animations
                setTimeout(() => map.invalidateSize(), 100);
                setTimeout(() => map.invalidateSize(), 600);
            }, 400);
        }

        $(document).ready(function() {
            // DataTables Initialization
            const table = $('#preorder-table').DataTable({
                "pageLength": 10,
                "lengthChange": false,
                "ordering": false,
                "info": false,
                "responsive": true,
                "dom": 'tpir',
                "language": {
                    "zeroRecords": "Data tidak ditemukan",
                    "paginate": {
                        "previous": '<i class="fa-solid fa-chevron-left"></i>',
                        "next": '<i class="fa-solid fa-chevron-right"></i>'
                    }
                }
            });

            // Custom Search
            $('#custom-search').on('keyup', function() {
                table.search(this.value).draw();
            });

            // --- Tab Navigation Logic ---
            $(document).on('click', '.modal-tab', function() {
                const tabId = $(this).data('tab');
                const $modalBox = $(this).closest('.modal-box');
                
                // Update tab buttons in current modal
                $modalBox.find('.modal-tab').removeClass('active');
                $(this).addClass('active');
                
                // Update panels in current modal
                $modalBox.find('.tab-panel').removeClass('active');
                $('#' + tabId).addClass('active');
            });

            // Close modal on overlay click
            $(document).on('click', '.modal-overlay', function(e) {
                if (e.target === this) {
                    $(this).removeClass('open');
                    document.body.style.overflow = '';
                }
            });

            // --- Geolocation Logic ---
            function setupGeolocation(btnId, inputId) {
                $('#' + btnId).on('click', function() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(position => {
                            $(`#${inputId}`).val(`${position.coords.latitude},${position.coords.longitude}`);
                            showToast('Titik koordinat diambil.', 'success');
                        }, err => {
                            showToast('Gagal! Pastikan izin lokasi diberikan.', 'error');
                        });
                    } else {
                        showToast('Geolocation tidak didukung.', 'error');
                    }
                });
            }

            setupGeolocation('get-location-btn', 'titik_koordinat');
            setupGeolocation('edit-get-location-btn', 'edit_titik_koordinat');
        });
    </script>
@endsection
