@php
    $currentTenant = $currentTenant ?? (app()->bound('currentTenant') ? app('currentTenant') : \App\Models\Tenant::on('mysql')->first());
    $companyProfile = null;
    try {
        if ($currentTenant) {
            $currentTenant->connect();
            $companyProfile = \App\Models\CompanyProfile::on('tenant')->first();
        }
    } catch (\Throwable $e) {}

    $activeAccentHex = strtolower($companyProfile->accent_color ?? ($currentTenant->accent_color ?? '#06B6D4'));
    $palettePresets = [
        '#4f46e5' => ['primary' => '#4F46E5', 'hover' => '#4338CA', 'subtle' => '#EEF2FF', 'border' => '#C7D2FE', 'text' => '#3730A3', 'name' => 'Electric Violet'],
        '#10b981' => ['primary' => '#10B981', 'hover' => '#059669', 'subtle' => '#ECFDF5', 'border' => '#A7F3D0', 'text' => '#065F46', 'name' => 'Emerald Growth'],
        '#f59e0b' => ['primary' => '#F59E0B', 'hover' => '#D97706', 'subtle' => '#FFFBEB', 'border' => '#FDE68A', 'text' => '#92400E', 'name' => 'Sunset Amber'],
        '#f43f5e' => ['primary' => '#F43F5E', 'hover' => '#E11D48', 'subtle' => '#FFF1F2', 'border' => '#FECDD3', 'text' => '#9F1239', 'name' => 'Rose Flare'],
        '#06b6d4' => ['primary' => '#06B6D4', 'hover' => '#0891B2', 'subtle' => '#ECFEFF', 'border' => '#A5F3FC', 'text' => '#0E7490', 'name' => 'Cyber Cyan'],
        '#475569' => ['primary' => '#475569', 'hover' => '#334155', 'subtle' => '#F1F5F9', 'border' => '#CBD5E1', 'text' => '#1E293B', 'name' => 'Slate Tech'],
        '#09090b' => ['primary' => '#09090B', 'hover' => '#18181B', 'subtle' => '#F4F4F5', 'border' => '#E4E4E7', 'text' => '#09090B', 'name' => 'Obsidian Dark'],
    ];
    $activePalette = $palettePresets[$activeAccentHex] ?? [
        'primary' => $activeAccentHex,
        'hover'   => $activeAccentHex,
        'subtle'  => $activeAccentHex . '1a',
        'border'  => $activeAccentHex . '4d',
        'text'    => $activeAccentHex,
        'name'    => 'Custom Accent'
    ];

    $pageBgColor      = $companyProfile->page_bg_color ?? '#0f172a';
    $fontColor        = $companyProfile->font_color ?? '#1e293b';
    $headerBgColor    = $companyProfile->header_bg_color ?? '#312e81';
    $sidebarBgColor   = $companyProfile->sidebar_bg_color ?? '#18181b';
    $tableBgColor     = $companyProfile->table_bg_color ?? '#ffffff';
    $tableBorderColor = $companyProfile->table_border_color ?? '#e2e8f0';
    $appearanceSettingsRaw = $companyProfile->appearance_settings ?? null;

    if (!function_exists('dealsync_is_hex_dark')) {
        function dealsync_is_hex_dark($hex) {
            $hex = ltrim($hex ?? '', '#');
            if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
            if (strlen($hex) !== 6) return false;
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return sqrt(0.299 * ($r * $r) + 0.587 * ($g * $g) + 0.114 * ($b * $b)) < 128;
        }
    }
    $isHeaderDark = dealsync_is_hex_dark($headerBgColor);
    $isSidebarDark = dealsync_is_hex_dark($sidebarBgColor);
    $isPageDark = dealsync_is_hex_dark($pageBgColor);
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="light" data-branding="apple" data-header-dark="{{ $isHeaderDark ? 'true' : 'false' }}" data-sidebar-dark="{{ $isSidebarDark ? 'true' : 'false' }}" data-page-dark="{{ $isPageDark ? 'true' : 'false' }}">
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
    <!-- Tailwind CSS (Play CDN for instant dynamic utility classes) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: ['class', '[data-theme="dark"]'],
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '{{ $activePalette["hover"] }}',
                            600: '{{ $activePalette["primary"] }}',
                            700: '{{ $activePalette["hover"] }}',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        zinc: {
                            50: '#fafafa',
                            100: '#f4f4f5',
                            200: '#e4e4e7',
                            300: '#d4d4d8',
                            400: '#a1a1aa',
                            500: '#71717a',
                            600: '#52525b',
                            700: '#3f3f46',
                            800: '#27272a',
                            900: '#18181b',
                            950: '#09090b',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        display: ['Inter Tight', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    }
                }
            }
        };
    </script>

    <!-- DealSync Static Design System Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/dealsync.css') }}?v=2.4">

    <!-- Tenant Dynamic CSS Variables (Directly rendered from Company Profile / Tenant settings) -->
    <style id="dealsync-tenant-root-styles">
        :root {
            /* Font Families */
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            --font-display: 'Inter Tight', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

            /* Brand Accent & Theme (Dynamically driven by tenant accent_color) */
            --color-primary: {{ $activePalette['primary'] }};
            --color-primary-hover: {{ $activePalette['hover'] }};
            --color-primary-subtle: {{ $activePalette['subtle'] }};
            --color-primary-border: {{ $activePalette['border'] }};
            --color-primary-text: {{ $activePalette['text'] }};

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

            /* Canvas & Surfaces (Dynamically driven by Tenant Admin Company Profile) */
            --bg-main: {{ $pageBgColor }};
            --header-bg: {{ $headerBgColor }};
            --sidebar-bg: {{ $sidebarBgColor }};
            --table-bg: {{ $tableBgColor }};
            --bg-card: {{ $tableBgColor }};
            --bg-card-hover: {{ $tableBgColor }};
            --border-card: {{ $tableBorderColor }};
            --table-border: {{ $tableBorderColor }};
            --border-subtle: {{ $tableBorderColor }};

            /* Text Scale */
            --text-primary: {{ $fontColor }};
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
    </style>
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 font-sans text-zinc-900 dark:text-zinc-100 antialiased min-h-screen overflow-x-hidden selection:bg-indigo-500 selection:text-white">
    <!-- Top SPA Navigation Progress Bar -->
    <div id="dealsync-spa-progress" class="fixed top-0 left-0 h-[2.5px] bg-gradient-to-r from-indigo-500 via-indigo-600 to-purple-500 z-[9999] opacity-0 transition-all duration-200 pointer-events-none" style="width: 0%;"></div>

    <div class="flex min-h-screen w-full bg-zinc-50 dark:bg-zinc-950">
        <!-- Sidebar Layout Spacer: occupies layout space in flex flow so main content doesn't shift on hover -->
        <div
            id="dealsync-sidebar-spacer"
            class="sidebar-spacer {{ (request()->is('settings*') || request()->is('admin*')) ? 'is-admin-mode' : '' }}"
            aria-hidden="true"
        ></div>

        <!-- Fixed Sidebar: solid, fixed, non-shifting -->
        @include('partials.sidebar')

        <!-- Main Workspace (no shifting, rock solid) -->
        <div id="main-content-wrapper" class="flex-1 flex flex-col min-w-0 min-h-screen bg-zinc-50 dark:bg-zinc-950">
            <!-- Header Partial -->
            @include('partials.header')

            <!-- Body View Container -->
            <main class="content-body flex-1 w-full p-4 sm:p-6 lg:p-8 max-w-[1680px] mx-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Modals Partial -->
    @include('partials.modals')

    <!-- Toast Container -->
    <div id="dealsync-toast-container"></div>

    <!-- DealSync Core Engine (Themes, Palettes, Data Tables, Modals, Shortcuts) -->
    <script src="{{ asset('js/dealsync-core.js') }}?v=2.4"></script>

    <!-- DealSync SPA Seamless Router (Browser pushState, popstate & DOM swapper) -->
    <script src="{{ asset('js/spa-navigation.js') }}?v=2.4"></script>

    <!-- Server Tenant Settings Initializer -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }

            // Initialize Themes & Tenant settings from Server
            if (typeof initThemes === 'function') {
                initThemes({
                    pageBg: '{{ $pageBgColor }}',
                    fontColor: '{{ $fontColor }}',
                    headerBg: '{{ $headerBgColor }}',
                    sidebarBg: '{{ $sidebarBgColor }}',
                    tableBg: '{{ $tableBgColor }}',
                    tableBorder: '{{ $tableBorderColor }}',
                    primaryColor: '{{ $activePalette["primary"] }}',
                    appearanceRaw: {!! json_encode($appearanceSettingsRaw ? (is_string($appearanceSettingsRaw) ? json_decode($appearanceSettingsRaw, true) : $appearanceSettingsRaw) : null) !!}
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
