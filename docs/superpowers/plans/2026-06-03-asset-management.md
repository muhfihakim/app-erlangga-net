# Asset Management Module Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement a full-featured Asset and Inventory management system to track items like ONT and cables, recording incoming/outgoing movements with automated stock updates.

**Architecture:** 
- **Database:** `assets` (master) and `asset_transactions` (history) tables.
- **Logic:** Eloquent Models with automated stock calculation in Controllers using DB Transactions.
- **UI:** Blade templates with DataTables, Modals, and Summary Cards consistent with existing modules.

**Tech Stack:** Laravel 12.x, MySQL, JavaScript/jQuery, DataTables.

---

### Task 1: Database Foundation

**Files:**
- Create: `database/migrations/YYYY_MM_DD_create_assets_table.php`
- Create: `database/migrations/YYYY_MM_DD_create_asset_transactions_table.php`
- Create: `app/Models/Asset.php`
- Create: `app/Models/AssetTransaction.php`

- [ ] **Step 1: Create Asset migration**
Define `name`, `type`, `unit`, `stock`, `description`.

- [ ] **Step 2: Create AssetTransaction migration**
Define `asset_id` (FK), `type` (Masuk/Keluar), `quantity`, `date`, `user_id` (FK, Nullable), `external_person_name` (Nullable), `description`.

- [ ] **Step 3: Implement Asset Model**
Add fillable and relationship to `transactions`.

- [ ] **Step 4: Implement AssetTransaction Model**
Add fillable and relationships to `asset` and `user`.

- [ ] **Step 5: Run migrations**
`php artisan migrate`

- [ ] **Step 6: Commit database setup**
```bash
git add database/app Models/
git commit -m "chore: setup database tables and models for asset management"
```

---

### Task 2: Backend Logic & Routes

**Files:**
- Create: `app/Http/Controllers/AssetController.php`
- Create: `app/Http/Controllers/Api/AssetController.php`
- Modify: `routes/web.php`
- Modify: `routes/api.php`

- [ ] **Step 1: Define Web Routes**
Add routes for Assets and Transactions inside `auth.custom` middleware group.

- [ ] **Step 2: Implement AssetController@index**
Fetch assets, calculate statistics for cards (Total items, total stock, items below threshold).

- [ ] **Step 3: Implement Asset Management (CRUD)**
Methods for `store`, `update`, `destroy` for the master asset data.

- [ ] **Step 4: Implement Transaction Logic**
Create a method to store transactions. **Crucial:** Use `DB::transaction` to update `assets.stock` whenever a transaction is recorded. Add validation to prevent outgoing quantity > current stock.

- [ ] **Step 5: Implement API Controller**
Expose stock levels and transaction history for mobile app integration.

- [ ] **Step 6: Commit backend logic**
```bash
git add app/Http/Controllers routes/
git commit -m "feat(asset): implement controller logic and routes"
```

---

### Task 3: UI/UX - Asset Dashboard & Master Data

**Files:**
- Create: `resources/views/assets/index.blade.php`
- Modify: `resources/views/components/layouts/sidebar.blade.php`

- [ ] **Step 1: Create Asset Index View**
Implement Summary Cards and DataTables for the Asset list.

- [ ] **Step 2: Implement Add/Edit Asset Modals**
Create modals consistent with the project's design language.

- [ ] **Step 3: Update Sidebar**
Add "Pengelolaan Aset" menu link (Role protected: Admin & Teknisi).

- [ ] **Step 4: Commit UI part 1**
```bash
git add resources/views/
git commit -m "feat(asset): add master asset management UI and sidebar link"
```

---

### Task 4: UI/UX - Transactions & History

**Files:**
- Create: `resources/views/assets/transactions.blade.php`
- Create: `resources/views/assets/modal_transaction.blade.php`

- [ ] **Step 1: Create Transaction History View**
List of all asset movements with filters (Type, Date Range).

- [ ] **Step 2: Create Dynamic Transaction Modal**
A single modal that adapts fields based on "Masuk" or "Keluar" selection.
- Masuk -> Field "Pengirim" (Text).
- Keluar -> Field "Pengambil" (Dropdown Users + Manual Text).

- [ ] **Step 3: Implement Client-Side Validation**
Ensure the new forms are compatible with the global validation system.

- [ ] **Step 4: Commit UI part 2**
```bash
git add resources/views/assets/
git commit -m "feat(asset): implement transaction logging UI and history"
```

---

### Task 5: Reporting & Verification

**Files:**
- Modify: `app/Http/Controllers/AssetController.php`

- [ ] **Step 1: Implement Export functionality**
Add method to download CSV/Excel report of transactions.

- [ ] **Step 2: Final Verification**
Test the full flow: Create Asset -> Add "Masuk" (Verify stock increase) -> Add "Keluar" (Verify stock decrease) -> Test validation (Outgoing > Stock).

- [ ] **Step 3: Push to main**
```bash
git push origin main
```
