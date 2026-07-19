# eSchool SaaS — API Documentation

## OpenAPI Specification

Full API spec di `openapi.yaml`. Open with:

- **Swagger UI:** https://editor.swagger.io/ — paste content
- **Redoc:** `npx @redocly/cli preview-docs openapi.yaml`
- **Postman:** Import → File → `openapi.yaml`

## Quick Reference (290+ endpoints across 11 phases)

### Public (no auth)
- `GET  /api/v1/branding/{subdomain}` — Branding for app boot
- `POST /api/v1/payments/webhook/{providerSlug}` — Gateway webhooks (signature verified per-provider)
- `GET  /api/v1/public/ppdb/{subdomain}/periods` — PPDB periods
- `POST /api/v1/public/ppdb/{subdomain}/register` — PPDB registration
- `GET  /api/v1/public/donations/{subdomain}/campaigns` — Donation campaigns
- `POST /api/v1/public/donations/{subdomain}/campaigns/{slug}/donate` — Donate
- `GET  /api/v1/public/events/{subdomain}` — Event listing
- `GET  /api/v1/public/alumni/{subdomain}` — Alumni directory
- `POST /api/v1/devices/gps-ping` — Vehicle GPS device push
- `POST /api/v1/devices/gate-scan` — ID gate device scan

### Authentication
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET  /api/v1/auth/me`

### Payments (Module 11b — Dynamic gateway)
- `GET  /api/v1/payments/methods`
- `POST /api/v1/payments/initiate` (with `Idempotency-Key` header)
- `GET  /api/v1/payments/{referenceNo}`
- `POST /api/v1/payments/{referenceNo}/cancel`
- `GET  /api/v1/admin/payment-providers` (CRUD)
- `GET  /api/v1/admin/payment-providers/presets/list` — Optional preset templates

### AI Assistant (Module 31 — BYOK, dynamic)
- `POST /api/v1/ai/study-assistant`
- `POST /api/v1/ai/lesson-plan`
- `POST /api/v1/ai/essay-grade`
- `GET  /api/v1/admin/ai/providers` (CRUD)
- `GET  /api/v1/admin/ai/usage` — Cost dashboard

### PPDB (Module 22)
- Admin: `GET /api/v1/admin/ppdb/applications`
- Admin: `POST /api/v1/admin/ppdb/{periodId}/run-selection`

### Transport (Module 23)
- Parent: `GET /api/v1/parent/children/{studentId}/bus-location`
- Parent: `GET /api/v1/parent/children/{studentId}/gate-events`

### Medical / UKS (Module 24)
- Nurse: `GET/POST /api/v1/medical/visits`
- `GET/PUT /api/v1/medical/students/{id}/record`

### Counseling (Module 25)
- `GET/POST /api/v1/counseling/sessions`
- `POST /api/v1/counseling/bullying-reports` — Anonymous supported
- `POST /api/v1/wellness/checkin`
- `GET  /api/v1/wellness/at-risk`

### Daily Report (Module 43)
- Parent: `GET /api/v1/parent/children/{studentId}/daily-reports`
- Admin: `POST /api/v1/admin/daily-reports/generate`

### Analytics (Module 45)
- `GET  /api/v1/analytics/risk-scores/at-risk`
- `GET  /api/v1/analytics/risk-scores/student/{id}`

## Multi-tenant Behavior

Setiap endpoint authenticated otomatis di-scope ke `school_id` user yang login.
Cross-school data tidak akan pernah ter-leak (di-enforce via `SchoolScope` global scope).

Public endpoints (no auth) mendapat `school_id` dari URL parameter (`subdomain`).

## Rate Limits

- Public webhook: unlimited (must respond <5s)
- Authenticated: 60 req/menit per user
- AI features: configured per `ai_models.input_price_per_1k` cost ceiling

## Idempotency

Critical mutating endpoints support `Idempotency-Key` header:
- `/payments/initiate` — duplicate key returns same transaction (24h cache)

## Webhooks (Inbound)

`POST /api/v1/payments/webhook/{providerSlug}` — gateway server-to-server callback.
Signature verification configured per-provider via `extra_config.signature`:

```json
{
  "signature": {
    "method": "sha512" | "sha256" | "hmac_sha256" | "hmac_sha512",
    "fields": ["order_id", "status_code", "gross_amount"],
    "signature_field": "signature_key",
    "signature_header": "x-callback-token"
  }
}
```

Invalid signature → 401, logged to `payment_webhook_logs`.
Duplicate event → 200 OK (idempotent), no state change.

## Error Format

```json
{
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

HTTP status codes:
- `200` OK
- `201` Created
- `401` Unauthenticated
- `403` Forbidden (cross-school access)
- `404` Not found
- `422` Validation / business rule error
- `429` Rate limit
- `500` Server error
