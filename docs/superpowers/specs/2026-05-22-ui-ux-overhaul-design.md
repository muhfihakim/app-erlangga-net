# Design Spec: UI/UX Overhaul (High-Density, Modern & Gradient)

## Overview

Refactor the Laravel web application UI to a modern, high-density aesthetic designed for heavy data monitoring and management. Transition from a standard AdminLTE/Bootstrap feel to a custom, responsive Tailwind-based design that supports **Light and Dark mode**, featuring bright light-blue gradients and soft rounded elements.

## Style Guide

- **Tone:** Modern, technical, dense (command-center feel), yet visually engaging and approachable.
- **Theme Mode:** Full Light & Dark Mode support via Tailwind's `class` strategy.
- **Colors:**
    - Primary (Gradient): Light Blue to Bright Blue (`bg-gradient-to-r from-sky-400 to-blue-500`)
    - Success: Emerald (`text-emerald-500`, `bg-emerald-500/10`)
    - Warning: Amber (`text-amber-500`, `bg-amber-500/10`)
    - Danger: Rose (`text-rose-500`, `bg-rose-500/10`)
    - Background Light: Slate-50 (`bg-slate-50`) with White (`bg-white`) cards.
    - Background Dark: Slate-900 (`bg-slate-900`) with Slate-800 (`bg-slate-800`) cards.
- **Border Radius:** `rounded-2xl` (1rem / 16px) for main cards, modals, and charts. `rounded-xl` for inner elements like inputs and buttons.
- **Typography:** Instrument Sans or Inter. Base size `text-sm` to accommodate a dense, information-rich layout.
- **Interactions:** Subtle hover states, smooth background color transitions (`transition-colors duration-200`), and optional light glassmorphism (background blur) for floating elements.

## Components

### 1. Sidebar & Navigation

- **Mobile First:** Hidden off-canvas sidebar on mobile (accessible via hamburger menu). Fixed rail on desktop.
- **Density:** Compact menu items with smaller icons to fit more navigation links without scrolling.
- **Styling:** Active states use the primary bright blue gradient with white text. Dark mode sidebar uses deep slate with subtle border-right (`border-slate-800`).

### 2. Dashboard (High-Density Layout)

- **Grid Layout:** 4-column to 6-column CSS Grids on desktop to display many data points simultaneously.
- **Stat Cards:** Compact cards with vibrant icons, sparkline charts, and absolute numbers.
- **Activity Stream:** A dense, vertically scrolling timeline on the right side for live logs or technician activities.

### 3. Forms & Tables

- **Tables:** Dense approach.
    - Reduced padding (`py-2 px-3`).
    - Smaller text size (`text-xs` or `text-sm`).
    - Alternating row colors (`even:bg-slate-50 dark:even:bg-slate-800/50`).
    - Horizontal scrolling enabled for mobile devices.
- **Forms:**
    - Multi-column form grids to avoid excessive vertical scrolling.
    - Clean top labels (`text-xs text-slate-500 dark:text-slate-400`).
    - Inputs with `rounded-xl`, soft focus rings (`focus:ring-2 focus:ring-sky-400`).

## Technical Implementation

- **Framework:** Tailwind CSS (via Vite) in Laravel.
- **Dark Mode Strategy:** Enable `darkMode: 'class'` in `tailwind.config.js`. Use a global toggle button that saves to `localStorage` and toggles the `dark` class on the `<html>` tag.
- **Icons:** Bootstrap Icons or Heroicons (Micro version).
- **Responsiveness:** Mobile-first approach utilizing Tailwind's `md:`, `lg:`, and `xl:` breakpoints.

## Out of Scope (Web)

- Geolocation tracking UI (moved to Mobile API).
