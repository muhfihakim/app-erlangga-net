<x-layouts.app>
    @section('Css')
        <style>
            .version-timeline {
                position: relative;
                padding-left: 2rem;
            }
            .version-timeline::before {
                content: '';
                position: absolute;
                top: 1.25rem;
                bottom: 1.25rem;
                left: 15px;
                width: 2px;
                background: #e2e8f0;
            }
            .version-node {
                position: relative;
                margin-bottom: 2rem;
            }
            .version-node:last-child {
                margin-bottom: 0;
            }
            .version-dot {
                position: absolute;
                left: -2rem;
                top: 0.85rem;
                width: 32px;
                height: 32px;
                border-radius: 9999px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #ffffff;
                border: 2px solid #cbd5e1;
                color: #64748b;
                z-index: 2;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            }
            .version-node.is-latest .version-dot {
                border-color: #10b981;
                background: #f0fdf4;
                color: #059669;
            }
            .version-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 1.25rem 1.5rem;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            }
            .version-node.is-latest .version-card {
                border-color: #cbd5e1;
            }
            .filter-pill {
                padding: 4px 12px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 500;
                border: 1px solid #e2e8f0;
                background: #ffffff;
                color: #475569;
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
                .version-timeline {
                    padding-left: 1.5rem;
                }
                .version-timeline::before {
                    left: 11px;
                }
                .version-dot {
                    left: -1.5rem;
                    width: 24px;
                    height: 24px;
                    font-size: 10px;
                }
                .version-card {
                    padding: 1rem;
                }
            }
        </style>
    @endsection

    <!-- Header Section -->
    <div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700">
                    <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight m-0">Riwayat Versi</h1>
                    <p class="text-xs text-slate-500 m-0 mt-0.5">Catatan rilis dan riwayat pembaruan sistem berdasarkan repositori GitHub.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Versi Terkini: v1.3.0
            </span>
        </div>
    </div>

    <!-- Repository Metadata Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
        <div class="p-3 bg-white rounded-lg border border-slate-200">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-1">Rilis Terbaru</div>
            <div class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                <span class="font-mono">v1.3.0</span>
                <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 border border-slate-200 font-mono">b92caa6</span>
            </div>
        </div>
        <div class="p-3 bg-white rounded-lg border border-slate-200">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-1">Tag Stabil</div>
            <div class="text-sm font-bold text-slate-900 font-mono truncate">v1.0.0-monolith-stable</div>
        </div>
        <div class="p-3 bg-white rounded-lg border border-slate-200">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-1">Total Catatan Rilis</div>
            <div class="text-sm font-bold text-slate-900">{{ count($releases) }} Rilis Resmi</div>
        </div>
        <div class="p-3 bg-white rounded-lg border border-slate-200">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-1">Repositori Git</div>
            <div class="text-sm font-bold text-slate-800 font-mono text-xs truncate">muhfihakim/app-erlangga-net</div>
        </div>
    </div>

    <!-- Toolbar Filters & Search -->
    <div class="p-3 bg-white rounded-lg border border-slate-200 mb-5 flex flex-col md:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0" id="versionFilters">
            <button type="button" class="filter-pill active shrink-0" onclick="filterVersion('all', this)">Semua Versi</button>
            @foreach($releases as $rel)
                <button type="button" class="filter-pill shrink-0 font-mono" onclick="filterVersion('{{ $rel['version'] }}', this)">{{ $rel['version'] }}</button>
            @endforeach
        </div>
        <div class="w-full md:w-72">
            <div class="search-wrap !max-w-full">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="search" id="changelogSearch" class="search-input !h-8.5 text-xs"
                    placeholder="Cari fitur, commit, atau modul..." autocomplete="off" oninput="searchChangelogs(this.value)">
            </div>
        </div>
    </div>

    <!-- Version Timeline -->
    <div class="version-timeline">
        @foreach($releases as $rel)
            <div class="version-node {{ $rel['is_current'] ? 'is-latest' : '' }}" data-version="{{ $rel['version'] }}">
                <!-- Spine Node Dot -->
                <div class="version-dot">
                    <i class="fa-solid fa-code-commit text-xs"></i>
                </div>

                <!-- Release Content Card -->
                <div class="version-card">
                    <!-- Release Header Info -->
                    <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-bold font-mono tracking-tight text-slate-900 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200">
                                {{ $rel['version'] }}
                            </span>
                            <span class="text-xs font-mono text-slate-500 bg-slate-50 px-2 py-0.5 rounded border border-slate-200">
                                Commit: {{ $rel['commit'] }}
                            </span>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded border {{ $rel['is_current'] ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                {{ $rel['tag_label'] }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar text-slate-400 text-xs"></i>
                            <span>{{ $rel['date'] }}</span>
                        </div>
                    </div>

                    <!-- Title & Summary -->
                    <div class="mt-3">
                        <h2 class="text-sm font-bold text-slate-900 mb-1 leading-snug">{{ $rel['title'] }}</h2>
                        <p class="text-xs text-slate-600 leading-relaxed m-0">{{ $rel['description'] }}</p>
                    </div>

                    <!-- Changelog Detail Sections -->
                    <div class="mt-4 space-y-3.5">
                        @foreach($rel['sections'] as $sec)
                            <div class="changelog-group">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border {{ $sec['badge_color'] }}">
                                        {{ $sec['badge'] }}
                                    </span>
                                    <span class="text-xs font-semibold text-slate-800">{{ $sec['name'] }}</span>
                                </div>
                                <ul class="m-0 pl-0 list-none space-y-1.5 text-xs text-slate-700">
                                    @foreach($sec['items'] as $item)
                                        <li class="flex items-start gap-2 leading-relaxed changelog-item">
                                            <i class="fa-solid fa-circle-check text-[11px] text-slate-400 mt-1 shrink-0"></i>
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

    <!-- Empty State -->
    <div id="noResultsState" class="hidden text-center py-10 bg-white rounded-lg border border-slate-200 mt-4">
        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-base mb-2">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <div class="text-sm font-semibold text-slate-800">Tidak ada riwayat rilis ditemukan</div>
        <div class="text-xs text-slate-400 mt-0.5">Coba gunakan kata kunci pencarian yang lain.</div>
    </div>

    @section('Scripts')
        <script>
            let currentFilterVersion = 'all';

            function filterVersion(version, btn) {
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
                const nodes = document.querySelectorAll('.version-node');
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
