# Module 33 — Visitor Management

## Status

✅ COMPLETE untuk canonical registration, blacklist, pre-registration, approval, QR/badge, check-in, check-out, active visitor list, tenant isolation, dan audit trail.

## Flow

`Visitor register/pre-register → identity and blacklist check → select same-school host → optional approval → QR/badge → security check-in → host notification integration point → visit → check-out → badge returned → audit log`.

Public registration must resolve an active school by subdomain or `school` slug. It never uses a first-school or ID-1 fallback.

## Data model

Legacy `visitor_logs`, `visitor_blacklist`, dan QR session tetap dipertahankan. Canonical enterprise tables: `visitors`, `visitor_visits`, `visitor_blacklists`, `visitor_badges`, dan `visitor_audit_logs`.

## API and routes

- `GET/POST /kunjungan` public pre-registration;
- `GET /api/v1/visitors`;
- `GET /api/v1/visitors/active`;
- `POST /api/v1/visitors/check-in`;
- `POST /api/v1/visitors/pre-register`;
- `POST /api/v1/visitors/{id}/approve`;
- `POST /api/v1/visitors/{id}/check-out`;
- `POST /api/v1/visitor/scan` QR scan compatibility path.

Actions use Sanctum plus `visitor.view`/`visitor.manage`. Every canonical mutation records actor, school, event, IP, user agent, dan metadata. Camera upload, notification provider, dan physical badge printer remain deployment integrations.
