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
            <main class="page-body relative" role="main">
                <div class="max-w-[1600px] mx-auto w-full">
                    {{ $slot }}
                </div>
            </main>

            <!-- Footer -->
            <x-layouts.footer />
        </div>
    </div>

    <!-- Mobile Navigation -->
    <x-layouts.mobile-nav />

    <!-- Global Delete Confirmation Modal -->
    <div class="modal-overlay" id="globalDeleteModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm border border-rose-100 shrink-0">
                        <i class="fa-solid fa-trash-can"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Hapus Data</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Konfirmasi penghapusan data sistem.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('globalDeleteModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-5 bg-white">
                <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-rose-900">
                        <i class="fa-solid fa-triangle-exclamation"></i> Peringatan:
                    </div>
                    <p class="m-0 leading-relaxed text-slate-700">
                        Apakah Anda yakin ingin menghapus data <strong class="text-rose-700 font-bold" id="global-delete-name"></strong>? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2">
                <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" 
                    onclick="closeCustomModal('globalDeleteModal')">
                    Batal
                </button>
                <form id="globalDeleteForm" method="POST" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !text-white !h-9 !px-5 text-xs font-semibold">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Global Toast Container -->
    <div id="toast-container" class="toast-container" style="z-index: 999999;"></div>

    <!-- Global Page Loader Skeleton -->
    <div id="page-skeleton" class="page-skeleton-overlay">
        <div class="skeleton-header">
            <div class="skeleton-title"></div>
            <div class="skeleton-btn"></div>
        </div>
        <div class="skeleton-card">
            <div class="skeleton-line full"></div>
            <div class="skeleton-line half"></div>
            <div class="skeleton-line three-quarter"></div>
            <div class="skeleton-line full mt-4"></div>
            <div class="skeleton-line full"></div>
            <div class="skeleton-line half"></div>
        </div>
        <div class="skeleton-card mt-4">
            <div class="skeleton-line full"></div>
            <div class="skeleton-line three-quarter"></div>
        </div>
    </div>

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
                // Only restore body scrolling if no other modals are still open
                if (!document.querySelector('.modal-overlay.open')) {
                    document.body.style.overflow = '';
                }
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
            @if($errors->any())
                @foreach($errors->all() as $error)
                    showToast('{{ str_replace("'", "\'", $error) }}', 'error');
                @endforeach
            @endif
            @if(session('api_token'))
                localStorage.setItem('app_token', '{{ session('api_token') }}');
            @endif
            
            // Global Modal Backdrop Click Delegation
            $(document).on('click', '.modal-overlay', function(e) {
                if (e.target === this) closeCustomModal(this.id);
            });

            // Global Modal Escape Key Listener
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    const openModals = document.querySelectorAll('.modal-overlay.open');
                    if (openModals.length > 0) {
                        const topModal = openModals[openModals.length - 1];
                        closeCustomModal(topModal.id);
                    }
                }
            });

            // Global Form Validation Interceptor (Smart Client-Side Validation)
            $('form').attr('novalidate', true);
            $(document).on('submit', 'form', function(e) {
                // Skip GET forms and forms with data-no-validation
                if (this.method.toLowerCase() === 'get' || $(this).data('no-validation')) return;

                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();

                    let firstInvalid = $(this).find(':invalid').first();
                    
                    if (firstInvalid.length) {
                        // 1. Handle Multi-Tab Support: Automatically switch to the tab containing the error
                        let tabPanel = firstInvalid.closest('.tab-panel');
                        if (tabPanel.length && !tabPanel.hasClass('active')) {
                            let tabId = tabPanel.attr('id');
                            let tabBtn = $('.modal-tab[data-tab="' + tabId + '"]');
                            if (tabBtn.length) {
                                tabBtn.click();
                            }
                        }

                        // 2. Smart Label Discovery
                        let labelText = '';
                        let id = firstInvalid.attr('id');
                        
                        // Try finding associated <label for="...">
                        if (id) {
                            let label = $(`label[for="${id}"]`);
                            if (label.length) labelText = label.text();
                        }
                        
                        // Fallback: Parent .form-group label (common pattern in this app)
                        if (!labelText) {
                            let parentLabel = firstInvalid.closest('.form-group').find('label, .form-label, .form-label-custom');
                            if (parentLabel.length) labelText = parentLabel.first().text();
                        }

                        // Final Fallbacks: Placeholder or Name
                        if (!labelText) {
                            labelText = firstInvalid.attr('placeholder') || firstInvalid.attr('name') || 'kolom ini';
                        }

                        // Clean up label text (remove asterisks and extra spaces)
                        labelText = labelText.replace('*', '').replace(':', '').trim();

                        // 3. Build User-Friendly Error Message
                        let errorMessage = `Bidang "${labelText}" wajib diisi!`;
                        
                        if (firstInvalid[0].type === 'file') {
                            errorMessage = `Silakan pilih file untuk "${labelText}".`;
                        } else if (firstInvalid[0].type === 'radio' || firstInvalid[0].type === 'checkbox') {
                            errorMessage = `Pilihan "${labelText}" harus dipilih.`;
                        } else if (firstInvalid[0].validity.typeMismatch && firstInvalid[0].type === 'email') {
                            errorMessage = `Format email "${labelText}" tidak valid.`;
                        }

                        // 4. Visual Feedback & Toast
                        showToast(errorMessage, 'error');
                        
                        setTimeout(() => {
                            firstInvalid.focus();
                            firstInvalid.addClass('!border-rose-500 !ring-2 !ring-rose-200 transition-all');
                            
                            // Shake Effect
                            firstInvalid.css('transition', 'transform 0.1s');
                            let offsets = [5, -5, 5, -5, 0];
                            offsets.forEach((offset, index) => {
                                setTimeout(() => {
                                    firstInvalid.css('transform', `translateX(${offset}px)`);
                                }, index * 100);
                            });
                        }, 50);
                    }
                } else {
                    // Valid form, add loading state to submit button
                    let submitBtn = $(this).find('button[type="submit"]');
                    if (submitBtn.length) {
                        if (submitBtn.data('is-loading')) {
                            e.preventDefault();
                            return;
                        }
                        submitBtn.data('is-loading', true);
                        
                        let originalWidth = submitBtn.outerWidth();
                        if (originalWidth > 0) {
                            submitBtn.css('min-width', originalWidth + 'px');
                        }
                        submitBtn.css('pointer-events', 'none').css('opacity', '0.8');
                        
                        let hasText = submitBtn.text().trim().length > 0;
                        if (hasText) {
                            submitBtn.html('<i class="fa-solid fa-spinner fa-spin"></i> Memproses...');
                        } else {
                            submitBtn.html('<i class="fa-solid fa-spinner fa-spin"></i>');
                        }
                    }
                }
            });

            // Auto-clear error state when user starts typing/selecting
            $(document).on('input change', 'input, select, textarea', function() {
                $(this).removeClass('!border-rose-500 !ring-2 !ring-rose-200');
            });
        });

        // Global Skeleton Loader Logic
        window.addEventListener('load', () => {
            const skeleton = document.getElementById('page-skeleton');
            if (skeleton) skeleton.classList.add('hidden');
        });

        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href && !link.href.includes('#') && !link.hasAttribute('download') && link.target !== '_blank' && link.origin === window.location.origin) {
                const skeleton = document.getElementById('page-skeleton');
                if (skeleton) skeleton.classList.remove('hidden');
            }
        });

        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!form.hasAttribute('target') && !form.classList.contains('no-loader')) {
                const skeleton = document.getElementById('page-skeleton');
                if (skeleton) skeleton.classList.remove('hidden');
            }
        });
    </script>

    @yield('Scripts')
    @stack('scripts')
    @include('partials.location_scripts')
</body>
</html>
