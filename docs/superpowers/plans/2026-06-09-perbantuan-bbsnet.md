# Perbantuan BBSNET Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement a new module "Perbantuan BBSNET" to track technician assistance in various activities with statistics and PDF export.

**Architecture:** Use a single table `supporting_activities` with a JSON column for partners, following existing project patterns.

**Tech Stack:** PHP (Laravel), MySQL, Blade, TailwindCSS (for layout), Barryvdh/Laravel-DomPDF.

---

### Task 1: Database Migration

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_supporting_activities_table.php`

- [ ] **Step 1: Create migration file**
Run: `php artisan make:migration create_supporting_activities_table`

- [ ] **Step 2: Define table schema**
Modify the migration file:
```php
Schema::create('supporting_activities', function (Blueprint $table) {
    $table->id();
    $table->enum('activity_type', ['PO', 'Pemeliharaan', 'Buat ODP', 'Aktivasi Jalur']);
    $table->text('description')->nullable();
    $table->foreignId('main_technician_id')->constrained('users');
    $table->json('partner_ids')->nullable();
    $table->string('photo')->nullable();
    $table->date('activity_date');
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});
```

- [ ] **Step 3: Run migration**
Run: `php artisan migrate`

- [ ] **Step 4: Commit**
```bash
git add database/migrations/*_create_supporting_activities_table.php
git commit -m "feat: add supporting_activities table migration"
```

---

### Task 2: Model Implementation

**Files:**
- Create: `app/Models/SupportingActivity.php`

- [ ] **Step 1: Create Model with relationships**
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportingActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_type',
        'description',
        'main_technician_id',
        'partner_ids',
        'photo',
        'activity_date',
        'created_by'
    ];

    protected $casts = [
        'partner_ids' => 'array',
        'activity_date' => 'date',
    ];

    public function mainTechnician()
    {
        return $this->belongsTo(User::class, 'main_technician_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
```

- [ ] **Step 2: Commit**
```bash
git add app/Models/SupportingActivity.php
git commit -m "feat: add SupportingActivity model"
```

---

### Task 3: Controller Implementation (Part 1 - Index & Stats)

**Files:**
- Create: `app/Http/Controllers/SupportingActivityController.php`

- [ ] **Step 1: Implement index method and stats logic**
```php
namespace App\Http\Controllers;

use App\Models\SupportingActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;

class SupportingActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportingActivity::with(['mainTechnician', 'createdBy']);

        // Filters
        if ($request->filled('start_date')) {
            $query->whereDate('activity_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('activity_date', '<=', $request->end_date);
        }
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $activities = $query->orderBy('activity_date', 'desc')->paginate(15);
        $technicians = User::where('jabatan', 'Teknisi')->get();

        // Calculate Stats
        $stats = $this->calculateStats($request);

        return view('supporting.index', compact('activities', 'technicians', 'stats'));
    }

    private function calculateStats(Request $request)
    {
        $all = SupportingActivity::all();
        $scoreboard = [];

        foreach ($all as $act) {
            // Main Tech
            $mid = $act->main_technician_id;
            $scoreboard[$mid] = ($scoreboard[$mid] ?? 0) + 1;

            // Partners
            if ($act->partner_ids) {
                foreach ($act->partner_ids as $pid) {
                    $scoreboard[$pid] = ($scoreboard[$pid] ?? 0) + 1;
                }
            }
        }

        // Map to names
        $techs = User::whereIn('id', array_keys($scoreboard))->get();
        $mappedScoreboard = $techs->map(function($t) use ($scoreboard) {
            return [
                'name' => $t->nama_lengkap,
                'count' => $scoreboard[$t->id]
            ];
        })->sortByDesc('count')->values();

        return [
            'total' => $all->count(),
            'scoreboard' => $mappedScoreboard
        ];
    }
}
```

- [ ] **Step 2: Commit**
```bash
git add app/Http/Controllers/SupportingActivityController.php
git commit -m "feat: implement SupportingActivityController index and stats"
```

---

### Task 4: Controller Implementation (Part 2 - CRUD & Export)

**Files:**
- Modify: `app/Http/Controllers/SupportingActivityController.php`

- [ ] **Step 1: Implement store, update, destroy, and export**
```php
    public function store(Request $request)
    {
        $validated = $request->validate([
            'activity_type' => 'required|in:PO,Pemeliharaan,Buat ODP,Aktivasi Jalur',
            'activity_date' => 'required|date',
            'main_technician_id' => 'required|exists:users,id',
            'partner_ids' => 'nullable|array',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('supporting_activities', 'public');
        }

        $validated['created_by'] = auth()->id();
        SupportingActivity::create($validated);

        return back()->with('success', 'Data perbantuan berhasil disimpan.');
    }

    public function export(Request $request)
    {
        $activities = SupportingActivity::with(['mainTechnician', 'createdBy'])
            ->whereBetween('activity_date', [$request->start_date, $request->end_date])
            ->get();

        $pdf = PDF::loadView('supporting.export', compact('activities'));
        return $pdf->download('laporan-perbantuan.pdf');
    }
```

- [ ] **Step 2: Commit**
```bash
git add app/Http/Controllers/SupportingActivityController.php
git commit -m "feat: complete CRUD and export in SupportingActivityController"
```

---

### Task 5: Routes & View Scaffolding

**Files:**
- Modify: `routes/web.php`
- Create: `resources/views/supporting/index.blade.php`
- Create: `resources/views/supporting/modal.blade.php`

- [ ] **Step 1: Add routes**
```php
use App\Http\Controllers\SupportingActivityController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('supporting', SupportingActivityController::class);
    Route::get('supporting-export', [SupportingActivityController::class, 'export'])->name('supporting.export');
});
```

- [ ] **Step 2: Create basic view structure**
Create `resources/views/supporting/index.blade.php` using the `x-layouts.app` component, copying styles from `monitoring/index.blade.php`.

- [ ] **Step 3: Implement table and stats in index.blade.php**
Ensure the table has headers: Tanggal, Tipe, Teknisi Utama, Partner, Keterangan, Foto, Aksi.

- [ ] **Step 4: Implement modal.blade.php**
Include a form with:
- Select `activity_type`
- Date `activity_date`
- Select `main_technician_id`
- Checkbox list for `partner_ids[]` (scrollable div style from `customers.index`)
- Textarea `description`
- File `photo`

- [ ] **Step 5: Commit**
```bash
git add routes/web.php resources/views/supporting/*
git commit -m "feat: add routes and views for supporting module"
```

---

### Task 6: Final Polish & PDF View

**Files:**
- Create: `resources/views/supporting/export.blade.php`

- [ ] **Step 1: Create PDF export template**
Use a simple HTML table for PDF generation.

- [ ] **Step 2: Add Live Search JS**
In `supporting/index.blade.php`, add a small script to filter table rows locally based on the search input.

- [ ] **Step 3: Verification**
Test adding, editing, deleting, and exporting. Verify stats cards update correctly.

- [ ] **Step 4: Commit**
```bash
git add resources/views/supporting/export.blade.php resources/views/supporting/index.blade.php
git commit -m "feat: finalize supporting module with PDF and live search"
```
