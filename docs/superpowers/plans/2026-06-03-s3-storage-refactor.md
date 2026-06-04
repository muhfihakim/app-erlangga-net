# S3 Storage Refactoring Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor application storage logic to support S3-compatible providers (Rustfs) via environment configuration.

**Architecture:** Utilize Laravel's `Storage` facade as the abstraction layer. Switch from hardcoded `asset('storage/...')` to `Storage::url()` in views and remove hardcoded disk names in controllers to allow switching via `FILESYSTEM_DISK`.

**Tech Stack:** Laravel 12.x, AWS S3 SDK (Flysystem), PHP 8.4.

---

### Task 1: Infrastructure & Environment Setup

**Files:**
- Modify: `composer.json`
- Modify: `.env.example`
- Modify: `config/filesystems.php`

- [ ] **Step 1: Install S3 adapter**
Run: `composer require league/flysystem-aws-s3-v3`

- [ ] **Step 2: Update .env.example with S3 variables**
Add standard AWS variables to `.env.example`.

- [ ] **Step 3: Update config/filesystems.php**
Ensure `s3` disk is configured correctly with `public` visibility.
```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    'visibility' => 'public',
],
```

- [ ] **Step 4: Commit setup**
```bash
git add composer.json composer.lock .env.example config/filesystems.php
git commit -m "chore: setup S3 storage dependencies and configuration"
```

---

### Task 2: Refactor Controllers (Write Operations)

**Files:**
- Modify: `app/Http/Controllers/OdpController.php`
- Modify: `app/Http/Controllers/PreOrderController.php`
- Modify: `app/Http/Controllers/MaintenanceController.php`
- Modify: `app/Http/Controllers/CustomerController.php`
- Modify: `app/Http/Controllers/DismantleController.php`
- Modify: `app/Http/Controllers/Api/*.php` (Odp, PreOrder, Maintenance, Customer, Dismantle)

- [ ] **Step 1: Refactor OdpController**
Remove `'public'` disk parameter from `store()` and `delete()` calls.
```php
// Old
$request->file('foto_odp')->store('odp-images', 'public');
Storage::disk('public')->delete($path);

// New
$request->file('foto_odp')->store('odp-images');
Storage::delete($path);
```

- [ ] **Step 2: Refactor PreOrderController**
Repeat same logic for PreOrder uploads and deletions.

- [ ] **Step 3: Refactor MaintenanceController**
Update maintenance issue, before, after, speedtest, and selfie uploads.

- [ ] **Step 4: Refactor CustomerController**
Update activation photo uploads.

- [ ] **Step 5: Refactor DismantleController**
Update device and house photo uploads.

- [ ] **Step 6: Update API Controllers**
Apply same changes to all controllers under `app/Http/Controllers/Api/`.

- [ ] **Step 7: Verify with local tests**
Run: `./vendor/bin/phpunit`
Expected: Tests should pass using local storage (current default).

- [ ] **Step 8: Commit controller changes**
```bash
git commit -am "refactor(storage): use default disk in controllers for S3 compatibility"
```

---

### Task 3: Refactor Views (Read Operations)

**Files:**
- Modify: `resources/views/customers/active_list.blade.php`
- Modify: `resources/views/maintenances/index.blade.php`
- Modify: `resources/views/preorders/index.blade.php`
- Modify: `resources/views/odp/index.blade.php`
- Modify: `resources/views/activitylog/dismantle-log.blade.php`
- Modify: `resources/views/dismantles/index.blade.php`

- [ ] **Step 1: Update customer views**
Replace `asset('storage/' . $path)` with `Storage::url($path)`.

- [ ] **Step 2: Update maintenance views**
Replace hardcoded storage assets in modals and lists.

- [ ] **Step 3: Update preorder and ODP views**
Ensure all dynamic images use `Storage::url()`.

- [ ] **Step 4: Update activity logs and dismantle views**
Final sweep of remaining `asset('storage/...')` calls.

- [ ] **Step 5: Commit view changes**
```bash
git commit -am "refactor(ui): use Storage::url() in views to support cloud storage"
```

---

### Task 4: Final Verification

- [ ] **Step 1: Check for any remaining hardcoded strings**
Run: `grep -r "asset('storage/" resources/views/`
Expected: No results.

- [ ] **Step 2: Check for remaining explicit 'public' disk usage**
Run: `grep -r "Storage::disk('public')" app/`
Expected: Minimal or zero results (verify if any are intentional).

- [ ] **Step 3: Final test run**
Run: `./vendor/bin/phpunit`
Expected: PASS.

- [ ] **Step 4: Commit final verification**
```bash
git commit -m "test(storage): verify S3-ready refactoring passes existing tests"
```
