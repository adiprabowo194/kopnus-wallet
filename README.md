# 💰 KOPNUS Wallet API Documentation

**Base URL**: `http://localhost:8000/api`
**Content-Type**: `application/json`
**Version**: `1.0.0`
**Last Updated**: `2026-04-29`

---

# 📚 Table of Contents

1. [Flow System](#-1-flow-system)
2. [Standar Response](#-2-standar-response)
3. [Error Codes](#-3-error-codes)
4. [Endpoint API](#-4-endpoint-api)
5. [Rate Limiter](#-5-rate-limiter)
6. [Unit Testing](#-6-testing)

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

# 🚀 5. Endpoint API

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
    "data": {
        "member_code": "M001",
        "name": "Adi",
        "balance": 1000000
    }
}
```

---

## 📜 2. Transaction History

### Endpoint

```
GET /wallet/{memberCode}/history
```

### Query Params

| Param | Type   | Description        |
| ----- | ------ | ------------------ |
| page  | int    | Pagination         |
| type  | string | deposit / withdraw |

---

### Response

```json
{
    "status": "success",
    "data": [
        {
            "type": "deposit",
            "amount": 50000,
            "description": "Top up"
        }
    ]
}
```

---

## 💰 3. Deposit

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
    "message": "Deposit berhasil",
    "data": {
        "amount": 50000
    }
}
```

---

## 💸 4. Withdraw

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
    "message": "Withdraw berhasil",
    "data": {
        "amount": 50000
    }
}
```

---

# 🔐 6. Rate Limiter

| Endpoint | Limit              |
| -------- | ------------------ |
| balance  | 60 request / menit |
| history  | 30 request / menit |
| deposit  | 10 request / menit |
| withdraw | 5 request / menit  |

---

# ⚡ 7. Race Condition Handling

Menggunakan database transaction + locking:

```sql
SELECT * FROM members WHERE id = ? FOR UPDATE;
```

### Tanpa Lock

- Bisa terjadi double withdraw
- Saldo tidak konsisten

### Dengan Lock

- Request kedua menunggu
- Data tetap aman

---

# ⚙️ 8. Setup & Run

```bash
git clone https://github.com/your-repo.git
cd project

composer install
cp .env.example .env
php artisan key:generate

php artisan migrate --seed

php artisan serve
```

---

# 🧪 9. Testing

```bash
php artisan test
```

---

# 🧾 10. Sample CURL

### Deposit

```bash
curl -X POST http://localhost:8000/api/wallet/M001/deposit \
-H "Content-Type: application/json" \
-d '{"amount":50000,"description":"Top up"}'
```

---

### Withdraw

```bash
curl -X POST http://localhost:8000/api/wallet/M001/withdraw \
-H "Content-Type: application/json" \
-d '{"amount":50000}'
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
