# Spec: Asset & Inventory Management Module

## 1. Overview
Implement a comprehensive Asset Management module to track the inventory of materials (ONT, Fiber Optic cables, etc.) and record all incoming and outgoing transactions.

## 2. Goals
- Provide real-time stock levels for all operational assets.
- Record detailed history of who receives or sends items.
- Generate monthly reports for asset movement.
- Ensure consistent UI/UX with existing modules (modals, pagination, search).

## 3. Technical Design
### Database Schema
Two primary tables are required:

#### `assets` (Master Data)
- `id`: Primary Key
- `name`: String (e.g., "ONT HG6243C")
- `type`: String (e.g., "ONT", "Cable", "Passive")
- `unit`: String (e.g., "Pcs", "Meter")
- `stock`: Integer (Default 0, auto-updated by transactions)
- `description`: Text (Optional)
- `timestamps`: standard Laravel timestamps

#### `asset_transactions` (Movement Log)
- `id`: Primary Key
- `asset_id`: Foreign Key to `assets`
- `type`: Enum ('Masuk', 'Keluar')
- `quantity`: Integer
- `date`: Date
- `user_id`: Foreign Key to `users` (Nullable, for 'Pengambil' or 'Penerima' if they are staff)
- `external_person_name`: String (Nullable, for manual name entry)
- `description`: Text
- `timestamps`: standard Laravel timestamps

### Controller Logic
- **Automatic Stock Management**: Using Database Transactions to ensure that every `asset_transaction` record creation correctly updates the `assets.stock` column.
- **Validation**: Prevent 'Keluar' transactions if `quantity > stock`.
- **API Support**: Expose endpoints for mobile app integration (Stock list, Transaction history).

### Access Control (RBAC)
- **Administrator**: Full CRUD on Assets and Transactions. Export reports.
- **Teknisi**: View-only for Asset stocks. View personal transaction history.

## 4. UI/UX Specifications
- **View Location**: `resources/views/assets/`
- **Dashboard Summary**:
    - Total Items
    - Stock Warning (items with low stock)
    - Transactions this month
- **Components**:
    - Modal for adding new assets.
    - Dynamic Modal for transactions (conditional fields for Masuk/Keluar).
    - DataTables for live search and pagination.
    - Date range filter for transaction history.

## 5. Reporting
- Export feature (Excel/CSV) for transactions filtered by type and date range.

## 6. Success Criteria
- Adding a "Masuk" transaction increases the asset's stock.
- Adding a "Keluar" transaction decreases the asset's stock.
- Stock cannot go below zero.
- Admins can see who took which items and when.
