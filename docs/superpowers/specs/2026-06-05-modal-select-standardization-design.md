# Design Spec: Modal Select Standardization

Standardize the UI/UX of `<select>` elements within all popup modals to ensure a consistent and professional appearance throughout the application.

## 1. UI Standard Pattern
Every select input within a modal must follow this exact structure:

```html
<div class="form-group">
    <label class="form-label text-slate-800 font-bold">
        Field Label <span class="text-rose-500">*</span>
    </label>
    <select name="field_name" class="form-input-custom w-full bg-white text-slate-900 border-slate-300" required>
        <option value="">-- Choose Option --</option>
        <!-- options -->
    </select>
</div>
```

### Key Components:
- **Wrapper**: `div.form-group`
- **Label**: `label.form-label.text-slate-800.font-bold`
- **Required Indicator**: `span.text-rose-500` containing `*`.
- **Select Element**:
    - Class: `form-input-custom w-full bg-white text-slate-900 border-slate-300`
    - Logic: Use standard Blade/Laravel attributes (`name`, `id`, `required`, `onchange`, etc.)

## 2. Target Files & Modals

### Accounts Management
- **File**: `resources/views/accounts/modal.blade.php`
- **Fields**: 
    - `jenis_kelamin` (Add/Edit)
    - `jabatan` (Add/Edit)

### Asset Management
- **File**: `resources/views/assets/index.blade.php`
- **Fields**:
    - `type` (Add/Edit Asset)
    - `type` (Transaction Modal)
    - `user_id` (Transaction Modal)

### Maintenance
- **File**: `resources/views/maintenances/index.blade.php`
- **Fields**:
    - `kategori_kendala` (Complete Maintenance Modal)

### ODP Management
- **File**: `resources/views/odp/index.blade.php`
- **Fields**:
    - `jenis_odp` (Add/Edit ODP)

### Pre-Order Management
- **File**: `resources/views/partials/modal_preorders.blade.php`
- **Fields**:
    - `paket_internet` (Add/Edit Pre-Order)

## 3. Implementation Strategy
- Replace legacy `.form-select` classes with `.form-input-custom`.
- Ensure labels have the correct weight and color.
- Remove redundant inline styles or Tailwind utility overrides that conflict with the standard.
- Maintain existing functionality (e.g., `onchange` handlers, validation attributes).
