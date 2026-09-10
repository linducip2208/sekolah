# Module 27 — Cafeteria / Kantin Cashless

## Status

✅ COMPLETE untuk wallet ledger, top-up, purchase, limit, refund, tenant isolation, idempotency, dan accounting hook.

## Flow

`Parent/student top-up → wallet credit ledger → scan/order menu → lock wallet row → validate limit/category/stock → debit ledger → receipt/order → optional accounting posting`.

`Refund approved → wallet credit ledger → refund record → optional reversal journal`.

## Data model

Legacy `canteen_wallets`, `canteen_topups`, `canteen_orders`, categories, dan menu tetap dipakai. Migration enterprise menambahkan `canteen_merchants`, `canteen_order_items`, immutable `wallet_transactions`, `wallet_refunds`, `wallet_settlements`, idempotency key, monthly limit, low-balance threshold, negative-balance flag, dan transfer flag.

Saldo cached pada `canteen_wallets.balance` hanya untuk read cepat. Saldo authoritative dihitung dari total credit dikurangi debit. Transfer antar-wallet disabled by default.

## API

- `GET /api/v1/canteen/menu`
- `GET /api/v1/canteen/wallet/{studentId}`
- `POST /api/v1/canteen/wallet/{studentId}/topup`
- `GET /api/v1/canteen/wallet/{studentId}/transactions`
- `POST /api/v1/canteen/orders`
- `GET /api/v1/canteen/orders/today`
- `PUT /api/v1/canteen/orders/{id}/status`
- `POST /api/v1/canteen/orders/{id}/refund`

Endpoint menggunakan Sanctum, tenant scope, ownership parent/student, dan permission `canteen.view`/`canteen.manage`.

## Security and accounting

Mutasi menggunakan transaction + `lockForUpdate`. Duplicate request dengan `Idempotency-Key` tidak membuat transaksi kedua. Menu dan student selalu diperiksa berada pada sekolah yang sama. Jika COA sekolah tersedia, top-up, sale, dan refund diposting sekali berdasarkan reference key.

## External configuration

Payment gateway dan notifikasi saldo rendah tetap membutuhkan provider credential dari sekolah/platform owner.
