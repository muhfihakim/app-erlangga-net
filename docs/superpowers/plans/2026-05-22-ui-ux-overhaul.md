# UI/UX Overhaul (Soft & Friendly) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor the web UI to a modern 'Soft & Friendly' aesthetic using Tailwind CSS while preserving existing Laravel logic.

**Architecture:** Component-based layout refactor. Replace Bootstrap/AdminLTE patterns with custom Tailwind classes in Blade components.

**Tech Stack:** Laravel, Tailwind CSS, Vite, Blade.

---

### Task 1: Main Layout Refactor (App Layout)

**Files:**
- Modify: `resources/views/components/layouts/app.blade.php`

- [ ] **Step 1: Update app.blade.php with modern container and background**
- [ ] **Step 2: Verify page renders without console errors**
- [ ] **Step 3: Commit**

### Task 2: Sidebar Refactor

**Files:**
- Modify: `resources/views/components/layouts/sidebar.blade.php`

- [ ] **Step 1: Apply Soft & Friendly styling to sidebar (rounded corners, soft colors)**
- [ ] **Step 2: Verify navigation links work**
- [ ] **Step 3: Commit**

### Task 3: Dashboard Refactor

**Files:**
- Modify: `resources/views/dashboard.blade.php`

- [ ] **Step 1: Replace small-boxes with modern Tailwind cards**
- [ ] **Step 2: Style Activity Log table with soft borders and padding**
- [ ] **Step 3: Verify dashboard data displays correctly**
- [ ] **Step 4: Commit**

### Task 4: Form & Table Global Styles

**Files:**
- Modify: `resources/css/app.css`

- [ ] **Step 1: Add custom Tailwind utilities for rounded-2xl inputs and soft-shadow cards**
- [ ] **Step 2: Verify build passes (`npm run build`)**
- [ ] **Step 3: Commit**