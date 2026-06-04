# Global Client-Side Form Validation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement a centralized client-side validation system to provide immediate user feedback before form submission across all application modules.

**Architecture:** A global JavaScript interceptor in the main layout will listen for form submissions, validate required fields, identify user-friendly labels, and handle automatic tab switching for complex forms.

**Tech Stack:** JavaScript (ES6+), Blade, Tailwind CSS (for selectors).

---

### Task 1: Global Interceptor Setup

**Files:**
- Modify: `resources/views/components/layouts/app.blade.php`

- [ ] **Step 1: Add the global validation script**
Inject the listener and `validateForm` shell into the scripts section of the layout.

```javascript
// Inside <script> in app.blade.php
document.addEventListener('submit', function(e) {
    const form = e.target;
    // Intercept only POST forms that aren't explicitly excluding validation
    if (form.method.toLowerCase() === 'get' || form.hasAttribute('data-no-validation')) return;

    if (!validateGlobalForm(form)) {
        e.preventDefault();
        e.stopPropagation();
    }
}, true);

function validateGlobalForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let firstInvalid = null;

    for (const field of requiredFields) {
        if (!field.value || (field.type === 'checkbox' && !field.checked)) {
            const label = findLabelText(field);
            showToast(`Bidang "${label}" wajib diisi!`, 'error');
            
            if (!firstInvalid) firstInvalid = field;
            break; // Show only first error as per Option A
        }
    }

    if (firstInvalid) {
        handleTabSwitching(firstInvalid);
        firstInvalid.focus();
        return false;
    }
    return true;
}
```

- [ ] **Step 2: Commit setup**
```bash
git add resources/views/components/layouts/app.blade.php
git commit -m "feat(validation): add global form submission interceptor"
```

---

### Task 2: Smart Label & Tab Discovery Logic

**Files:**
- Modify: `resources/views/components/layouts/app.blade.php`

- [ ] **Step 1: Implement findLabelText**
Add logic to find the most appropriate name for a field.

```javascript
function findLabelText(field) {
    // 1. Associated <label>
    if (field.id) {
        const label = document.querySelector(`label[for="${field.id}"]`);
        if (label) return label.innerText.replace('*', '').trim();
    }
    // 2. Parent .form-group label
    const parentGroup = field.closest('.form-group');
    if (parentGroup) {
        const label = parentGroup.querySelector('label, .form-label');
        if (label) return label.innerText.replace('*', '').trim();
    }
    // 3. Placeholder or Name fallback
    return field.placeholder || field.name || 'Field';
}
```

- [ ] **Step 2: Implement handleTabSwitching**
Add logic to switch tabs if the invalid field is hidden.

```javascript
function handleTabSwitching(field) {
    const panel = field.closest('.tab-panel');
    if (panel && !panel.classList.contains('active')) {
        const panelId = panel.id;
        const tabBtn = document.querySelector(`[data-tab="${panelId}"]`);
        if (tabBtn) tabBtn.click();
    }
}
```

- [ ] **Step 3: Commit logic refinements**
```bash
git commit -am "feat(validation): implement smart label discovery and tab switching"
```

---

### Task 3: Global Integration & UX Polish

**Files:**
- Modify: `resources/views/components/layouts/app.blade.php`
- Modify: `resources/views/partials/modal_preorders.blade.php`
- Modify: `resources/views/odp/index.blade.php`

- [ ] **Step 1: Suppress browser default tooltips**
Add `novalidate` to forms to allow our custom Toast logic to take precedence.

- [ ] **Step 2: Add visual focus styling**
Optionally add a temporary red border to the invalid field.

- [ ] **Step 3: Commit integration**
```bash
git commit -am "chore(validation): suppress native validation and apply visual polish"
```

---

### Task 4: Final Verification

- [ ] **Step 1: Test Pre-Order Form**
Submit with empty "Media" tab fields while on the "Biodata" tab.
Expected: Swaps to "Media" tab and shows Toast.

- [ ] **Step 2: Test ODP Form**
Submit without location.
Expected: Shows Toast for location field.

- [ ] **Step 3: Final commit**
```bash
git push origin main
```
