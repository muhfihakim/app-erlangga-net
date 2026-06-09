# Design Spec - Perbantuan BBSNET Module

## 1. Goal
Add a new module "Perbantuan BBSNET" to track technician assistance in various activities like PO, Maintenance, ODP creation, and Line Activation. This module will allow recording which technicians were involved and provide statistical summaries.

## 2. Technical Architecture

### 2.1 Database Schema
Table: `supporting_activities`
- `id`: BigIncrements
- `activity_type`: Enum ('PO', 'Pemeliharaan', 'Buat ODP', 'Aktivasi Jalur')
- `description`: Text (optional)
- `main_technician_id`: UnsignedBigInteger (Foreign Key to `users.id`)
- `partner_ids`: JSON (Stores array of technician IDs)
- `photo`: String (Nullable, path to uploaded image)
- `activity_date`: Date
- `created_by`: UnsignedBigInteger (Foreign Key to `users.id`)
- `timestamps`

### 2.2 Models
- `SupportingActivity`:
    - Relationships: `mainTechnician()`, `createdBy()`.
    - Accessor for partners: `getPartnersAttribute()` to fetch user models from `partner_ids`.
    - Casts: `partner_ids` to `array`, `activity_date` to `date`.

### 2.3 Controllers
- `SupportingActivityController`:
    - `index()`: List data with filters, search, and stats calculation.
    - `store()`: Save new assistance record.
    - `update()`: Edit existing record.
    - `destroy()`: Delete record.
    - `export()`: Generate PDF report.

### 2.4 Routes (web.php)
- `GET /supporting`: List & Index
- `POST /supporting`: Store
- `PUT /supporting/{id}`: Update
- `DELETE /supporting/{id}`: Destroy
- `GET /supporting/export`: PDF Export

## 3. UI/UX Design

### 3.1 Views (`resources/views/supporting/`)
- `index.blade.php`: Main table view with:
    - **Stats Cards**: Total assistance count per technician (Main + Partner).
    - **Filters**: Date range, Activity Type, Technician.
    - **Table**: Date, Type, Main Tech, Partners, Photo, Actions.
    - **Live Search**: JS-based filtering of the table rows.
- `modal.blade.php`: Unified modal for Create/Edit.
- `export.blade.php`: PDF layout for report generation.

### 3.2 UI Components
- **Selects**: Activity Type (Select), Main Tech (Select), Partners (Checkbox list in scrollable div to match `customers.index` style).
- **Pagination**: Standard Laravel pagination styled with existing CSS.
- **Photos**: Lightbox or simple modal preview for uploaded photos.

## 4. Logic for Statistics
The "Technician Scoreboard" will be calculated by:
1. Fetching all activities.
2. Iterating through each:
    - Increment count for `main_technician_id`.
    - Iterate `partner_ids` and increment count for each ID.
3. Sorting technicians by total count.

## 5. Implementation Steps
1. Create Migration for `supporting_activities`.
2. Create Model `SupportingActivity`.
3. Create Controller `SupportingActivityController`.
4. Define Web and API routes.
5. Build the Blade views in `resources/views/supporting/`.
6. Implement PDF Export logic.
7. Verify functionality and visual consistency.
