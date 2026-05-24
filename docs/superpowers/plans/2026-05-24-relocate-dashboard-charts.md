# Relocate Maintenance & Dismantle Charts to Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Move trend and top technician charts for both Maintenance and Dismantle from their index pages to the Dashboard, visible only to Administrators.

**Architecture:** Update `DashboardController` to include stats logic for Admin, update `dashboard.blade.php` with 4 new charts, and remove old logic/UI from `MaintenanceController`, `DismantleController`, and their views.

**Tech Stack:** Laravel, Chart.js, Carbon.

---

### Task 1: Update DashboardController with Maintenance & Dismantle Stats

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`

- [ ] **Step 1: Add necessary models at the top of the file**
Include `Maintenance` and `Dismantle` models.

- [ ] **Step 2: Update index method to fetch maintenance and dismantle stats for Administrators**
Add the logic inside the `if ($user->jabatan === 'Administrator')` block or a similar check.

```php
// Inside DashboardController@index
$mChartLabels = []; $mChartValues = [];
$mDonutLabels = []; $mDonutValues = [];
$dChartLabels = []; $dChartValues = [];
$dDonutLabels = []; $dDonutValues = [];

if ($user->jabatan === 'Administrator') {
    $thisMonth = Carbon::now()->month;
    $thisYear = Carbon::now()->year;

    // Maintenance Trends (7 Days)
    for ($i = 6; $i >= 0; $i--) {
        $date = Carbon::now()->subDays($i);
        $mChartLabels[] = $date->translatedFormat('d M');
        $mChartValues[] = \App\Models\Maintenance::whereDate('created_at', $date->toDateString())->count();
    }

    // Maintenance Top Teknisi
    $mTop = \App\Models\Maintenance::where('status', 'Selesai')
        ->whereMonth('completed_at', $thisMonth)
        ->whereYear('completed_at', $thisYear)
        ->with('teknisiEksekutor')
        ->get()
        ->groupBy('id_teknisi_eksekutor')
        ->map(fn($g) => ['name' => $g->first()->teknisiEksekutor->nama_lengkap ?? 'Unknown', 'count' => $g->count()])
        ->sortByDesc('count')->take(5);
    $mDonutLabels = $mTop->pluck('name')->toArray();
    $mDonutValues = $mTop->pluck('count')->toArray();

    // Dismantle Trends (7 Days)
    for ($i = 6; $i >= 0; $i--) {
        $date = Carbon::now()->subDays($i);
        $dChartLabels[] = $date->translatedFormat('d M');
        $dChartValues[] = \App\Models\Dismantle::whereDate('created_at', $date->toDateString())->count();
    }

    // Dismantle Top Teknisi
    $dTop = \App\Models\Dismantle::where('status', 'Selesai')
        ->whereMonth('completed_at', $thisMonth)
        ->whereYear('completed_at', $thisYear)
        ->with('teknisiEksekutor')
        ->get()
        ->groupBy('id_teknisi_eksekutor')
        ->map(fn($g) => ['name' => $g->first()->teknisiEksekutor->nama_lengkap ?? 'Unknown', 'count' => $g->count()])
        ->sortByDesc('count')->take(5);
    $dDonutLabels = $dTop->pluck('name')->toArray();
    $dDonutValues = $dTop->pluck('count')->toArray();
}

return view('dashboard', compact(
    // ... existing vars ...
    'mChartLabels', 'mChartValues', 'mDonutLabels', 'mDonutValues',
    'dChartLabels', 'dChartValues', 'dDonutLabels', 'dDonutValues'
));
```

- [ ] **Step 3: Commit changes**
```bash
git add app/Http/Controllers/DashboardController.php
git commit -m "feat(dashboard): add maintenance and dismantle stats logic for admin"
```

---

### Task 2: Update dashboard.blade.php UI & Scripts

**Files:**
- Modify: `resources/views/dashboard.blade.php`

- [ ] **Step 1: Add chart containers in a new section for Administrators**
Insert before "Aktivitas Terbaru".

- [ ] **Step 2: Add Chart.js scripts and initialization**
Include the 4 new charts.

- [ ] **Step 3: Commit changes**
```bash
git add resources/views/dashboard.blade.php
git commit -m "feat(dashboard): add maintenance and dismantle charts UI and scripts"
```

---

### Task 3: Cleanup MaintenanceController and view

**Files:**
- Modify: `app/Http/Controllers/MaintenanceController.php`
- Modify: `resources/views/maintenances/index.blade.php`

- [ ] **Step 1: Remove stats logic from MaintenanceController**
Remove the trend and top teknisi loops/queries.

- [ ] **Step 2: Remove charts from maintenance index view**
Remove the canvas elements and the script section initializing the charts.

- [ ] **Step 3: Commit changes**
```bash
git add app/Http/Controllers/MaintenanceController.php resources/views/maintenances/index.blade.php
git commit -m "refactor(maintenance): remove redundant charts and stats logic"
```

---

### Task 4: Cleanup DismantleController and view

**Files:**
- Modify: `app/Http/Controllers/DismantleController.php`
- Modify: `resources/views/dismantles/index.blade.php`

- [ ] **Step 1: Remove stats logic from DismantleController**
Remove the trend and top teknisi loops/queries.

- [ ] **Step 2: Remove charts from dismantle index view**
Remove the canvas elements and the script section initializing the charts.

- [ ] **Step 3: Commit changes**
```bash
git add app/Http/Controllers/DismantleController.php resources/views/dismantles/index.blade.php
git commit -m "refactor(dismantle): remove redundant charts and stats logic"
```
