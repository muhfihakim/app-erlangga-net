<x-layouts.app>
    @section('Css')
        <style>
            .wa-container {
                display: flex;
                height: calc(100vh - 120px);
                min-height: 580px;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06);
            }

            /* Left Sidebar: Contacts */
            .wa-sidebar {
                width: 380px;
                min-width: 320px;
                max-width: 420px;
                border-right: 1px solid #e2e8f0;
                display: flex;
                flex-direction: column;
                background: #f8fafc;
                transition: all 0.25s ease;
            }

            .wa-sidebar-header {
                padding: 1rem 1.15rem;
                background: #ffffff;
                border-bottom: 1px solid #f1f5f9;
            }

            .wa-filter-tabs {
                display: flex;
                gap: 6px;
                padding: 0.65rem 1rem;
                background: #f8fafc;
                border-bottom: 1px solid #e2e8f0;
                overflow-x: auto;
            }

            .wa-filter-pill {
                padding: 4px 10px;
                font-size: 11px;
                font-weight: 700;
                border-radius: 20px;
                background: #ffffff;
                color: #64748b;
                border: 1px solid #e2e8f0;
                cursor: pointer;
                white-space: nowrap;
                transition: all 0.15s;
            }

            .wa-filter-pill:hover {
                border-color: #cbd5e1;
                color: #0f172a;
            }

            .wa-filter-pill.active {
                background: #10b981;
                border-color: #10b981;
                color: #ffffff;
            }

            .wa-contact-list {
                flex: 1;
                overflow-y: auto;
                padding: 4px;
            }

            .wa-contact-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px 14px;
                border-radius: 12px;
                margin-bottom: 2px;
                cursor: pointer;
                transition: all 0.15s ease;
                border: 1px solid transparent;
            }

            .wa-contact-item:hover {
                background: #f1f5f9;
            }

            .wa-contact-item.active {
                background: #ecfdf5;
                border-color: #a7f3d0;
            }

            .wa-avatar {
                width: 42px;
                height: 42px;
                border-radius: 12px;
                background: linear-gradient(135deg, #10b981, #059669);
                color: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 800;
                font-size: 14px;
                flex-shrink: 0;
                box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
            }

            /* Right Pane: Chat Window */
            .wa-chat-area {
                flex: 1;
                display: flex;
                flex-direction: column;
                background: #f1f5f9;
                position: relative;
            }

            .wa-chat-header {
                padding: 0.85rem 1.25rem;
                background: #ffffff;
                border-bottom: 1px solid #e2e8f0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                z-index: 10;
            }

            .wa-chat-messages {
                flex: 1;
                overflow-y: auto;
                padding: 1.25rem 1.5rem;
                display: flex;
                flex-direction: column;
                gap: 10px;
                background-color: #efeae2;
                background-image: radial-gradient(#cbd5e1 0.75px, transparent 0.75px);
                background-size: 16px 16px;
            }

            /* Message Bubbles */
            .wa-bubble-wrapper {
                display: flex;
                width: 100%;
            }

            .wa-bubble-wrapper.outbound {
                justify-content: flex-end;
            }

            .wa-bubble-wrapper.inbound {
                justify-content: flex-start;
            }

            .wa-bubble {
                max-width: 72%;
                padding: 9px 13px;
                border-radius: 12px;
                font-size: 13px;
                line-height: 1.45;
                position: relative;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
                word-break: break-word;
                white-space: pre-wrap;
            }

            .wa-bubble.outbound {
                background: #d9fdd3;
                color: #111b21;
                border-top-right-radius: 2px;
            }

            .wa-bubble.inbound {
                background: #ffffff;
                color: #111b21;
                border-top-left-radius: 2px;
            }

            .wa-bubble.invoice-bubble {
                border-left: 4px solid #10b981;
            }

            .wa-bubble-meta {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 4px;
                font-size: 10.5px;
                color: #64748b;
                margin-top: 4px;
            }

            /* Composer Footer */
            .wa-composer {
                padding: 0.85rem 1.25rem;
                background: #ffffff;
                border-top: 1px solid #e2e8f0;
            }

            .wa-composer-input {
                width: 100%;
                border: 1px solid #cbd5e1;
                border-radius: 12px;
                padding: 9px 13px;
                font-size: 13px;
                resize: none;
                max-height: 120px;
                min-height: 44px;
                outline: none;
                transition: border-color 0.2s;
            }

            .wa-composer-input:focus {
                border-color: #10b981;
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
            }

            /* Empty state placeholder */
            .wa-empty-chat {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                background: #f8fafc;
                color: #64748b;
                text-align: center;
                padding: 2rem;
            }

            /* Responsive behavior */
            @media (max-width: 900px) {
                .wa-container {
                    height: calc(100vh - 150px);
                }

                .wa-sidebar {
                    width: 100%;
                    max-width: 100%;
                }

                .wa-container.chat-open .wa-sidebar {
                    display: none;
                }

                .wa-container:not(.chat-open) .wa-chat-area {
                    display: none;
                }
            }
        </style>
    @endsection

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
        <div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base border border-emerald-200">
                    <i class="fa-brands fa-whatsapp text-lg"></i>
                </div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight m-0">WhatsApp Center</h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $isConfigured ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $isConfigured ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' }}"></span>
                    {{ $isConfigured ? 'Gateway Terhubung' : 'Mode Simulasi / WA Web' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Kelola komunikasi pelanggan, kirim invoice tagihan, pesan pengingat jatuh tempo, dan informasi layanan via WhatsApp.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('settings.index') }}?tab=whatsapp" class="btn-cancel !h-8.5 !px-3 text-xs font-semibold flex items-center gap-1.5" title="Konfigurasi WhatsApp Gateway">
                <i class="fa-solid fa-gear text-slate-500"></i> Pengaturan Gateway
            </a>
            <a href="{{ route('invoices.index') }}" class="btn-cancel !h-8.5 !px-3 text-xs font-semibold flex items-center gap-1.5" title="Buka Daftar Tagihan">
                <i class="fa-solid fa-file-invoice-dollar text-slate-500"></i> Daftar Invoice
            </a>
        </div>
    </div>

    @if (!$isConfigured)
        <div class="p-3 mb-3.5 rounded-xl border border-amber-200 bg-amber-50/70 text-amber-900 text-xs flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-amber-600 text-base shrink-0"></i>
                <div>
                    <strong>Pemberitahuan Gateway WhatsApp:</strong> API Key belum diisi di Pengaturan. Sistem berjalan dalam <em>Mode Simulasi</em>. Anda tetap dapat mengirimkan pesan langsung ke pelanggan menggunakan tombol <strong>Buka di WhatsApp Web (wa.me)</strong> tanpa biaya template!
                </div>
            </div>
            <a href="{{ route('settings.index') }}?tab=whatsapp" class="text-xs font-bold text-amber-700 underline shrink-0 hover:text-amber-900">Isi API Key &rarr;</a>
        </div>
    @endif

    <!-- WhatsApp Dual Pane Workspace -->
    <div class="wa-container" id="waAppContainer">
        <!-- 1. LEFT PANE: Contacts & Invoices Directory -->
        <aside class="wa-sidebar">
            <div class="wa-sidebar-header">
                <div class="relative">
                    <input type="text" id="contactSearchInput" placeholder="Cari nama, nomor WA, username PPPoE..." 
                        class="w-full pl-9 pr-8 py-2 text-xs bg-slate-100 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:outline-none transition-all">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <button type="button" id="clearSearchBtn" class="hidden absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="wa-filter-tabs">
                <button type="button" class="wa-filter-pill active" data-filter="all">Semua Kontak</button>
                <button type="button" class="wa-filter-pill" data-filter="unpaid">
                    <i class="fa-solid fa-clock mr-1 text-rose-500"></i> Ada Tagihan
                </button>
                <button type="button" class="wa-filter-pill" data-filter="active">Pelanggan Aktif</button>
            </div>

            <!-- Contacts list -->
            <div class="wa-contact-list" id="contactListContainer">
                <div class="p-8 text-center text-slate-400 text-xs flex flex-col items-center justify-center">
                    <i class="fa-solid fa-circle-notch fa-spin text-emerald-500 text-xl mb-2"></i>
                    <span>Memuat kontak pelanggan...</span>
                </div>
            </div>
        </aside>

        <!-- 2. RIGHT PANE: Conversation & Quick Actions -->
        <main class="wa-chat-area" id="waChatArea">
            <!-- Empty state when no contact selected -->
            <div class="wa-empty-chat" id="waEmptyState">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl mb-4 border border-emerald-200 shadow-sm">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800 m-0">WhatsApp Center Erlangga Net</h3>
                <p class="text-xs text-slate-500 max-w-sm mt-1">Pilih kontak pelanggan di panel kiri untuk membuka percakapan, mengirim rincian tagihan, atau mengirim pesan template otomatis.</p>
                <div class="flex items-center gap-2 mt-4">
                    <button type="button" class="btn-primary-admin !h-8 !px-3.5 text-xs font-semibold !bg-emerald-600 hover:!bg-emerald-700 flex items-center gap-1.5" onclick="selectFirstUnpaidContact()">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Pilih Pelanggan Menunggak
                    </button>
                </div>
            </div>

            <!-- Active Chat Interface -->
            <div class="flex flex-col h-full hidden" id="waActiveChat">
                <!-- Chat Header -->
                <div class="wa-chat-header">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" class="lg:hidden text-slate-500 hover:text-slate-800 p-1" onclick="backToContacts()" title="Kembali ke Kontak">
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <div class="wa-avatar !w-10 !h-10 !text-sm" id="chatContactAvatar">EN</div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-900 truncate m-0" id="chatContactName">-</h3>
                                <span class="hidden px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200" id="chatUnpaidBadge">Tagihan Belum Bayar</span>
                            </div>
                            <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5 truncate">
                                <span class="font-mono text-emerald-700 font-semibold" id="chatContactPhone">-</span>
                                <span class="text-slate-300">•</span>
                                <span id="chatContactPaket" class="truncate">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Header Quick Actions -->
                    <div class="flex items-center gap-1.5 shrink-0">
                        <!-- Direct WA Web button (100% Free wa.me) -->
                        <a href="#" target="_blank" id="btnOpenWaWeb" class="topbar-icon-btn !w-8.5 !h-8.5 !rounded-lg hover:!border-emerald-300 hover:!bg-emerald-50 text-emerald-600" title="Buka Langsung di WhatsApp Web (wa.me)">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>

                        <!-- Send Invoice Button -->
                        <button type="button" class="btn-primary-admin !h-8 !px-3 text-xs font-semibold !bg-emerald-600 hover:!bg-emerald-700 flex items-center gap-1.5" onclick="openInvoiceModal()" title="Kirim Tagihan Invoice">
                            <i class="fa-solid fa-file-invoice-dollar"></i> <span class="hidden sm:inline">Kirim Tagihan</span>
                        </button>

                        <!-- Quick Template Dropdown -->
                        <div class="relative" id="templateDropdownContainer">
                            <button type="button" class="btn-cancel !h-8 !px-3 text-xs font-semibold flex items-center gap-1.5" onclick="toggleTemplateMenu()" title="Pilih Template Pesan">
                                <i class="fa-solid fa-bolt text-amber-500"></i> <span class="hidden sm:inline">Template</span> <i class="fa-solid fa-chevron-down text-[10px] ml-0.5"></i>
                            </button>
                            <div class="hidden absolute right-0 mt-1 w-64 bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-30" id="templateDropdownMenu">
                                <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                    Pilih Template Pesan
                                </div>
                                @foreach ($templates as $t)
                                    <button type="button" class="w-full text-left px-3 py-2 text-xs hover:bg-slate-50 flex items-start gap-2.5 transition-colors"
                                        onclick="applyTemplate('{{ $t['id'] }}')">
                                        <i class="fa-solid {{ $t['icon'] }} text-emerald-600 mt-0.5 text-xs"></i>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-800 leading-tight">{{ $t['title'] }}</div>
                                            <div class="text-[10.5px] text-slate-400 truncate mt-0.5">{{ $t['description'] }}</div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Customer Details Info Toggle -->
                        <button type="button" class="topbar-icon-btn !w-8.5 !h-8.5 !rounded-lg text-slate-500" onclick="toggleContactDrawer()" title="Lihat Rincian Akun">
                            <i class="fa-solid fa-circle-info"></i>
                        </button>
                    </div>
                </div>

                <!-- Chat Body (Messages History) -->
                <div class="wa-chat-messages" id="chatMessagesContainer">
                    <div class="text-center py-6 text-slate-400 text-xs">
                        <i class="fa-solid fa-circle-notch fa-spin text-emerald-500 text-lg mb-2"></i>
                        <div>Memuat riwayat pesan...</div>
                    </div>
                </div>

                <!-- Chat Composer -->
                <div class="wa-composer">
                    <!-- Quick Variable Chips -->
                    <div class="flex items-center gap-1.5 mb-2 overflow-x-auto pb-1 text-[11px]">
                        <span class="text-slate-400 font-semibold mr-1 shrink-0 text-[10.5px]">Sisipkan variabel:</span>
                        <button type="button" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 font-mono text-[11px] shrink-0" onclick="insertVariable('{nama}')">{nama}</button>
                        <button type="button" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 font-mono text-[11px] shrink-0" onclick="insertVariable('{total}')">{total}</button>
                        <button type="button" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 font-mono text-[11px] shrink-0" onclick="insertVariable('{nomor_invoice}')">{nomor_invoice}</button>
                        <button type="button" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 font-mono text-[11px] shrink-0" onclick="insertVariable('{jatuh_tempo}')">{jatuh_tempo}</button>
                        <button type="button" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 font-mono text-[11px] shrink-0" onclick="insertVariable('{paket}')">{paket}</button>
                    </div>

                    <div class="flex items-end gap-2">
                        <div class="flex-1 relative">
                            <textarea id="composerInput" rows="2" class="wa-composer-input" 
                                placeholder="Ketik pesan WhatsApp ke pelanggan (Shift+Enter untuk baris baru, Enter untuk kirim)..."></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            <!-- Direct send via WA Web -->
                            <button type="button" class="btn-cancel !h-10 !px-3.5 text-xs font-semibold flex items-center gap-1.5 border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100" 
                                onclick="sendViaWaWeb()" title="Buka teks ini langsung di WhatsApp Web">
                                <i class="fa-brands fa-whatsapp text-sm text-emerald-600"></i>
                                <span class="hidden md:inline">WA Web</span>
                            </button>

                            <!-- Send via Backend API -->
                            <button type="button" id="btnSendApi" class="btn-primary-admin !h-10 !px-4 text-xs font-semibold !bg-emerald-600 hover:!bg-emerald-700 flex items-center gap-1.5 shadow-sm" 
                                onclick="sendMessageApi()">
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                                <span>Kirim</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Kirim Tagihan Invoice -->
    <div class="modal-overlay" id="invoiceModal">
        <div class="modal-box max-w-xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm border border-emerald-200 shrink-0">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Kirim Tagihan Invoice WhatsApp</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Pilih faktur pelanggan untuk dikirimkan secara resmi.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeInvoiceModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body p-6 bg-white">
                <div class="form-group mb-4">
                    <label class="form-label text-slate-700 font-bold text-xs mb-1.5 block">Pilih Invoice Pelanggan <span class="text-rose-500">*</span></label>
                    <select id="selectInvoice" class="form-input-custom !h-10 !font-normal w-full bg-white text-slate-800 border-slate-300 text-xs" onchange="onSelectInvoiceChange(this.value)">
                        <option value="">-- Pilih Faktur Pelanggan --</option>
                    </select>
                </div>

                <div class="form-group mb-0">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="form-label text-slate-700 font-bold text-xs block m-0">Pratinjau Pesan WhatsApp</label>
                        <span class="text-[11px] text-slate-400">Dapat disesuaikan sebelum dikirim</span>
                    </div>
                    <textarea id="invoiceMessagePreview" rows="8" class="form-input-custom !font-mono text-xs w-full bg-slate-50 border-slate-300" placeholder="Pilih invoice di atas untuk membuat teks otomatis..."></textarea>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2 rounded-b-2xl">
                <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5" onclick="closeInvoiceModal()">
                    <i class="fa-solid fa-xmark"></i> Batal
                </button>

                <div class="flex items-center gap-2">
                    <!-- Send via WA Web deep link -->
                    <button type="button" class="btn-cancel !h-8.5 !px-3.5 text-xs font-semibold border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 flex items-center gap-1.5" onclick="sendInvoiceViaWaWeb()">
                        <i class="fa-brands fa-whatsapp text-emerald-600"></i> Buka di WA Web
                    </button>
                    <!-- Send via API -->
                    <button type="button" id="btnSendInvoiceApi" class="btn-primary-admin !h-8.5 !px-4 text-xs font-semibold !bg-emerald-600 hover:!bg-emerald-700 flex items-center gap-1.5" onclick="sendInvoiceApi()">
                        <i class="fa-solid fa-paper-plane text-xs"></i> Kirim via API
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Drawer Detail Akun & Pelanggan -->
    <div class="modal-overlay hidden" id="contactDrawer">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-5 py-3.5 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 m-0 flex items-center gap-2">
                    <i class="fa-solid fa-id-card text-emerald-600"></i> Informasi Lengkap Pelanggan
                </h3>
                <button type="button" class="modal-close" onclick="toggleContactDrawer()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-5 bg-white text-xs space-y-3" id="contactDrawerContent">
                <!-- Populated dynamically via JS -->
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-5 py-3 flex items-center justify-end">
                <button type="button" class="btn-cancel !h-8 !px-4 text-xs font-semibold" onclick="toggleContactDrawer()">Tutup</button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script>
            // State data
            const TEMPLATES = @json($templates);
            let currentContacts = [];
            let activeContact = null;
            let activePhone = '{{ $initialPhone }}';
            let initialInvoiceId = '{{ $initialInvoiceId }}';
            let currentFilter = 'all';
            let pollInterval = null;

            document.addEventListener('DOMContentLoaded', function() {
                loadContacts();

                // Search binding with debounce
                let searchTimeout = null;
                const searchInput = document.getElementById('contactSearchInput');
                searchInput.addEventListener('input', function(e) {
                    const val = e.target.value.trim();
                    document.getElementById('clearSearchBtn').classList.toggle('hidden', val.length === 0);
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        loadContacts(val);
                    }, 350);
                });

                document.getElementById('clearSearchBtn').addEventListener('click', function() {
                    searchInput.value = '';
                    this.classList.add('hidden');
                    loadContacts('');
                });

                // Filter pills
                document.querySelectorAll('.wa-filter-pill').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.wa-filter-pill').forEach(p => p.classList.remove('active'));
                        this.classList.add('active');
                        currentFilter = this.dataset.filter;
                        loadContacts(searchInput.value.trim());
                    });
                });

                // Composer enter to send
                const composer = document.getElementById('composerInput');
                composer.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendMessageApi();
                    }
                });

                // Close template dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    const container = document.getElementById('templateDropdownContainer');
                    const menu = document.getElementById('templateDropdownMenu');
                    if (container && !container.contains(e.target)) {
                        menu.classList.add('hidden');
                    }
                });

                // Start active phone conversation if provided
                if (activePhone) {
                    selectContactByPhone(activePhone);
                }
            });

            // 1. Load contacts via AJAX
            function loadContacts(search = '') {
                const container = document.getElementById('contactListContainer');
                container.innerHTML = `
                    <div class="p-8 text-center text-slate-400 text-xs flex flex-col items-center justify-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-emerald-500 text-xl mb-2"></i>
                        <span>Memuat kontak...</span>
                    </div>`;

                const url = `{{ route('whatsapp.contacts') }}?search=${encodeURIComponent(search)}&filter=${currentFilter}`;
                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success || !data.contacts.length) {
                            container.innerHTML = `
                                <div class="p-8 text-center text-slate-400 text-xs">
                                    <i class="fa-solid fa-address-book text-slate-300 text-2xl mb-2 block"></i>
                                    Tidak ada kontak yang cocok dengan filter.
                                </div>`;
                            currentContacts = [];
                            return;
                        }

                        currentContacts = data.contacts;
                        renderContacts(data.contacts);
                    })
                    .catch(err => {
                        container.innerHTML = `<div class="p-4 text-center text-rose-500 text-xs">Gagal memuat kontak. Silakan refresh.</div>`;
                    });
            }

            function renderContacts(contacts) {
                const container = document.getElementById('contactListContainer');
                container.innerHTML = '';

                contacts.forEach(c => {
                    const initials = getInitials(c.name);
                    const isActive = activeContact && activeContact.normalized_phone === c.normalized_phone;

                    const item = document.createElement('div');
                    item.className = `wa-contact-item ${isActive ? 'active' : ''}`;
                    item.dataset.phone = c.normalized_phone;
                    item.onclick = () => selectContact(c);

                    let unpaidBadgeHtml = '';
                    if (c.unpaid_count > 0) {
                        unpaidBadgeHtml = `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">Rp ${Number(c.unpaid_amount).toLocaleString('id-ID')}</span>`;
                    }

                    const lastMsgText = c.last_message ? escapeHtml(c.last_message.message) : (c.paket || 'Pelanggan');
                    const lastMsgTime = c.last_message ? c.last_message.created_at : '';

                    item.innerHTML = `
                        <div class="wa-avatar">${initials}</div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <h4 class="text-xs font-bold text-slate-900 truncate m-0">${escapeHtml(c.name)}</h4>
                                <span class="text-[10px] text-slate-400 shrink-0 font-medium">${lastMsgTime}</span>
                            </div>
                            <div class="flex items-center justify-between gap-1 mt-0.5">
                                <div class="text-[11px] text-slate-500 truncate flex-1">${lastMsgText}</div>
                                ${unpaidBadgeHtml}
                            </div>
                        </div>
                    `;
                    container.appendChild(item);
                });
            }

            // 2. Select contact & load conversation
            function selectContact(contact) {
                activeContact = contact;
                activePhone = contact.normalized_phone;

                // Update UI layout
                document.getElementById('waAppContainer').classList.add('chat-open');
                document.getElementById('waEmptyState').classList.add('hidden');
                document.getElementById('waActiveChat').classList.remove('hidden');

                // Highlight active item
                document.querySelectorAll('.wa-contact-item').forEach(el => {
                    el.classList.toggle('active', el.dataset.phone === activePhone);
                });

                // Fill header
                document.getElementById('chatContactName').textContent = contact.name;
                document.getElementById('chatContactPhone').textContent = contact.phone || contact.normalized_phone;
                document.getElementById('chatContactPaket').textContent = `${contact.paket || 'Paket Internet'} (${contact.username || '-'})`;
                document.getElementById('chatContactAvatar').textContent = getInitials(contact.name);

                // Unpaid badge in header
                const unpaidBadge = document.getElementById('chatUnpaidBadge');
                if (contact.unpaid_count > 0) {
                    unpaidBadge.classList.remove('hidden');
                    unpaidBadge.textContent = `${contact.unpaid_count} Tagihan (Rp ${Number(contact.unpaid_amount).toLocaleString('id-ID')})`;
                } else {
                    unpaidBadge.classList.add('hidden');
                }

                // Setup deep link wa.me
                updateWaWebLink();

                // Fetch conversation
                loadConversation(activePhone);

                // Auto polling for new messages every 8 seconds
                clearInterval(pollInterval);
                pollInterval = setInterval(() => {
                    if (activePhone) {
                        loadConversation(activePhone, true);
                    }
                }, 8000);

                // If launched with an initial invoice, trigger the modal automatically
                if (initialInvoiceId) {
                    setTimeout(() => {
                        openInvoiceModal(initialInvoiceId);
                        initialInvoiceId = null;
                    }, 400);
                }
            }

            function selectContactByPhone(phone) {
                const norm = normalizePhone(phone);
                fetch(`{{ url('whatsapp/conversation') }}/${norm}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.contact) {
                            selectContact(data.contact);
                        }
                    });
            }

            function selectFirstUnpaidContact() {
                const btn = document.querySelector('.wa-filter-pill[data-filter="unpaid"]');
                if (btn) btn.click();
            }

            function backToContacts() {
                document.getElementById('waAppContainer').classList.remove('chat-open');
            }

            // 3. Load messages conversation
            function loadConversation(phone, isPolling = false) {
                const container = document.getElementById('chatMessagesContainer');
                if (!isPolling) {
                    container.innerHTML = `
                        <div class="text-center py-8 text-slate-400 text-xs">
                            <i class="fa-solid fa-circle-notch fa-spin text-emerald-500 text-lg mb-2"></i>
                            <div>Memuat percakapan...</div>
                        </div>`;
                }

                fetch(`{{ url('whatsapp/conversation') }}/${phone}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return;

                        if (data.contact) {
                            activeContact = data.contact;
                        }

                        renderMessages(data.messages);
                    });
            }

            function renderMessages(messages) {
                const container = document.getElementById('chatMessagesContainer');

                if (!messages.length) {
                    container.innerHTML = `
                        <div class="text-center py-10 text-slate-500 text-xs max-w-sm mx-auto">
                            <div class="w-12 h-12 rounded-xl bg-white text-emerald-600 flex items-center justify-center text-xl mx-auto mb-2.5 shadow-xs border border-slate-200">
                                <i class="fa-regular fa-comments"></i>
                            </div>
                            <div class="font-bold text-slate-700">Belum ada riwayat pesan tercatat</div>
                            <p class="text-[11px] text-slate-400 mt-1">Mulai obrolan baru dengan mengetik pesan di bawah, atau kirimkan tagihan invoice pelanggan ini.</p>
                            <div class="flex items-center justify-center gap-2 mt-3">
                                <button type="button" class="btn-primary-admin !h-7 !px-3 text-xs !bg-emerald-600 font-semibold" onclick="openInvoiceModal()">
                                    <i class="fa-solid fa-file-invoice-dollar mr-1"></i> Kirim Tagihan
                                </button>
                                <button type="button" class="btn-cancel !h-7 !px-3 text-xs font-semibold" onclick="applyTemplate('welcome_new')">
                                    <i class="fa-solid fa-handshake mr-1"></i> Sapa Pelanggan
                                </button>
                            </div>
                        </div>`;
                    return;
                }

                const shouldScroll = (container.scrollTop + container.clientHeight >= container.scrollHeight - 60);

                container.innerHTML = '';
                messages.forEach(m => {
                    const isOut = m.direction === 'outbound';
                    const isInvoice = m.type === 'invoice';
                    const wrapper = document.createElement('div');
                    wrapper.className = `wa-bubble-wrapper ${isOut ? 'outbound' : 'inbound'}`;

                    let statusIcon = '';
                    if (isOut) {
                        if (m.status === 'simulated') {
                            statusIcon = '<span class="text-[9px] font-bold px-1 rounded bg-amber-100 text-amber-700" title="Tercatat dalam mode simulasi">SIM</span>';
                        } else if (m.status === 'sent') {
                            statusIcon = '<i class="fa-solid fa-check text-slate-400" title="Terkirim"></i>';
                        } else if (m.status === 'delivered') {
                            statusIcon = '<i class="fa-solid fa-check-double text-slate-400" title="Tersampaikan"></i>';
                        } else if (m.status === 'read') {
                            statusIcon = '<i class="fa-solid fa-check-double text-sky-500" title="Dibaca"></i>';
                        } else if (m.status === 'failed') {
                            statusIcon = '<i class="fa-solid fa-triangle-exclamation text-rose-500" title="Gagal"></i>';
                        }
                    }

                    wrapper.innerHTML = `
                        <div class="wa-bubble ${isOut ? 'outbound' : 'inbound'} ${isInvoice ? 'invoice-bubble' : ''}">
                            <div class="wa-bubble-text">${formatMessageText(m.message)}</div>
                            <div class="wa-bubble-meta">
                                <span>${m.time}</span>
                                ${statusIcon}
                            </div>
                        </div>
                    `;
                    container.appendChild(wrapper);
                });

                if (shouldScroll) {
                    container.scrollTop = container.scrollHeight;
                }
            }

            // 4. Send message via Backend API
            function sendMessageApi() {
                if (!activeContact) return;
                const input = document.getElementById('composerInput');
                const text = input.value.trim();
                if (!text) return;

                const btn = document.getElementById('btnSendApi');
                const origHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin text-xs"></i>`;

                fetch(`{{ route('whatsapp.send') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        phone: activeContact.normalized_phone,
                        message: text,
                        pppoe_id: activeContact.pppoe_id,
                        customer_id: activeContact.customer_id,
                    })
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;

                    if (data.success) {
                        input.value = '';
                        loadConversation(activeContact.normalized_phone);
                    } else {
                        alert(data.message || 'Gagal mengirim pesan.');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    alert('Terjadi kesalahan jaringan.');
                });
            }

            // 5. Send directly via WhatsApp Web (wa.me)
            function sendViaWaWeb() {
                if (!activeContact) return;
                const input = document.getElementById('composerInput');
                const text = input.value.trim();
                const phone = activeContact.normalized_phone;

                const url = `https://web.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(text)}`;
                window.open(url, '_blank');
            }

            function updateWaWebLink() {
                if (!activeContact) return;
                const btn = document.getElementById('btnOpenWaWeb');
                btn.href = `https://wa.me/${activeContact.normalized_phone}`;
            }

            // 6. Template Management
            function toggleTemplateMenu() {
                document.getElementById('templateDropdownMenu').classList.toggle('hidden');
            }

            function applyTemplate(templateId) {
                const tpl = TEMPLATES.find(t => t.id === templateId);
                if (!tpl || !activeContact) return;

                let body = tpl.body;
                body = body.replace(/\{nama\}/g, activeContact.name || 'Pelanggan');
                body = body.replace(/\{username\}/g, activeContact.username || '-');
                body = body.replace(/\{paket\}/g, activeContact.paket || 'Internet');
                body = body.replace(/\{total\}/g, activeContact.unpaid_total ? Number(activeContact.unpaid_total).toLocaleString('id-ID') : '0');
                body = body.replace(/\{periode\}/g, new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(new Date()));
                body = body.replace(/\{jatuh_tempo\}/g, activeContact.invoices && activeContact.invoices.length ? activeContact.invoices[0].due_date : '-');
                body = body.replace(/\{nomor_invoice\}/g, activeContact.invoices && activeContact.invoices.length ? activeContact.invoices[0].invoice_number : 'INV-...');
                body = body.replace(/\{waktu_pemeliharaan\}/g, 'Malam ini pukul 23:00 - 03:00 WIB');
                body = body.replace(/\{estimasi_durasi\}/g, '± 2-4 Jam');

                const composer = document.getElementById('composerInput');
                composer.value = body;
                composer.focus();
                document.getElementById('templateDropdownMenu').classList.add('hidden');
            }

            function insertVariable(varName) {
                const composer = document.getElementById('composerInput');
                const start = composer.selectionStart;
                const end = composer.selectionEnd;
                const text = composer.value;
                composer.value = text.substring(0, start) + varName + text.substring(end);
                composer.focus();
                composer.selectionStart = composer.selectionEnd = start + varName.length;
            }

            // 7. Invoice Modal
            function openInvoiceModal(preselectedId = null) {
                if (!activeContact) return;

                const select = document.getElementById('selectInvoice');
                select.innerHTML = '<option value="">-- Pilih Faktur Pelanggan --</option>';

                if (activeContact.invoices && activeContact.invoices.length) {
                    activeContact.invoices.forEach(inv => {
                        const opt = document.createElement('option');
                        opt.value = inv.id;
                        opt.textContent = `${inv.invoice_number} - Rp ${Number(inv.total).toLocaleString('id-ID')} (${inv.status.toUpperCase()}) - JT: ${inv.due_date}`;
                        if (preselectedId && inv.id == preselectedId) {
                            opt.selected = true;
                        }
                        select.appendChild(opt);
                    });

                    if (preselectedId) {
                        onSelectInvoiceChange(preselectedId);
                    } else if (activeContact.invoices.length === 1) {
                        select.selectedIndex = 1;
                        onSelectInvoiceChange(activeContact.invoices[0].id);
                    }
                } else {
                    const opt = document.createElement('option');
                    opt.disabled = true;
                    opt.textContent = 'Tidak ada invoice tercatat untuk pelanggan ini.';
                    select.appendChild(opt);
                }

                document.getElementById('invoiceModal').classList.add('active');
            }

            function closeInvoiceModal() {
                document.getElementById('invoiceModal').classList.remove('active');
            }

            function onSelectInvoiceChange(invId) {
                if (!invId) {
                    document.getElementById('invoiceMessagePreview').value = '';
                    return;
                }

                fetch(`{{ url('whatsapp/preview-invoice') }}/${invId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('invoiceMessagePreview').value = data.text;
                        }
                    });
            }

            function sendInvoiceApi() {
                const invId = document.getElementById('selectInvoice').value;
                if (!invId) {
                    alert('Silakan pilih invoice terlebih dahulu.');
                    return;
                }

                const customMsg = document.getElementById('invoiceMessagePreview').value;
                const btn = document.getElementById('btnSendInvoiceApi');
                const origHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin text-xs"></i> Mengirim...`;

                fetch(`{{ route('whatsapp.send_invoice') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        invoice_id: invId,
                        phone: activeContact.normalized_phone,
                        custom_message: customMsg,
                    })
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    if (data.success) {
                        closeInvoiceModal();
                        loadConversation(activeContact.normalized_phone);
                    } else {
                        alert(data.message || 'Gagal mengirim tagihan.');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    alert('Terjadi kesalahan jaringan.');
                });
            }

            function sendInvoiceViaWaWeb() {
                const text = document.getElementById('invoiceMessagePreview').value;
                if (!text || !activeContact) {
                    alert('Pilih invoice terlebih dahulu.');
                    return;
                }
                const url = `https://web.whatsapp.com/send?phone=${activeContact.normalized_phone}&text=${encodeURIComponent(text)}`;
                window.open(url, '_blank');
                closeInvoiceModal();
            }

            // 8. Contact Details Drawer
            function toggleContactDrawer() {
                const drawer = document.getElementById('contactDrawer');
                drawer.classList.toggle('hidden');
                if (!drawer.classList.contains('hidden') && activeContact) {
                    const content = document.getElementById('contactDrawerContent');
                    content.innerHTML = `
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Identitas Pelanggan</div>
                            <div class="text-sm font-bold text-slate-900 mt-1">${escapeHtml(activeContact.name)}</div>
                            <div class="text-slate-600 font-mono mt-0.5">${activeContact.phone}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                                <span class="text-[10px] text-slate-400 font-bold uppercase block">Akun PPPoE</span>
                                <span class="font-bold text-slate-800 text-xs">${activeContact.username || '-'}</span>
                            </div>
                            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                                <span class="text-[10px] text-slate-400 font-bold uppercase block">Paket Layanan</span>
                                <span class="font-bold text-emerald-700 text-xs">${activeContact.paket || '-'}</span>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Pemasangan</div>
                            <div class="text-slate-700 mt-1 leading-relaxed">${escapeHtml(activeContact.address || '-')}</div>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Tagihan Aktif</span>
                                <span class="font-bold text-rose-600">${activeContact.unpaid_count} Belum Bayar</span>
                            </div>
                            <div class="text-base font-black text-rose-700">Rp ${Number(activeContact.unpaid_total || 0).toLocaleString('id-ID')}</div>
                            ${activeContact.pppoe_id ? `<a href="{{ url('invoices') }}/${activeContact.pppoe_id}" class="mt-2 text-xs text-sky-600 font-bold hover:underline inline-flex items-center gap-1">Buka Riwayat Invoice &rarr;</a>` : ''}
                        </div>
                    `;
                }
            }

            // Utilities
            function getInitials(name) {
                if (!name) return 'EN';
                const parts = name.trim().split(' ');
                if (parts.length >= 2) {
                    return (parts[0][0] + parts[1][0]).toUpperCase();
                }
                return name.substring(0, 2).toUpperCase();
            }

            function normalizePhone(p) {
                if (!p) return '';
                let d = p.replace(/[^0-9]/g, '');
                if (d.startsWith('08')) d = '62' + d.substring(1);
                else if (d.startsWith('8')) d = '62' + d;
                return d;
            }

            function escapeHtml(text) {
                if (!text) return '';
                return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function formatMessageText(text) {
                if (!text) return '';
                let escaped = escapeHtml(text);
                // WhatsApp bold: *text* -> <b>text</b>
                escaped = escaped.replace(/\*(.*?)\*/g, '<strong>$1</strong>');
                // WhatsApp italic: _text_ -> <em>text</em>
                escaped = escaped.replace(/_(.*?)_/g, '<em>$1</em>');
                // WhatsApp inline code: `text` -> <code>text</code>
                escaped = escaped.replace(/`(.*?)`/g, '<code class="bg-black/5 px-1 rounded text-xs">$1</code>');
                // Auto linkify URLs
                escaped = escaped.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" class="text-sky-600 underline">$1</a>');
                return escaped;
            }
        </script>
    @endsection
</x-layouts.app>
