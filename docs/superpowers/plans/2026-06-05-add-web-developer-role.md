# Add Web Developer Role Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a new "Web Developer" role with identical permissions to the "Administrator" role.

**Architecture:** Update the user role enum, centralize administrative checks in the User model, and refactor existing checks to use the new centralized logic.

**Tech Stack:** Laravel, PHP, MySQL.

---

### Task 1: Database Migration

**Files:**
- Create: `database/migrations/2026_06_05_100000_add_web_developer_to_jabatan_enum.php`

- [ ] **Step 1: Create the migration file**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, we need to alter the enum
        DB::statement("ALTER TABLE users MODIFY COLUMN jabatan ENUM('Administrator', 'Sales', 'Teknisi', 'Web Developer') DEFAULT 'Teknisi'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN jabatan ENUM('Administrator', 'Sales', 'Teknisi') DEFAULT 'Teknisi'");
    }
};
```

- [ ] **Step 2: Run the migration**

Run: `php artisan migrate`
Expected: Migration table updated.

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_06_05_100000_add_web_developer_to_jabatan_enum.php
git commit -m "database: add Web Developer to jabatan enum"
```

---

### Task 2: Update User Model

**Files:**
- Modify: `app/Models/User.php`

- [ ] **Step 1: Add isAdmin method**

```php
    public function isAdmin(): bool
    {
        return in_array($this->jabatan, ['Administrator', 'Web Developer']);
    }
```

- [ ] **Step 2: Commit**

```bash
git add app/Models/User.php
git commit -m "feat(user): add isAdmin helper method"
```

---

### Task 3: Update Middleware

**Files:**
- Modify: `app/Http/Middleware/EnsureIsAdministrator.php`

- [ ] **Step 1: Update handle method to use isAdmin()**

```php
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return $next($request);
        }
        // ... rest of method
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Middleware/EnsureIsAdministrator.php
git commit -m "refactor(middleware): use isAdmin helper in EnsureIsAdministrator"
```

---

### Task 4: Refactor AccountController

**Files:**
- Modify: `app/Http/Controllers/AccountController.php`

- [ ] **Step 1: Update access checks and statistics**

Replace `auth()->user()->jabatan !== 'Administrator'` with `!auth()->user()->isAdmin()`.
Update `index()` stats:
```php
        $totalAdmin = User::whereIn('jabatan', ['Administrator', 'Web Developer'])->count();
```

- [ ] **Step 2: Update validation in store and update**

Change `'jabatan' => 'required|in:Administrator,Sales,Teknisi'` to `'jabatan' => 'required|in:Administrator,Sales,Teknisi,Web Developer'`.

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/AccountController.php
git commit -m "refactor(accounts): update access checks and role validation for Web Developer"
```

---

### Task 5: Refactor Other Controllers (Batch)

**Files:**
- Modify: `app/Http/Controllers/AssetController.php`
- Modify: `app/Http/Controllers/OltController.php`
- Modify: `app/Http/Controllers/OdpController.php`
- Modify: `app/Http/Controllers/ActivityLogController.php`
- Modify: `app/Http/Controllers/PreOrderController.php`
- Modify: `app/Http/Controllers/CustomerController.php`
- Modify: `app/Http/Controllers/MaintenanceController.php`
- Modify: `app/Http/Controllers/DismantleController.php`

- [ ] **Step 1: Replace hardcoded role checks with isAdmin()**

Search for `jabatan === 'Administrator'` or `jabatan !== 'Administrator'` and replace with `$user->isAdmin()` or `!$user->isAdmin()`.

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/AssetController.php app/Http/Controllers/OltController.php app/Http/Controllers/OdpController.php app/Http/Controllers/ActivityLogController.php app/Http/Controllers/PreOrderController.php app/Http/Controllers/CustomerController.php app/Http/Controllers/MaintenanceController.php app/Http/Controllers/DismantleController.php
git commit -m "refactor: replace hardcoded Administrator checks with isAdmin helper"
```

---

### Task 6: Update Blade Views

**Files:**
- Modify: `resources/views/accounts/modal.blade.php`
- Modify: `resources/views/assets/index.blade.php`
- Modify: `resources/views/monitoring/olt.blade.php`
- Modify: `resources/views/preorders/index.blade.php`

- [ ] **Step 1: Add Web Developer option to accounts modal**

```html
<option value="Web Developer">Web Developer</option>
```

- [ ] **Step 2: Update @if checks in views**

Example for `assets/index.blade.php`:
```php
@php $isAdmin = auth()->user()->isAdmin(); @endphp
```

Example for `monitoring/olt.blade.php`:
```php
@if (auth()->user()->isAdmin())
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/accounts/modal.blade.php resources/views/assets/index.blade.php resources/views/monitoring/olt.blade.php resources/views/preorders/index.blade.php
git commit -m "ui: update views to support Web Developer role"
```

---

### Task 7: Verification

- [ ] **Step 1: Create a test user with Web Developer role**
- [ ] **Step 2: Login as Web Developer and verify access to all admin menus**
- [ ] **Step 3: Verify that Web Developer can manage other accounts**
- [ ] **Step 4: Verify that non-admins (Sales/Teknisi) are still restricted**
