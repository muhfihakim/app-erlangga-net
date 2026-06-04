# Spec: Global Client-Side Form Validation

## 1. Overview
Implement a centralized client-side validation system to provide immediate user feedback before form submission across all application modules (ODP, Pre-Order, Customer, Maintenance, Dismantle, and Accounts).

## 2. Goals
- Eliminate unnecessary server-side validation rounds for empty required fields.
- Provide a consistent user experience using existing Toast notifications.
- Support complex multi-tab forms by directing users to the error source automatically.

## 3. Technical Design
### Centralized Interceptor
A global event listener will be added to the layout component (`app.blade.php`) to intercept all `submit` events.

```javascript
document.addEventListener('submit', function(e) {
    const form = e.target;
    // Skip GET forms and forms with 'novalidate' attribute
    if (form.method === 'get' || form.hasAttribute('novalidate')) return;

    if (!validateForm(form)) {
        e.preventDefault();
        e.stopPropagation();
    }
}, true);
```

### Validation Logic (`validateForm`)
1. **Required Fields**: Iterate through all `input`, `select`, and `textarea` elements with the `required` attribute.
2. **Label Detection**:
    - First, try to find a `<label>` element associated with the input.
    - If not found, look for a `.form-label` in the parent container.
    - Fallback to the `placeholder` attribute or the `name` attribute (capitalized).
3. **Dynamic Feedback**:
    - Use `showToast("Field [Name] is required!", "error")`.
    - Automatically focus the first invalid field.
4. **Multi-Tab Support**:
    - If the invalid field is inside a hidden tab panel, identify the parent `.tab-panel` ID.
    - Find the corresponding `.modal-tab` button and trigger a `click` event to switch to that tab.

## 4. Specific Module Handling
- **Pre-Order**: Validation must handle the 3 tabs (Biodata, Paket, Media).
- **Aktivasi/Customer**: Handle radio buttons (Payment, Modem Type) and file uploads.
- **Maintenance/Dismantle**: Ensure hidden ID fields and mandatory photo uploads are validated.

## 5. Visual Feedback
- Primary feedback: Existing `showToast` system.
- Secondary feedback: Set focus to the problematic element.

## 6. Success Criteria
- Submitting a form with empty required fields results in a Toast message and no page reload.
- Users are automatically switched to the correct tab when an error is hidden.
- Native HTML5 validation popups are suppressed in favor of custom Toasts.
