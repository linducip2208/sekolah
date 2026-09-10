# SIKAD Pro — Design System

## Prinsip

1. **Semantic tokens** — view tidak pernah hardcode warna; semua lewat `var(--color-*)`.
2. **White-label safe** — token brand (`--c-*`) dioverride per sekolah via `BrandingService` + route `branding.css`; token semantik mewarisi brand.
3. **Dark mode first-class** — setiap token punya pasangan `html[data-theme="dark"]`.
4. **Satu icon family** — inline SVG stroke (heroicons outline path), tanpa emoji untuk UI chrome.
5. **Komponen, bukan copy-paste** — semua pola UI lewat Blade component `x-ui.*`.

## Token (`resources/css/app.css` §1)

### Brand
| Token | Light | Keterangan |
|---|---|---|
| `--color-primary` | `var(--c-primary, #2563EB)` | Enterprise Blue |
| `--color-primary-hover` | `var(--c-secondary, #1D4ED8)` | |
| `--color-accent` | `var(--c-accent, #F59E0B)` | Warm Amber |
| `--color-sidebar` | `var(--c-sidebar, primary)` | Sidebar gradient |

### Semantic
`--color-success` #15803D · `--color-warning` #D97706 · `--color-danger` #DC2626 · `--color-info` #2563EB — masing-masing punya `-soft` (10–14% alpha).

### Surface & Text
`--color-background` #F8FAFC · `--color-surface` #FFFFFF · `--color-border` #E2E8F0 · `--color-text` #0F172A · `--color-text-secondary` #64748B · `--color-text-muted` #94A3B8

### Dark mode
Background #0F172A · Surface #1E293B · Border rgba(255,255,255,.10) · Text #F1F5F9. Badge/alert memakai warna terang khusus agar kontras AA terjaga.

### Radius / Shadow / Motion
`--radius-sm|md|lg|xl` 10/12/16/22px · `--shadow-xs…lg` · `--duration-fast|base|slow` 150/200/300ms + `--ease` cubic-bezier(.16,1,.3,1).

## Typography — Manrope

| Elemen | Kelas | Ukuran |
|---|---|---|
| Page title | `.page-title` | 22–26px / 700 / tracking -0.02em |
| Section title | `.section-title` | 16px / 700 |
| Body | `body` | 14px / 1.5 |
| Caption/hint | `.form-hint` | 12px |
| Kicker (legacy elite) | `.elite-kicker` | 11px uppercase |

## Component Inventory

### `x-ui.*` — primitives
| Komponen | Props utama |
|---|---|
| `x-ui.button` | href, variant (primary/secondary/ghost/success/danger), icon, size |
| `x-ui.badge` | variant (default/success/warning/danger/info/accent/primary) |
| `x-ui.status` | status — **33 status → tone terpusat** (paid=Lunas/success, overdue=Terlambat/danger, dst.) |
| `x-ui.icon` | name — 30+ path SVG |
| `x-ui.avatar` | name, size, img |
| `x-ui.input / select / textarea` | standar + `.label`, `.form-hint`, `.form-error` |
| `x-ui.card` | padding, hover |

### `x-ui.*` — composition (baru)
| Komponen | Fungsi |
|---|---|
| `x-ui.page-header` | Header halaman: title + subtitle + back link + action slot |
| `x-ui.stat` | KPI card: label, value, tone, delta (+/-), hint, href, icon |
| `x-ui.alert-card` | Alert actionable: tone + count + CTA |
| `x-ui.tabs` | Link-tabs accessible (aria-selected, underline aktif, scroll-x) |
| `x-ui.timeline` | Timeline: items[{title,time,desc,tone,done}] |
| `x-ui.filter-bar` | Filter chips dari query string + clear-all + **Saved Views** |
| `x-ui.drawer` | Slide-over accessible (focus trap via x-trap, ESC) |

### `x-feedback.*`
`empty-state` (icon+title+desc+action) · `error-state` · `skeleton`.

### `x-navigation.*`
`breadcrumbs` · `command-palette` (⌘K) · `notification-center` · `profile-menu` · `theme-toggle` · `help-center`.

### `x-overlays.*`
`confirm-dialog` (typed confirmation untuk aksi kritis) · `toast`.

## Aturan Penulisan

1. **Jangan hardcode warna** di view — pakai token atau `x-ui.*`.
2. **Status selalu `x-ui.status`** — dilarang badge manual per modul (konsistensi antar modul).
3. **Ikon bukan emoji** — `x-ui.icon` / inline SVG path.
4. **Empty state wajib kontekstual** — sebutkan apa yang kosong + CTA.
5. **Aksi destruktif wajib `data-confirm`** dengan deskripsi dampak.
6. **Touch target ≥44px** di mobile; input 16px di mobile (anti zoom iOS).
7. **Portal legacy classes** (`bg-white`, `text-gray-*`) diizinkan di portal — sudah di-map ke token via compat layer app.css §20.
