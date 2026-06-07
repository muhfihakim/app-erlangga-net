# Modal Select Standardization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Standardize all `<select>` inputs within modals to use the `form-input-custom` styling and consistent label weights.

**Architecture:** Surgical updates to Blade template files to align with the project's premium UI/UX standard.

**Tech Stack:** Blade (Laravel), CSS (Tailwind/Custom).

---

### Task 1: Accounts Modal

**Files:**
- Modify: `resources/views/accounts/modal.blade.php`

- [ ] **Step 1: Update Add Account Selects**
Update `jenis_kelamin` and `jabatan` selects in the `#addUserModal`.
```html
<select class="form-input-custom w-full bg-white text-slate-900 border-slate-300" name="jenis_kelamin">
```

- [ ] **Step 2: Update Edit Account Selects**
Update `edit_jenis_kelamin` and `edit_jabatan` selects in the `#editModal`.

- [ ] **Step 3: Update Labels**
Ensure labels have `text-slate-800 font-bold`.

- [ ] **Step 4: Commit**
`git add resources/views/accounts/modal.blade.php && git commit -m "style: standardize select inputs in accounts modal"`

---

### Task 2: Assets Index Modal

**Files:**
- Modify: `resources/views/assets/index.blade.php`

- [ ] **Step 1: Update Add/Edit Asset Type**
Update `type` select in `#addAssetModal` and `#editAssetModal`.

- [ ] **Step 2: Update Transaction Modal Selects**
Update `type` and `user_id` in `#transactionModal`.

- [ ] **Step 3: Commit**
`git add resources/views/assets/index.blade.php && git commit -m "style: standardize select inputs in asset modals"`

---

### Task 3: Maintenance Index Modal

**Files:**
- Modify: `resources/views/maintenances/index.blade.php`

- [ ] **Step 1: Update Complete Maintenance Kategori**
Update `kategori_kendala` select in the completion modal (near line 607).

- [ ] **Step 2: Commit**
`git add resources/views/maintenances/index.blade.php && git commit -m "style: standardize select inputs in maintenance modals"`

---

### Task 4: ODP Index Modal

**Files:**
- Modify: `resources/views/odp/index.blade.php`

- [ ] **Step 1: Update Jenis ODP Select**
Update `jenis_odp` in `#addOdpModal` and `#editOdpModal`.

- [ ] **Step 2: Commit**
`git add resources/views/odp/index.blade.php && git commit -m "style: standardize select inputs in ODP modals"`

---

### Task 5: Pre-Order Modal Partial

**Files:**
- Modify: `resources/views/partials/modal_preorders.blade.php`

- [ ] **Step 1: Update Paket Internet Select**
Update `paket_internet` in add and edit modals.

- [ ] **Step 2: Commit**
`git add resources/views/partials/modal_preorders.blade.php && git commit -m "style: standardize select inputs in pre-order modals"`

---

### Task 6: Final Verification

- [ ] **Step 1: Visual Check**
Manually verify that all selects have the consistent "white background, indigo/slate border, bold label" appearance.
- [ ] **Step 2: Functional Check**
Verify that all selects still correctly capture data on save/update.
