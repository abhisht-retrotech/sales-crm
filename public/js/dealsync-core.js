/**
 * DealSync Enterprise Core Engine
 * Extracted from app.blade.php
 *
 * Includes:
 * - Theme Engine (Light/Dark mode, Apple/Revolut styles, Brand Accents, Canvas BGs)
 * - Tenant Custom Colors & Wallpaper Background Image Engine
 * - Dynamic Contrast Detection (dealsyncIsColorDark)
 * - Toast Notifications
 * - Sidebar Pinning, Hover Floating & Keyboard Shortcuts
 * - Universal Data Tables Engine (Column resizing, Sorting, Checkbox selection, Density, Search)
 * - Modal & Dropdown Controllers
 */

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

window.__dealSyncConfig = window.__dealSyncConfig || {};

function applyThemeMode(mode) {
    document.documentElement.setAttribute('data-theme', mode);
    try { localStorage.setItem(THEME_STORAGE_KEY, mode); } catch (e) {}
    // Update quick toggle icon
    const darkIcon = document.getElementById('theme-toggle-icon');
    if (darkIcon && window.lucide) {
        darkIcon.setAttribute('data-lucide', mode === 'dark' ? 'sun' : 'moon');
        lucide.createIcons();
    }
}

function applyBranding(brand) {
    document.documentElement.setAttribute('data-branding', brand);
    try { localStorage.setItem(BRANDING_STORAGE_KEY, brand); } catch (e) {}
}

const COLOR_PALETTES_MAP = {
    '#4f46e5': { primary: '#4F46E5', hover: '#4338CA', subtle: '#EEF2FF', border: '#C7D2FE', text: '#3730A3', name: 'Electric Violet' },
    '#10b981': { primary: '#10B981', hover: '#059669', subtle: '#ECFDF5', border: '#A7F3D0', text: '#065F46', name: 'Emerald Growth' },
    '#f59e0b': { primary: '#F59E0B', hover: '#D97706', subtle: '#FFFBEB', border: '#FDE68A', text: '#92400E', name: 'Sunset Amber' },
    '#f43f5e': { primary: '#F43F5E', hover: '#E11D48', subtle: '#FFF1F2', border: '#FECDD3', text: '#9F1239', name: 'Rose Flare' },
    '#06b6d4': { primary: '#06B6D4', hover: '#0891B2', subtle: '#ECFEFF', border: '#A5F3FC', text: '#0E7490', name: 'Cyber Cyan' },
    '#475569': { primary: '#475569', hover: '#334155', subtle: '#F1F5F9', border: '#CBD5E1', text: '#1E293B', name: 'Slate Tech' },
    '#09090b': { primary: '#09090B', hover: '#18181B', subtle: '#F4F4F5', border: '#E4E4E7', text: '#09090B', name: 'Obsidian Dark' }
};

function applyAccentColor(hex) {
    if (!hex) return;
    const cleanHex = hex.startsWith('#') ? hex : ('#' + hex);
    const lower = cleanHex.toLowerCase();
    const palette = COLOR_PALETTES_MAP[lower] || {
        primary: cleanHex,
        hover: cleanHex,
        subtle: cleanHex + '1a',
        border: cleanHex + '4d',
        text: cleanHex,
        name: cleanHex
    };

    const root = document.documentElement;
    root.style.setProperty('--color-primary', palette.primary);
    root.style.setProperty('--color-primary-hover', palette.hover);
    root.style.setProperty('--color-primary-subtle', palette.subtle);
    root.style.setProperty('--color-primary-border', palette.border);
    root.style.setProperty('--color-primary-text', palette.text);
    root.style.setProperty('--primary-500', palette.hover);
    root.style.setProperty('--primary-600', palette.primary);
    root.style.setProperty('--primary-700', palette.hover);

    try { 
        localStorage.setItem(ACCENT_STORAGE_KEY, palette.primary); 
        window.dispatchEvent(new CustomEvent('dealsync:accent-changed', { detail: palette }));
    } catch (e) {}
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

function dealsyncIsColorDark(hex) {
    if (!hex || typeof hex !== 'string') return false;
    hex = hex.replace('#', '').trim();
    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
    if (hex.length !== 6) return false;
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    const hsp = Math.sqrt(0.299 * (r * r) + 0.587 * (g * g) + 0.114 * (b * b));
    return hsp < 128;
}

function applyAppearanceBackgroundImages(settingsInput) {
    let settings = {};
    if (typeof settingsInput === 'string') {
        try { settings = JSON.parse(settingsInput); } catch (e) { settings = {}; }
    } else if (settingsInput && typeof settingsInput === 'object') {
        settings = settingsInput;
    }

    let styleEl = document.getElementById('dealsync-custom-appearance-style');
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = 'dealsync-custom-appearance-style';
        document.head.appendChild(styleEl);
    }

    let cssRules = '';

    // Page background image
    if (settings.page?.mode === 'image' && settings.page.imageUrl) {
        const p = settings.page;
        const opacity = (p.opacity !== undefined ? p.opacity : 100) / 100;
        const brightness = p.brightness !== undefined ? p.brightness : 100;
        const contrast = p.contrast !== undefined ? p.contrast : 100;
        const blur = p.softness !== undefined ? p.softness : 0;
        const bgSize = p.fit === 'repeat' ? 'auto' : p.fit === 'contain' ? 'contain' : 'cover';
        const bgRepeat = p.fit === 'repeat' ? 'repeat' : 'no-repeat';

        cssRules += `
          body {
            position: relative !important;
            background-color: transparent !important;
          }
          body::before {
            content: "" !important;
            position: fixed !important;
            inset: 0 !important;
            z-index: -9999 !important;
            background-image: url("${p.imageUrl}") !important;
            background-size: ${bgSize} !important;
            background-repeat: ${bgRepeat} !important;
            background-position: center center !important;
            background-attachment: fixed !important;
            opacity: ${opacity} !important;
            filter: brightness(${brightness}%) contrast(${contrast}%) blur(${blur}px) !important;
            pointer-events: none !important;
          }
        `;
    }

    // Header background image
    if (settings.header?.mode === 'image' && settings.header.imageUrl) {
        const h = settings.header;
        const opacity = (h.opacity !== undefined ? h.opacity : 100) / 100;
        const brightness = h.brightness !== undefined ? h.brightness : 100;
        const contrast = h.contrast !== undefined ? h.contrast : 100;
        const blur = h.softness !== undefined ? h.softness : 0;
        const bgSize = h.fit === 'repeat' ? 'auto' : h.fit === 'contain' ? 'contain' : 'cover';
        const bgRepeat = h.fit === 'repeat' ? 'repeat' : 'no-repeat';

        cssRules += `
          #dealsync-header, .app-header {
            position: sticky !important;
            top: 0 !important;
            z-index: 30 !important;
          }
          #dealsync-header::before, .app-header::before {
            content: "" !important;
            position: absolute !important;
            inset: 0 !important;
            z-index: 0 !important;
            background-image: url("${h.imageUrl}") !important;
            background-size: ${bgSize} !important;
            background-repeat: ${bgRepeat} !important;
            background-position: center center !important;
            opacity: ${opacity} !important;
            filter: brightness(${brightness}%) contrast(${contrast}%) blur(${blur}px) !important;
            pointer-events: none !important;
          }
          #dealsync-header > *, .app-header > * {
            position: relative !important;
            z-index: 1 !important;
          }
        `;
    }

    // Sidebar background image
    if (settings.sidebar?.mode === 'image' && settings.sidebar.imageUrl) {
        const s = settings.sidebar;
        const opacity = (s.opacity !== undefined ? s.opacity : 100) / 100;
        const brightness = s.brightness !== undefined ? s.brightness : 100;
        const contrast = s.contrast !== undefined ? s.contrast : 100;
        const blur = s.softness !== undefined ? s.softness : 0;
        const bgSize = s.fit === 'repeat' ? 'auto' : s.fit === 'contain' ? 'contain' : 'cover';
        const bgRepeat = s.fit === 'repeat' ? 'repeat' : 'no-repeat';

        cssRules += `
          #dealsync-sidebar, .fixed-sidebar-container {
            position: fixed !important;
            overflow: hidden !important;
          }
          #dealsync-sidebar::before, .fixed-sidebar-container::before {
            content: "" !important;
            position: absolute !important;
            inset: 0 !important;
            z-index: 0 !important;
            background-image: url("${s.imageUrl}") !important;
            background-size: ${bgSize} !important;
            background-repeat: ${bgRepeat} !important;
            background-position: center center !important;
            opacity: ${opacity} !important;
            filter: brightness(${brightness}%) contrast(${contrast}%) blur(${blur}px) !important;
            pointer-events: none !important;
          }
          #dealsync-sidebar > *, .fixed-sidebar-container > * {
            position: relative !important;
            z-index: 1 !important;
          }
        `;
    }

    // Table / Card background image
    if (settings.table?.mode === 'image' && settings.table.imageUrl) {
        const t = settings.table;
        const opacity = (t.opacity !== undefined ? t.opacity : 100) / 100;
        const brightness = t.brightness !== undefined ? t.brightness : 100;
        const contrast = t.contrast !== undefined ? t.contrast : 100;
        const blur = t.softness !== undefined ? t.softness : 0;
        const bgSize = t.fit === 'repeat' ? 'auto' : t.fit === 'contain' ? 'contain' : 'cover';
        const bgRepeat = t.fit === 'repeat' ? 'repeat' : 'no-repeat';

        cssRules += `
          .card, .card-table-wrapper {
            position: relative !important;
            overflow: hidden !important;
          }
          .card::before, .card-table-wrapper::before {
            content: "" !important;
            position: absolute !important;
            inset: 0 !important;
            z-index: 0 !important;
            background-image: url("${t.imageUrl}") !important;
            background-size: ${bgSize} !important;
            background-repeat: ${bgRepeat} !important;
            background-position: center center !important;
            opacity: ${opacity} !important;
            filter: brightness(${brightness}%) contrast(${contrast}%) blur(${blur}px) !important;
            pointer-events: none !important;
          }
          .card > *, .card-table-wrapper > * {
            position: relative !important;
            z-index: 1 !important;
          }
          .data-table td {
            background-color: transparent !important;
          }
        `;
    }

    styleEl.textContent = cssRules;
    try {
        localStorage.setItem('dealsync_appearance_settings', JSON.stringify(settings));
    } catch (e) {}
}

function applyTenantCustomColors(colors) {
    if (!colors) return;
    const root = document.documentElement;

    if (colors.page_bg_color) {
        root.style.setProperty('--bg-main', colors.page_bg_color);
        if (document.body) document.body.style.backgroundColor = colors.page_bg_color;
        const isDark = dealsyncIsColorDark(colors.page_bg_color);
        root.setAttribute('data-page-dark', isDark ? 'true' : 'false');
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
        const isDark = dealsyncIsColorDark(colors.header_bg_color);
        root.setAttribute('data-header-dark', isDark ? 'true' : 'false');
        const headerEl = document.getElementById('dealsync-header');
        if (headerEl) headerEl.style.setProperty('background', colors.header_bg_color, 'important');
        try { localStorage.setItem('dealsync_header_bg_color', colors.header_bg_color); } catch (e) {}
    }
    if (colors.sidebar_bg_color) {
        root.style.setProperty('--sidebar-bg', colors.sidebar_bg_color);
        const isDark = dealsyncIsColorDark(colors.sidebar_bg_color);
        root.setAttribute('data-sidebar-dark', isDark ? 'true' : 'false');
        const sidebarEl = document.getElementById('dealsync-sidebar');
        if (sidebarEl) sidebarEl.style.setProperty('background', colors.sidebar_bg_color, 'important');
        try { localStorage.setItem('dealsync_sidebar_bg_color', colors.sidebar_bg_color); } catch (e) {}
    }
    if (colors.table_bg_color) {
        root.style.setProperty('--table-bg', colors.table_bg_color);
        root.style.setProperty('--bg-card', colors.table_bg_color);
        root.style.setProperty('--bg-card-hover', colors.table_bg_color);
        try { localStorage.setItem('dealsync_table_bg_color', colors.table_bg_color); } catch (e) {}
    }
    if (colors.table_border_color) {
        root.style.setProperty('--table-border', colors.table_border_color);
        root.style.setProperty('--border-card', colors.table_border_color);
        root.style.setProperty('--border-subtle', colors.table_border_color);
        try { localStorage.setItem('dealsync_table_border_color', colors.table_border_color); } catch (e) {}
    }
    if (colors.accent_color) {
        applyAccentColor(colors.accent_color);
    }
    if (colors.appearance_settings !== undefined) {
        applyAppearanceBackgroundImages(colors.appearance_settings);
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
        const savedBg = localStorage.getItem(BG_STORAGE_KEY) || window.__dealSyncConfig?.pageBg || '#fafafa';
        applyPageBackground(savedBg);
        showToast('Reset theme to Classic Clean');
    }
}

function resetThemeDefaults() {
    applyThemeMode('light');
    applyBranding('apple');
    applyAccentColor(window.__dealSyncConfig?.primaryColor || '#4F46E5');
    applyAmbientTheme('classic');
    applyPageBackground(window.__dealSyncConfig?.pageBg || '#fafafa');
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

// Initialize Theme from Server Tenant Settings & localStorage
function initThemes(config) {
    if (config) {
        window.__dealSyncConfig = Object.assign(window.__dealSyncConfig, config);
    }
    const serverBg = window.__dealSyncConfig.pageBg || '#fafafa';
    const serverFont = window.__dealSyncConfig.fontColor || '#09090b';
    const serverHeaderBg = window.__dealSyncConfig.headerBg || '#ffffff';
    const serverSidebarBg = window.__dealSyncConfig.sidebarBg || '#ffffff';
    const serverTableBg = window.__dealSyncConfig.tableBg || '#ffffff';
    const serverTableBorder = window.__dealSyncConfig.tableBorder || '#e2e8f0';
    const serverAccent = window.__dealSyncConfig.primaryColor || '#4F46E5';
    const serverAppearance = window.__dealSyncConfig.appearanceRaw || null;

    const savedTheme = localStorage.getItem(THEME_STORAGE_KEY) || 'light';
    const savedBranding = localStorage.getItem(BRANDING_STORAGE_KEY) || 'apple';

    applyThemeMode(savedTheme);
    applyBranding(savedBranding);

    // Apply Server Tenant appearance settings
    applyTenantCustomColors({
        page_bg_color: serverBg,
        font_color: serverFont,
        header_bg_color: serverHeaderBg,
        sidebar_bg_color: serverSidebarBg,
        table_bg_color: serverTableBg,
        table_border_color: serverTableBorder,
        accent_color: serverAccent,
        appearance_settings: serverAppearance
    });
}

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
    if (window.lucide) lucide.createIcons();
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.25s ease';
        setTimeout(() => toast.remove(), 250);
    }, 3000);
}

// =========================================================================
// 3. SIDEBAR PINNING & SLIDE-OUT INTERACTION (1:1 React Parity)
// =========================================================================
let isPinned = localStorage.getItem('dealsync_sidebar_collapsed') !== null
    ? localStorage.getItem('dealsync_sidebar_collapsed') !== 'true'
    : (localStorage.getItem('dealsync_sidebar_pinned') !== 'false');

function applyPinState(pinned) {
    isPinned = pinned;
    try {
        localStorage.setItem('dealsync_sidebar_collapsed', String(!pinned));
        localStorage.setItem('dealsync_sidebar_pinned', String(pinned));
    } catch(e) {}

    const sidebar = document.getElementById('dealsync-sidebar');
    const sidebarSpacer = document.getElementById('dealsync-sidebar-spacer');
    const pinBtn = document.getElementById('sidebar-pin-btn');
    const pinIcon = document.getElementById('sidebar-pin-icon');
    const footerPinText = document.getElementById('sidebar-footer-pin-text');
    const footerPinIcon = document.getElementById('sidebar-footer-pin-icon');

    if (pinned) {
        if (sidebar) {
            sidebar.classList.remove('is-collapsed', 'is-floating');
        }
        if (sidebarSpacer) {
            sidebarSpacer.classList.remove('is-collapsed');
        }
        if (pinBtn) {
            pinBtn.title = "Unpin sidebar (Ctrl+B)";
            pinBtn.style.color = "var(--color-primary, #4338ca)";
            pinBtn.style.background = "var(--color-primary-subtle, #eef2ff)";
            pinBtn.style.borderColor = "var(--color-primary-border, #c7d2fe)";
        }
        if (pinIcon) {
            pinIcon.style.transform = 'rotate(45deg)';
        }
        if (footerPinText) footerPinText.textContent = 'Pinned';
        if (footerPinIcon) footerPinIcon.style.transform = 'rotate(45deg)';
    } else {
        if (sidebar) {
            sidebar.classList.add('is-collapsed');
            sidebar.classList.remove('is-floating');
        }
        if (sidebarSpacer) {
            sidebarSpacer.classList.add('is-collapsed');
        }
        if (pinBtn) {
            pinBtn.title = "Pin sidebar open (Ctrl+B)";
            pinBtn.style.color = "var(--zinc-400, #94a3b8)";
            pinBtn.style.background = "transparent";
            pinBtn.style.borderColor = "transparent";
        }
        if (pinIcon) {
            pinIcon.style.transform = 'none';
        }
        if (footerPinText) footerPinText.textContent = 'Pin';
        if (footerPinIcon) footerPinIcon.style.transform = 'none';
    }

    const headerBtn = document.getElementById('header-sidebar-toggle-btn');
    const headerIcon = document.getElementById('header-sidebar-toggle-icon');
    if (headerBtn) {
        headerBtn.title = pinned ? "Unpin Sidebar (Ctrl+B)" : "Pin Sidebar Open (Ctrl+B)";
    }
    if (headerIcon && window.lucide) {
        headerIcon.setAttribute('data-lucide', pinned ? 'panel-left-close' : 'panel-left-open');
    }
    if (window.lucide) lucide.createIcons();
}

function toggleSidebarPin() {
    applyPinState(!isPinned);
}

// Hover expand with 120ms debounce when unpinned
let sidebarHoverTimeout = null;

function initSidebarListeners() {
    const sidebar = document.getElementById('dealsync-sidebar');
    const pinBtn = document.getElementById('sidebar-pin-btn');

    pinBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        applyPinState(!isPinned);
    });

    sidebar?.addEventListener('mouseenter', () => {
        if (sidebarHoverTimeout) {
            clearTimeout(sidebarHoverTimeout);
            sidebarHoverTimeout = null;
        }
        if (!isPinned && sidebar.classList.contains('is-collapsed')) {
            sidebar.classList.add('is-floating');
            if (window.lucide) lucide.createIcons();
        }
    });

    sidebar?.addEventListener('mouseleave', () => {
        if (!isPinned && sidebar.classList.contains('is-collapsed')) {
            sidebarHoverTimeout = setTimeout(() => {
                sidebar.classList.remove('is-floating');
            }, 120);
        }
    });

    // Apply saved pin state
    applyPinState(isPinned);
}

// Keyboard Shortcut Ctrl+B or Ctrl+[ to toggle sidebar pin
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && (e.key.toLowerCase() === 'b' || e.key === '[')) {
        e.preventDefault();
        applyPinState(!isPinned);
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

// Global initialization on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    initSidebarListeners();
    initUniversalDataTables();

    // Global search input in header
    const globalSearch = document.getElementById('global-search-input');
    globalSearch?.addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase().trim();
        document.querySelectorAll('.data-table tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // Notification Dropdown Toggle
    const notifBtn = document.getElementById('notif-dropdown-btn');
    const notifMenu = document.getElementById('notif-dropdown-menu');
    notifBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        if (notifMenu) {
            notifMenu.style.display = notifMenu.style.display === 'block' ? 'none' : 'block';
        }
    });
});

// Expose on window for global blade template triggers (e.g. onclick="..." and subagents)
window.applyThemeMode = applyThemeMode;
window.applyBranding = applyBranding;
window.applyAccentColor = applyAccentColor;
window.dealsyncIsColorDark = dealsyncIsColorDark;
window.applyAppearanceBackgroundImages = applyAppearanceBackgroundImages;
window.applyTenantCustomColors = applyTenantCustomColors;
window.handleHeaderTenantChange = handleHeaderTenantChange;
window.applyPageBackground = applyPageBackground;
window.applyAmbientTheme = applyAmbientTheme;
window.resetThemeDefaults = resetThemeDefaults;
window.resetTableColWidths = resetTableColWidths;
window.initThemes = initThemes;
window.toggleDarkMode = toggleDarkMode;
window.showToast = showToast;
window.applyPinState = applyPinState;
window.toggleSidebarPin = toggleSidebarPin;
window.initUniversalDataTables = initUniversalDataTables;
window.initColumnResizing = initColumnResizing;
window.initTableSorting = initTableSorting;
window.sortTableRows = sortTableRows;
window.initTableSelection = initTableSelection;
window.updateSelectionCount = updateSelectionCount;
window.clearTableSelection = clearTableSelection;
window.setTableDensity = setTableDensity;
window.filterTableRows = filterTableRows;
window.openNewRecordModal = openNewRecordModal;
window.openThemeModal = openThemeModal;
window.openUserProfileModal = openUserProfileModal;
window.closeModal = closeModal;
window.switchPersona = switchPersona;
