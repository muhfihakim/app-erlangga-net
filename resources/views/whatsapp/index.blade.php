<x-layouts.app>
    @section('Css')
        <style>
            .wa-workspace {
                display: flex;
                height: calc(100vh - 130px);
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
                position: relative;
            }

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

            .wa-placeholder.is-hidden {
                display: none !important;
            }

            .wa-chat-view {
                display: flex;
                flex-direction: column;
                height: 100%;
                width: 100%;
            }

            .wa-chat-view.is-hidden {
                display: none !important;
            }

            .wa-chat-header {
                height: 54px;
                padding: 0 16px;
                background: #ffffff;
                border-bottom: 1px solid #e2e8f0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-shrink: 0;
                z-index: 10;
            }

            /* WhatsApp Web Styled Message Stream */
            .wa-chat-stream {
                flex: 1;
                overflow-y: auto;
                padding: 14px 18px;
                display: flex;
                flex-direction: column;
                gap: 6px;
                background-color: #efeae2;
                background-image: radial-gradient(#d1d7db 0.8px, transparent 0.8px);
                background-size: 16px 16px;
                scroll-behavior: smooth;
            }

            /* Date Divider Badge */
            .wa-date-divider {
                display: flex;
                justify-content: center;
                margin: 10px 0 6px;
                user-select: none;
            }

            .wa-date-pill {
                background: #ffffff;
                color: #54656f;
                font-size: 10.5px;
                font-weight: 600;
                padding: 3px 12px;
                border-radius: 6px;
                box-shadow: 0 1px 1px rgba(0, 0, 0, 0.08);
                border: 1px solid #e2e8f0;
                text-transform: uppercase;
                letter-spacing: 0.02em;
            }

            /* Message Row & Bubbles */
            .wa-msg-row {
                display: flex;
                width: 100%;
                position: relative;
            }

            .wa-msg-row.outbound {
                justify-content: flex-end;
            }

            .wa-msg-row.inbound {
                justify-content: flex-start;
            }

            .wa-msg-bubble {
                max-width: 70%;
                min-width: 90px;
                padding: 6px 10px 5px;
                border-radius: 7.5px;
                position: relative;
                box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.13);
                display: flex;
                flex-direction: column;
            }

            .wa-msg-bubble.outbound {
                background: #d9fdd3;
                color: #111b21;
                border: 1px solid #c1f2be;
                border-top-right-radius: 0;
            }

            .wa-msg-bubble.inbound {
                background: #ffffff;
                color: #111b21;
                border: 1px solid #e2e8f0;
                border-top-left-radius: 0;
            }

            .wa-msg-bubble.is-deleted {
                background: #f1f5f9 !important;
                border-color: #cbd5e1 !important;
                color: #64748b !important;
                font-style: italic;
            }

            .wa-msg-text {
                font-size: 12.8px;
                line-height: 1.45;
                word-break: break-word;
                white-space: pre-wrap;
                color: #111b21;
            }

            .wa-msg-meta {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 3.5px;
                font-size: 10px;
                color: #667781;
                margin-top: 3px;
                align-self: flex-end;
                user-select: none;
            }

            /* Quoted Reply inside Bubble */
            .wa-msg-quote {
                padding: 4px 8px;
                border-left: 3.5px solid #059669;
                background: rgba(0, 0, 0, 0.04);
                border-radius: 4px;
                margin-bottom: 5px;
                font-size: 11px;
                cursor: default;
            }

            .wa-msg-quote.inbound-accent {
                border-left-color: #2563eb;
            }

            .wa-msg-quote-sender {
                font-weight: 700;
                font-size: 10.5px;
                margin-bottom: 1px;
            }

            .wa-msg-quote-text {
                color: #475569;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            /* Invoice Preview inside Bubble */
            .wa-msg-invoice-card {
                padding: 6px 8px;
                background: rgba(5, 150, 105, 0.08);
                border-left: 3.5px solid #059669;
                border-radius: 4px;
                margin-bottom: 5px;
                font-size: 11.5px;
            }

            /* Message Option Hover Trigger */
            .wa-msg-action-trigger {
                position: absolute;
                top: 4px;
                right: 4px;
                width: 18px;
                height: 18px;
                display: none;
                align-items: center;
                justify-content: center;
                color: #64748b;
                background: rgba(255, 255, 255, 0.9);
                border-radius: 50%;
                cursor: pointer;
                font-size: 9px;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.12);
            }

            .wa-msg-bubble:hover .wa-msg-action-trigger {
                display: flex;
            }

            /* Floating Context Dropdown */
            .wa-context-menu {
                position: fixed;
                background: #ffffff;
                border: 1px solid #cbd5e1;
                border-radius: 6px;
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.14);
                padding: 4px 0;
                min-width: 130px;
                z-index: 100;
            }

            .wa-context-item {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 6px 12px;
                font-size: 11.5px;
                font-weight: 500;
                color: #334155;
                cursor: pointer;
                transition: background-color 0.1s;
                width: 100%;
                text-align: left;
                border: none;
                background: transparent;
            }

            .wa-context-item:hover {
                background: #f1f5f9;
                color: #0f172a;
            }

            .wa-context-item.danger {
                color: #dc2626;
            }

            .wa-context-item.danger:hover {
                background: #fef2f2;
                color: #b91c1c;
            }

            /* CRITICAL: Pre-composer banners MUST be hidden by default */
            .wa-pre-composer-bar {
                display: none !important;
                padding: 7px 14px;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                font-size: 11.5px;
            }

            .wa-pre-composer-bar.is-visible {
                display: flex !important;
            }

            .wa-reply-bar {
                background: #ecfdf5;
                border-top: 1px solid #a7f3d0;
                border-left: 4px solid #059669;
            }

            .wa-edit-bar {
                background: #fffbeb;
                border-top: 1px solid #fde68a;
                border-left: 4px solid #d97706;
            }

            /* Composer Footer */
            .wa-chat-footer {
                background: #f0f2f5;
                border-top: 1px solid #e2e8f0;
                flex-shrink: 0;
            }

            .wa-chips-row {
                padding: 6px 14px 2px;
                display: flex;
                align-items: center;
                gap: 5px;
                overflow-x: auto;
                scrollbar-width: none;
            }

            .wa-chips-row::-webkit-scrollbar {
                display: none;
            }

            .wa-chip {
                padding: 2.5px 7px;
                font-size: 10.5px;
                font-family: ui-monospace, monospace;
                color: #334155;
                background: #ffffff;
                border: 1px solid #cbd5e1;
                border-radius: 4px;
                cursor: pointer;
                white-space: nowrap;
                transition: all 0.12s;
            }

            .wa-chip:hover {
                background: #e2e8f0;
                color: #0f172a;
            }

            .wa-composer-inner {
                display: flex;
                align-items: flex-end;
                gap: 8px;
                padding: 6px 14px 10px;
            }

            .wa-composer-input-box {
                flex: 1;
                background: #ffffff;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                padding: 6px 10px;
                display: flex;
                align-items: center;
                transition: border-color 0.15s, box-shadow 0.15s;
                min-height: 38px;
            }

            .wa-composer-input-box:focus-within {
                border-color: #059669;
                box-shadow: 0 0 0 1px #059669;
            }

            .wa-composer-textarea {
                width: 100%;
                border: none;
                outline: none;
                resize: none;
                font-size: 12.5px;
                line-height: 1.45;
                min-height: 22px;
                max-height: 110px;
                font-family: inherit;
                background: transparent;
                padding: 0;
                color: #0f172a;
            }

            .wa-btn-send {
                width: 38px;
                height: 38px;
                border-radius: 8px;
                background: #059669;
                color: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                border: none;
                transition: background-color 0.15s, transform 0.1s;
                flex-shrink: 0;
            }

            .wa-btn-send:hover {
                background: #047857;
            }

            .wa-btn-send:active {
                transform: scale(0.96);
            }

            .wa-btn-send:disabled {
                background: #94a3b8;
                cursor: not-allowed;
            }

            /* Responsive Mobile */
            @media (max-width: 860px) {
                .wa-workspace {
                    height: calc(100dvh - 130px);
                    border-radius: 6px;
                }

                .wa-sidebar {
                    width: 100%;
                    max-width: 100%;
                }

                .wa-workspace.chat-selected .wa-sidebar {
                    display: none !important;
                }

                .wa-workspace:not(.chat-selected) .wa-chat-panel {
                    display: none !important;
                }

                .wa-msg-bubble {
                    max-width: 86%;
                }

                .wa-msg-action-trigger {
                    display: flex !important;
                }
            }
        </style>
    @endsection

    <!-- Page Header (Standard Compact) -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:0.75rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:19px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                WhatsApp Center
            </h1>
            <p style="font-size:11.5px;color:#64748b;margin:1px 0 0;">
                Komunikasi obrolan pelanggan, pengiriman tagihan resmi, dan notifikasi via gateway Api.co.id.
            </p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('settings.index') }}?tab=whatsapp" class="btn-cancel !h-7.5 !px-3 text-xs font-semibold flex items-center gap-1.5" title="Buka Pengaturan WhatsApp">
                <i class="fa-solid fa-gear text-slate-500"></i> Pengaturan
            </a>
            <a href="{{ route('invoices.index') }}" class="btn-cancel !h-7.5 !px-3 text-xs font-semibold flex items-center gap-1.5" title="Daftar Invoice Tagihan">
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
            <div class="wa-chat-view is-hidden" id="chatContainer">
                <!-- Chat Topbar -->
                <div class="wa-chat-header">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <button type="button" class="md:hidden text-slate-600 hover:text-slate-900 p-1.5 rounded hover:bg-slate-100" onclick="backToSidebar()" title="Kembali ke Kontak">
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
                        <!-- Send Invoice Button -->
                        <button type="button" class="btn-primary-admin !h-7.5 !px-2.5 text-xs font-semibold !bg-emerald-600 hover:!bg-emerald-700 flex items-center gap-1.5" onclick="openInvoiceModal()">
                            <i class="fa-solid fa-file-invoice-dollar"></i> <span class="hidden sm:inline">Kirim Tagihan</span>
                        </button>

                        <!-- Quick Template Dropdown -->
                        <div class="relative" id="templateMenuWrap">
                            <button type="button" class="btn-cancel !h-7.5 !px-2 text-xs font-semibold flex items-center gap-1" onclick="toggleTemplateMenu()">
                                <i class="fa-solid fa-list-check text-slate-500"></i> <span class="hidden md:inline">Template</span>
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
                    <!-- Reply Banner (Hidden by default via CSS) -->
                    <div id="replyBar" class="wa-pre-composer-bar wa-reply-bar">
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-emerald-800 text-[11px] flex items-center gap-1">
                                <i class="fa-solid fa-reply"></i>
                                <span>Membalas <span id="replySenderName">-</span></span>
                            </div>
                            <div class="text-slate-600 truncate text-[11px] mt-0.5" id="replyPreviewText">-</div>
                        </div>
                        <button type="button" class="text-slate-400 hover:text-slate-700 p-1" onclick="cancelReply()">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>

                    <!-- Edit Banner (Hidden by default via CSS) -->
                    <div id="editBar" class="wa-pre-composer-bar wa-edit-bar">
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-amber-800 text-[11px] flex items-center gap-1">
                                <i class="fa-solid fa-pen"></i>
                                <span>Mengedit Pesan</span>
                            </div>
                            <div class="text-slate-600 truncate text-[11px] mt-0.5" id="editPreviewText">-</div>
                        </div>
                        <button type="button" class="text-slate-400 hover:text-slate-700 p-1" onclick="cancelEdit()">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>

                    <!-- Variable Shortcuts -->
                    <div class="wa-chips-row">
                        <span class="text-slate-400 text-[10px] mr-1 shrink-0 font-medium">Variabel:</span>
                        <button type="button" class="wa-chip" onclick="insertTag('{nama}')">{nama}</button>
                        <button type="button" class="wa-chip" onclick="insertTag('{total}')">{total}</button>
                        <button type="button" class="wa-chip" onclick="insertTag('{nomor_invoice}')">{nomor_invoice}</button>
                        <button type="button" class="wa-chip" onclick="insertTag('{jatuh_tempo}')">{jatuh_tempo}</button>
                    </div>

                    <!-- Input & Send Button Row -->
                    <div class="wa-composer-inner">
                        <div class="wa-composer-input-box">
                            <textarea id="composerInput" rows="1" class="wa-composer-textarea"
                                placeholder="Ketik pesan (Enter untuk kirim, Shift+Enter untuk baris baru)..."></textarea>
                        </div>

                        <button type="button" id="btnSendApi" class="wa-btn-send" onclick="submitComposer()" title="Kirim Pesan">
                            <i class="fa-solid fa-paper-plane text-xs" id="btnSendIcon"></i>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Floating Context Menu (Actions on Bubble) -->
    <div id="msgContextMenu" class="wa-context-menu hidden">
        <button type="button" class="wa-context-item" id="menuActionReply" onclick="triggerReplyFromMenu()">
            <i class="fa-solid fa-reply text-slate-500 w-3.5 text-center"></i> <span>Balas</span>
        </button>
        <button type="button" class="wa-context-item" id="menuActionEdit" onclick="triggerEditFromMenu()">
            <i class="fa-solid fa-pen text-slate-500 w-3.5 text-center"></i> <span>Edit Pesan</span>
        </button>
        <button type="button" class="wa-context-item" id="menuActionCopy" onclick="triggerCopyFromMenu()">
            <i class="fa-regular fa-copy text-slate-500 w-3.5 text-center"></i> <span>Salin Teks</span>
        </button>
        <button type="button" class="wa-context-item danger" id="menuActionDelete" onclick="triggerDeleteFromMenu()">
            <i class="fa-regular fa-trash-can w-3.5 text-center"></i> <span>Hapus Pesan</span>
        </button>
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
                    <button type="button" id="btnSendInvoiceApi" class="btn-primary-admin !h-8 !px-4 text-xs font-semibold !bg-emerald-600 hover:!bg-emerald-700 flex items-center gap-1" onclick="sendInvoiceApi()">
                        <i class="fa-solid fa-paper-plane text-xs"></i> Kirim Tagihan
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

    <!-- Modal Konfirmasi Hapus Pesan -->
    <div class="modal-overlay hidden" id="deleteMsgModal">
        <div class="modal-box max-w-xs bg-white border border-slate-300 shadow-xl rounded-xl">
            <div class="modal-header border-b border-slate-200 px-4 py-3 flex items-center justify-between">
                <h3 class="text-xs font-bold text-rose-700 m-0 flex items-center gap-1.5">
                    <i class="fa-regular fa-trash-can"></i> Hapus Pesan
                </h3>
                <button type="button" class="modal-close" onclick="closeDeleteModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-4 bg-white text-xs text-slate-700">
                <p class="m-0">Apakah Anda yakin ingin menghapus pesan ini dari riwayat percakapan?</p>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-4 py-2 flex items-center justify-end gap-2 rounded-b-xl">
                <button type="button" class="btn-cancel !h-7.5 !px-3 text-xs font-semibold" onclick="closeDeleteModal()">Batal</button>
                <button type="button" id="btnConfirmDelete" class="btn-primary-admin !h-7.5 !px-3 text-xs font-semibold !bg-rose-600 hover:!bg-rose-700" onclick="confirmDeleteMessage()">
                    Hapus
                </button>
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

            // Interactive States
            let replyingMessage = null; // { id, sender, text }
            let editingMessage = null;  // { id, text }
            let activeMenuMsg = null;   // message object for context menu
            let pendingDeleteId = null; // message ID to delete

            document.addEventListener('DOMContentLoaded', function() {
                // Ensure reply & edit bars are strictly hidden on start
                cancelReply();
                cancelEdit();

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

                // Composer: Enter to send, Shift+Enter for newline
                const composer = document.getElementById('composerInput');
                composer.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        submitComposer();
                    }
                });

                // Auto-growing textarea
                composer.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                });

                // Close menus when clicking outside
                document.addEventListener('click', function(e) {
                    // Close template dropdown
                    const templateWrap = document.getElementById('templateMenuWrap');
                    const templateDropdown = document.getElementById('templateDropdown');
                    if (templateWrap && !templateWrap.contains(e.target)) {
                        templateDropdown.classList.add('hidden');
                    }

                    // Close context menu
                    const menu = document.getElementById('msgContextMenu');
                    if (menu && !menu.contains(e.target) && !e.target.closest('.wa-msg-action-trigger')) {
                        menu.classList.add('hidden');
                        activeMenuMsg = null;
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
                    .then(res => {
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);
                        return res.json();
                    })
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
                    const cleanPhone = normalizePhone(c.normalized_phone || c.phone || '');
                    const isActive = activeContact && normalizePhone(activeContact.normalized_phone || activeContact.phone || '') === cleanPhone;
                    const row = document.createElement('div');
                    row.className = `wa-contact-row ${isActive ? 'active' : ''}`;
                    row.dataset.phone = cleanPhone;
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
                const cleanPhone = normalizePhone(contact.normalized_phone || contact.phone || '');
                activeContact.normalized_phone = cleanPhone;
                activePhone = cleanPhone;

                document.getElementById('waWorkspace').classList.add('chat-selected');

                // Explicitly toggle placeholder vs active chat view
                const placeholder = document.getElementById('emptyPlaceholder');
                const chatBox = document.getElementById('chatContainer');
                placeholder.classList.add('is-hidden');
                placeholder.style.display = 'none';
                chatBox.classList.remove('is-hidden');
                chatBox.style.display = 'flex';

                document.querySelectorAll('.wa-contact-row').forEach(el => {
                    el.classList.toggle('active', el.dataset.phone === activePhone);
                });

                document.getElementById('headerName').textContent = contact.name || 'Pelanggan';
                document.getElementById('headerPhone').textContent = contact.phone || contact.display_phone || cleanPhone || '-';
                document.getElementById('headerPaket').textContent = `${contact.paket || 'Paket'} (${contact.username || '-'})`;
                document.getElementById('headerAvatar').textContent = getInitials(contact.name);

                const tag = document.getElementById('headerUnpaidTag');
                if (contact.unpaid_count > 0) {
                    tag.classList.remove('hidden');
                    tag.textContent = `${contact.unpaid_count} Faktur Belum Bayar`;
                } else {
                    tag.classList.add('hidden');
                }

                // Strictly reset reply and edit states
                cancelReply();
                cancelEdit();

                loadConversation(activePhone);

                clearInterval(pollTimer);
                pollTimer = setInterval(() => {
                    if (activePhone && !editingMessage) {
                        loadConversation(activePhone, true);
                    }
                }, 7000);

                if (initialInvoiceId) {
                    setTimeout(() => {
                        openInvoiceModal(initialInvoiceId);
                        initialInvoiceId = null;
                    }, 350);
                }
            }

            function selectContactByPhone(phone) {
                const norm = normalizePhone(phone);
                if (!norm) return;

                fetch(`{{ url('whatsapp/conversation') }}/${norm}`)
                    .then(res => {
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);
                        return res.json();
                    })
                    .then(data => {
                        if (data.success && data.contact) {
                            data.contact.normalized_phone = norm;
                            selectContact(data.contact);
                        }
                    })
                    .catch(err => {
                        console.error('selectContactByPhone error:', err);
                    });
            }

            function backToSidebar() {
                document.getElementById('waWorkspace').classList.remove('chat-selected');
                const placeholder = document.getElementById('emptyPlaceholder');
                const chatBox = document.getElementById('chatContainer');
                placeholder.classList.remove('is-hidden');
                placeholder.style.display = 'flex';
                chatBox.classList.add('is-hidden');
                chatBox.style.display = 'none';

                activeContact = null;
                activePhone = null;
                cancelReply();
                cancelEdit();
                clearInterval(pollTimer);
            }

            function loadConversation(phone, isPolling = false) {
                const cleanPhone = normalizePhone(phone);
                const stream = document.getElementById('chatStream');

                if (!cleanPhone) {
                    if (!isPolling) {
                        stream.innerHTML = `
                            <div class="text-center py-8 text-slate-500 text-xs">
                                <i class="fa-solid fa-circle-exclamation text-amber-500 text-2xl mb-2 block"></i>
                                <div class="font-bold text-slate-700">Nomor WhatsApp Tidak Tersedia</div>
                                <div class="text-[11px] text-slate-400 mt-1">Kontak ini belum memiliki nomor telepon WhatsApp yang valid.</div>
                            </div>`;
                    }
                    return;
                }

                if (!isPolling) {
                    stream.innerHTML = `
                        <div class="text-center py-8 text-slate-400 text-xs">
                            <i class="fa-solid fa-circle-notch fa-spin text-slate-400 mb-1.5 text-base block"></i>
                            <div>Memuat pesan...</div>
                        </div>`;
                }

                fetch(`{{ url('whatsapp/conversation') }}/${cleanPhone}`)
                    .then(res => {
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);
                        return res.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            if (!isPolling) {
                                stream.innerHTML = `
                                    <div class="text-center py-8 text-rose-500 text-xs">
                                        <i class="fa-solid fa-triangle-exclamation text-rose-500 text-2xl mb-2 block"></i>
                                        <div class="font-bold">${escapeHtml(data.message || 'Gagal memuat percakapan.')}</div>
                                        <button type="button" class="btn-cancel !h-7.5 !px-3 text-xs mt-3 inline-flex items-center gap-1.5" onclick="loadConversation('${cleanPhone}')">
                                            <i class="fa-solid fa-rotate-right"></i> Coba Lagi
                                        </button>
                                    </div>`;
                            }
                            return;
                        }

                        if (data.contact) {
                            activeContact = data.contact;
                            if (!activeContact.normalized_phone) {
                                activeContact.normalized_phone = cleanPhone;
                            }
                        }

                        renderChatStream(data.messages || []);
                    })
                    .catch(err => {
                        console.error('loadConversation error:', err);
                        if (!isPolling) {
                            stream.innerHTML = `
                                <div class="text-center py-8 text-rose-500 text-xs">
                                    <i class="fa-solid fa-triangle-exclamation text-rose-500 text-2xl mb-2 block"></i>
                                    <div class="font-bold text-slate-800">Gagal Memuat Pesan</div>
                                    <div class="text-[11px] text-slate-500 mt-1 mb-3">Terjadi kendala saat memuat riwayat pesan (${escapeHtml(err.message)}).</div>
                                    <button type="button" class="btn-cancel !h-7.5 !px-3 text-xs inline-flex items-center gap-1.5" onclick="loadConversation('${cleanPhone}')">
                                        <i class="fa-solid fa-rotate-right"></i> Coba Lagi
                                    </button>
                                </div>`;
                        }
                    });
            }

            function renderChatStream(messages) {
                const stream = document.getElementById('chatStream');

                if (!messages.length) {
                    stream.innerHTML = `
                        <div class="text-center py-10 text-slate-400 text-xs">
                            <i class="fa-regular fa-comments text-slate-300 text-3xl mb-2 block"></i>
                            <div class="font-bold text-slate-700">Belum Ada Percakapan</div>
                            <div class="text-[11px] text-slate-400 mt-1">Ketik pesan di bawah atau gunakan tombol Kirim Tagihan.</div>
                        </div>`;
                    return;
                }

                const isNearBottom = (stream.scrollTop + stream.clientHeight >= stream.scrollHeight - 70);

                stream.innerHTML = '';
                let lastDate = null;

                messages.forEach(m => {
                    // Date Divider
                    const msgDate = m.date || 'Hari ini';
                    if (msgDate !== lastDate) {
                        const dateRow = document.createElement('div');
                        dateRow.className = 'wa-date-divider';
                        dateRow.innerHTML = `<span class="wa-date-pill">${m.is_today ? 'Hari Ini' : msgDate}</span>`;
                        stream.appendChild(dateRow);
                        lastDate = msgDate;
                    }

                    const isOut = m.direction === 'outbound';
                    const isInv = m.type === 'invoice';
                    const isDeleted = m.status === 'deleted';

                    const row = document.createElement('div');
                    row.className = `wa-msg-row ${isOut ? 'outbound' : 'inbound'}`;
                    row.id = `msg-row-${m.id}`;

                    let statusMark = '';
                    if (isOut && !isDeleted) {
                        if (m.status === 'simulated') {
                            statusMark = '<span class="text-[9px] font-bold text-amber-700 bg-amber-100 px-1 rounded">SIM</span>';
                        } else if (m.status === 'sent') {
                            statusMark = '<i class="fa-solid fa-check text-slate-400 text-[10px]" title="Terkirim ke server"></i>';
                        } else if (m.status === 'delivered') {
                            statusMark = '<i class="fa-solid fa-check-double text-slate-400 text-[10px]" title="Tersampaikan"></i>';
                        } else if (m.status === 'read') {
                            statusMark = '<i class="fa-solid fa-check-double text-[#53bdeb] text-[10px]" title="Dibaca"></i>';
                        } else if (m.status === 'failed') {
                            statusMark = '<i class="fa-solid fa-triangle-exclamation text-rose-500 text-[10px]" title="Gagal terkirim"></i>';
                        }
                    }

                    // Quoted preview inside bubble
                    let quoteHtml = '';
                    if (m.reply_to && !isDeleted) {
                        const quoteSender = escapeHtml(m.reply_to.sender || 'Pesan');
                        const quoteText = escapeHtml(m.reply_to.message || '');
                        const accentClass = m.reply_to.sender === 'Admin' || m.reply_to.sender === 'Anda' ? '' : 'inbound-accent';
                        quoteHtml = `
                            <div class="wa-msg-quote ${accentClass}">
                                <div class="wa-msg-quote-sender">${quoteSender}</div>
                                <div class="wa-msg-quote-text">${quoteText}</div>
                            </div>`;
                    }

                    // Invoice tag header
                    let invoiceHtml = '';
                    if (isInv && !isDeleted) {
                        const invNo = m.metadata && m.metadata.invoice_number ? m.metadata.invoice_number : 'FAKTUR RESMI';
                        invoiceHtml = `
                            <div class="wa-msg-invoice-card flex items-center justify-between gap-2">
                                <span class="font-bold text-emerald-800 flex items-center gap-1">
                                    <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i> ${escapeHtml(invNo)}
                                </span>
                                <span class="text-[10px] font-semibold text-emerald-700">TAGIHAN INTERNET</span>
                            </div>`;
                    }

                    // Edited tag
                    const editedMark = m.is_edited && !isDeleted ? '<span class="italic text-[9.5px] text-slate-400 mr-0.5">(diedit)</span>' : '';

                    // Action chevron button
                    const actionBtn = !isDeleted ? `
                        <button type="button" class="wa-msg-action-trigger" onclick="openMsgMenu(event, ${JSON.stringify(m).replace(/"/g, '&quot;')})" title="Opsi Pesan">
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>` : '';

                    row.innerHTML = `
                        <div class="wa-msg-bubble ${isOut ? 'outbound' : 'inbound'} ${isDeleted ? 'is-deleted' : ''}">
                            ${actionBtn}
                            ${quoteHtml}
                            ${invoiceHtml}
                            <div class="wa-msg-text">${formatMsgText(m.message)}</div>
                            <div class="wa-msg-meta">
                                ${editedMark}
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

            // Message Action Menu Handling
            function openMsgMenu(e, msg) {
                e.stopPropagation();
                activeMenuMsg = msg;

                const menu = document.getElementById('msgContextMenu');
                const btnEdit = document.getElementById('menuActionEdit');
                const btnDelete = document.getElementById('menuActionDelete');

                // Edit is only allowed for outbound non-deleted messages
                if (msg.direction === 'outbound' && msg.status !== 'deleted') {
                    btnEdit.classList.remove('hidden');
                } else {
                    btnEdit.classList.add('hidden');
                }

                // Delete is allowed for any non-deleted message
                if (msg.status !== 'deleted') {
                    btnDelete.classList.remove('hidden');
                } else {
                    btnDelete.classList.add('hidden');
                }

                // Position menu near the click target
                const rect = e.currentTarget.getBoundingClientRect();
                menu.style.top = `${rect.bottom + 4}px`;
                menu.style.left = `${Math.min(window.innerWidth - 150, Math.max(10, rect.left - 100))}px`;
                menu.classList.remove('hidden');
            }

            function triggerReplyFromMenu() {
                if (!activeMenuMsg) return;
                const menu = document.getElementById('msgContextMenu');
                menu.classList.add('hidden');

                replyingMessage = {
                    id: activeMenuMsg.id,
                    sender: activeMenuMsg.direction === 'outbound' ? 'Anda' : (activeMenuMsg.customer_name || 'Pelanggan'),
                    text: activeMenuMsg.message
                };

                // Cancel edit if any
                cancelEdit();

                // Show reply bar by adding is-visible
                document.getElementById('replySenderName').textContent = replyingMessage.sender;
                document.getElementById('replyPreviewText').textContent = replyingMessage.text;
                document.getElementById('replyBar').classList.add('is-visible');

                // Focus composer
                const composer = document.getElementById('composerInput');
                composer.focus();
            }

            function cancelReply() {
                replyingMessage = null;
                const el = document.getElementById('replyBar');
                if (el) el.classList.remove('is-visible');
            }

            function triggerEditFromMenu() {
                if (!activeMenuMsg) return;
                const menu = document.getElementById('msgContextMenu');
                menu.classList.add('hidden');

                editingMessage = {
                    id: activeMenuMsg.id,
                    text: activeMenuMsg.message
                };

                // Cancel reply if any
                cancelReply();

                // Show edit bar by adding is-visible
                document.getElementById('editPreviewText').textContent = editingMessage.text;
                document.getElementById('editBar').classList.add('is-visible');

                // Put text in composer
                const composer = document.getElementById('composerInput');
                composer.value = editingMessage.text;
                composer.focus();
                composer.dispatchEvent(new Event('input'));
            }

            function cancelEdit() {
                editingMessage = null;
                const el = document.getElementById('editBar');
                if (el) el.classList.remove('is-visible');
                const composer = document.getElementById('composerInput');
                if (composer) {
                    composer.value = '';
                    composer.style.height = 'auto';
                }
            }

            function triggerCopyFromMenu() {
                if (!activeMenuMsg) return;
                const menu = document.getElementById('msgContextMenu');
                menu.classList.add('hidden');

                navigator.clipboard.writeText(activeMenuMsg.message).then(() => {
                    showToast('Teks pesan berhasil disalin');
                }).catch(() => {
                    alert('Gagal menyalin teks ke clipboard.');
                });
            }

            function triggerDeleteFromMenu() {
                if (!activeMenuMsg) return;
                const menu = document.getElementById('msgContextMenu');
                menu.classList.add('hidden');

                pendingDeleteId = activeMenuMsg.id;
                document.getElementById('deleteMsgModal').classList.remove('hidden');
            }

            function closeDeleteModal() {
                pendingDeleteId = null;
                document.getElementById('deleteMsgModal').classList.add('hidden');
            }

            function confirmDeleteMessage() {
                if (!pendingDeleteId) return;

                const btn = document.getElementById('btnConfirmDelete');
                btn.disabled = true;

                fetch(`{{ url('whatsapp/messages') }}/${pendingDeleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ permanent: true })
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    closeDeleteModal();
                    if (data.success) {
                        if (activePhone) loadConversation(activePhone);
                    } else {
                        alert(data.message || 'Gagal menghapus pesan.');
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    closeDeleteModal();
                    alert('Terjadi kesalahan jaringan.');
                });
            }

            // Unified Submit Composer (Send or Update)
            function submitComposer() {
                if (!activeContact) {
                    alert('Silakan pilih kontak terlebih dahulu.');
                    return;
                }

                const input = document.getElementById('composerInput');
                const text = input.value.trim();
                if (!text) return;

                if (editingMessage) {
                    updateMessageApi(editingMessage.id, text);
                } else {
                    sendMessageApi(text);
                }
            }

            function sendMessageApi(text) {
                const btn = document.getElementById('btnSendApi');
                const input = document.getElementById('composerInput');
                btn.disabled = true;

                const payload = {
                    phone: activeContact.normalized_phone,
                    message: text,
                    pppoe_id: activeContact.pppoe_id,
                    customer_id: activeContact.customer_id,
                    reply_to_id: replyingMessage ? replyingMessage.id : null
                };

                fetch(`{{ route('whatsapp.send') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    if (data.success) {
                        input.value = '';
                        input.style.height = 'auto';
                        cancelReply();
                        showToast('Pesan terkirim ke WhatsApp');
                        loadConversation(activeContact.normalized_phone);
                    } else {
                        alert(data.message || 'Gagal mengirim pesan ke WhatsApp.');
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    alert('Terjadi kesalahan jaringan saat mengirim pesan.');
                });
            }

            function updateMessageApi(msgId, newText) {
                const btn = document.getElementById('btnSendApi');
                const input = document.getElementById('composerInput');
                btn.disabled = true;

                fetch(`{{ url('whatsapp/messages') }}/${msgId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: newText })
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    if (data.success) {
                        input.value = '';
                        input.style.height = 'auto';
                        cancelEdit();
                        loadConversation(activeContact.normalized_phone);
                    } else {
                        alert(data.message || 'Gagal memperbarui pesan.');
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    alert('Terjadi kesalahan jaringan saat mengedit pesan.');
                });
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
                composer.dispatchEvent(new Event('input'));
                document.getElementById('templateDropdown').classList.add('hidden');
            }

            function insertTag(tag) {
                const c = document.getElementById('composerInput');
                const start = c.selectionStart;
                const end = c.selectionEnd;
                c.value = c.value.substring(0, start) + tag + c.value.substring(end);
                c.focus();
                c.selectionStart = c.selectionEnd = start + tag.length;
                c.dispatchEvent(new Event('input'));
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

            function toggleCustomerInfo() {
                const modal = document.getElementById('customerInfoModal');
                modal.classList.toggle('hidden');

                if (!modal.classList.contains('hidden') && activeContact) {
                    const body = document.getElementById('customerInfoBody');
                    body.innerHTML = `
                        <div class="p-2.5 bg-slate-50 border border-slate-200 rounded">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Nama Pelanggan</div>
                            <div class="text-xs font-bold text-slate-900 mt-0.5">${escapeHtml(activeContact.name)}</div>
                            <div class="text-slate-500 font-mono text-[11px]">${activeContact.phone || activeContact.normalized_phone}</div>
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
                let d = String(p).replace(/[^0-9]/g, '');
                if (d.startsWith('08')) d = '62' + d.substring(1);
                else if (d.startsWith('8')) d = '62' + d;
                return d;
            }

            function escapeHtml(t) {
                if (!t) return '';
                return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function formatMsgText(t) {
                if (!t) return '';
                let s = escapeHtml(t);
                s = s.replace(/\*(.*?)\*/g, '<strong>$1</strong>');
                s = s.replace(/_(.*?)_/g, '<em>$1</em>');
                s = s.replace(/`(.*?)`/g, '<code class="bg-black/5 px-1 rounded text-xs font-mono">$1</code>');
                s = s.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-sky-600 hover:underline">$1</a>');
                return s;
            }

            function showToast(msg) {
                const toast = document.createElement('div');
                toast.className = 'fixed bottom-5 right-5 bg-slate-900 text-white text-xs font-semibold px-3 py-2 rounded-lg shadow-lg z-50 transition-opacity';
                toast.textContent = msg;
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 200);
                }, 2200);
            }
        </script>
    @endsection
</x-layouts.app>