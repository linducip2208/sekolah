{{-- Elite Academy shared <head> assets — Eton/Harrow/Phillips Exeter aesthetic --}}
@php $p = $platform ?? app(\App\Services\PlatformSettingsService::class)->all(); @endphp

@if(!empty($p['favicon_url']))
    <link rel="icon" href="{{ $p['favicon_url'] }}">
    <link rel="apple-touch-icon" href="{{ $p['favicon_url'] }}">
@else
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%230b1d3a' d='M12 2 2 7l10 5 10-5-10-5zm0 7L2 14l10 5 10-5-10-5z'/%3E%3Cpath fill='%23b8860b' d='M12 14 2 9v5l10 5 10-5V9l-10 5z'/%3E%3C/svg%3E">
@endif

<meta name="theme-color" content="{{ $p['color_primary'] ?? '#0b1d3a' }}">

{{-- PWA --}}
<link rel="manifest" href="/manifest.webmanifest">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(()=>{}));
}
</script>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>

<style>
    :root {
        --c-primary: {{ $p['color_primary'] ?? '#0F766E' }};
        --c-secondary: {{ $p['color_secondary'] ?? '#134E4A' }};
        --c-accent: {{ $p['color_accent'] ?? '#F59E0B' }};
        --c-paper: {{ $p['color_paper'] ?? '#F8FAF9' }};
        --c-ink: #17201E;
        --c-muted: #66736F;
        --c-rule: #E2E8E5;
    }

    html { scroll-behavior: smooth; }
    body {
        font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        background: var(--c-paper);
        color: var(--c-ink);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .font-display { font-family: 'Plus Jakarta Sans', ui-sans-serif, sans-serif; font-weight: 700; }
    .font-serif   { font-family: 'Plus Jakarta Sans', ui-sans-serif, sans-serif; }
    .font-script  { font-family: 'Plus Jakarta Sans', ui-sans-serif, sans-serif; font-style: italic; font-weight: 500; }

    .elite-h1 { font-family: 'Plus Jakarta Sans', ui-sans-serif, sans-serif; font-weight: 800; letter-spacing: -.03em; line-height: 1.1; }
    .elite-h2 { font-family: 'Plus Jakarta Sans', ui-sans-serif, sans-serif; font-weight: 700; letter-spacing: -.025em; }
    .elite-h3 { font-family: 'Plus Jakarta Sans', ui-sans-serif, sans-serif; font-weight: 700; }
    .elite-lead { font-family: 'Plus Jakarta Sans', ui-sans-serif, sans-serif; font-weight: 400; font-size: 1.125rem; line-height: 1.6; color: #2d2a26; }

    .elite-kicker {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: .68rem;
        letter-spacing: .35em;
        text-transform: uppercase;
        font-weight: 600;
        color: var(--c-accent);
    }

    .elite-rule { display: inline-flex; align-items: center; gap: .75rem; color: var(--c-accent); }
    .elite-rule::before, .elite-rule::after { content: ''; display: block; width: 2.5rem; height: 1px; background: currentColor; opacity: .55; }

    .paper { background-color: var(--c-paper); }
    .ink-primary { color: var(--c-primary); }
    .ink-secondary { color: var(--c-secondary); }
    .ink-accent { color: var(--c-accent); }
    .bg-primary { background: var(--c-primary); }
    .bg-secondary { background: var(--c-secondary); }
    .bg-accent { background: var(--c-accent); }

    .border-rule { border-color: var(--c-rule); }

    .btn-elite {
        display: inline-flex; align-items: center; justify-content: center;
        padding: .85rem 1.85rem;
        font-family: 'Inter', sans-serif;
        font-size: .78rem;
        letter-spacing: .22em;
        text-transform: uppercase;
        font-weight: 600;
        border: 1px solid var(--c-primary);
        background: var(--c-primary);
        color: #fff;
        transition: all .25s ease;
    }
    .btn-elite:hover { background: var(--c-secondary); border-color: var(--c-secondary); }
    .btn-elite-ghost {
        display: inline-flex; align-items: center; justify-content: center;
        padding: .85rem 1.85rem;
        font-family: 'Inter', sans-serif;
        font-size: .78rem;
        letter-spacing: .22em;
        text-transform: uppercase;
        font-weight: 600;
        border: 1px solid var(--c-primary);
        background: transparent;
        color: var(--c-primary);
        transition: all .25s ease;
    }
    .btn-elite-ghost:hover { background: var(--c-primary); color: #fff; }
    .btn-elite-gold {
        display: inline-flex; align-items: center; justify-content: center;
        padding: .85rem 1.85rem;
        font-family: 'Inter', sans-serif;
        font-size: .78rem;
        letter-spacing: .22em;
        text-transform: uppercase;
        font-weight: 600;
        border: 1px solid var(--c-accent);
        background: var(--c-accent);
        color: #fff;
        transition: all .25s ease;
    }
    .btn-elite-gold:hover { filter: brightness(.92); }

    .ornament { position: relative; }
    .ornament::before { content: '❦'; display: block; font-family: serif; color: var(--c-accent); font-size: 1.5rem; line-height: 1; opacity: .85; }
    .ornament-center::before { content: '❦'; display: block; text-align: center; font-family: serif; color: var(--c-accent); font-size: 1.6rem; opacity: .85; margin-bottom: .75rem; }

    .crest-mark {
        display: inline-flex; align-items: center; justify-content: center;
        width: 3.5rem; height: 3.5rem;
        border: 1.5px solid var(--c-accent);
        color: var(--c-accent);
        background: rgba(184, 134, 11, .04);
    }
    .crest-mark.lg { width: 5rem; height: 5rem; }

    .quote-mark {
        font-family: 'Playfair Display', Georgia, serif;
        color: var(--c-accent);
        font-size: 5rem;
        line-height: 0.6;
        opacity: .45;
    }

    .deco-frame { position: relative; padding: 1.5rem; }
    .deco-frame::before, .deco-frame::after {
        content: ''; position: absolute; width: 1.5rem; height: 1.5rem;
        border: 1px solid var(--c-accent);
    }
    .deco-frame::before { top: 0; left: 0; border-right: 0; border-bottom: 0; }
    .deco-frame::after { bottom: 0; right: 0; border-left: 0; border-top: 0; }

    [x-cloak] { display: none !important; }

    .elite-card {
        background: #fff;
        border: 1px solid var(--c-rule);
        transition: all .3s ease;
    }
    .elite-card:hover { box-shadow: 0 18px 40px -20px rgba(11,29,58,.18); }

    /* Print-style footnote */
    .footnote { font-family: 'Cormorant Garamond', Georgia, serif; font-style: italic; color: #5a544c; }

    /* Form polishing — applied to all default inputs */
    input[type=text], input[type=email], input[type=password], input[type=url], input[type=tel],
    input[type=number], input[type=search], input[type=date], input[type=color], select, textarea {
        border-radius: 2px !important;
    }
    input:focus, select:focus, textarea:focus {
        --tw-ring-color: var(--c-accent) !important;
        border-color: var(--c-accent) !important;
    }

    .table-elite { font-family: 'Inter', sans-serif; }
    .table-elite thead th {
        font-size: .68rem; letter-spacing: .25em; text-transform: uppercase;
        font-weight: 600; color: var(--c-primary);
        border-bottom: 2px solid var(--c-primary);
        padding: 1rem .75rem;
    }
    .table-elite tbody td { padding: 1rem .75rem; border-bottom: 1px solid var(--c-rule); }
    .table-elite tbody tr:hover { background: rgba(184,134,11,.04); }

    /* =========================================================
       RESPONSIVE BASE — applies globally to every layout/page.
       Mobile-first; we scale UP at sm/md/lg/xl breakpoints.
       Target viewports: 375 / 414 / 768 / 1024 / 1280.
       ========================================================= */

    /* Auto-wrap every Tailwind <table> in a horizontal scroll container.
       Pair with .table-scroll wrapper in blade to be explicit. */
    .table-scroll {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-scroll > table { max-width: none; }

    /* Mobile drawer backdrop for sidebars */
    .sidebar-backdrop {
        position: fixed; inset: 0;
        background: rgba(11,29,58,.55);
        z-index: 39;
        backdrop-filter: blur(2px);
    }

    /* =================  TABLET (<= 1023px)  ================= */
    @media (max-width: 1023px) {
        .elite-h1 { font-size: clamp(1.6rem, 5vw, 2.4rem); }
        .elite-h2 { font-size: clamp(1.35rem, 4.2vw, 1.9rem); }
        .elite-h3 { font-size: clamp(1.05rem, 3vw, 1.35rem); }
        .elite-lead { font-size: 1.05rem; line-height: 1.55; }
        .deco-frame { padding: 1rem; }
        .btn-elite, .btn-elite-ghost, .btn-elite-gold {
            padding: .75rem 1.3rem; font-size: .72rem; letter-spacing: .16em;
        }
        .table-elite thead th, .table-elite tbody td { padding: .75rem .55rem; font-size: 12.5px; }
    }

    /* =================  MOBILE  (<= 640px)  ================= */
    @media (max-width: 640px) {
        body { font-size: 14px; }

        /* WCAG 2.5.5 touch targets — every interactive element ≥ 38px */
        button, .btn-elite, .btn-elite-ghost, .btn-elite-gold,
        a.btn-brand, input[type=submit], input[type=button] {
            min-height: 38px;
        }
        input[type=text], input[type=email], input[type=password], input[type=tel],
        input[type=number], input[type=search], input[type=date], input[type=url],
        select, textarea {
            min-height: 40px;
            font-size: 16px; /* prevent iOS zoom-on-focus */
        }

        /* Buttons should grow to full width when standalone */
        .btn-elite, .btn-elite-ghost, .btn-elite-gold {
            padding: .7rem 1.1rem; font-size: .68rem; letter-spacing: .14em;
        }

        /* Mobile-safe horizontal padding */
        .elite-h1 { font-size: clamp(1.4rem, 6vw, 2rem); }
        .elite-h2 { font-size: clamp(1.2rem, 5vw, 1.6rem); }
        .elite-h3 { font-size: 1.05rem; }
        .elite-kicker { font-size: .6rem; letter-spacing: .25em; }

        /* Tables — convert long admin tables to card-style rows
           by adding class .table-stack to <table>. Otherwise wrap in .table-scroll. */
        .table-stack thead { display: none; }
        .table-stack tbody tr {
            display: block;
            border: 1px solid var(--c-rule);
            margin-bottom: .75rem;
            padding: .5rem .25rem;
            background: #fff;
        }
        .table-stack tbody td {
            display: flex; justify-content: space-between; align-items: flex-start;
            gap: .75rem;
            padding: .55rem .75rem !important;
            border-bottom: 1px dashed var(--c-rule);
            font-size: 13px;
        }
        .table-stack tbody td:last-child { border-bottom: 0; }
        .table-stack tbody td::before {
            content: attr(data-label);
            font-family: 'Inter', sans-serif;
            font-size: .6rem;
            letter-spacing: .15em;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--c-primary);
            flex-shrink: 0;
        }

        /* Hide pure decoration on small screens to save vertical space */
        .deco-frame::before, .deco-frame::after { display: none; }
        .deco-frame { padding: .85rem; }

        /* Crest mark smaller */
        .crest-mark { width: 2.5rem; height: 2.5rem; }
        .crest-mark.lg { width: 3.5rem; height: 3.5rem; }

        /* Modal / drawer overrides */
        .sidebar-mobile-drawer {
            position: fixed !important;
            top: 0; left: 0; bottom: 0;
            width: 84vw !important;
            max-width: 320px !important;
            z-index: 40;
            box-shadow: 4px 0 24px rgba(0,0,0,.35);
        }
    }

    /* ==========  ACCESSIBILITY  ========== */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: .01ms !important;
            transition-duration: .01ms !important;
            scroll-behavior: auto !important;
        }
    }

    /* ==========  PRINT  ========== */
    @media print {
        .no-print, header, aside, nav, .sidebar-backdrop,
        .sidebar-mobile-drawer { display: none !important; }
        main { padding: 0 !important; }
        body { background: #fff !important; color: #000 !important; }
        .table-elite tbody tr:hover { background: transparent !important; }
    }

    /* ==========  SCROLLBARS — subtle  ========== */
    ::-webkit-scrollbar { width: 10px; height: 10px; }
    ::-webkit-scrollbar-track { background: rgba(11,29,58,.04); }
    ::-webkit-scrollbar-thumb {
        background: rgba(11,29,58,.25);
        border-radius: 6px;
        border: 2px solid var(--c-paper);
    }
    ::-webkit-scrollbar-thumb:hover { background: rgba(11,29,58,.45); }
</style>

<script>
// Auto-wrap any unwrapped <table> inside <main> with .table-scroll so wide
// tables become horizontally scrollable on small viewports without editing
// every view. Opt out by adding class "no-auto-scroll" to the table itself,
// any ancestor, or by pre-wrapping with .table-scroll / .table-stack.
document.addEventListener('DOMContentLoaded', () => {
    const tables = document.querySelectorAll('main table, .auto-scroll table');
    tables.forEach((t) => {
        if (!t.parentElement) return;
        if (t.parentElement.classList.contains('table-scroll')) return;
        if (t.classList.contains('no-auto-scroll') || t.closest('.no-auto-scroll')) return;
        if (t.classList.contains('table-stack')) return;
        const wrap = document.createElement('div');
        wrap.className = 'table-scroll';
        t.parentNode.insertBefore(wrap, t);
        wrap.appendChild(t);
    });
});
</script>
