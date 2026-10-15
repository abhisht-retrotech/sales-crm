<!DOCTYPE html>
<html lang="en" data-theme="light" data-branding="apple">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DealSync — Enterprise Sales & Operations CRM')</title>
    <!-- Google Fonts: Inter & Inter Tight -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            /* Font Families */
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            --font-display: 'Inter Tight', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

            /* Brand Accent & Theme */
            --color-primary: #4f46e5;
            --color-primary-hover: #4338ca;
            --color-primary-subtle: #eef2ff;
            --color-primary-border: #c7d2fe;
            --color-primary-text: #3730a3;

            /* Zinc Light Palette */
            --zinc-50: #f8fafc;
            --zinc-100: #f1f5f9;
            --zinc-200: #e2e8f0;
            --zinc-300: #cbd5e1;
            --zinc-400: #94a3b8;
            --zinc-500: #64748b;
            --zinc-600: #475569;
            --zinc-700: #334155;
            --zinc-800: #1e293b;
            --zinc-900: #0f172a;
            --zinc-950: #020617;

            /* Canvas & Surfaces (Default: Active Tenant Acme Enterprise Tech) */
            --bg-main: #efe6e6;
            --header-bg: #ededed;
            --sidebar-bg: #f8f1f1;
            --bg-card: #ffffff;
            --bg-card-hover: #ffffff;
            --border-card: #e2e8f0;
            --border-subtle: rgba(226, 232, 240, 0.85);

            /* Text Scale */
            --text-primary: #3636ab;
            --text-secondary: var(--zinc-600);
            --text-tertiary: var(--zinc-500);
            --text-muted: var(--zinc-400);

            /* Shadows & Radii */
            --shadow-xs: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.03);
            --shadow-card: 0 1px 3px 0 rgba(0, 0, 0, 0.02), 0 0 0 1px rgba(0, 0, 0, 0.04);
            --shadow-card-hover: 0 4px 12px -2px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.02);
            --shadow-modal: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.03);

            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 10px;
            --radius-xl: 14px;
            --radius-2xl: 18px;
            --radius-full: 9999px;

            /* Table Row Heights */
            --table-row-height: 32px;
            --table-font-size: 0.785rem;
        }

        /* ========================================================================= */
        /* DARK MODE PALETTE ([data-theme="dark"])                                   */
        /* ========================================================================= */
        [data-theme="dark"] {
            --bg-main: #09090b;
            --header-bg: #121215;
            --sidebar-bg: #121215;
            --bg-card: #18181b;
            --bg-card-hover: #222226;
            --border-card: #27272a;
            --border-subtle: #3f3f46;

            --text-primary: #f4f4f5;
            --text-secondary: #a1a1aa;
            --text-tertiary: #71717a;
            --text-muted: #52525b;

            --zinc-50: #121215;
            --zinc-100: #18181b;
            --zinc-200: #27272a;
            --zinc-300: #3f3f46;
            --zinc-400: #71717a;
            --zinc-500: #a1a1aa;
            --zinc-600: #d4d4d8;
            --zinc-700: #e4e4e7;
            --zinc-800: #f4f4f5;
            --zinc-900: #fafafa;
            --zinc-950: #ffffff;

            --shadow-card: 0 1px 3px 0 rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.06);
            --shadow-card-hover: 0 6px 16px -2px rgba(0, 0, 0, 0.45);
        }

        /* ========================================================================= */
        /* REVOLUT DESIGN SYSTEM ([data-branding="revolut"])                         */
        /* ========================================================================= */
        [data-branding="revolut"] {
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-display: 'Inter Tight', 'Aeonik Pro', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;
        }
        [data-branding="revolut"] .btn,
        [data-branding="revolut"] .dashboard-btn {
            border-radius: 9999px !important;
        }
        [data-branding="revolut"] .card,
        [data-branding="revolut"] .dashboard-hero-card,
        [data-branding="revolut"] .dashboard-kpi-card,
        [data-branding="revolut"] .card-table-wrapper {
            border-radius: 20px !important;
            border-width: 1px !important;
        }
        [data-branding="revolut"][data-theme='dark'] {
            --bg-main: #000000;
            --header-bg: #101214;
            --sidebar-bg: #101214;
            --bg-card: #16181a;
            --bg-card-hover: #1f2226;
            --border-card: rgba(255, 255, 255, 0.12);
            --border-subtle: rgba(255, 255, 255, 0.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            background-color: #efe6e6;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-main, #efe6e6);
            color: var(--text-primary);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        button, input, select, textarea {
            font-family: inherit;
        }

        /* Minimal Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--zinc-300);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--zinc-400);
        }

        /* App Layout Shell */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            background: var(--bg-main, #efe6e6);
            transition: background 0.2s ease;
        }

        /* Sidebar Styling */
        .sidebar-spacer {
            width: 72px;
            flex-shrink: 0;
            transition: width 0.22s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        .sidebar-spacer.is-pinned {
            width: 250px;
        }

        .fixed-sidebar-container {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 72px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-card);
            z-index: 50;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: width 0.22s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.2s ease, background-color 0.2s ease;
        }
        .fixed-sidebar-container.is-expanded,
        .fixed-sidebar-container.is-pinned {
            width: 250px;
        }
        .fixed-sidebar-container.is-floating {
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05);
        }

        .sidebar-brand-row {
            height: 58px;
            padding: 0 10px;
            border-bottom: 1px solid var(--border-card);
            display: flex;
            align-items: center;
            background: var(--sidebar-bg);
            flex-shrink: 0;
        }

        .sidebar-icon-slot {
            width: 48px;
            min-width: 48px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-slide-text {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-right: 0.65rem;
            white-space: nowrap;
            overflow: hidden;
            opacity: 0;
            transform: translateX(-12px);
            pointer-events: none;
            transition: opacity 0.18s cubic-bezier(0.4, 0, 0.2, 1), transform 0.22s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        .fixed-sidebar-container.is-expanded .sidebar-slide-text,
        .fixed-sidebar-container.is-pinned .sidebar-slide-text {
            opacity: 1;
            transform: translateX(0);
            pointer-events: auto;
        }

        .sidebar-category-header {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--zinc-400);
            padding: 0.75rem 0.75rem 0.25rem 0.75rem;
            white-space: nowrap;
            overflow: hidden;
            opacity: 0;
            transform: translateX(-10px);
            transition: opacity 0.18s ease, transform 0.22s ease;
        }
        .fixed-sidebar-container.is-expanded .sidebar-category-header,
        .fixed-sidebar-container.is-pinned .sidebar-category-header {
            opacity: 1;
            transform: translateX(0);
        }

        .sidebar-nav-btn {
            display: flex;
            align-items: center;
            width: 100%;
            height: 38px;
            padding: 0;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 8px;
            color: var(--zinc-700);
            font-size: 0.8125rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            margin-bottom: 2px;
            transition: all 0.12s ease;
            position: relative;
        }
        .sidebar-nav-btn:hover {
            background: var(--zinc-100);
            color: var(--zinc-950);
        }
        .sidebar-nav-btn.is-active {
            background: var(--color-primary-subtle);
            color: var(--color-primary-text);
            font-weight: 600;
            border-color: var(--color-primary-border);
        }
        .sidebar-nav-btn.is-active .sidebar-icon-slot svg {
            color: var(--color-primary);
            stroke-width: 2.2;
        }

        /* Top Header */
        .app-header {
            height: 58px;
            background: var(--header-bg);
            border-bottom: 1px solid var(--border-card);
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .header-search-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--zinc-100);
            border: 1px solid var(--zinc-200);
            border-radius: var(--radius-md);
            padding: 0.38rem 0.75rem;
            width: 380px;
            transition: all 0.15s ease;
        }
        .header-search-bar:focus-within {
            background: var(--bg-card);
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }
        .header-search-bar input {
            border: none;
            outline: none;
            background: transparent;
            font-size: 0.8125rem;
            color: var(--zinc-900);
            width: 100%;
        }

        /* Main Workspace Container */
        .main-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--bg-main, #efe6e6);
        }

        .content-body {
            flex: 1;
            padding: 2.25rem 2.5rem;
            max-width: 1240px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
            background-color: var(--bg-main, #efe6e6);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.475rem 0.95rem;
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: 0.8125rem;
            line-height: 1.25;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
            text-decoration: none;
        }
        .btn:active {
            transform: scale(0.985);
        }
        .btn-primary {
            background: var(--color-primary);
            color: #ffffff !important;
            border: 1px solid var(--color-primary);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.12);
        }
        .btn-primary:hover {
            background: var(--color-primary-hover);
            border-color: var(--color-primary-hover);
        }
        .btn-secondary {
            background: var(--bg-card);
            color: var(--zinc-800);
            border: 1px solid var(--zinc-200);
            box-shadow: var(--shadow-xs);
        }
        .btn-secondary:hover {
            background: var(--zinc-100);
            border-color: var(--zinc-300);
            color: var(--zinc-950);
        }
        .btn-sm {
            padding: 0.32rem 0.65rem;
            font-size: 0.75rem;
            border-radius: var(--radius-sm);
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.2rem 0.55rem;
            border-radius: var(--radius-full);
            font-size: 0.725rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            white-space: nowrap;
        }
        .badge-blue { background: #f0f5ff; color: #1d4ed8; border: 1px solid #dbeafe; }
        .badge-indigo { background: #eef2ff; color: #4338ca; border: 1px solid #e0e7ff; }
        .badge-green { background: #ecfdf5; color: #047857; border: 1px solid #d1fae5; }
        .badge-amber { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }
        .badge-purple { background: #faf5ff; color: #7e22ce; border: 1px solid #f3e8ff; }
        .badge-red { background: #fff1f2; color: #be123c; border: 1px solid #ffe4e6; }
        .badge-gray { background: var(--zinc-100); color: var(--zinc-600); border: 1px solid var(--zinc-200); }

        [data-theme="dark"] .badge-blue { background: rgba(30, 58, 138, 0.4); color: #93c5fd; border-color: rgba(59, 130, 246, 0.4); }
        [data-theme="dark"] .badge-indigo { background: rgba(55, 48, 163, 0.4); color: #c7d2fe; border-color: rgba(99, 102, 241, 0.4); }
        [data-theme="dark"] .badge-green { background: rgba(6, 78, 59, 0.4); color: #6ee7b7; border-color: rgba(16, 185, 129, 0.4); }
        [data-theme="dark"] .badge-amber { background: rgba(120, 53, 15, 0.4); color: #fde68a; border-color: rgba(245, 158, 11, 0.4); }
        [data-theme="dark"] .badge-purple { background: rgba(88, 28, 135, 0.4); color: #e9d5ff; border-color: rgba(168, 85, 247, 0.4); }
        [data-theme="dark"] .badge-red { background: rgba(136, 19, 55, 0.4); color: #fda4af; border-color: rgba(244, 63, 94, 0.4); }
        [data-theme="dark"] .badge-gray { background: #27272a; color: #d4d4d8; border-color: #3f3f46; }

        /* ========================================================================= */
        /* GLOBAL FORM CONTROLS & INPUT ARCHITECTURE                                  */
        /* ========================================================================= */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            margin-bottom: 0.75rem;
        }

        .form-label {
            font-size: 0.775rem;
            font-weight: 600;
            color: var(--zinc-700);
            letter-spacing: -0.01em;
            display: block;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            box-sizing: border-box;
            background: var(--zinc-50);
            border: 1px solid var(--zinc-200);
            border-radius: var(--radius-md);
            color: var(--zinc-950);
            padding: 0.5rem 0.8rem;
            font-size: 0.8125rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
            height: 38px;
        }

        .form-textarea {
            height: auto;
            min-height: 80px;
            padding: 0.65rem 0.8rem;
        }

        .form-input:hover,
        .form-select:hover,
        .form-textarea:hover {
            border-color: var(--zinc-300);
            background: #ffffff;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus,
        .form-input:focus-visible,
        .form-select:focus-visible,
        .form-textarea:focus-visible {
            background: #ffffff !important;
            border-color: var(--color-primary) !important;
            box-shadow: 0 0 0 3px var(--color-primary-subtle) !important;
        }

        .form-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.15em 1.15em;
            padding-right: 2.25rem;
            cursor: pointer;
        }

        .form-select option {
            background: #ffffff;
            color: var(--zinc-900);
            padding: 0.5rem;
        }

        [data-theme="dark"] .form-input,
        [data-theme="dark"] .form-select,
        [data-theme="dark"] .form-textarea {
            background-color: #1a1a1e !important;
            border-color: #2e2e33 !important;
            color: #f4f4f5 !important;
        }

        [data-theme="dark"] .form-select option {
            background-color: #1a1a1e !important;
            color: #f4f4f5 !important;
        }

        /* ========================================================================= */
        /* DATA TABLE ARCHITECTURE: VARIABLE WIDTHS, SORTING & RESIZING              */
        /* ========================================================================= */
        .card-table-wrapper {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-card);
            position: relative;
        }

        .table-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.65rem 1rem;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-card);
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .table-scroll-container {
            overflow-x: auto;
            width: 100%;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            text-align: left;
            font-size: var(--table-font-size);
            table-layout: auto;
        }

        .data-table th {
            background: var(--zinc-100);
            color: var(--zinc-600);
            font-weight: 600;
            font-size: 0.725rem;
            letter-spacing: 0.02em;
            padding: 0.45rem 0.75rem;
            height: 36px;
            line-height: 1.2;
            border-bottom: 1px solid var(--border-card);
            border-right: 1px solid var(--border-card);
            white-space: nowrap;
            position: relative;
            user-select: none;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .data-table th:hover {
            background: var(--zinc-200);
            color: var(--zinc-950);
        }

        .data-table th.is-sorted {
            color: var(--color-primary);
            background: var(--color-primary-subtle);
        }

        .data-table td {
            padding: 0.45rem 0.75rem;
            height: var(--table-row-height);
            line-height: 1.2;
            border-bottom: 1px solid var(--border-subtle);
            border-right: 1px solid var(--border-subtle);
            font-size: var(--table-font-size);
            color: var(--zinc-800);
            vertical-align: middle;
            background: var(--bg-card);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: background 0.1s ease;
        }

        .data-table tbody tr:hover td {
            background: var(--bg-card-hover);
        }

        .data-table tbody tr.is-selected td {
            background: var(--color-primary-subtle) !important;
        }

        .data-table th:last-child,
        .data-table td:last-child {
            border-right: none;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }

        /* Draggable Column Width Resizer Divider Handle */
        .table-col-resizer {
            position: absolute;
            top: 0;
            right: 0;
            width: 7px;
            height: 100%;
            cursor: col-resize;
            user-select: none;
            z-index: 10;
            background: transparent;
            transition: background-color 0.15s ease;
        }
        .table-col-resizer:hover,
        .table-col-resizer.is-resizing {
            background-color: var(--color-primary) !important;
            opacity: 0.85;
        }

        /* Checkbox & Row Index column */
        .col-index {
            width: 44px;
            min-width: 44px;
            max-width: 44px;
            text-align: center !important;
            padding: 0.35rem 0.4rem !important;
        }

        /* Table Density Classes */
        .data-table.is-compact td {
            height: 30px;
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
        }
        .data-table.is-comfortable td {
            height: 44px;
            padding: 0.75rem 1rem;
            font-size: 0.8125rem;
        }

        /* Selection Floating Action Bar */
        .table-selection-bar {
            display: none;
            position: sticky;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            background: var(--zinc-900);
            color: #ffffff;
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            box-shadow: var(--shadow-modal);
            z-index: 30;
            align-items: center;
            gap: 1rem;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .table-selection-bar.is-visible {
            display: inline-flex;
        }

        /* Cards & Surfaces */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-xl);
            padding: 1.25rem;
            box-shadow: var(--shadow-card);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card:hover {
            box-shadow: var(--shadow-card-hover);
        }

        /* Dashboard Specific Styles */
        .dashboard-hero-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            box-shadow: var(--shadow-xs);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (min-width: 768px) {
            .dashboard-hero-card {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .dashboard-telemetry-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.15rem 0.55rem;
            border-radius: 9999px;
            font-size: 0.725rem;
            font-weight: 600;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        [data-theme="dark"] .dashboard-telemetry-badge {
            background: rgba(6, 78, 59, 0.4);
            color: #6ee7b7;
            border-color: rgba(16, 185, 129, 0.4);
        }

        .dashboard-pulse-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10b981;
            animation: dashboardPulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes dashboardPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.15); }
        }

        .dashboard-hero-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--zinc-950);
            letter-spacing: -0.025em;
            margin: 0.25rem 0 0.15rem 0;
        }
        .dashboard-hero-subtitle {
            font-size: 0.8125rem;
            color: var(--zinc-500);
            margin: 0;
        }

        .dashboard-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .dashboard-kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 16px;
            padding: 1.15rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            transition: all 0.18s ease;
        }
        .dashboard-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-card-hover);
        }
        .dashboard-kpi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--zinc-600);
        }
        .dashboard-kpi-main-val {
            font-size: 1.625rem;
            font-weight: 800;
            color: var(--zinc-950);
            letter-spacing: -0.03em;
        }
        .dashboard-progress-track {
            height: 6px;
            background: var(--zinc-100);
            border-radius: 9999px;
            overflow: hidden;
        }
        .dashboard-progress-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.4s ease;
        }

        /* AI Copilot Card */
        .dashboard-ai-card {
            background: linear-gradient(135deg, var(--bg-card) 0%, var(--zinc-100) 100%);
            border: 1px solid var(--border-card);
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            box-shadow: var(--shadow-xs);
        }

        /* Funnel & Cockpit */
        .cockpit-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }
        @media (min-width: 1024px) {
            .cockpit-grid {
                grid-template-columns: 7fr 5fr;
            }
        }

        /* Complete Dashboard Styling matching React client/src/styles/dashboard.css */
        .dashboard-actions-cluster {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.625rem;
        }
        .dashboard-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.85rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s ease;
            line-height: 1;
        }
        .dashboard-btn-indigo {
            background: #4f46e5;
            color: #ffffff !important;
            border-color: #4f46e5;
            box-shadow: 0 1px 2px 0 rgba(79, 70, 229, 0.2);
        }
        .dashboard-btn-indigo:hover {
            background: #4338ca;
            border-color: #4338ca;
        }
        .dashboard-btn-slate {
            background: #0f172a;
            color: #ffffff !important;
            border-color: #0f172a;
            box-shadow: 0 1px 2px 0 rgba(15, 23, 42, 0.15);
        }
        .dashboard-btn-slate:hover {
            background: #1e293b;
            border-color: #1e293b;
        }
        .dashboard-btn-emerald {
            background: #059669;
            color: #ffffff !important;
            border-color: #059669;
        }
        .dashboard-btn-outline {
            background: #ffffff;
            color: #334155;
            border-color: #cbd5e1;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04);
        }
        .dashboard-btn-outline:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }
        .dashboard-filter-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.625rem 1rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .dashboard-role-tabs {
            display: inline-flex;
            background: #f1f5f9;
            padding: 3px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            gap: 2px;
        }
        .dashboard-role-tab {
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            border: none;
            background: transparent;
            color: #475569;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .dashboard-role-tab.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        .dashboard-select {
            font-size: 0.75rem;
            font-weight: 600;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.4rem 0.65rem;
            color: #334155;
            outline: none;
            cursor: pointer;
        }
        .dashboard-badge-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.1rem 0.45rem;
            border-radius: 4px;
            font-size: 0.6875rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .dashboard-badge-emerald {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .dashboard-badge-indigo {
            background: #eef2ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
        }
        .dashboard-badge-blue {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .dashboard-badge-amber {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .dashboard-badge-rose {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }
        .dashboard-kpi-value-row {
            margin: 0.75rem 0;
            display: flex;
            align-items: baseline;
            justify-content: space-between;
        }
        .dashboard-kpi-sub-val {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
        }
        .dashboard-kpi-footer {
            font-size: 0.6875rem;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.35rem;
        }
        .dashboard-link-action {
            color: #4f46e5;
            font-weight: 600;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }
        .dashboard-link-action:hover {
            text-decoration: underline;
        }
        .dashboard-funnel-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
        }
        .dashboard-funnel-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.625rem;
        }
        @media (min-width: 768px) {
            .dashboard-funnel-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }
        .dashboard-stage-card {
            padding: 0.75rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: rgba(248, 250, 252, 0.7);
            cursor: pointer;
            transition: all 0.15s ease;
            position: relative;
        }
        .dashboard-stage-card:hover {
            border-color: #818cf8;
            background: rgba(238, 242, 255, 0.5);
        }
        .dashboard-stage-card.active {
            border-color: #6366f1;
            background: rgba(238, 242, 255, 0.8);
            box-shadow: 0 1px 2px 0 rgba(99, 102, 241, 0.15);
        }
        .dashboard-stage-card.won-active {
            border-color: #10b981;
            background: rgba(236, 253, 245, 0.8);
        }
        .dashboard-stage-name {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
        }
        .dashboard-stage-value {
            font-size: 1.125rem;
            font-weight: 800;
            color: #0f172a;
            margin-top: 0.25rem;
        }
        .dashboard-stage-meta {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.15rem;
        }
        .dashboard-stage-inspector {
            margin-top: 1rem;
            padding: 0.85rem 1rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .dashboard-cockpit-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        @media (min-width: 1024px) {
            .dashboard-cockpit-grid {
                grid-template-columns: 7fr 5fr;
            }
        }
        .dashboard-cockpit-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Modal Backdrop */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(5px);
            z-index: 100;
            display: none !important;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .modal-overlay.is-open {
            display: flex !important;
        }
        .modal-card {
            background: var(--bg-card);
            color: var(--text-primary);
            border-radius: 18px;
            width: 100%;
            max-width: 640px;
            box-shadow: var(--shadow-modal);
            border: 1px solid var(--border-card);
            overflow: hidden;
            animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Toast Container */
        #dealsync-toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            pointer-events: none;
        }
        .dealsync-toast {
            pointer-events: auto;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: var(--zinc-900);
            color: #ffffff;
            padding: 0.65rem 1rem;
            border-radius: 10px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            font-size: 0.8125rem;
            font-weight: 500;
            animation: slideInToast 0.25s ease forwards;
        }
        @keyframes slideInToast {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* ========================================================================= */
        /* AMBIENT THEMES WORKSPACE ISOLATION & CANVAS TRANSPARENCY                  */
        /* ========================================================================= */
        body[class*="-theme-"] .app-wrapper,
        body[class*="-theme-"] .main-content,
        body[class*="-theme-"] .content-body,
        body[class*="-theme-"] .page-wrapper {
            background-color: transparent !important;
            background: transparent !important;
        }

        body[class*="-theme-"] .card,
        body[class*="-theme-"] .card-table-wrapper,
        body[class*="-theme-"] .dashboard-kpi-card,
        body[class*="-theme-"] .table-container,
        body[class*="-theme-"] .compact-data-table,
        body[class*="-theme-"] .data-table-wrapper,
        body[class*="-theme-"] .data-table,
        body[class*="-theme-"] table,
        body[class*="-theme-"] .modal-content,
        body[class*="-theme-"] .modal-card {
            background-color: #ffffff !important;
            color: #0f172a !important;
        }

        body[class*="-theme-"] table thead th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        body[class*="-theme-"] table tbody tr {
            background-color: #ffffff !important;
        }

        body[class*="-theme-"] table tbody tr:hover,
        body[class*="-theme-"] table tbody tr:hover td {
            background-color: #f8fafc !important;
            color: #0f172a !important;
        }

        body[class*="-theme-"] header {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            color: #0f172a !important;
        }

        body[class*="-theme-"] .fixed-sidebar,
        body[class*="-theme-"] .sidebar-nav-container,
        body[class*="-theme-"] aside {
            background: #ffffff !important;
            color: #0f172a !important;
        }

        /* ========================================================================= */
        /* AMBIENT LIGHTING & GLOW STUDIO THEMES                                      */
        /* ========================================================================= */
        body.ambient-theme-sunset, body.surprise-theme-sunset {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 40%, #fed7aa 100%) !important;
            background-attachment: fixed !important;
        }
        body.ambient-theme-sunset .card, body.ambient-theme-sunset .card-table-wrapper, body.ambient-theme-sunset .dashboard-kpi-card {
            border: 1.5px solid #fdba74 !important;
            box-shadow: 0 6px 24px rgba(251, 146, 60, 0.22), 0 0 12px rgba(245, 158, 11, 0.15) !important;
        }
        body.ambient-theme-sunset header { border-bottom: 1.5px solid #fed7aa !important; }

        body.ambient-theme-ocean, body.surprise-theme-ocean {
            background: radial-gradient(circle at 50% 20%, #064e5c 0%, #022027 100%) !important;
            background-attachment: fixed !important;
        }
        body.ambient-theme-ocean .card, body.ambient-theme-ocean .card-table-wrapper, body.ambient-theme-ocean .dashboard-kpi-card {
            border: 1.5px solid #22d3ee !important;
            box-shadow: 0 6px 24px rgba(34, 211, 238, 0.28), 0 0 15px rgba(6, 182, 212, 0.22) !important;
        }
        body.ambient-theme-ocean header { border-bottom: 1.5px solid #22d3ee !important; }

        body.ambient-theme-cyberpunk, body.surprise-theme-cyberpunk {
            background-color: #0a0a0f !important;
            background-image: 
                linear-gradient(rgba(0, 242, 254, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 242, 254, 0.05) 1px, transparent 1px) !important;
            background-size: 32px 32px !important;
            background-attachment: fixed !important;
        }
        body.ambient-theme-cyberpunk .card, body.ambient-theme-cyberpunk .card-table-wrapper, body.ambient-theme-cyberpunk .dashboard-kpi-card {
            border: 1.5px solid #00f2fe !important;
            box-shadow: 0 0 20px rgba(0, 242, 254, 0.32), 0 6px 25px rgba(244, 63, 94, 0.16) !important;
        }
        body.ambient-theme-cyberpunk header { border-bottom: 1.5px solid #00f2fe !important; }

        body.ambient-theme-cosmic, body.surprise-theme-cosmic {
            background-color: #070913 !important;
            background-image: 
                radial-gradient(ellipse 80% 60% at 50% -20%, rgba(99, 102, 241, 0.3), transparent),
                radial-gradient(circle at 10% 80%, rgba(168, 85, 247, 0.22), transparent),
                radial-gradient(circle at 90% 60%, rgba(56, 189, 248, 0.2), transparent) !important;
            background-attachment: fixed !important;
        }
        body.ambient-theme-cosmic .card, body.ambient-theme-cosmic .card-table-wrapper, body.ambient-theme-cosmic .dashboard-kpi-card {
            border: 1.5px solid #818cf8 !important;
            box-shadow: 0 6px 25px rgba(99, 102, 241, 0.24), 0 0 15px rgba(129, 140, 248, 0.18) !important;
        }
        body.ambient-theme-cosmic header { border-bottom: 1.5px solid #818cf8 !important; }

        body.ambient-theme-synthwave, body.surprise-theme-synthwave {
            background: linear-gradient(180deg, #18092a 0%, #2f0743 60%, #41006f 100%) !important;
            background-attachment: fixed !important;
        }
        body.ambient-theme-synthwave .card, body.ambient-theme-synthwave .card-table-wrapper, body.ambient-theme-synthwave .dashboard-kpi-card {
            border: 1.5px solid #ff758f !important;
            box-shadow: 0 6px 24px rgba(255, 117, 143, 0.25), 0 0 14px rgba(255, 209, 102, 0.18) !important;
        }
        body.ambient-theme-synthwave header { border-bottom: 1.5px solid #ff758f !important; }

        body.ambient-theme-sakura, body.surprise-theme-sakura {
            background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 50%, #fce7f3 100%) !important;
            background-attachment: fixed !important;
        }
        body.ambient-theme-sakura .card, body.ambient-theme-sakura .card-table-wrapper, body.ambient-theme-sakura .dashboard-kpi-card {
            border: 1.5px solid #f43f5e !important;
            box-shadow: 0 6px 22px rgba(244, 63, 94, 0.18), 0 0 12px rgba(251, 113, 133, 0.14) !important;
        }
        body.ambient-theme-sakura header { border-bottom: 1.5px solid #fecdd3 !important; }

        body.ambient-theme-matrix, body.surprise-theme-matrix {
            background-color: #030804 !important;
            background-attachment: fixed !important;
        }
        body.ambient-theme-matrix .card, body.ambient-theme-matrix .card-table-wrapper, body.ambient-theme-matrix .dashboard-kpi-card {
            border: 1.5px solid #16a34a !important;
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.28), 0 4px 20px rgba(22, 163, 74, 0.18) !important;
        }
        body.ambient-theme-matrix header { border-bottom: 1.5px solid #16a34a !important; }

        body.ambient-theme-pastel, body.surprise-theme-pastel {
            background: linear-gradient(120deg, #fbcfe8 0%, #e0e7ff 50%, #cffafe 100%) !important;
            background-attachment: fixed !important;
        }
        body.ambient-theme-pastel .card, body.ambient-theme-pastel .card-table-wrapper, body.ambient-theme-pastel .dashboard-kpi-card {
            border: 1.5px solid #f472b6 !important;
            box-shadow: 0 8px 30px rgba(244, 114, 182, 0.18), 0 0 15px rgba(192, 132, 252, 0.14) !important;
        }
        body.ambient-theme-pastel header { border-bottom: 1.5px solid #fbcfe8 !important; }

        body.ambient-theme-it-hardware, body.surprise-theme-it-hardware {
            background-color: #0b1120 !important;
            background-image: 
                radial-gradient(circle at 50% 10%, rgba(56, 189, 248, 0.18), transparent 45%),
                linear-gradient(rgba(148, 163, 184, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.04) 1px, transparent 1px) !important;
            background-size: 100% 100%, 28px 28px, 28px 28px !important;
            background-attachment: fixed !important;
        }
        body.ambient-theme-it-hardware .card, body.ambient-theme-it-hardware .card-table-wrapper, body.ambient-theme-it-hardware .dashboard-kpi-card {
            border: 1.5px solid #38bdf8 !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25), 0 0 15px rgba(56, 189, 248, 0.22) !important;
        }
        body.ambient-theme-it-hardware header { border-bottom: 1.5px solid #38bdf8 !important; }
    </style>
</head>
<body style="background-color: #efe6e6;">
    <div class="app-wrapper" style="background-color: #efe6e6;">
        <!-- Sidebar Partial -->
        @include('partials.sidebar')

        <!-- Main Workspace -->
        <main class="main-content" style="background-color: #efe6e6;">
            <!-- Header Partial -->
            @include('partials.header')

            <!-- Body View Container -->
            <div class="content-body" style="background-color: #efe6e6;">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Modals Partial -->
    @include('partials.modals')

    <!-- Toast Container -->
    <div id="dealsync-toast-container"></div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // =========================================================================
        // 1. THEME ENGINE: LIGHT/DARK MODE, REVOLUT/APPLE, ACCENTS & BG COLORS
        // =========================================================================
        const THEME_STORAGE_KEY = 'dealsync_theme_mode';
        const BRANDING_STORAGE_KEY = 'dealsync_branding';
        const ACCENT_STORAGE_KEY = 'dealsync_accent_color';
        const BG_STORAGE_KEY = 'dealsync_page_bg_color';
        const AMBIENT_STORAGE_KEY = 'dealsync_ambient_theme';

        const ALL_AMBIENT_THEMES = [
            'sunset', 'ocean', 'cyberpunk', 'cosmic', 'synthwave',
            'sakura', 'matrix', 'pastel', 'it-hardware', 'paper-craft', 'classic'
        ];

        function applyThemeMode(mode) {
            document.documentElement.setAttribute('data-theme', mode);
            try { localStorage.setItem(THEME_STORAGE_KEY, mode); } catch (e) {}
            // Update quick toggle icon
            const darkIcon = document.getElementById('theme-toggle-icon');
            if (darkIcon) {
                darkIcon.setAttribute('data-lucide', mode === 'dark' ? 'sun' : 'moon');
                lucide.createIcons();
            }
        }

        function applyBranding(brand) {
            document.documentElement.setAttribute('data-branding', brand);
            try { localStorage.setItem(BRANDING_STORAGE_KEY, brand); } catch (e) {}
        }

        function applyAccentColor(hex) {
            const root = document.documentElement;
            root.style.setProperty('--color-primary', hex);
            root.style.setProperty('--color-primary-hover', hex);
            root.style.setProperty('--color-primary-subtle', hex + '1a');
            root.style.setProperty('--color-primary-border', hex + '4d');
            root.style.setProperty('--color-primary-text', hex);
            try { localStorage.setItem(ACCENT_STORAGE_KEY, hex); } catch (e) {}
        }

        const TENANT_BRANDING_MAP = {
            'tenant-acme': {
                name: 'Acme Enterprise Tech',
                page_bg_color: '#efe6e6',
                font_color: '#3636ab',
                header_bg_color: '#ededed',
                sidebar_bg_color: '#f8f1f1',
                accent_color: '#4F46E5'
            },
            'tenant-acme-corp': {
                name: 'Acme Enterprise Tech',
                page_bg_color: '#efe6e6',
                font_color: '#3636ab',
                header_bg_color: '#ededed',
                sidebar_bg_color: '#f8f1f1',
                accent_color: '#4F46E5'
            },
            'tenant-apex': {
                name: 'Apex Global Logistics',
                page_bg_color: '#fafafa',
                font_color: '#09090b',
                header_bg_color: '#ffffff',
                sidebar_bg_color: '#ffffff',
                accent_color: '#2563eb'
            },
            'tenant-apex-logistics': {
                name: 'Apex Global Logistics',
                page_bg_color: '#fafafa',
                font_color: '#09090b',
                header_bg_color: '#ffffff',
                sidebar_bg_color: '#ffffff',
                accent_color: '#2563eb'
            },
            'tenant-skyline': {
                name: 'Skyline Cloud Systems',
                page_bg_color: '#f0f9ff',
                font_color: '#0369a1',
                header_bg_color: '#ffffff',
                sidebar_bg_color: '#f8fafc',
                accent_color: '#0284c7'
            }
        };

        function applyTenantCustomColors(colors) {
            if (!colors) return;
            const root = document.documentElement;
            if (colors.page_bg_color) {
                root.style.setProperty('--bg-main', colors.page_bg_color);
                document.body.style.backgroundColor = colors.page_bg_color;
                try { localStorage.setItem(BG_STORAGE_KEY, colors.page_bg_color); } catch (e) {}
            }
            if (colors.font_color) {
                root.style.setProperty('--text-primary', colors.font_color);
                root.style.setProperty('--zinc-950', colors.font_color);
                root.style.setProperty('--zinc-900', colors.font_color);
                root.style.setProperty('--zinc-800', colors.font_color);
                try { localStorage.setItem('dealsync_font_color', colors.font_color); } catch (e) {}
            }
            if (colors.header_bg_color) {
                root.style.setProperty('--header-bg', colors.header_bg_color);
                try { localStorage.setItem('dealsync_header_bg_color', colors.header_bg_color); } catch (e) {}
            }
            if (colors.sidebar_bg_color) {
                root.style.setProperty('--sidebar-bg', colors.sidebar_bg_color);
                try { localStorage.setItem('dealsync_sidebar_bg_color', colors.sidebar_bg_color); } catch (e) {}
            }
            if (colors.accent_color) {
                applyAccentColor(colors.accent_color);
            }
        }

        function handleHeaderTenantChange(tenantId) {
            const branding = TENANT_BRANDING_MAP[tenantId] || TENANT_BRANDING_MAP['tenant-acme'];
            applyTenantCustomColors(branding);
            showToast('Active Workspace: ' + branding.name);
        }

        function applyPageBackground(color) {
            if (color) {
                ALL_AMBIENT_THEMES.forEach(t => {
                    document.body.classList.remove(`ambient-theme-${t}`);
                    document.body.classList.remove(`surprise-theme-${t}`);
                });
                document.documentElement.style.setProperty('--bg-main', color);
                document.body.style.backgroundColor = color;
                try {
                    localStorage.setItem(BG_STORAGE_KEY, color);
                    localStorage.setItem(AMBIENT_STORAGE_KEY, 'classic');
                } catch (e) {}
                showToast(`Canvas background set to ${color}`);
            }
        }

        function applyAmbientTheme(themeId) {
            ALL_AMBIENT_THEMES.forEach(t => {
                document.body.classList.remove(`ambient-theme-${t}`);
                document.body.classList.remove(`surprise-theme-${t}`);
            });

            if (themeId && themeId !== 'classic' && themeId !== 'none') {
                document.body.classList.add(`ambient-theme-${themeId}`);
                document.body.classList.add(`surprise-theme-${themeId}`);
                try { localStorage.setItem(AMBIENT_STORAGE_KEY, themeId); } catch (e) {}
                showToast(`Ambient Theme set to ${themeId}`);
            } else {
                document.body.style.background = '';
                try { localStorage.setItem(AMBIENT_STORAGE_KEY, 'classic'); } catch (e) {}
                const savedBg = localStorage.getItem(BG_STORAGE_KEY) || '#efe6e6';
                applyPageBackground(savedBg);
                showToast('Reset theme to Classic Clean');
            }
        }

        function resetThemeDefaults() {
            applyThemeMode('light');
            applyBranding('apple');
            applyAccentColor('#4f46e5');
            applyAmbientTheme('classic');
            applyPageBackground('#efe6e6');
            showToast('Reset theme to DealSync Defaults');
        }

        function resetTableColWidths(tableId) {
            const table = document.getElementById(tableId);
            if (!table) return;
            const storageKey = `dealsync_col_widths_${tableId}`;
            try { localStorage.removeItem(storageKey); } catch (e) {}
            table.querySelectorAll('thead th').forEach(th => {
                th.style.width = '';
                th.style.minWidth = '';
            });
            showToast(`Reset column widths for ${tableId}`);
        }

        // Initialize Theme from localStorage on load
        (function initThemes() {
            const savedTheme = localStorage.getItem(THEME_STORAGE_KEY) || 'light';
            const savedBranding = localStorage.getItem(BRANDING_STORAGE_KEY) || 'apple';
            const savedAccent = localStorage.getItem(ACCENT_STORAGE_KEY) || '#4f46e5';

            // Clean up any old white/slate background in localStorage so #efe6e6 takes full precedence
            let savedBg = localStorage.getItem(BG_STORAGE_KEY);
            if (!savedBg || savedBg === '#f8fafc' || savedBg === '#ffffff' || savedBg === '#fff' || savedBg === 'white' || savedBg.toLowerCase() === '#f8fafc' || savedBg.toLowerCase() === '#ffffff') {
                savedBg = '#efe6e6';
                try { localStorage.setItem(BG_STORAGE_KEY, '#efe6e6'); } catch (e) {}
            }

            const savedFont = localStorage.getItem('dealsync_font_color') || '#3636ab';
            const savedHeaderBg = localStorage.getItem('dealsync_header_bg_color') || '#ededed';
            const savedSidebarBg = localStorage.getItem('dealsync_sidebar_bg_color') || '#f8f1f1';
            const savedAmbient = localStorage.getItem(AMBIENT_STORAGE_KEY) || 'classic';

            applyThemeMode(savedTheme);
            applyBranding(savedBranding);
            applyAccentColor(savedAccent);
            applyTenantCustomColors({
                page_bg_color: savedBg,
                font_color: savedFont,
                header_bg_color: savedHeaderBg,
                sidebar_bg_color: savedSidebarBg,
                accent_color: savedAccent
            });

            document.documentElement.style.setProperty('--bg-main', savedBg);
            document.body.style.backgroundColor = savedBg;

            if (savedAmbient && savedAmbient !== 'classic' && savedAmbient !== 'none') {
                applyAmbientTheme(savedAmbient);
            }
        })();

        function toggleDarkMode() {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            applyThemeMode(next);
            showToast(`Theme switched to ${next === 'dark' ? 'Dark Mode' : 'Light Mode'}`);
        }

        // =========================================================================
        // 2. TOAST NOTIFICATIONS HELPER
        // =========================================================================
        function showToast(message, type = 'info') {
            const container = document.getElementById('dealsync-toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = 'dealsync-toast';
            toast.innerHTML = `<i data-lucide="info" style="width: 15px; height: 15px; color: var(--color-primary);"></i> <span>${message}</span>`;
            container.appendChild(toast);
            lucide.createIcons();
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                toast.style.transition = 'all 0.25s ease';
                setTimeout(() => toast.remove(), 250);
            }, 3000);
        }

        // =========================================================================
        // 3. SIDEBAR PINNING & SLIDE-OUT INTERACTION
        // =========================================================================
        const sidebar = document.getElementById('dealsync-sidebar');
        const sidebarSpacer = document.getElementById('dealsync-sidebar-spacer');
        const pinBtn = document.getElementById('sidebar-pin-btn');

        let isPinned = localStorage.getItem('dealsync_sidebar_pinned') !== 'false';

        function applyPinState(pinned) {
            isPinned = pinned;
            localStorage.setItem('dealsync_sidebar_pinned', String(pinned));
            if (pinned) {
                sidebar.classList.add('is-pinned');
                sidebarSpacer.classList.add('is-pinned');
                if (pinBtn) {
                    pinBtn.title = "Unpin sidebar (hover slide-out mode)";
                    pinBtn.style.color = "var(--color-primary)";
                    pinBtn.style.background = "var(--color-primary-subtle)";
                }
            } else {
                sidebar.classList.remove('is-pinned');
                sidebarSpacer.classList.remove('is-pinned');
                if (pinBtn) {
                    pinBtn.title = "Pin sidebar open (Ctrl+B)";
                    pinBtn.style.color = "var(--zinc-400)";
                    pinBtn.style.background = "transparent";
                }
            }

            const headerBtn = document.getElementById('header-sidebar-toggle-btn');
            const headerIcon = document.getElementById('header-sidebar-toggle-icon');
            if (headerBtn) {
                headerBtn.title = pinned ? "Unpin Sidebar - Slide-Out on Hover (Ctrl+B)" : "Pin Sidebar Open (Ctrl+B)";
            }
            if (headerIcon) {
                headerIcon.setAttribute('data-lucide', pinned ? 'panel-left-close' : 'panel-left-open');
                if (window.lucide) lucide.createIcons();
            }
        }
        applyPinState(isPinned);

        window.toggleSidebarPin = function() {
            applyPinState(!isPinned);
        };

        pinBtn?.addEventListener('click', () => {
            applyPinState(!isPinned);
        });

        // Keyboard Shortcut Ctrl+B to toggle sidebar pin
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
                e.preventDefault();
                applyPinState(!isPinned);
            }
        });

        // Hover expand when unpinned
        sidebar?.addEventListener('mouseenter', () => {
            if (!isPinned) {
                sidebar.classList.add('is-expanded', 'is-floating');
            }
        });
        sidebar?.addEventListener('mouseleave', () => {
            if (!isPinned) {
                sidebar.classList.remove('is-expanded', 'is-floating');
            }
        });

        // =========================================================================
        // 4. UNIVERSAL DATA TABLES ENGINE:
        //    - VARIABLE COLUMN WIDTH RESIZING (DRAG & PERSIST)
        //    - ASCENDING / DESCENDING SORTING WITH INDICATORS
        //    - CHECKBOX SELECTION
        //    - DENSITY TOGGLE (COMPACT / COMFORTABLE)
        //    - LIVE SEARCH FILTER
        // =========================================================================
        document.addEventListener('DOMContentLoaded', () => {
            initUniversalDataTables();
        });

        function initUniversalDataTables() {
            document.querySelectorAll('.data-table').forEach((table, tableIndex) => {
                const tableId = table.id || `crm_table_${tableIndex}`;
                table.id = tableId;

                // 1. Column Resizing
                initColumnResizing(table, tableId);

                // 2. Sorting
                initTableSorting(table);

                // 3. Row Selection Checkboxes
                initTableSelection(table);
            });
        }

        // --- Column Resizing Engine ---
        function initColumnResizing(table, tableId) {
            const storageKey = `dealsync_col_widths_${tableId}`;
            let savedWidths = {};
            try {
                savedWidths = JSON.parse(localStorage.getItem(storageKey) || '{}');
            } catch (e) {}

            const headers = table.querySelectorAll('thead th');
            headers.forEach((th, colIndex) => {
                // Apply saved width if any
                const colKey = th.getAttribute('data-col') || `col_${colIndex}`;
                if (savedWidths[colKey]) {
                    th.style.width = savedWidths[colKey] + 'px';
                    th.style.minWidth = savedWidths[colKey] + 'px';
                }

                // Append resizer handle if not already present
                if (!th.querySelector('.table-col-resizer') && !th.classList.contains('col-index')) {
                    const resizer = document.createElement('div');
                    resizer.className = 'table-col-resizer';
                    resizer.title = 'Drag to resize column (Double click to reset)';

                    resizer.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        e.stopPropagation();

                        const startX = e.clientX;
                        const startWidth = th.offsetWidth;
                        resizer.classList.add('is-resizing');

                        function onMouseMove(moveEvent) {
                            const diff = moveEvent.clientX - startX;
                            const newWidth = Math.max(50, startWidth + diff);
                            th.style.width = newWidth + 'px';
                            th.style.minWidth = newWidth + 'px';
                        }

                        function onMouseUp() {
                            resizer.classList.remove('is-resizing');
                            document.removeEventListener('mousemove', onMouseMove);
                            document.removeEventListener('mouseup', onMouseUp);

                            // Save to localStorage
                            savedWidths[colKey] = th.offsetWidth;
                            try {
                                localStorage.setItem(storageKey, JSON.stringify(savedWidths));
                            } catch (e) {}
                        }

                        document.addEventListener('mousemove', onMouseMove);
                        document.addEventListener('mouseup', onMouseUp);
                    });

                    // Double click to reset width
                    resizer.addEventListener('dblclick', (e) => {
                        e.stopPropagation();
                        th.style.width = '';
                        th.style.minWidth = '';
                        delete savedWidths[colKey];
                        try {
                            localStorage.setItem(storageKey, JSON.stringify(savedWidths));
                        } catch (e) {}
                    });

                    th.appendChild(resizer);
                }
            });
        }

        // --- Ascending / Descending Sorting Engine ---
        function initTableSorting(table) {
            const headers = table.querySelectorAll('thead th');
            headers.forEach((th, colIndex) => {
                if (th.classList.contains('col-index') || th.classList.contains('no-sort')) return;

                th.style.cursor = 'pointer';

                // Add sort indicator icon placeholder if needed
                if (!th.querySelector('.sort-indicator')) {
                    const indicator = document.createElement('span');
                    indicator.className = 'sort-indicator';
                    indicator.style.display = 'inline-flex';
                    indicator.style.alignItems = 'center';
                    indicator.style.marginLeft = '6px';
                    indicator.style.opacity = '0.5';
                    indicator.innerHTML = '↕';
                    th.appendChild(indicator);
                }

                th.addEventListener('click', (e) => {
                    // Ignore clicks on the resizer handle
                    if (e.target.classList.contains('table-col-resizer')) return;

                    const currentDir = th.getAttribute('data-sort-dir');
                    const newDir = currentDir === 'asc' ? 'desc' : 'asc';

                    // Reset all other headers in this table
                    headers.forEach(h => {
                        h.removeAttribute('data-sort-dir');
                        h.classList.remove('is-sorted');
                        const ind = h.querySelector('.sort-indicator');
                        if (ind) ind.innerHTML = '↕';
                    });

                    th.setAttribute('data-sort-dir', newDir);
                    th.classList.add('is-sorted');
                    const ind = th.querySelector('.sort-indicator');
                    if (ind) ind.innerHTML = newDir === 'asc' ? '▲' : '▼';

                    sortTableRows(table, colIndex, newDir);
                });
            });
        }

        function sortTableRows(table, colIndex, direction) {
            const tbody = table.querySelector('tbody');
            if (!tbody) return;
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((rowA, rowB) => {
                const cellA = rowA.children[colIndex]?.innerText.trim() || '';
                const cellB = rowB.children[colIndex]?.innerText.trim() || '';

                // Strip currency, commas, and percentage signs for numeric comparison
                const numA = parseFloat(cellA.replace(/[^0-9.-]+/g, ''));
                const numB = parseFloat(cellB.replace(/[^0-9.-]+/g, ''));

                if (!isNaN(numA) && !isNaN(numB) && !cellA.match(/[a-zA-Z]{3,}/)) {
                    return direction === 'asc' ? numA - numB : numB - numA;
                }

                return direction === 'asc' 
                    ? cellA.localeCompare(cellB) 
                    : cellB.localeCompare(cellA);
            });

            // Re-append sorted rows to tbody
            rows.forEach(r => tbody.appendChild(r));
        }

        // --- Checkbox Selection & Master Select All ---
        function initTableSelection(table) {
            const masterCheckbox = table.querySelector('thead th.col-index input[type="checkbox"]');
            const rowCheckboxes = table.querySelectorAll('tbody td.col-index input[type="checkbox"]');

            if (masterCheckbox) {
                masterCheckbox.addEventListener('change', () => {
                    const checked = masterCheckbox.checked;
                    rowCheckboxes.forEach(cb => {
                        cb.checked = checked;
                        const row = cb.closest('tr');
                        if (row) row.classList.toggle('is-selected', checked);
                    });
                    updateSelectionCount(table);
                });
            }

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    const row = cb.closest('tr');
                    if (row) row.classList.toggle('is-selected', cb.checked);
                    if (masterCheckbox) {
                        const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
                        masterCheckbox.checked = allChecked;
                    }
                    updateSelectionCount(table);
                });
            });
        }

        function updateSelectionCount(table) {
            const checkedCount = table.querySelectorAll('tbody td.col-index input[type="checkbox"]:checked').length;
            let bar = table.parentElement.querySelector('.table-selection-bar');
            if (!bar) {
                bar = document.createElement('div');
                bar.className = 'table-selection-bar';
                table.parentElement.appendChild(bar);
            }
            if (checkedCount > 0) {
                bar.classList.add('is-visible');
                bar.innerHTML = `
                    <span>✓ ${checkedCount} record(s) selected</span>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="alert('Exporting ${checkedCount} selected rows to CSV...');">Export Selected</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="clearTableSelection('${table.id}');">Deselect All</button>
                `;
            } else {
                bar.classList.remove('is-visible');
            }
        }

        function clearTableSelection(tableId) {
            const table = document.getElementById(tableId);
            if (!table) return;
            table.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
                const row = cb.closest('tr');
                if (row) row.classList.remove('is-selected');
            });
            updateSelectionCount(table);
        }

        // --- Table Density Switcher ---
        function setTableDensity(tableId, density) {
            const table = document.getElementById(tableId);
            if (!table) return;
            table.classList.remove('is-compact', 'is-comfortable');
            if (density === 'compact') table.classList.add('is-compact');
            if (density === 'comfortable') table.classList.add('is-comfortable');
            showToast(`Table density set to ${density}`);
        }

        // --- Table Row Search Filter ---
        function filterTableRows(tableId, query) {
            const table = document.getElementById(tableId);
            if (!table) return;
            const rows = table.querySelectorAll('tbody tr');
            const q = query.toLowerCase().trim();
            rows.forEach(r => {
                const text = r.innerText.toLowerCase();
                r.style.display = text.includes(q) ? '' : 'none';
            });
        }

        // Global search input in header
        const globalSearch = document.getElementById('global-search-input');
        globalSearch?.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.data-table tbody tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
            });
        });

        // =========================================================================
        // 5. MODAL HELPERS & NOTIFICATIONS DROPDOWN
        // =========================================================================
        function openNewRecordModal(moduleName) {
            const modal = document.getElementById('new-record-modal');
            if (modal) {
                modal.classList.add('is-open');
                if (moduleName) {
                    const select = document.getElementById('new-record-module-select');
                    if (select) select.value = moduleName;
                }
            }
        }

        function openThemeModal() {
            const modal = document.getElementById('theme-palette-modal');
            if (modal) modal.classList.add('is-open');
        }

        function openUserProfileModal() {
            const modal = document.getElementById('user-profile-modal');
            if (modal) modal.classList.add('is-open');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.remove('is-open');
        }

        // Persona filter pill switcher in Dashboard
        function switchPersona(persona) {
            document.querySelectorAll('.persona-pill-btn').forEach(btn => {
                if (btn.getAttribute('data-persona') === persona) {
                    btn.style.background = '#0f172a';
                    btn.style.color = '#ffffff';
                } else {
                    btn.style.background = '#ffffff';
                    btn.style.color = '#475569';
                }
            });

            document.querySelectorAll('.kpi-persona-group').forEach(group => {
                if (group.getAttribute('data-persona') === persona) {
                    group.style.display = 'grid';
                } else {
                    group.style.display = 'none';
                }
            });
        }

        // Notification Dropdown Toggle
        const notifBtn = document.getElementById('notif-dropdown-btn');
        const notifMenu = document.getElementById('notif-dropdown-menu');
        notifBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            if (notifMenu) {
                notifMenu.style.display = notifMenu.style.display === 'block' ? 'none' : 'block';
            }
        });
        document.addEventListener('click', () => {
            if (notifMenu) notifMenu.style.display = 'none';
        });
    </script>
</body>
</html>
