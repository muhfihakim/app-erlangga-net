<x-layouts.app>
    @section('Css')
        <style>
            .wa-workspace {
                display: flex;
                height: calc(100vh - 135px);
                min-height: 520px;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                overflow: hidden;
            }

            /* Left: Directory / Contact List */
            .wa-sidebar {
                width: 330px;
                min-width: 290px;
                max-width: 360px;
                border-right: 1px solid #e2e8f0;
                display: flex;
                flex-direction: column;
                background: #f8fafc;
            }

            .wa-sidebar-search {
                padding: 10px 12px;
                background: #ffffff;
                border-bottom: 1px solid #e2e8f0;
            }

            .wa-filter-row {
                display: flex;
                gap: 4px;
                padding: 6px 10px;
                background: #f1f5f9;
                border-bottom: 1px solid #e2e8f0;
            }

            .wa-filter-btn {
                flex: 1;
                padding: 4px 6px;
                font-size: 11px;
                font-weight: 600;
                border-radius: 4px;
                background: transparent;
                color: #64748b;
                border: 1px solid transparent;
                cursor: pointer;
                text-align: center;
                transition: all 0.15s;
            }

            .wa-filter-btn:hover {
                color: #0f172a;
            }

            .wa-filter-btn.active {
                background: #ffffff;
                border-color: #cbd5e1;
                color: #0f172a;
                font-weight: 700;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            }

            .wa-contact-list {
                flex: 1;
                overflow-y: auto;
            }

            .wa-contact-row {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 9px 12px;
                cursor: pointer;
                border-bottom: 1px solid #f1f5f9;
                border-left: 3px solid transparent;
                background: #ffffff;
                transition: background-color 0.1s;
            }

            .wa-contact-row:hover {
                background: #f8fafc;
            }

            .wa-contact-row.active {
                background: #f1f5f9;
                border-left-color: #059669;
            }

            .wa-contact-avatar {
                width: 34px;
                height: 34px;
                border-radius: 6px;
                background: #e2e8f0;
                color: #334155;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 11.5px;
                flex-shrink: 0;
                border: 1px solid #cbd5e1;
            }

            /* Right: Chat Panel */
            .wa-chat-panel {
                flex: 1;
                display: flex;
                flex-direction: column;
                background: #ffffff;
            }

            .wa-chat-header {
                height: 52px;
                padding: 0 16px;
                background: #ffffff;
                border-bottom: 1px solid #e2e8f0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-shrink: 0;
            }

            .wa-chat-stream {
                flex: 1;
                overflow-y: auto;
                padding: 14px 18px;
                display: flex;
                flex-direction: column;
                gap: 8px;
                background: #f8fafc;
            }

            /* Message Bubbles */
            .wa-msg-row {
                display: flex;
                width: 100%;
            }

            .wa-msg-row.outbound {
                justify-content: flex-end;
            }

            .wa-msg-row.inbound {
                justify-content: flex-start;
            }

            .wa-msg-bubble {
                max-width: 68%;
                padding: 8px 12px;
                border-radius: 6px;
                font-size: 12.5px;
                line-height: 1.45;
                word-break: break-word;
                white-space: pre-wrap;
            }

            .wa-msg-bubble.outbound {
                background: #ecfdf5;
                border: 1px solid #a7f3d0;
                color: #064e3b;
            }

            .wa-msg-bubble.inbound {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                color: #0f172a;
            }

            .wa-msg-bubble.invoice-type {
                border-left: 3px solid #059669;
            }

            .wa-msg-meta {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 4px;
                font-size: 10px;
                color: #64748b;
                margin-top: 3px;
            }

            /* Composer */
            .wa-chat-footer {
                padding: 10px 14px;
                background: #ffffff;
                border-top: 1px solid #e2e8f0;
                flex-shrink: 0;
            }

            .wa-textarea {
                width: 100%;
                border: 1px solid #cbd5e1;
                border-radius: 6px;
                padding: 8px 10px;
                font-size: 12.5px;
                resize: none;
                max-height: 100px;
                min-height: 40px;
                outline: none;
                font-family: inherit;
            }

            .wa-textarea:focus {
                border-color: #059669;
            }

            /* Empty state */
            .wa-placeholder {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                background: #f8fafc;
                color: #64748b;
                text-align: center;
                padding: 24px;
            }

            @media (max-width: 860px) {
                .wa-workspace {
                    height: calc(100vh - 145px);
                }

                .wa-sidebar {
                    width: 100%;
                    max-width: 100%;
                }

                .wa-workspace.chat-selected .wa-sidebar {
                    display: none;
                }

                .wa-workspace:not(.chat-selected) .wa-chat-panel {
                    display: none;
                }
            }
        </style>
    @endsection

    <!-- Page Header (Standard Compact) -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:0.85rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:20px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                WhatsApp Center
            </h1>
            <p style="font-size:12px;color:#64748b;margin:2px 0 0;">
                Komunikasi dengan pelanggan, pengiriman faktur tagihan, dan notifikasi layanan pelanggan.
            </p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('settings.index') }}?tab=whatsapp" class="btn-cancel !h-8 !px-3 text-xs font-semibold flex items-center gap-1.5" title="Buka Pengaturan WhatsApp">
                <i class="fa-solid fa-gear text-slate-500"></i> Pengaturan
            </a>
            <a href="{{ route('invoices.index') }}" class="btn-cancel !h-8 !px-3 text-xs font-semibold flex items-center gap-1.5" title="Daftar Invoice Tagihan">
                <i class="fa-solid fa-file-invoice-dollar text-slate-500"></i> Tagihan
            </a>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="wa-workspace" id="waWorkspace">
        <!-- 1. LEFT DIRECTORY -->
        <aside class="wa-sidebar">
            <div class="wa-sidebar-search">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Cari nama, nomor WA, username..."
                        class="form-input-custom !h-8 !pl-8 !pr-7 !text-xs w-full bg-slate-50">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                    <button type="button" id="searchClearBtn" class="hidden absolute right-2 top-2 text-slate-400 hover:text-slate-600 text-xs">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="wa-filter-row">
                <button type="button" class="wa-filter-btn active" data-filter="all">Semua</button>
                <button type="button" class="wa-filter-btn" data-filter="unpaid">Belum Bayar</button>
                <button type="button" class="wa-filter-btn" data-filter="active">Aktif</button>
            </div>

            <!-- Contact List Items -->
            <div class="wa-contact-list" id="contactListContainer">
                <div class="p-6 text-center text-slate-400 text-xs">
                    <i class="fa-solid fa-circle-notch fa-spin text-slate-400 mb-1.5"></i>
                    <div>Memuat kontak...</div>
                </div>
            </div>
        </aside>

        <!-- 2. RIGHT CHAT PANEL -->
        <main class="wa-chat-panel">
            <!-- Empty state -->
            <div class="wa-placeholder" id="emptyPlaceholder">
                <i class="fa-regular fa-comments text-slate-300 text-3xl mb-2"></i>
                <div class="font-bold text-slate-700 text-xs">Pilih Kontak Pelanggan</div>
                <p class="text-[11px] text-slate-400 max-w-xs mt-0.5">Pilih salah satu kontak di sisi kiri untuk membuka riwayat pesan atau mengirim rincian tagihan.</p>
            </div>

            <!-- Active Conversation -->
            <div class="flex flex-col h-full hidden" id="chatContainer">
                <!-- Chat Topbar -->
                <div class="wa-chat-header">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <button type="button" class="lg:hidden text-slate-500 hover:text-slate-800 p-1" onclick="backToSidebar()" title="Kembali">
                            <i class="fa-solid fa-arrow-left text-xs"></i>
                        </button>
                        <div class="wa-contact-avatar !w-8 !h-8 !text-[11px]" id="headerAvatar">--</div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-slate-900 text-xs truncate" id="headerName">-</span>
                                <span class="hidden px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200" id="headerUnpaidTag">Unpaid</span>
                            </div>
                            <div class="text-[11px] text-slate-500 font-mono flex items-center gap-1.5 mt-0.5">
                                <span id="headerPhone">-</span>
                                <span class="text-slate-300">•</span>
                                <span id="headerPaket" class="font-sans text-slate-600 truncate">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-1.5 shrink-0">
                        <!-- Direct WhatsApp Web link -->
                        <a href="#" target="_blank" id="btnWaWebDeepLink" class="btn-cancel !h-7.5 !px-2.5 text-xs font-semibold flex items-center gap-1" title="Buka langsung di WhatsApp Web">
                            <i class="fa-brands fa-whatsapp text-emerald-600"></i> <span class="hidden sm:inline">WA Web</span>
                        </a>

                        <!-- Send Invoice Button -->
                        <button type="button" class="btn-primary-admin !h-7.5 !px-3 text-xs font-semibold !bg-emerald-600 hover:!bg-emerald-700 flex items-center gap-1.5" onclick="openInvoiceModal()">
                            <i class="fa-solid fa-file-invoice-dollar"></i> <span>Kirim Tagihan</span>
                        </button>

                        <!-- Quick Template Dropdown -->
                        <div class="relative" id="templateMenuWrap">
                            <button type="button" class="btn-cancel !h-7.5 !px-2.5 text-xs font-semibold flex items-center gap-1" onclick="toggleTemplateMenu()">
                                <i class="fa-solid fa-list-check text-slate-500"></i> <span class="hidden sm:inline">Template</span>
                            </button>
                            <div class="hidden absolute right-0 mt-1 w-60 bg-white border border-slate-200 rounded-md shadow-lg py-1 z-30" id="templateDropdown">
                                <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                    Pilih Template
                                </div>
                                @foreach ($templates as $t)
                                    <button type="button" class="w-full text-left px-3 py-1.5 text-xs hover:bg-slate-50 flex items-center gap-2"
                                        onclick="applyTemplate('{{ $t['id'] }}')">
                                        <i class="fa-solid {{ $t['icon'] }} text-slate-500 text-xs w-3 text-center"></i>
                                        <span class="font-semibold text-slate-700 truncate">{{ $t['title'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Customer Info Drawer -->
                        <button type="button" class="topbar-icon-btn !w-7.5 !h-7.5 !rounded" onclick="toggleCustomerInfo()" title="Rincian Pelanggan">
                            <i class="fa-solid fa-circle-info text-slate-500 text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Chat Messages Stream -->
                <div class="wa-chat-stream" id="chatStream">
                    <div class="text-center py-6 text-slate-400 text-xs">
                        <i class="fa-solid fa-circle-notch fa-spin text-slate-400 mb-1"></i>
                        <div>Memuat pesan...</div>
                    </div>
                </div>

                <!-- Chat Footer Composer -->
                <div class="wa-chat-footer">
                    <!-- Variable Shortcuts -->
                    <div class="flex items-center gap-1 mb-1.5 overflow-x-auto text-[10.5px]">
                        <span class="text-slate-400 text-[10px] mr-1 shrink-0">Variabel:</span>
                        <button type="button" class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 font-mono text-[10.5px] border border-slate-200 shrink-0" onclick="insertTag('{nama}')">{nama}</button>
                        <button type="button" class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 font-mono text-[10.5px] border border-slate-200 shrink-0" onclick="insertTag('{total}')">{total}</button>
                        <button type="button" class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 font-mono text-[10.5px] border border-slate-200 shrink-0" onclick="insertTag('{nomor_invoice}')">{nomor_invoice}</button>
                        <button type="button" class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 font-mono text-[10.5px] border border-slate-200 shrink-0" onclick="insertTag('{jatuh_tempo}')">{jatuh_tempo}</button>
                    </div>

                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <textarea id="composerInput" rows="2" class="wa-textarea" placeholder="Tulis pesan (Shift+Enter untuk baris baru, Enter untuk kirim)..."></textarea>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" class="btn-cancel !h-8.5 !px-3 text-xs font-semibold flex items-center gap-1 border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100" onclick="sendViaWaWeb()">
                                <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                <span class="hidden md:inline">WA Web</span>
                            </button>

                            <button type="button" id="btnSendApi" class="btn-primary-admin !h-8.5 !px-3.5 text-xs font-semibold !bg-emerald-600 hover:!bg-emerald-700 flex items-center gap-1" onclick="sendMessageApi()">
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
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-xl rounded-xl">
            <div class="modal-header border-b border-slate-200 px-5 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar text-slate-600 text-sm"></i>
                    <h3 class="text-sm font-bold text-slate-900 m-0">Kirim Tagihan Invoice</h3>
                </div>
                <button type="button" class="modal-close" onclick="closeInvoiceModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body p-5 bg-white text-xs">
                <div class="form-group mb-3">
                    <label class="form-label text-slate-700 font-bold text-xs mb-1 block">Pilih Faktur Tagihan</label>
                    <select id="selectInvoice" class="form-input-custom !h-9 text-xs w-full" onchange="onSelectInvoiceChange(this.value)">
                        <option value="">-- Pilih Faktur Pelanggan --</option>
                    </select>
                </div>

                <div class="form-group mb-0">
                    <label class="form-label text-slate-700 font-bold text-xs mb-1 block">Isi Pesan WhatsApp</label>
                    <textarea id="invoiceMessagePreview" rows="8" class="form-input-custom !font-mono text-xs w-full bg-slate-50" placeholder="Pilih faktur untuk menyusun teks..."></textarea>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-5 py-2.5 flex items-center justify-between rounded-b-xl">
                <button type="button" class="btn-cancel !h-8 !px-3 text-xs font-semibold" onclick="closeInvoiceModal()">Batal</button>
                <div class="flex items-center gap-2">
                    <button type="button" class="btn-cancel !h-8 !px-3 text-xs font-semibold border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 flex items-center gap-1" onclick="sendInvoiceViaWaWeb()">
                        <i class="fa-brands fa-whatsapp text-emerald-600"></i> Buka di WA Web
                    </button>
                    <button type="button" id="btnSendInvoiceApi" class="btn-primary-admin !h-8 !px-3.5 text-xs font-semibold !bg-emerald-600 hover:!bg-emerald-700 flex items-center gap-1" onclick="sendInvoiceApi()">
                        <i class="fa-solid fa-paper-plane text-xs"></i> Kirim
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Rincian Akun Pelanggan -->
    <div class="modal-overlay hidden" id="customerInfoModal">
        <div class="modal-box max-w-sm bg-white border border-slate-300 shadow-xl rounded-xl">
            <div class="modal-header border-b border-slate-200 px-4 py-3 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-900 m-0">Rincian Pelanggan</h3>
                <button type="button" class="modal-close" onclick="toggleCustomerInfo()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-4 bg-white text-xs space-y-2.5" id="customerInfoBody">
                <!-- Dynamically rendered -->
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-4 py-2 flex items-center justify-end rounded-b-xl">
                <button type="button" class="btn-cancel !h-7.5 !px-3 text-xs font-semibold" onclick="toggleCustomerInfo()">Tutup</button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script>
            const TEMPLATES = @json($templates);
            let currentContacts = [];
            let activeContact = null;
            let activePhone = '{{ $initialPhone }}';
            let initialInvoiceId = '{{ $initialInvoiceId }}';
            let currentFilter = 'all';
            let pollTimer = null;

            document.addEventListener('DOMContentLoaded', function() {
                loadContacts();

                // Search binding
                let searchDebounce = null;
                const searchEl = document.getElementById('searchInput');
                searchEl.addEventListener('input', function(e) {
                    const q = e.target.value.trim();
                    document.getElementById('searchClearBtn').classList.toggle('hidden', q.length === 0);
                    clearTimeout(searchDebounce);
                    searchDebounce = setTimeout(() => loadContacts(q), 300);
                });

                document.getElementById('searchClearBtn').addEventListener('click', function() {
                    searchEl.value = '';
                    this.classList.add('hidden');
                    loadContacts('');
                });

                // Filter buttons
                document.querySelectorAll('.wa-filter-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.wa-filter-btn').forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        currentFilter = this.dataset.filter;
                        loadContacts(searchEl.value.trim());
                    });
                });

                // Enter to send in composer
                const composer = document.getElementById('composerInput');
                composer.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendMessageApi();
                    }
                });

                // Close template dropdown when clicking elsewhere
                document.addEventListener('click', function(e) {
                    const wrap = document.getElementById('templateMenuWrap');
                    const dropdown = document.getElementById('templateDropdown');
                    if (wrap && !wrap.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });

                if (activePhone) {
                    selectContactByPhone(activePhone);
                }
            });

            function loadContacts(search = '') {
                const container = document.getElementById('contactListContainer');
                container.innerHTML = `
                    <div class="p-6 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-circle-notch fa-spin text-slate-400 mb-1"></i>
                        <div>Memuat kontak...</div>
                    </div>`;

                const url = `{{ route('whatsapp.contacts') }}?search=${encodeURIComponent(search)}&filter=${currentFilter}`;
                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success || !data.contacts.length) {
                            container.innerHTML = `<div class="p-6 text-center text-slate-400 text-xs">Tidak ada kontak ditemukan.</div>`;
                            currentContacts = [];
                            return;
                        }

                        currentContacts = data.contacts;
                        renderContactList(data.contacts);
                    })
                    .catch(() => {
                        container.innerHTML = `<div class="p-4 text-center text-rose-500 text-xs">Gagal memuat kontak.</div>`;
                    });
            }

            function renderContactList(contacts) {
                const container = document.getElementById('contactListContainer');
                container.innerHTML = '';

                contacts.forEach(c => {
                    const isActive = activeContact && activeContact.normalized_phone === c.normalized_phone;
                    const row = document.createElement('div');
                    row.className = `wa-contact-row ${isActive ? 'active' : ''}`;
                    row.dataset.phone = c.normalized_phone;
                    row.onclick = () => selectContact(c);

                    let unpaidTag = '';
                    if (c.unpaid_count > 0) {
                        unpaidTag = `<span class="text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-1 rounded">Rp ${Number(c.unpaid_amount).toLocaleString('id-ID')}</span>`;
                    }

                    const subInfo = c.last_message ? escapeHtml(c.last_message.message) : (c.username || c.paket || '-');

                    row.innerHTML = `
                        <div class="wa-contact-avatar">${getInitials(c.name)}</div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-xs font-bold text-slate-800 truncate">${escapeHtml(c.name)}</span>
                                <span class="text-[10px] text-slate-400 shrink-0">${c.last_message ? c.last_message.created_at : ''}</span>
                            </div>
                            <div class="flex items-center justify-between gap-1 mt-0.5">
                                <span class="text-[11px] text-slate-500 truncate font-mono">${subInfo}</span>
                                ${unpaidTag}
                            </div>
                        </div>
                    `;
                    container.appendChild(row);
                });
            }

            function selectContact(contact) {
                activeContact = contact;
                activePhone = contact.normalized_phone;

                document.getElementById('waWorkspace').classList.add('chat-selected');
                document.getElementById('emptyPlaceholder').classList.add('hidden');
                document.getElementById('chatContainer').classList.remove('hidden');

                document.querySelectorAll('.wa-contact-row').forEach(el => {
                    el.classList.toggle('active', el.dataset.phone === activePhone);
                });

                document.getElementById('headerName').textContent = contact.name;
                document.getElementById('headerPhone').textContent = contact.phone || contact.normalized_phone;
                document.getElementById('headerPaket').textContent = `${contact.paket || 'Paket'} (${contact.username || '-'})`;
                document.getElementById('headerAvatar').textContent = getInitials(contact.name);

                const tag = document.getElementById('headerUnpaidTag');
                if (contact.unpaid_count > 0) {
                    tag.classList.remove('hidden');
                    tag.textContent = `${contact.unpaid_count} Faktur Belum Bayar`;
                } else {
                    tag.classList.add('hidden');
                }

                document.getElementById('btnWaWebDeepLink').href = `https://wa.me/${contact.normalized_phone}`;

                loadConversation(activePhone);

                clearInterval(pollTimer);
                pollTimer = setInterval(() => {
                    if (activePhone) loadConversation(activePhone, true);
                }, 8000);

                if (initialInvoiceId) {
                    setTimeout(() => {
                        openInvoiceModal(initialInvoiceId);
                        initialInvoiceId = null;
                    }, 350);
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

            function backToSidebar() {
                document.getElementById('waWorkspace').classList.remove('chat-selected');
            }

            function loadConversation(phone, isPolling = false) {
                const stream = document.getElementById('chatStream');
                if (!isPolling) {
                    stream.innerHTML = `
                        <div class="text-center py-6 text-slate-400 text-xs">
                            <i class="fa-solid fa-circle-notch fa-spin text-slate-400 mb-1"></i>
                            <div>Memuat pesan...</div>
                        </div>`;
                }

                fetch(`{{ url('whatsapp/conversation') }}/${phone}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return;
                        if (data.contact) activeContact = data.contact;
                        renderChatStream(data.messages);
                    });
            }

            function renderChatStream(messages) {
                const stream = document.getElementById('chatStream');

                if (!messages.length) {
                    stream.innerHTML = `
                        <div class="text-center py-8 text-slate-400 text-xs">
                            <div class="font-semibold text-slate-600">Belum ada riwayat pesan tercatat</div>
                            <div class="text-[11px] text-slate-400 mt-1">Gunakan formulir di bawah atau pilih Kirim Tagihan.</div>
                        </div>`;
                    return;
                }

                const isNearBottom = (stream.scrollTop + stream.clientHeight >= stream.scrollHeight - 60);

                stream.innerHTML = '';
                messages.forEach(m => {
                    const isOut = m.direction === 'outbound';
                    const isInv = m.type === 'invoice';
                    const row = document.createElement('div');
                    row.className = `wa-msg-row ${isOut ? 'outbound' : 'inbound'}`;

                    let statusMark = '';
                    if (isOut) {
                        if (m.status === 'simulated') {
                            statusMark = '<span class="text-[9px] font-bold text-amber-700 bg-amber-100 px-1 rounded">SIM</span>';
                        } else if (m.status === 'sent') {
                            statusMark = '<i class="fa-solid fa-check text-slate-400 text-[10px]" title="Terkirim"></i>';
                        } else if (m.status === 'delivered') {
                            statusMark = '<i class="fa-solid fa-check-double text-slate-400 text-[10px]" title="Tersampaikan"></i>';
                        } else if (m.status === 'read') {
                            statusMark = '<i class="fa-solid fa-check-double text-sky-600 text-[10px]" title="Dibaca"></i>';
                        } else if (m.status === 'failed') {
                            statusMark = '<i class="fa-solid fa-triangle-exclamation text-rose-500 text-[10px]" title="Gagal"></i>';
                        }
                    }

                    row.innerHTML = `
                        <div class="wa-msg-bubble ${isOut ? 'outbound' : 'inbound'} ${isInv ? 'invoice-type' : ''}">
                            <div>${formatMsgText(m.message)}</div>
                            <div class="wa-msg-meta">
                                <span>${m.time}</span>
                                ${statusMark}
                            </div>
                        </div>
                    `;
                    stream.appendChild(row);
                });

                if (isNearBottom) {
                    stream.scrollTop = stream.scrollHeight;
                }
            }

            function sendMessageApi() {
                if (!activeContact) return;
                const input = document.getElementById('composerInput');
                const text = input.value.trim();
                if (!text) return;

                const btn = document.getElementById('btnSendApi');
                btn.disabled = true;

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
                    if (data.success) {
                        input.value = '';
                        loadConversation(activeContact.normalized_phone);
                    } else {
                        alert(data.message || 'Gagal mengirim pesan.');
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    alert('Terjadi kesalahan jaringan.');
                });
            }

            function sendViaWaWeb() {
                if (!activeContact) return;
                const text = document.getElementById('composerInput').value.trim();
                const url = `https://web.whatsapp.com/send?phone=${activeContact.normalized_phone}&text=${encodeURIComponent(text)}`;
                window.open(url, '_blank');
            }

            function toggleTemplateMenu() {
                document.getElementById('templateDropdown').classList.toggle('hidden');
            }

            function applyTemplate(id) {
                const t = TEMPLATES.find(x => x.id === id);
                if (!t || !activeContact) return;

                let body = t.body;
                body = body.replace(/\{nama\}/g, activeContact.name || 'Pelanggan');
                body = body.replace(/\{username\}/g, activeContact.username || '-');
                body = body.replace(/\{paket\}/g, activeContact.paket || 'Internet');
                body = body.replace(/\{total\}/g, activeContact.unpaid_total ? Number(activeContact.unpaid_total).toLocaleString('id-ID') : '0');
                body = body.replace(/\{periode\}/g, new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(new Date()));
                body = body.replace(/\{jatuh_tempo\}/g, activeContact.invoices && activeContact.invoices.length ? activeContact.invoices[0].due_date : '-');
                body = body.replace(/\{nomor_invoice\}/g, activeContact.invoices && activeContact.invoices.length ? activeContact.invoices[0].invoice_number : 'INV-...');
                body = body.replace(/\{waktu_pemeliharaan\}/g, 'Pukul 23:00 - 03:00 WIB');
                body = body.replace(/\{estimasi_durasi\}/g, '2-4 Jam');

                const composer = document.getElementById('composerInput');
                composer.value = body;
                composer.focus();
                document.getElementById('templateDropdown').classList.add('hidden');
            }

            function insertTag(tag) {
                const c = document.getElementById('composerInput');
                const start = c.selectionStart;
                const end = c.selectionEnd;
                c.value = c.value.substring(0, start) + tag + c.value.substring(end);
                c.focus();
                c.selectionStart = c.selectionEnd = start + tag.length;
            }

            function openInvoiceModal(preselectedId = null) {
                if (!activeContact) return;

                const select = document.getElementById('selectInvoice');
                select.innerHTML = '<option value="">-- Pilih Faktur Pelanggan --</option>';

                if (activeContact.invoices && activeContact.invoices.length) {
                    activeContact.invoices.forEach(inv => {
                        const opt = document.createElement('option');
                        opt.value = inv.id;
                        opt.textContent = `${inv.invoice_number} - Rp ${Number(inv.total).toLocaleString('id-ID')} (${inv.status.toUpperCase()}) - JT: ${inv.due_date}`;
                        if (preselectedId && inv.id == preselectedId) opt.selected = true;
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

            function onSelectInvoiceChange(id) {
                if (!id) {
                    document.getElementById('invoiceMessagePreview').value = '';
                    return;
                }
                fetch(`{{ url('whatsapp/preview-invoice') }}/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('invoiceMessagePreview').value = data.text;
                        }
                    });
            }

            function sendInvoiceApi() {
                const id = document.getElementById('selectInvoice').value;
                if (!id) {
                    alert('Silakan pilih invoice terlebih dahulu.');
                    return;
                }

                const msg = document.getElementById('invoiceMessagePreview').value;
                const btn = document.getElementById('btnSendInvoiceApi');
                btn.disabled = true;

                fetch(`{{ route('whatsapp.send_invoice') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        invoice_id: id,
                        phone: activeContact.normalized_phone,
                        custom_message: msg,
                    })
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    if (data.success) {
                        closeInvoiceModal();
                        loadConversation(activeContact.normalized_phone);
                    } else {
                        alert(data.message || 'Gagal mengirim tagihan.');
                    }
                })
                .catch(() => {
                    btn.disabled = false;
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

            function toggleCustomerInfo() {
                const modal = document.getElementById('customerInfoModal');
                modal.classList.toggle('hidden');

                if (!modal.classList.contains('hidden') && activeContact) {
                    const body = document.getElementById('customerInfoBody');
                    body.innerHTML = `
                        <div class="p-2.5 bg-slate-50 border border-slate-200 rounded">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Nama Pelanggan</div>
                            <div class="text-xs font-bold text-slate-900 mt-0.5">${escapeHtml(activeContact.name)}</div>
                            <div class="text-slate-500 font-mono text-[11px]">${activeContact.phone}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="p-2 bg-slate-50 border border-slate-200 rounded">
                                <span class="text-[10px] text-slate-400 font-bold uppercase block">PPPoE</span>
                                <span class="font-bold text-slate-800 text-xs">${activeContact.username || '-'}</span>
                            </div>
                            <div class="p-2 bg-slate-50 border border-slate-200 rounded">
                                <span class="text-[10px] text-slate-400 font-bold uppercase block">Paket</span>
                                <span class="font-bold text-slate-800 text-xs">${activeContact.paket || '-'}</span>
                            </div>
                        </div>
                        <div class="p-2.5 bg-slate-50 border border-slate-200 rounded">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Alamat</div>
                            <div class="text-slate-700 mt-0.5">${escapeHtml(activeContact.address || '-')}</div>
                        </div>
                        <div class="p-2.5 bg-slate-50 border border-slate-200 rounded flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Tagihan Tertunggak</span>
                                <span class="font-bold text-rose-700 text-xs">Rp ${Number(activeContact.unpaid_total || 0).toLocaleString('id-ID')} (${activeContact.unpaid_count})</span>
                            </div>
                            ${activeContact.pppoe_id ? `<a href="{{ url('invoices') }}/${activeContact.pppoe_id}" class="text-[11px] font-bold text-sky-600 hover:underline">Riwayat &rarr;</a>` : ''}
                        </div>
                    `;
                }
            }

            function getInitials(name) {
                if (!name) return 'EN';
                const p = name.trim().split(' ');
                if (p.length >= 2) return (p[0][0] + p[1][0]).toUpperCase();
                return name.substring(0, 2).toUpperCase();
            }

            function normalizePhone(p) {
                if (!p) return '';
                let d = p.replace(/[^0-9]/g, '');
                if (d.startsWith('08')) d = '62' + d.substring(1);
                else if (d.startsWith('8')) d = '62' + d;
                return d;
            }

            function escapeHtml(t) {
                if (!t) return '';
                return t.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function formatMsgText(t) {
                if (!t) return '';
                let s = escapeHtml(t);
                s = s.replace(/\*(.*?)\*/g, '<strong>$1</strong>');
                s = s.replace(/_(.*?)_/g, '<em>$1</em>');
                s = s.replace(/`(.*?)`/g, '<code class="bg-black/5 px-1 rounded text-xs">$1</code>');
                s = s.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" class="text-sky-600 underline">$1</a>');
                return s;
            }
        </script>
    @endsection
</x-layouts.app>
