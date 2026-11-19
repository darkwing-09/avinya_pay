# 🏦 AvinyaPay

A self-hosted, multi-merchant UPI payment gateway designed for modern applications and Indian merchants. AvinyaPay generates dynamic UPI QR codes, automates payment verification, provides account rotation, and delivers signed, durable webhook notifications directly to your application without third-party aggregator settlement holds.

---

## ⚡ Highlights

- **Direct Settlements:** Payments go straight from customer to merchant UPI VPAs. No intermediary escrow or holding account.
- **Dynamic UPI QR:** Generates distinct UPI QR codes and intent links (GPay, PhonePe, Paytm, BHIM) per order.
- **Automated Verification:** Multi-provider verification engine supporting automated transaction status reconciliation.
- **Account Rotation:** Distribute volume across multiple UPI VPAs with configurable daily transaction limits.
- **Live & Sandbox Environments:** Separate credentials, webhooks, and simulated payments for testing.
- **Signed Webhooks:** HMAC-SHA256 signature verification with timestamps, deduplication IDs, and durable outbox delivery.
- **Docker-Ready:** Includes production Dockerfile and startup scripts tailored for Render, VPS, or containerized hosting.

---

## 🚀 Quick Start (Deployment on Render)

### 1. Requirements
- **PHP 8.2+** with extensions: `pdo_mysql`, `mbstring`, `xml`, `bcmath`, `fileinfo`, `curl`, `openssl`.
- **MySQL 8.0+** (or compatible remote MySQL like TiDB Cloud Serverless).
- **HTTPS** enabled on public domain.

### 2. Environment Configuration

Configure the following environment variables in your hosting environment:

| Variable | Description | Example |
|---|---|---|
| `APP_NAME` | Service Name | `AvinyaPay` |
| `APP_ENV` | Environment mode | `production` |
| `APP_KEY` | 32-char AES key | Auto-generated during setup |
| `APP_URL` | Public HTTPS domain | `https://your-service.onrender.com` |
| `INSTALLER_ALLOW_REMOTE_DB` | Allow remote MySQL connections | `true` |
| `DB_CONNECTION` | Database engine | `mysql` |
| `DB_HOST` | MySQL Host | e.g. `gateway.tidbcloud.com` |
| `DB_PORT` | MySQL Port | `4000` or `3306` |
| `DB_DATABASE` | Database Name | `avinyapay` |
| `DB_USERNAME` | Database User | `your_user` |
| `DB_PASSWORD` | Database Password | `your_password` |
| `GATEWAY_VERIFICATION_NODE_ID` | Stable worker node ID | `avinyapay-prod-node-1` |

### 3. Run the Web Installer

After deploying the container, open your service URL:
```text
https://your-service.onrender.com/install
```
1. **Requirements check** &rarr; Validates server environment.
2. **Database configuration** &rarr; Connects to your MySQL database.
3. **Run migrations** &rarr; Generates required database tables.
4. **Create admin** &rarr; Configures primary administrator credentials.

Once setup is completed, the installer is automatically locked.

---

## 📡 API Reference

Merchants interact with AvinyaPay using Bearer token authentication via the `X-Api-Key` and `X-Api-Secret` headers.

### Create Payment Order

```http
POST /api/v1/orders
Content-Type: application/json
X-Api-Key: key_xxxxxxxxxxxxxxxx
X-Api-Secret: sec_xxxxxxxxxxxxxxxx

{
  "amount": 499.00,
  "currency": "INR",
  "receipt": "ORDER_1001",
  "notes": {
    "customer_name": "Rohan Sharma"
  }
}
```

#### Response (`201 Created`)
```json
{
  "id": "ord_8f1a2b3c4d5e",
  "amount": 499.00,
  "currency": "INR",
  "status": "pending",
  "payment_url": "https://your-service.onrender.com/pay/ord_8f1a2b3c4d5e",
  "upi_intent": "upi://pay?pa=merchant@upi&pn=AvinyaPay&am=499.00&tr=ord_8f1a2b3c4d5e",
  "expires_at": "2026-11-20T05:00:00Z"
}
```

### Check Order Status

```http
GET /api/v1/orders/ord_8f1a2b3c4d5e
X-Api-Key: key_xxxxxxxxxxxxxxxx
X-Api-Secret: sec_xxxxxxxxxxxxxxxx
```

---

## 🔔 Webhook Integration

AvinyaPay delivers real-time notifications when an order status changes (e.g., `order.paid`).

### Headers Sent:
- `X-AvinyaPay-Signature`: HMAC-SHA256 signature calculated over timestamp + raw payload.
- `X-AvinyaPay-Timestamp`: Unix timestamp (requests older than 300 seconds should be rejected).

### Signature Verification (Python / FastAPI)

```python
import hmac
import hashlib
import time

def verify_webhook(raw_body: bytes, signature: str, timestamp: str, secret: str) -> bool:
    if abs(time.time() - int(timestamp)) > 300:
        return False
    expected = hmac.new(
        secret.encode(),
        f"{timestamp}.".encode() + raw_body,
        hashlib.sha256
    ).hexdigest()
    return hmac.compare_digest(expected, signature)
```

### Webhook Payload Example:
```json
{
  "event": "order.paid",
  "event_id": "evt_9a8b7c6d5e4f",
  "occurred_at": 1794954200,
  "data": {
    "order_id": "ord_8f1a2b3c4d5e",
    "amount": 499.00,
    "utr": "432109876543",
    "status": "paid",
    "receipt": "ORDER_1001"
  }
}
```

---

## 🏗️ Architecture & Security

- **Tamper-Proof Ledger:** All transactions and capture transitions are recorded idempotently.
- **Outbox Pattern:** Webhook deliveries are staged transactionally and retried with exponential backoff.
- **Node Identity Leases:** Distributed locking prevents double verification of pending payments.
- **Zero-Storage of UPI PINs:** All payments occur inside official customer UPI apps via standardized NPCI intent protocols.

---

## 📄 License

Distributed under the MIT License. See `LICENSE` for details.
