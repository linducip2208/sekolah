/* =====================================================================
   Sikad Pro — Global frontend behaviour (Alpine CDN components)
   Loaded as a classic <script> (via Vite::asset) BEFORE the Alpine CDN
   defer script so every x-data="..." factory is already defined.
   No ES imports/exports so it runs as a plain script.
   ===================================================================== */
(function () {
    'use strict';

    var LS_PREFIX = 'sikadpro:';

    function storageGet(key, fallback) {
        try {
            var v = localStorage.getItem(LS_PREFIX + key);
            return v === null ? fallback : JSON.parse(v);
        } catch (e) { return fallback; }
    }
    function storageSet(key, value) {
        try { localStorage.setItem(LS_PREFIX + key, JSON.stringify(value)); } catch (e) {}
    }
    function debounce(fn, wait) {
        var t; return function () {
            var args = arguments, ctx = this;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, wait);
        };
    }

    /* -----------------------------------------------------------
       Theme manager (light / dark / system)
       ----------------------------------------------------------- */
    window.themeManager = function () {
        var media = window.matchMedia('(prefers-color-scheme: dark)');
        var stored = storageGet('theme', 'system');
        function apply(mode) {
            var dark = mode === 'dark' || (mode === 'system' && media.matches);
            document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
        }
        media.addEventListener('change', function () { if (storageGet('theme', 'system') === 'system') apply('system'); });
        apply(stored);
        return {
            mode: stored,
            set: function (mode) { storageSet('theme', mode); this.mode = mode; apply(mode); },
        };
    };

    /* -----------------------------------------------------------
       Sidebar state — mobile drawer + desktop collapse (persisted)
       ----------------------------------------------------------- */
    window.sidebarState = function () {
        var collapsed = storageGet('sidebarCollapsed', false);
        var isMobile = function () { return window.innerWidth < 1024; };
        return {
            open: !isMobile(),
            collapsed: collapsed,
            mobile: isMobile(),
            toggleMobile: function () { this.open = !this.open; },
            closeMobile: function () { this.open = false; },
            toggleCollapse: function () {
                this.collapsed = !this.collapsed;
                storageSet('sidebarCollapsed', this.collapsed);
            },
            onResize: function () {
                this.mobile = isMobile();
                this.open = !this.mobile;
            },
            init: function () {
                var self = this;
                window.addEventListener('resize', function () { self.onResize(); });
            },
        };
    };

    /* -----------------------------------------------------------
       Toast store (global) — window.dispatchToast(type, message)
       ----------------------------------------------------------- */
    window.toastStore = function () {
        var store = {
            toasts: [],
            add: function (t) {
                var id = Date.now() + Math.random();
                store.toasts.push({ id: id, type: t.type || 'info', message: t.message || '' });
                setTimeout(function () { store.remove(id); }, t.duration || 5000);
            },
            remove: function (id) { store.toasts = store.toasts.filter(function (x) { return x.id !== id; }); },
        };
        window.addEventListener('sikadpro:toast', function (e) { store.add(e.detail || {}); });
        return store;
    };
    window.dispatchToast = function (type, message) {
        window.dispatchEvent(new CustomEvent('sikadpro:toast', { detail: { type: type, message: message } }));
    };

    /* -----------------------------------------------------------
       Confirmation dialog (global) — window.dispatchConfirm(opts)
       opts: { title, message, confirmLabel, cancelLabel, danger, onConfirm }
       Also: global [data-confirm] click handler for inline usage.
       ----------------------------------------------------------- */
    window.confirmDialog = function () {
        var store = {
            open: false,
            title: '', message: '', confirmLabel: 'Hapus', cancelLabel: 'Batal', danger: true,
            pending: null,
            ask: function (o) {
                store.title = o.title || 'Konfirmasi';
                store.message = o.message || '';
                store.confirmLabel = o.confirmLabel || 'Lanjutkan';
                store.cancelLabel = o.cancelLabel || 'Batal';
                store.danger = o.danger !== false;
                store.pending = o.onConfirm || null;
                store.open = true;
            },
            confirm: function () { var cb = store.pending; store.open = false; store.pending = null; if (cb) cb(); },
            cancel: function () { store.open = false; store.pending = null; },
        };
        window.addEventListener('sikadpro:confirm', function (e) { store.ask(e.detail || {}); });
        return store;
    };
    window.dispatchConfirm = function (opts) {
        window.dispatchEvent(new CustomEvent('sikadpro:confirm', { detail: opts }));
    };
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-confirm]');
        if (!el) return;
        e.preventDefault();
        var message = el.getAttribute('data-confirm');
        window.dispatchConfirm({
            title: el.getAttribute('data-confirm-title') || 'Konfirmasi',
            message: message,
            confirmLabel: el.getAttribute('data-confirm-label') || 'Lanjutkan',
            danger: el.getAttribute('data-confirm-danger') !== 'false',
            onConfirm: function () {
                if (el.tagName === 'FORM') { el.submit(); }
                else if (el.getAttribute('href')) { window.location.href = el.getAttribute('href'); }
                else { el.click(); }
            },
        });
    }, true);

    /* -----------------------------------------------------------
       Offline indicator — mirrors offline-sync.js events
       ----------------------------------------------------------- */
    window.offlineIndicator = function () {
        var state = { show: false, queueCount: 0 };
        function onOnline(e) { state.show = !(e.detail && e.detail.online); }
        function onQueue(e) { state.queueCount = (e.detail && e.detail.count) || 0; }
        window.addEventListener('sikadpro:online-change', onOnline);
        window.addEventListener('sikadpro:queue-changed', onQueue);
        return {
            show: false,
            queueCount: 0,
            init: function () {
                var self = this;
                window.addEventListener('sikadpro:online-change', function (e) {
                    self.show = !(e.detail && e.detail.online);
                });
                window.addEventListener('sikadpro:queue-changed', function (e) {
                    self.queueCount = (e.detail && e.detail.count) || 0;
                });
            },
            dismiss: function () { this.show = false; },
        };
    };

    /* -----------------------------------------------------------
       Generic dropdown (click outside + escape)
       ----------------------------------------------------------- */
    window.dropdown = function () {
        return {
            open: false,
            toggle: function () { this.open = !this.open; },
            close: function () { this.open = false; },
            init: function () {
                var self = this;
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape') self.open = false; });
            },
        };
    };

    /* -----------------------------------------------------------
       Command palette (Cmd/Ctrl + K) — search + actions + navigation
       ----------------------------------------------------------- */
    window.commandPalette = function (cfg) {
        cfg = cfg || {};
        var searchUrl = cfg.searchUrl || '';
        var actions = cfg.actions || [];
        var nav = cfg.nav || [];
        var maxRecent = 5;

        return {
            open: false,
            query: '',
            loading: false,
            error: false,
            results: [],
            filteredActions: [],
            filteredNav: [],
            active: 0,
            mode: 'idle', // idle | search | results
            recent: storageGet('recentSearches', []),
            restoreFocus: null,

            init: function () {
                var self = this;
                window.addEventListener('open-search', function () { self.show(); });
                document.addEventListener('keydown', function (e) {
                    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                        e.preventDefault(); self.show();
                    }
                });
            },
            show: function () {
                this.restoreFocus = document.activeElement;
                this.open = true;
                this.query = '';
                this.results = [];
                this.active = 0;
                this.mode = 'idle';
                this.filteredActions = actions;
                this.filteredNav = nav;
                var self = this;
                this.$nextTick(function () { self.$refs.input.focus(); });
            },
            hide: function () {
                this.open = false;
                if (this.restoreFocus && this.restoreFocus.focus) this.restoreFocus.focus();
            },
            totalCount: function () {
                return this.filteredActions.length + this.filteredNav.length + (this.mode === 'results' ? this.results.length : 0);
            },
            onInput: debounce(function () {
                var self = this;
                var q = this.query.trim();
                if (q.length < 2) {
                    this.mode = 'idle';
                    this.filteredActions = actions.filter(function (a) { return (a.title + ' ' + (a.group || '')).toLowerCase().includes(q.toLowerCase()); });
                    this.filteredNav = nav.filter(function (n) { return (n.title + ' ' + (n.group || '')).toLowerCase().includes(q.toLowerCase()); });
                    this.results = [];
                    this.active = 0;
                    return;
                }
                this.mode = 'search';
                this.loading = true;
                this.error = false;
                this.filteredActions = [];
                this.filteredNav = [];
                fetch(searchUrl + '?q=' + encodeURIComponent(q))
                    .then(function (r) {
                        if (!r.ok) throw new Error('HTTP ' + r.status);
                        return r.json();
                    })
                    .then(function (data) {
                        self.results = (data.results || []).map(function (r) { return { group: 'search', icon: r.icon, title: r.title, sub: r.sub, url: r.url }; });
                        self.mode = 'results';
                        self.active = 0;
                    })
                    .catch(function () {
                        self.error = true;
                        self.mode = 'error';
                    })
                    .finally(function () { self.loading = false; });
            }, 250),
            choose: function (item) {
                if (item.url) {
                    this.pushRecent(this.query.trim());
                    this.hide();
                    window.location.href = item.url;
                }
            },
            openActive: function () {
                if (this.mode === 'results' && this.results[this.active]) {
                    this.choose(this.results[this.active]);
                    return;
                }
                var offset = this.mode === 'results' ? this.results.length : 0;
                var i = this.active - offset;
                if (i < 0) return;
                if (i < this.filteredActions.length) { this.choose(this.filteredActions[i]); }
                else {
                    var j = i - this.filteredActions.length;
                    if (this.filteredNav[j]) this.choose(this.filteredNav[j]);
                }
            },
            move: function (dir) {
                var total = this.totalCount();
                if (total === 0) return;
                this.active = (this.active + dir + total) % total;
                this.$nextTick(function () {
                    var el = this.$el.querySelector('.command-item.active');
                    if (el) el.scrollIntoView({ block: 'nearest' });
                });
            },
            onKeydown: function (e) {
                if (e.key === 'Escape') { this.hide(); return; }
                if (e.key === 'ArrowDown') { e.preventDefault(); this.move(1); return; }
                if (e.key === 'ArrowUp') { e.preventDefault(); this.move(-1); return; }
                if (e.key === 'Enter') { e.preventDefault(); this.openActive(); return; }
                if (e.key === 'Tab') {
                    e.preventDefault();
                    this.move(e.shiftKey ? -1 : 1);
                }
            },
            pushRecent: function (q) {
                if (!q || q.length < 2) return;
                var r = this.recent.filter(function (x) { return x !== q; });
                r.unshift(q);
                r = r.slice(0, maxRecent);
                this.recent = r;
                storageSet('recentSearches', r);
            },
            runRecent: function (q) {
                this.query = q;
                this.onInput();
            },
        };
    };

    /* -----------------------------------------------------------
       Favorites (sidebar pin) — global store + star injection
       ----------------------------------------------------------- */
    var favState = { items: storageGet('favorites', []) };
    window.favorites = {
        all: function () { return favState.items; },
        has: function (href) { return favState.items.some(function (i) { return i.href === href; }); },
        toggle: function (label, href) {
            if (window.favorites.has(href)) {
                favState.items = favState.items.filter(function (i) { return i.href !== href; });
            } else {
                favState.items.unshift({ label: label, href: href });
                favState.items = favState.items.slice(0, 12);
            }
            storageSet('favorites', favState.items);
            window.dispatchEvent(new CustomEvent('sikadpro:favorites-changed'));
        },
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.sidebar-link, .sidebar-sub-link').forEach(function (link) {
            var href = link.getAttribute('href');
            if (!href || href === '#' || link.querySelector('.fav-toggle')) return;
            var label = link.textContent.replace(/\s+/g, ' ').trim();
            var star = document.createElement('button');
            star.type = 'button';
            star.className = 'fav-toggle' + (window.favorites.has(href) ? ' on' : '');
            star.setAttribute('aria-label', 'Tambahkan ke favorit');
            star.innerHTML = '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.98 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>';
            star.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                window.favorites.toggle(label, href);
                star.classList.toggle('on', window.favorites.has(href));
            });
            link.appendChild(star);
        });
    });
})();
