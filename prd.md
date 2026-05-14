# PRD — Aplikasi Pencatatan Transaksi BRILink H12

## Overview

Aplikasi sederhana untuk mencatat transaksi harian agen BRILink. Tujuan utama: mengurangi effort karyawan dalam pencatatan transaksi dengan menyediakan dua channel input — web app (form) dan Telegram bot (foto struk + dialog manual).

---

## Problem Statement

Karyawan agen BRILink harus mencatat setiap transaksi (tarik tunai, setor, transfer, topup, pembayaran) secara manual. Proses ini memakan waktu dan rawan terlewat. Dibutuhkan cara yang lebih cepat dan praktis.

---

## Solution

### Channel Input

1. **Web App (Laravel)**
   - Form transaksi dengan field: jenis, nominal, jumlah transaksi, fee, catatan
   - Dashboard rekap harian/bulanan

2. **Telegram Bot**
   - **Foto struk** → OCR parse otomatis (jenis, nominal, jumlah transaksi) → bot tanya fee → simpan
   - **Dialog manual** (`/catat`) → bot tanya step-by-step: jenis → nominal → jumlah → fee → simpan

---

## Kategori Transaksi

| Kategori | Flow Kas | Flow Saldo BRILink |
|----------|----------|--------------------|
| Tarik Tunai | Keluar (kas berkurang) | Masuk (saldo bertambah) |
| Setor Tunai | Masuk (kas bertambah) | Keluar (saldo berkurang) |
| Transfer | Keluar | Masuk |
| Topup E-Wallet | Keluar | Masuk |
| Pembayaran (listrik, BPJS, dll) | Keluar | Masuk |
| Lainnya | Manual pilih | Manual pilih |

---

## Logika Keuangan

- Setiap transaksi punya **fee** yang didapat dari nasabah
- Fee langsung masuk ke **laba** (tidak perlu wallet/dompet terpisah)
- Metode pembayaran fee (cash/transfer dilebihin) tidak perlu di-track

---

## Data Model

### transactions
- `id` — primary key
- `user_id` — karyawan yang input
- `type` — enum: tarik_tunai, setor_tunai, transfer, topup_ewallet, pembayaran, lainnya
- `amount` — nominal per transaksi (Rupiah)
- `quantity` — jumlah transaksi (dari struk, default 1)
- `fee` — fee yang didapat (Rupiah)
- `cash_flow` — enum: in, out (otomatis dari type)
- `note` — catatan opsional
- `source` — enum: web, telegram
- `receipt_image` — path foto struk (nullable)
- `transaction_date` — tanggal transaksi
- `created_at`
- `updated_at`

### users
- `id`
- `name`
- `email`
- `password`
- `telegram_chat_id` — nullable, untuk link akun Telegram
- `role` — enum: admin, karyawan
- `created_at`
- `updated_at`

---

## Telegram Bot Flow

### Input via Foto Struk
```
User kirim foto struk
→ OCR parse: jenis transaksi, nominal, jumlah transaksi
→ Bot: "Tarik Tunai - Rp 1.000.000 x 2. Fee berapa?"
→ User: "10000"
→ Bot: "Tercatat ✓ (Tarik Tunai, Rp 1.000.000 x 2, Fee Rp 10.000)"
```

### Input Manual
```
User: /catat
→ Bot: "Pilih jenis transaksi:" [inline keyboard]
→ User tap: Tarik Tunai
→ Bot: "Nominal per transaksi?"
→ User: "1000000"
→ Bot: "Jumlah transaksi?"
→ User: "2"
→ Bot: "Fee yang didapat?"
→ User: "10000"
→ Bot: "Tercatat ✓ (Tarik Tunai, Rp 1.000.000 x 2, Fee Rp 10.000)"
```

---

## Dashboard (Web)

### Ringkasan Hari Ini
- Total uang masuk (kas)
- Total uang keluar (kas)
- Total fee / laba hari ini
- Jumlah transaksi

### Tabel Transaksi
- Filter: tanggal, jenis, karyawan
- Kolom: waktu, jenis, nominal, qty, fee, source, karyawan

### Rekap
- Harian & bulanan
- Grafik tren sederhana

---

## Tech Stack

- **Backend:** Laravel 11 + MySQL
- **Frontend:** Blade + template Envato (TBD)
- **Telegram Bot:** Laravel + Telegram Bot API (webhook)
- **OCR:** AI model (TBD — opsi: Google Vision, Gemini, OpenAI Vision)
- **Hosting:** TBD

---

## MVP Scope (Phase 1)

1. Setup Laravel + database migration
2. CRUD transaksi via web form
3. Dashboard rekap sederhana
4. Telegram bot — input manual (dialog)

## Phase 2

- OCR foto struk
- Multi karyawan + auth
- Export laporan (PDF/Excel)
- Notifikasi rekap harian otomatis via Telegram
