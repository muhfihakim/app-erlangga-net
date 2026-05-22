<!-- Top bar -->
<div class="topbar">
    <div style="display:flex;align-items:center;gap:8px;">
        <button class="mobile-menu-btn" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="topbar-breadcrumb">
            <i class="fa-solid fa-house text-xs"></i>
            <span class="sep">/</span>
            <span class="current">Erlangga Network</span>
        </div>
    </div>
    <div class="topbar-actions">
        <button class="topbar-icon-btn" title="Mode gelap" onclick="toggleTheme()">
            <i class="fa-regular fa-moon" id="theme-icon"></i>
        </button>
        <div class="flex items-center gap-3 ml-2">
            <div class="text-right hidden sm:block">
                <p class="text-xs user-name leading-none">{{ auth()->user()->nama_lengkap ?? 'Pengguna' }}</p>
                <p class="text-[10px] user-role mt-1">{{ auth()->user()->jabatan ?? 'Jabatan' }}</p>
            </div>
            <div class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 dark:text-slate-500">
                <i class="fa-solid fa-user"></i>
            </div>
        </div>
    </div>
</div>
