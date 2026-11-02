<x-layouts.app>
    @section('Css')
        <style>
            .milestone-timeline {
                position: relative;
                padding-left: 2.25rem;
            }
            .milestone-timeline::before {
                content: '';
                position: absolute;
                top: 1.5rem;
                bottom: 1.5rem;
                left: 15px;
                width: 2px;
                background: linear-gradient(180deg, #10b981 0%, #38bdf8 35%, #94a3b8 70%, #cbd5e1 100%);
                border-radius: 9999px;
            }
            .milestone-node {
                position: relative;
                margin-bottom: 2.5rem;
            }
            .milestone-node:last-child {
                margin-bottom: 0;
            }
            .milestone-dot {
                position: absolute;
                left: -2.25rem;
                top: 0.65rem;
                width: 32px;
                height: 32px;
                border-radius: 9999px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fff;
                border: 2px solid #cbd5e1;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
                z-index: 10;
                transition: all 0.25s ease;
            }
            .milestone-node.is-latest .milestone-dot {
                border-color: #10b981;
                background: #ecfdf5;
                color: #059669;
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.18);
            }
            .milestone-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1.25rem 1.5rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
                transition: all 0.2s ease;
            }
            .milestone-card:hover {
                border-color: #cbd5e1;
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
            }
            .milestone-node.is-latest .milestone-card {
                border-color: #a7f3d0;
                box-shadow: 0 4px 16px rgba(16, 185, 129, 0.06);
            }
            .filter-pill {
                padding: 5px 12px;
                border-radius: 9999px;
                font-size: 12px;
                font-weight: 600;
                border: 1px solid #e2e8f0;
                background: #ffffff;
                color: #64748b;
                cursor: pointer;
                transition: all 0.15s ease;
            }
            .filter-pill:hover,
            .filter-pill.active {
                background: #0f172a;
                color: #ffffff;
                border-color: #0f172a;
            }
            @media (max-width: 640px) {
                .milestone-timeline {
                    padding-left: 1.75rem;
                }
                .milestone-timeline::before {
                    left: 11px;
                }
                .milestone-dot {
                    left: -1.75rem;
                    width: 24px;
                    height: 24px;
                    font-size: 10px;
                }
                .milestone-card {
                    padding: 1rem;
                }
            }
        </style>
    @endsection

    <!-- Header Title -->
    <div class="flex items-start justify-between gap-4 mb-6 flex-wrap">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700">
                    <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight m-0">Riwayat Versi & Milestone</h1>
                    <p class="text-xs text-slate-500 m-0 mt-0.5">Catatan perkembangan sistem, pembaruan fitur, optimasi, dan penyelarasan antarmuka Erlangga Net.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Versi Saat Ini: v2.5.0
            </span>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 mb-6">
        <div class="p-3.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Rilis Aktif</div>
            <div class="text-base font-extrabold text-slate-900 flex items-center gap-1.5">
                <span>v2.5.0</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">Stable</span>
            </div>
        </div>
        <div class="p-3.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Total Milestone</div>
            <div class="text-base font-extrabold text-slate-900">{{ count($milestones) }} Milestone Rilis</div>
        </div>
        <div class="p-3.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Bulan Pembaruan</div>
            <div class="text-base font-extrabold text-slate-900">November 2026</div>
        </div>
        <div class="p-3.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Arsitektur Inti</div>
            <div class="text-base font-extrabold text-slate-900 truncate">Laravel 11 • Radius</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs mb-6 flex flex-col md:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0" id="versionFilters">
            <button type="button" class="filter-pill active shrink-0" onclick="filterMilestone('all', this)">Semua Versi</button>
            @foreach($milestones as $m)
                <button type="button" class="filter-pill shrink-0" onclick="filterMilestone('{{ $m['version'] }}', this)">{{ $m['version'] }}</button>
            @endforeach
        </div>
        <div class="w-full md:w-72">
            <div class="search-wrap !max-w-full">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="search" id="changelogSearch" class="search-input !h-8.5 text-xs"
                    placeholder="Cari perubahan, fitur, modul..." autocomplete="off" oninput="searchChangelogs(this.value)">
            </div>
        </div>
    </div>

    <!-- Milestone Vertical Timeline -->
    <div class="milestone-timeline">
        @foreach($milestones as $m)
            <div class="milestone-node {{ $m['highlight'] ? 'is-latest' : '' }}" data-version="{{ $m['version'] }}">
                <!-- Spine Node Dot -->
                <div class="milestone-dot">
                    @if($m['highlight'])
                        <i class="fa-solid fa-sparkles text-xs"></i>
                    @else
                        <i class="fa-solid fa-tag text-[10px] text-slate-400"></i>
                    @endif
                </div>

                <!-- Milestone Content Card -->
                <div class="milestone-card">
                    <!-- Release Header Info -->
                    <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-base font-black font-mono tracking-tight text-slate-900 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                                {{ $m['version'] }}
                            </span>
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-md border {{ $m['badge_color'] }}">
                                {{ $m['badge'] }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar text-slate-400 text-xs"></i>
                            <span>{{ $m['date'] }}</span>
                        </div>
                    </div>

                    <!-- Milestone Title & Summary -->
                    <div class="mt-3">
                        <h2 class="text-sm font-bold text-slate-900 mb-1 leading-snug">{{ $m['title'] }}</h2>
                        <p class="text-xs text-slate-600 leading-relaxed m-0">{{ $m['summary'] }}</p>
                    </div>

                    <!-- Milestone Change Sections -->
                    <div class="mt-4 space-y-4">
                        @foreach($m['sections'] as $sec)
                            <div class="changelog-section">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded border {{ $sec['badge_color'] }}">
                                        {{ $sec['badge'] }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-800">{{ $sec['name'] }}</span>
                                </div>
                                <ul class="m-0 pl-0 list-none space-y-1.5 text-xs text-slate-700">
                                    @foreach($sec['items'] as $item)
                                        <li class="flex items-start gap-2 leading-relaxed changelog-item">
                                            <i class="fa-solid fa-check text-[10px] text-emerald-600 mt-1 shrink-0"></i>
                                            <div>{!! preg_replace('/`([^`]+)`/', '<code class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-800 font-mono text-[11px]">$1</code>', htmlspecialchars($item, ENT_NOQUOTES)) !!}</div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Empty state for search -->
    <div id="noResultsState" class="hidden text-center py-12 bg-white rounded-xl border border-slate-200 shadow-2xs mt-4">
        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-lg mb-2">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <div class="text-sm font-bold text-slate-800">Tidak ada catatan perubahan ditemukan</div>
        <div class="text-xs text-slate-400 mt-1">Coba gunakan kata kunci pencarian yang lain.</div>
    </div>

    @section('Scripts')
        <script>
            let currentFilterVersion = 'all';

            function filterMilestone(version, btn) {
                currentFilterVersion = version;
                document.querySelectorAll('#versionFilters .filter-pill').forEach(el => el.classList.remove('active'));
                if (btn) btn.classList.add('active');

                applyFilters();
            }

            function searchChangelogs(keyword) {
                applyFilters();
            }

            function applyFilters() {
                const keyword = document.getElementById('changelogSearch').value.toLowerCase().trim();
                const nodes = document.querySelectorAll('.milestone-node');
                let visibleCount = 0;

                nodes.forEach(node => {
                    const version = node.getAttribute('data-version');
                    const text = node.innerText.toLowerCase();

                    const matchesVersion = (currentFilterVersion === 'all' || version === currentFilterVersion);
                    const matchesKeyword = (keyword === '' || text.includes(keyword));

                    if (matchesVersion && matchesKeyword) {
                        node.style.display = 'block';
                        visibleCount++;
                    } else {
                        node.style.display = 'none';
                    }
                });

                const noResults = document.getElementById('noResultsState');
                if (visibleCount === 0) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            }
        </script>
    @endsection
</x-layouts.app>
