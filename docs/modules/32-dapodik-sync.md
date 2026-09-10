# Module 32 — Dapodik Sync

## Status

REQUIRES EXTERNAL CONFIGURATION — architecture, preview/confirm flow, queue, mapping, conflicts, CSV, dan fake adapter tersedia; live Dapodik endpoint serta credential sekolah belum dapat diverifikasi tanpa akses eksternal.

## Flow

`Configure connection → test connection → fetch/CSV → normalize → preview → user confirms → queued sync → external-id mapping → import/update → conflict/error summary`.

Default direction adalah Dapodik → SIKAD. Tidak ada destructive sync otomatis. Matching memakai `dapodik_id` atau mapping table, bukan nama.

## Data model

Legacy `dapodik_config` dan `dapodik_sync_logs` dipertahankan. Struktur enterprise menambahkan `dapodik_connections`, `dapodik_sync_runs`, `dapodik_sync_items`, `dapodik_entity_mappings`, dan `dapodik_conflicts`. Credential disimpan encrypted dan tidak dikembalikan raw oleh API.

## Supported entities

Preview API menerima `students`, `staff`, `class_sections`, dan `subjects`. Student sync end-to-end tersedia. Entitas lain ditolak saat sync sampai normalizer/domain mapping masing-masing disediakan, sehingga tidak ada klaim import palsu.

## API

- `GET/PUT /api/v1/admin/dapodik/config`
- `POST /api/v1/admin/dapodik/test-connection`
- `POST /api/v1/admin/dapodik/preview`
- `GET /api/v1/admin/dapodik/runs`
- `POST /api/v1/admin/dapodik/runs/{runId}/confirm`
- `GET /api/v1/admin/dapodik/conflicts`
- `POST /api/v1/admin/dapodik/conflicts/{id}/resolve`
- CSV import/export legacy path.

Endpoint paths dikonfigurasi oleh sekolah di field mapping `_endpoints`; adapter tidak menebak URL vendor. Job memiliki retry dan timeout.
