# Design Spec: Add Web Developer Role

Standardize and implement a new administrative role "Web Developer" with permissions identical to the existing "Administrator" role.

## 1. Database Changes
Update the `jabatan` column in the `users` table to include `'Web Developer'`.

### Migration
- **File**: `database/migrations/2026_06_05_100000_add_web_developer_to_jabatan_enum.php`
- **Change**: Alter the `jabatan` column to `ENUM('Administrator', 'Sales', 'Teknisi', 'Web Developer')`.

## 2. Model Logic
Centralize administrative permission checks in the `User` model to avoid hardcoded string comparisons throughout the codebase.

### User Model
- **File**: `app/Models/User.php`
- **Method**: Add `isAdmin()`
```php
public function isAdmin(): bool
{
    return in_array($this->jabatan, ['Administrator', 'Web Developer']);
}
```

## 3. Middleware Update
Update the existing `admin` middleware to use the new `isAdmin()` check.

### EnsureIsAdministrator Middleware
- **File**: `app/Http/Middleware/EnsureIsAdministrator.php`
- **Change**: Replace `auth()->user()->jabatan === 'Administrator'` with `auth()->user()->isAdmin()`.

## 4. Codebase Refactoring
Replace manual string checks for `'Administrator'` with the `$user->isAdmin()` method or include 'Web Developer' in the logic.

### Controllers
- **Files**:
    - `AssetController.php`: Check in `store`, `update`, `destroy`.
    - `AccountController.php`: Check in `index`, `store`, `update`, `reset2FA`, `show2FA`, `destroy`. Also update `jabatan` validation.
    - `ActivityLogController.php`: Check in `index`.
    - `OltController.php`: Check in `index`, `getDashboardData`, `getOnuDetails`, `executeScript`.
    - `OdpController.php`: Check in `index`, `store`, `update`, `destroy`.
    - `CustomerController.php`: Check in `index`, `store`.
    - `PreOrderController.php`: Check in `index`, `store`, `update`, `destroy`, `unspek`, `approveUnspek`, `reopenUnspek`.
    - `MaintenanceController.php`: Check in `index`, `destroy`.
    - `DismantleController.php`: Check in `index`, `destroy`, `deleteCustomer`.
- **Change**: Update `if` statements and `abort` checks. Replace `auth()->user()->jabatan === 'Administrator'` with `auth()->user()->isAdmin()`.

### Blade Views
- **Files**:
    - `resources/views/assets/index.blade.php`: Check `@php $isAdmin = ...` and `@if ($isAdmin)`.
    - `resources/views/activitylog/activity-log.blade.php`: Page title/access logic.
    - `resources/views/monitoring/olt.blade.php`: Tab visibility logic.
    - `resources/views/preorders/index.blade.php`: Option visibility.
    - `resources/views/accounts/index.blade.php`: Access logic and statistics (total admin count).
- **Change**: Update `@if` directives to use `$user->isAdmin()`.

## 5. UI Updates
Update user management forms to allow selecting the new role and update statistics.

### Accounts Management
- **File**: `resources/views/accounts/modal.blade.php`
- **Change**: Add `<option value="Web Developer">Web Developer</option>` to both add and edit modals.

### Dashboard/Stats
- **File**: `app/Http/Controllers/AccountController.php`
- **Change**: Update `index` statistics to include Web Developer in "Total Admin" or add a new stat for Web Developer.

## 6. Seeders (Optional but Recommended)
Update seeders to reflect the updated enum.

### Users Seeder
- **File**: `database/seeders/UsersSeeder.php`
