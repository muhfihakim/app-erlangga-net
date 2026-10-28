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
                    $('#edit_paket_id').val($btn.data('po-paket'));
                    $('#edit_no_identitas').val($btn.data('po-identitas'));
                    $('#edit_no_whatsapp').val($btn.data('po-wa'));
                    $('#edit_alamat_email').val($btn.data('po-email'));
                    $('#edit_alamat_lengkap').val($btn.data('po-alamat'));
                    $('#edit_titik_koordinat').val($btn.data('po-koordinat'));

                    const fotoKtpEdit = $btn.data('foto-ktp');
                    const fotoRumahEdit = $btn.data('foto-rumah');

                    if (fotoKtpEdit) {
                        $('#current_foto_ktp').html(`<img src="${fotoKtpEdit}" class="w-full h-auto max-h-48 object-contain bg-slate-50">`);
                    } else {
                        $('#current_foto_ktp').html(`<div class="w-full h-24 bg-slate-50 flex items-center justify-center text-slate-400 text-xs font-bold">TIDAK ADA FOTO</div>`);
                    }

                    if (fotoRumahEdit) {
                        $('#current_foto_rumah').html(`<img src="${fotoRumahEdit}" class="w-full h-auto max-h-48 object-contain bg-slate-50">`);
                    } else {
                        $('#current_foto_rumah').html(`<div class="w-full h-24 bg-slate-50 flex items-center justify-center text-slate-400 text-xs font-bold">TIDAK ADA FOTO</div>`);
                    }
                    
                    // Reset tabs to first
                    $(`#${modalId} .modal-tab`).first().trigger('click');
                }

                if (modalId === 'detailModal') {
                    $('#detail_nama_lengkap').text($btn.data('nama') || '-');
                    $('#detail_identitas').text($btn.data('identitas') || '-');
                    $('#detail_email').text($btn.data('email-lengkap') || '-');
                    $('#detail_alamat').text($btn.data('alamat-lengkap') || '-');
                    $('#detail_paket').text($btn.data('paket') || '-');
                    $('#detail_paket_harga').text($btn.data('paket-harga') || '');
                    $('#detail_sales').text($btn.data('sales') || '-');
                    $('#detail_tgl').text($btn.data('tgl') || '-');

                    const statusText = $btn.data('status') || 'Pending';
                    const statusBadge = $btn.data('status-badge') || 'badge-draft';
                    $('#detail_status_badge').text(statusText.toUpperCase()).attr('class', `badge ${statusBadge} text-[10px] py-0.5 px-2`);

                    // WhatsApp link
                    const wa = $btn.data('wa');
                    if (wa && wa !== '-') {
                        $('#detail_wa').text(wa);
                        let cleanWa = String(wa).replace(/[^0-9]/g, '');
                        if (cleanWa.startsWith('0')) cleanWa = '62' + cleanWa.substring(1);
                        $('#detail_wa_btn').attr('href', `https://wa.me/${cleanWa}`).removeClass('hidden');
                    } else {
                        $('#detail_wa').text('-');
                        $('#detail_wa_btn').addClass('hidden');
                    }

                    // Coordinates & Maps link
                    const koordinat = $btn.data('koordinat');
                    if (koordinat && String(koordinat).includes(',')) {
                        $('#detail_koordinat').text(koordinat);
                        const mapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${koordinat}`;
                        $('#detail_maps_link').attr('href', mapsUrl).removeClass('hidden');
                    } else {
                        $('#detail_koordinat').text(koordinat || 'Belum diatur');
                        $('#detail_maps_link').addClass('hidden');
                    }

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

                if (modalId === 'createPppoeModal') {
                    $('#pppoe_pre_order_id').val($btn.data('po-id'));
                    $('#create_pppoe_po_nama_akun, #create_pppoe_po_nama_pembayaran').text($btn.data('po-nama'));
                    $('#create_pppoe_po_alamat_akun, #create_pppoe_po_alamat_pembayaran').text($btn.data('po-alamat') || '-');
                    $('#create_pppoe_paket_id').val($btn.data('po-paket'));
                    $(`#${modalId} .modal-tab`).first().trigger('click');
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
                processing: true,
                serverSide: true,
                ajax: window.location.href,
                columns: [
                    { data: 'no', name: 'no', searchable: false, orderable: false, className: 'text-center text-slate-400 font-bold', render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }},
                    { data: 'created_at', name: 'created_at', className: 'text-center' },
                    { data: 'sales', name: 'nama_sales', className: 'text-center' },
                    { data: 'nama_lengkap', name: 'nama_lengkap', className: 'text-left' },
                    { data: 'paket', name: 'paket_id', className: 'text-center' },
                    { data: 'status_opsi', name: 'status_opsi', searchable: false, orderable: false, className: 'text-center' }
                ],
                "pageLength": 10,
                "lengthChange": false,
                "ordering": false,
                "info": false,
                "responsive": true,
                "dom": 'tpir',
                "language": {
                    "processing": "<i class='fa-solid fa-circle-notch fa-spin text-sky-500 text-2xl'></i>",
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
