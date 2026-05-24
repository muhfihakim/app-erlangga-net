# Design Spec: Relocate Maintenance & Dismantle Charts to Dashboard

**Status:** Draft
**Author:** Gemini CLI
**Date:** 2026-05-24

## 1. Goal

Move "Tren Maintenance", "Top Teknisi Maintenance", "Tren Dismantle", and "Top Teknisi Dismantle" charts from their respective index pages to the central Dashboard. These charts will be exclusively visible to Administrators.

## 2. Architecture

### 2.1 Controller Changes

- **DashboardController**: Add logic to calculate trends (1 bulan berjalan) and top technicians (current month) for both Maintenance and Dismantle models. This data fetching will be guarded by an `Administrator` check to optimize performance.
- **MaintenanceController**: Remove trend and top technician calculation logic.
- **DismantleController**: Remove trend and top technician calculation logic.

### 2.2 View Changes

- **dashboard.blade.php**:
    - Add a new section for "Maintenance & Dismantle Insights" visible only to `Administrator`.
    - Include 4 new chart containers (2 bar charts for trends, 2 donut charts for top technicians).
    - Add Chart.js initialization for these 4 charts.
- **maintenances/index.blade.php**: Remove chart containers and associated scripts.
- **dismantles/index.blade.php**: Remove chart containers and associated scripts.

## 3. Data Requirements

### 3.1 Trends (1 Bulan Berjalan)

- Bar Chart.
- X-axis: Last 1 Bulan Berjalan (formatted as `d M`).
- Y-axis: Count of records created on that date.

### 3.2 Top Technicians (Current Month)

- Donut Chart.
- Top 5 technicians by completed tasks in the current month.
- Requires joining/with `teknisiEksekutor`.

## 4. Layout Design

- Section title: "Analisis & Performa Operasional"
- Grid layout:
    - Row 1: Maintenance Tren (Left) | Top Teknisi Maintenance (Right)
    - Row 2: Dismantle Tren (Left) | Top Teknisi Dismantle (Right)

## 5. Security & Access

- Visibility: `Auth::user()->jabatan === 'Administrator'`.
- Data isolation: Logic only executes if the user is an Admin.

## 6. Testing Strategy

- Verify dashboard loads correctly for Admin with all 4 charts.
- Verify dashboard loads correctly for Sales/Teknisi without errors (and without charts).
- Verify Maintenance and Dismantle index pages still load correctly without charts.
- Verify chart data accuracy against database records.
