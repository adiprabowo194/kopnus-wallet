# 💰 KOPNUS Wallet API Documentation

**Base URL**: `http://localhost:8000/api`
**Content-Type**: `application/json`
**Version**: `1.0.0`
**Last Updated**: `2026-04-29`

---

# 📚 Table of Contents

1. [Flow System](#1-flow-system)
2. [Standard Response](#2-standard-response)
3. [Error Codes](#3-error-codes)
4. [Endpoint API](#4-endpoint-api)
5. [Rate Limiter](#5-rate-limiter)
6. [Concurrency Handling](#6-concurrency-handling)
7. [Setup & Run](#7-setup--run)
8. [Testing](#8-testing)
9. [API Documentation](#9-api-documentation)

---

# 1. Flow System

```
routes/api.php
   ↓
RateLimiter
   ↓
WalletController
   ↓
Exception
   ↓
Model (Member, Transaction)
   ↓
Database
   ↓
Resource (JsonResponse)

```

---

# 2. Standar Response

## ✅ Success

```json
{
    "status": "success",
    "code": 200,
    "message": "Success message",
    "data": {}
}
```

---

## ❌ Error

```json
{
    "status": "error",
    "code": 400,
    "message": "Error message"
}
```

---

# ⚠️ 3. Error Codes

| Code | Description                        |
| ---- | ---------------------------------- |
| 200  | Success                            |
| 400  | Bad Request                        |
| 403  | Member tidak aktif                 |
| 404  | Member tidak ditemukan             |
| 422  | Validasi gagal / saldo tidak cukup |
| 429  | Rate limit / banyak request        |
| 500  | Server error                       |

---

# 🚀 4. Endpoint API

---

## 🟢 1. Get Balance

### Endpoint

```
GET /wallet/{memberCode}/balance
```

### Response

```json
{
    "status": "success",
    "code": 200,
    "message": "Saldo ditampilkan.",
    "data": {
        "member_code": "MBR-0001",
        "name": "Budi Santoso",
        "balance": 500000
    }
}
```

---

## 💰 2. Deposit

### Endpoint

```
POST /wallet/{memberCode}/deposit
```

### Request Body

```json
{
    "amount": 50000,
    "description": "Top up saldo"
}
```

---

### Response

```json
{
    "status": "success",
    "code": 200,
    "message": "Deposit berhasil.",
    "data": {
        "id": 4,
        "reference_no": "TXN-20260429-030820-PCnO2",
        "type": "deposit",
        "amount": 100000,
        "balance_before": 600000,
        "balance_after": 700000,
        "description": "Top Saldo",
        "member": {
            "member_code": "MBR-0001",
            "name": "Budi Santoso"
        },
        "created_at": "2026-04-29 03:08:20"
    }
}
```

---

## 💸 3. Withdraw

### Endpoint

```
POST /wallet/{memberCode}/withdraw
```

### Request Body

```json
{
    "amount": 50000,
    "description": "Tarik saldo"
}
```

---

### Response

```json
{
    "status": "success",
    "code": 200,
    "message": "Withdraw berhasil.",
    "data": {
        "id": 3,
        "reference_no": "TXN-20260429-030719-JPN7J",
        "type": "withdraw",
        "amount": 100000,
        "balance_before": 700000,
        "balance_after": 600000,
        "description": null,
        "member": {
            "member_code": "MBR-0001",
            "name": "Budi Santoso"
        },
        "created_at": "2026-04-29 03:07:19"
    }
}
```

---

# 🔐 5. Rate Limiter

| Endpoint | Limit              |
| -------- | ------------------ |
| global   | 60 request / menit |

---

# ⚡ 6. Race Condition Handling

Menggunakan database transaction + locking:

### Tanpa Lock

- Bisa terjadi double withdraw
- Saldo tidak konsisten

### Dengan Lock

- Request kedua menunggu (Lock for update)
- Data tetap aman

---

# ⚙️ 7. Setup & Run

```bash
git clone https://github.com/kopnus-wallet
cd kopnus-wallet

composer install
cp .env.example .env
php artisan key:generate

php artisan migrate --seed

php artisan serve
```

---

# 🧪 8. Testing

```bash
php artisan test
```

---

# 🧾 9. Documentasi API

## Dokumentasi menggunakan Scramble

CEK URL dibawah ini

```
http://localhost:8000/docs/api
```

---

## Example Request

### Withdraw

```bash
curl -X POST http://localhost:8000/api/wallet/M001/withdraw \
-H "Content-Type: application/json" \
-d '{"amount":50000, "description":"Tarik Saldo"}'
```

---

# 🎯 Kesimpulan

- API sudah menggunakan **transaction & locking**
- Response sudah **konsisten**
- Sudah handle:
    - validasi
    - error handling
    - rate limiting
    - concurrency issue
