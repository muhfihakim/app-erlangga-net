<!doctype html>
<html lang="id">
<x-layouts.header />

<body class="bg-slate-100 text-slate-900 antialiased font-sans">
    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="admin-layout">
        <!-- Sidebar -->
        <x-layouts.sidebar />

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Navbar -->
            <x-layouts.navbar />

            <!-- Main Content -->
            <main class="page-body" role="main">
                <div class="max-w-[1600px] mx-auto w-full">
                    {{ $slot }}
                </div>
            </main>

            <!-- Footer -->
            <x-layouts.footer />
        </div>
    </div>

    <!-- Global Delete Confirmation Modal -->
    <div class="modal-overlay" id="globalDeleteModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-trash text-rose-600"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Hapus Data?</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('globalDeleteModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-8 bg-white">
                <div class="bg-rose-50 p-4 rounded-2xl border border-rose-100 text-center">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-3xl mb-3"></i>
                    <p class="text-sm font-bold text-slate-800 leading-relaxed">
                        Apakah Anda yakin ingin menghapus data <span class="text-rose-600" id="global-delete-name"></span>? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <form id="globalDeleteForm" method="POST" class="w-full flex justify-center gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" 
                        style="padding: 10px 24px;"
                        onclick="closeCustomModal('globalDeleteModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !bg-rose-600 !flex !items-center !justify-center gap-2"
                        style="padding: 10px 24px;">
                        <i class="fa-solid fa-trash-can"></i> Ya, Hapus!
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Global Toast Container -->
    <div id="toast-container" class="toast-container" style="z-index: 999999;"></div>

    <script>
        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const ov = document.getElementById('sidebar-overlay');
            sb.classList.toggle('open');
            ov.classList.toggle('show');
        }

        // Custom Modal Functions
        function openCustomModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeCustomModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('open');
                document.body.style.overflow = '';
            }
        }

        // Global Delete Confirmation
        function confirmGlobalDelete(actionUrl, itemName) {
            const form = document.getElementById('globalDeleteForm');
            const nameEl = document.getElementById('global-delete-name');
            if (form && nameEl) {
                form.action = actionUrl;
                nameEl.textContent = itemName;
                openCustomModal('globalDeleteModal');
            }
        }

        // Global Toast System
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `custom-toast toast-${type}`;
            
            let icon = 'fa-circle-check';
            let title = 'Berhasil';
            if (type === 'error') { icon = 'fa-circle-xmark'; title = 'Gagal'; }
            if (type === 'info') { icon = 'fa-circle-info'; title = 'Informasi'; }

            toast.innerHTML = `
                <div class="toast-icon">
                    <i class="fa-solid ${icon}"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
            `;
            
            container.appendChild(toast);
            
            // Auto remove after 4 seconds
            setTimeout(() => {
                toast.classList.add('out');
                setTimeout(() => {
                    if (toast.parentNode === container) {
                        container.removeChild(toast);
                    }
                }, 400);
            }, 4000);
        }

        // Handle Session Toasts
        $(document).ready(function() {
            @if(session('success'))
                showToast('{{ session('success') }}', 'success');
            @endif
            @if(session('error'))
                showToast('{{ session('error') }}', 'error');
            @endif
            
            // Handle clicking outside to close
            $('.modal-overlay').on('click', function(e) {
                if (e.target === this) closeCustomModal(this.id);
            });

            // Global Form Validation Interceptor
            $('form').attr('novalidate', true);
            $('form').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();

                    let firstInvalid = $(this).find(':invalid').first();
                    
                    if (firstInvalid.length) {
                        // Check if it's inside a hidden tab
                        let tabPanel = firstInvalid.closest('.tab-panel');
                        if (tabPanel.length && !tabPanel.hasClass('active')) {
                            let tabId = tabPanel.attr('id');
                            let tabBtn = $('.modal-tab[data-tab="' + tabId + '"]');
                            if (tabBtn.length) {
                                tabBtn.click();
                            }
                        }

                        // Determine label/name
                        let id = firstInvalid.attr('id');
                        let labelText = '';
                        if (id) {
                            let label = $(this).find('label[for="' + id + '"]');
                            if (label.length) labelText = label.text().replace('*', '').trim();
                        }
                        
                        // Coba cari label berdasarkan parent (untuk radio/checkbox/file)
                        if (!labelText) {
                            let parentLabel = firstInvalid.closest('.form-group').find('.form-label, .form-label-custom');
                            if (parentLabel.length) labelText = parentLabel.text().replace('*', '').trim();
                        }

                        if (!labelText) labelText = firstInvalid.attr('placeholder') || firstInvalid.attr('name') || 'kolom ini';

                        // Custom error message
                        let errorMessage = 'Harap isi ' + labelText + ' dengan benar.';
                        if (firstInvalid[0].validity.valueMissing) {
                            errorMessage = labelText + ' wajib diisi / dipilih.';
                        } else if (firstInvalid[0].validity.typeMismatch) {
                            errorMessage = 'Format ' + labelText + ' tidak valid (contoh: email harus pakai @).';
                        } else if (firstInvalid[0].validity.tooShort) {
                            errorMessage = labelText + ' terlalu pendek.';
                        }

                        // Show Toast Notification
                        showToast(errorMessage, 'error');

                        // Visual Feedback
                        firstInvalid.focus();
                        firstInvalid.addClass('!border-rose-500 !ring-2 !ring-rose-200 transition-all');
                        
                        // Shake Animation Effect using JS
                        let x = 0;
                        let interval = setInterval(() => {
                            firstInvalid.css('transform', `translateX(${x % 2 === 0 ? '5px' : '-5px'})`);
                            x++;
                            if (x > 5) {
                                clearInterval(interval);
                                firstInvalid.css('transform', 'none');
                            }
                        }, 50);
                    }
                }
            });

            // Remove visual feedback upon typing or selecting
            $('input, select, textarea').on('input change', function() {
                if (this.checkValidity()) {
                    $(this).removeClass('!border-rose-500 !ring-2 !ring-rose-200 transition-all');
                }
            });
        });
    </script>

    @yield('Scripts')
    @stack('scripts')
    @include('partials.location_scripts')
</body>
</html>
