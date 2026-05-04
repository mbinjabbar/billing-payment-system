<div align="center">

# 🏥 Medical Billing & Payment Management System

**A full-stack, role-based medical billing platform built for healthcare clinics**

*From patient visit to settled bill — fully digital, fully traceable*

---

![Angular](https://img.shields.io/badge/Angular-19-DD0031?style=for-the-badge&logo=angular&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Node.js](https://img.shields.io/badge/Node.js-Express-339933?style=for-the-badge&logo=node.js&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-7.0-DC382D?style=for-the-badge&logo=redis&logoColor=white)

</div>

---

## 📖 Table of Contents

- [About the Project](#-about-the-project)
- [Tech Stack](#-tech-stack)
- [System Architecture](#-system-architecture)
- [Features](#-features)
- [User Roles & Permissions](#-user-roles--permissions)
- [Project Structure](#-project-structure)
- [Database Schema](#-database-schema)
- [Getting Started](#-getting-started)
- [Environment Variables](#-environment-variables)
- [API Reference](#-api-reference)
- [Bill Lifecycle](#-bill-lifecycle)
- [Payment Workflow](#-payment-workflow)
- [Security](#-security)
- [Key Design Decisions](#-key-design-decisions)

---

## 📌 About the Project

This system replaces manual billing records and disconnected spreadsheets with a centralized, role-controlled web platform. Every financial event is tracked, every document is auto-generated, and every action is permission-gated by user role.

```
Patient → Patient Case → Appointment → Visit → Bill → Payment(s) → Documents
```

### What it does

| Module | Description |
|--------|-------------|
| **Billing** | Generate bills from patient visits using CPT procedure codes, insurance coverage, discounts, and tax |
| **Payments** | Post multi-mode payments, handle refunds, upload cheque images |
| **Documents** | Auto-generate Invoice PDFs, NF2 forms, receipts — all authenticated downloads |
| **Patients** | Full patient profiles with case, appointment, visit, and billing history |
| **Settings** | Clinic config, procedure code master, insurance firm management |
| **Roles** | Three distinct roles — Admin, Biller, Payment Poster — enforced at every level |

---

## 🛠 Tech Stack

### Frontend
| Technology | Version | Purpose |
|------------|---------|---------|
| Angular | 19 | SPA framework — standalone components, Signals, lazy routing |
| Tailwind CSS | — | Utility-first styling with custom design tokens |
| Angular HttpClient | — | HTTP with auth interceptor (auto JWT attachment) |
| Reactive Forms | — | Form handling with validators |

### Backend — Auth Service (Node.js)
| Technology | Version | Purpose |
|------------|---------|---------|
| Node.js / Express | 5.x | REST API for auth and user management |
| Sequelize | 6.x | ORM for MySQL |
| jsonwebtoken | 9.x | JWT generation and verification |
| bcryptjs | 3.x | Password hashing (10 salt rounds) |
| express-validator | 7.x | Input validation middleware |
| redis | 5.x | Token blacklisting on logout |
| nodemon | 3.x | Dev auto-restart |

### Backend — Billing Service (Laravel)
| Technology | Version | Purpose |
|------------|---------|---------|
| Laravel | 11 | REST API for all billing operations |
| Eloquent ORM | — | Database with SoftDeletes throughout |
| firebase/php-jwt | — | JWT verification (shared secret with Node) |
| barryvdh/laravel-dompdf | — | PDF generation (Invoice, NF2, Receipt) |
| maatwebsite/excel | — | Excel export for bills and payments |

### Infrastructure
| Technology | Purpose |
|------------|---------|
| MySQL 8 | Primary database — shared between both backends |
| Redis 7 | JWT blacklist — tokens stored with TTL on logout |

---

## 🏗 System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Angular 19  (Port 4200)                  │
│                                                             │
│  authGuard · roleGuard · authInterceptor · Signals · Forms  │
└────────────────────┬────────────────────┬───────────────────┘
                     │                    │
          Auth Requests             Billing Requests
                     │                    │
          ┌──────────▼──────┐   ┌─────────▼───────────┐
          │  Node.js API    │   │    Laravel 11 API   │
          │  (Port 3000)    │   │    (Port 8000)      │
          │                 │   │                     │
          │  Login          │   │  Bills & Payments   │
          │  Logout         │   │  Documents          │
          │  JWT Auth       │   │  Patients & Visits  │
          │  User CRUD      │   │  Settings           │
          │  Redis BL       │   │  PDF Generation     │
          └────────┬────────┘   └─────────┬───────────┘
                   │                      │
          ┌────────▼──────────────────────▼──────────┐
          │              MySQL Database               │
          │        (Shared — same credentials)        │
          └──────────────────────────────────────────┘
                   │
          ┌────────▼────────┐
          │     Redis       │
          │  JWT Blacklist  │
          └─────────────────┘
```

> **Why two backends?**
> Node.js handles stateless auth operations — lightweight and fast for JWT + Redis flows.
> Laravel handles complex billing domain logic — superior ORM, PDF generation, and validation pipeline.
> Both use the **same `JWT_SECRET`** so tokens issued by Node are verified by Laravel without any inter-service communication.

---

## Features

<details>
<summary><strong>Billing</strong></summary>

- Create bills from completed patient visits using CPT procedure codes
- Apply insurance coverage (%), discount amount, and tax
- Live bill amount calculation: `(charges - insurance - discount) + tax`
- Save as **Draft** or submit as **Pending**
- Edit bills before any payment is posted
- Manually change bill status (Cancel, Write Off)
- Auto-generated bill numbers: `B-YYMMDD-XXXXX`
- Export bills to Excel with date, status, and amount filters

</details>

<details>
<summary><strong>Payments</strong></summary>

- Post payments against Pending or Partial bills
- 7 payment modes: Cash, Cheque, Bank Transfer, Credit Card, Debit Card, Insurance, Online Payment
- Upload cheque images (PDF / JPG / PNG — max 5MB, validated on both ends)
- Edit Pending or Failed payments only
- Refund Completed payments with automatic bill reversal
- Auto-generated payment numbers: `P-YYMMDD-XXXXX`
- Export payments to Excel

</details>

<details>
<summary><strong>Documents</strong></summary>

- **Invoice PDF** — generated on bill creation, regenerated on every change or payment
- **NF2 Form** — auto-generated for car accident cases (`car_accident = true`)
- **Receipt PDF** — generated on every Completed payment
- **Cheque Image** — securely stored on upload
- All downloads are **authenticated** — JWT required, no public file URLs
- Angular uses `HttpClient` with `responseType: 'blob'` so the auth interceptor attaches the token automatically

</details>

<details>
<summary><strong>Patient Management</strong></summary>

- Full patient profiles with demographics, contact info, emergency contacts
- Medical cases with car accident flag for NF2 generation
- Appointments, visits, bills — all visible in a single patient detail view
- Search by name, phone, or email with multi-word support

</details>

<details>
<summary><strong>Settings & Master Data</strong></summary>

- Clinic name, address, phone, email, default tax rate, default due days
- Procedure codes (CPT) — CRUD with active/inactive toggle
- Insurance firms — Auto type (car accident) and Health type (regular cases)
- Active codes and firms auto-populate bill creation dropdowns

</details>

---

## 👥 User Roles & Permissions

| Feature | Admin | Biller | Payment Poster |
|---------|:-----:|:------:|:--------------:|
| View Bills | ✅ | ✅ | ✅ |
| Create / Edit Bills | ✅ | ✅ | ❌ |
| Change Bill Status | ✅ | ✅ | ❌ |
| Delete Bills | ✅ | ❌ | ❌ |
| View Payments | ✅ | ❌ | ✅ |
| Post / Edit Payments | ✅ | ❌ | ✅ |
| Refund Payments | ✅ | ❌ | ✅ |
| Delete Payments | ✅ | ❌ | ❌ |
| View Patients | ✅ | ✅ | ❌ |
| View Visits | ✅ | ✅ | ❌ |
| View Documents | ✅ | ✅ | ✅ |
| Manage Users | ✅ | ❌ | ❌ |
| Manage Settings | ✅ | ❌ | ❌ |
| Manage Procedure Codes | ✅ | ❌ | ❌ |
| Manage Insurance Firms | ✅ | ❌ | ❌ |
| Export to Excel | ✅ | ✅ | ✅ |

> Role is embedded in the JWT payload and enforced at **three levels**:
> 1. Angular route guards (`roleGuard`)
> 2. Laravel `RoleMiddleware` on every API route
> 3. Node.js `authorize` middleware on user management routes

---

## 📁 Project Structure

```
medical-billing/
│
├── frontend/                        # Angular 19 SPA
│   └── src/app/
│       ├── core/
│       │   ├── guards/              # authGuard, roleGuard, loginGuard
│       │   ├── interceptors/        # authInterceptor — JWT on every request
│       │   └── services/            # auth, bill, payment, patient,
│       │                            # document, visit, user, settings,
│       │                            # procedure-codes, insurance-firms
│       ├── features/
│       │   ├── admin/               # Dashboard, Users, Settings (3 tabs)
│       │   ├── auth/                # Login
│       │   ├── biller/              # Biller dashboard
│       │   ├── bills/               # List, Create, Edit, Invoice, Visit
│       │   ├── documents/           # Document list with download
│       │   ├── patients/            # Patient list & detail
│       │   ├── payment-poster/      # Payment Poster dashboard
│       │   └── payments/            # List, Form (create & edit)
│       └── shared/                  # Header, Sidebar, 403, 404
│
├── node/                            # Auth Service — Port 3000
│   └── src/
│       ├── configs/                 # DB (Sequelize), Redis, env
│       ├── controllers/             # auth.controller, user.controller
│       ├── errors/                  # AppError, AuthError, NotFoundError,
│       │                            # ConflictError, ValidationError, ForbiddenError
│       ├── middlewares/             # authenticate, errorHandler,
│       │                            # responseApi, validateRequest
│       ├── models/                  # User (Sequelize)
│       ├── repositories/            # user.repository (paginate helper)
│       ├── routes/                  # /auth, /users
│       ├── services/                # auth.service, user.service
│       ├── utils/                   # ApiResponse, helpers (paginate)
│       └── validators/              # auth.validator, user.validator
│
└── laravel/                         # Billing Service — Port 8000
    └── app/
        ├── Exports/                 # BillsExport, PaymentsExport
        ├── Http/
        │   ├── Controllers/         # bill, payment, document, patient,
        │   │                        # visit, procedureCodes, insuranceFirms,
        │   │                        # settings
        │   ├── Middleware/          # FirebaseJwt, Role, ValidateRequest,
        │   │                        # filevalidation
        │   └── Rules/               # StoreBill, UpdateBill,
        │                            # StorePayment, UpdatePayment
        ├── Models/                  # Bill, Payment, Document, Patient,
        │                            # Patientcase, Visit, Appointment,
        │                            # InsuranceFirm, ProcedureMaster,
        │                            # Setting, Nf2Detail, User
        ├── Services/                # BillService, PaymentService,
        │                            # DocumentService, VisitService,
        │                            # SettingService
        └── Traits/                  # ApiResponse
```

---

## 🗄 Database Schema

```
┌──────────────┐     ┌──────────────────┐     ┌──────────────────┐
│    users     │     │    patients      │     │  patient_cases   │
├──────────────┤     ├──────────────────┤     ├──────────────────┤
│ id           │     │ id               │     │ id               │
│ first_name   │     │ first_name       │     │ patient_id  (FK) │
│ last_name    │     │ last_name        │     │ case_number      │
│ email        │     │ middle_name      │     │ case_type        │
│ password     │     │ dob · gender     │     │ car_accident     │
│ role         │     │ phone · email    │     │ status           │
│ deleted_at   │     │ deleted_at       │     │ deleted_at       │
└──────────────┘     └──────────────────┘     └────────┬─────────┘
                                                        │
                     ┌──────────────────┐               │
                     │  appointments    │◄──────────────┘
                     ├──────────────────┤
                     │ id               │
                     │ patient_case_id  │
                     │ doctor_name      │
                     │ appointment_date │
                     │ status           │
                     │ deleted_at       │
                     └────────┬─────────┘
                              │
                     ┌────────▼─────────┐
                     │     visits       │
                     ├──────────────────┤
                     │ id               │
                     │ appointment_id   │
                     │ visit_date       │
                     │ diagnosis        │
                     │ treatment_notes  │
                     │ deleted_at       │
                     └────────┬─────────┘
                              │
                     ┌────────▼─────────┐
                     │      bills       │
                     ├──────────────────┤
                     │ id               │
                     │ visit_id    (FK) │
                     │ bill_number      │
                     │ created_by  (FK) │
                     │ insurance_firm_id│
                     │ procedure_codes  │ ← JSON snapshot
                     │ charges          │
                     │ insurance_%      │
                     │ discount_amount  │
                     │ tax_amount       │
                     │ bill_amount      │
                     │ paid_amount      │
                     │ outstanding      │
                     │ status           │
                     │ deleted_at       │
                     └──┬───────┬───┬───┘
                        │       │   │
           ┌────────────┘  ┌────┘   └───────────────┐
           │               │                        │
  ┌────────▼───────┐ ┌─────▼──────────┐ ┌───────────▼────┐
  │    payments    │ │   documents    │ │  nf2_details   │
  ├────────────────┤ ├────────────────┤ ├────────────────┤
  │ id             │ │ id             │ │ id             │
  │ bill_id   (FK) │ │ bill_id   (FK) │ │ bill_id   (FK) │
  │ payment_number │ │ payment_id(FK) │ │ accident_date  │
  │ received_by(FK)│ │ document_type  │ │ claim_number   │
  │ amount_paid    │ │ file_path      │ │ policy_number  │
  │ payment_mode   │ │ deleted_at     │ └────────────────┘
  │ payment_status │ └────────────────┘
  │ deleted_at     │
  └────────────────┘
```

> ⚠️ **All tables use SoftDeletes** — `deleted_at` is set on deletion, records are never permanently removed.

**Supporting tables:** `insurance_firms` · `procedure_masters` · `settings`

---

## 🚀 Getting Started

### Prerequisites

```
Node.js     18+
PHP         8.2+
Composer    2.x
MySQL       8.x
Redis       7.x
Angular CLI 19.x
```

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/medical-billing-system.git
cd medical-billing-system
```

### 2. Database Setup

```sql
CREATE DATABASE medical_billing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Laravel — Billing Backend

```bash
cd laravel
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials and `JWT_SECRET`, then:

```bash
php artisan migrate
php artisan db:seed          # seeds clinic settings and default data
php artisan storage:link     # required for cheque image public access
php artisan serve --port=8000
```

### 4. Node.js — Auth Backend

```bash
cd node
npm install
cp .env.example .env         # update DB, Redis, and JWT_SECRET
npm run dev                  # starts with nodemon on port 3000
```

### 5. Angular — Frontend

```bash
cd frontend
npm install
ng serve                     # starts on port 4200
```

### Access the App

| Service | URL |
|---------|-----|
| Frontend | http://localhost:4200 |
| Node API | http://localhost:3000 |
| Laravel API | http://localhost:8000 |

---

## ⚙️ Environment Variables

### Node.js — `node/.env`

```env
PORT=3000

# MySQL — same database as Laravel
DATABASE_URL=mysql://root:your_password@localhost:3306/medical_billing

# Redis — for JWT blacklisting on logout
REDIS_URL=redis://127.0.0.1:6379

# JWT — must match Laravel JWT_SECRET exactly
JWT_SECRET=your_strong_secret_here
JWT_EXPIRES_IN=8h
```

### Laravel — `laravel/.env`

```env
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medical_billing
DB_USERNAME=root
DB_PASSWORD=your_password

# Must match Node.js JWT_SECRET exactly
JWT_SECRET=your_strong_secret_here

FILESYSTEM_DISK=local
```

> 🔑 **Critical:** `JWT_SECRET` must be **identical** in both `.env` files.
> Node generates the token. Laravel verifies it using the same secret.
> They never talk to each other — the shared secret is the only link.

---

## 📡 API Reference

### Node.js API — Base URL: `http://localhost:3000/api`

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/auth/login` | Public | Login — returns JWT |
| `POST` | `/auth/logout` | JWT | Blacklist token in Redis |
| `GET` | `/auth/me` | JWT | Current user from token |
| `GET` | `/users` | JWT + Admin | Paginated user list |
| `POST` | `/users` | JWT + Admin | Create new user |
| `GET` | `/users/:id` | JWT + Admin | Get user by ID |
| `PATCH` | `/users/:id` | JWT + Admin | Update user |
| `DELETE` | `/users/:id` | JWT + Admin | Soft delete user |

### Laravel API — Base URL: `http://localhost:8000/api`

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/settings` | Public | Clinic settings |
| `GET` | `/bills` | JWT | Paginated bills |
| `GET` | `/bills/stats` | JWT | Bill statistics |
| `GET` | `/bills/{id}` | JWT | Bill detail |
| `POST` | `/bills` | Admin/Biller | Create bill |
| `PUT` | `/bills/{id}` | Admin/Biller | Update bill |
| `PATCH` | `/bills/{id}/status` | Admin/Biller | Change status |
| `DELETE` | `/bills/{id}` | Admin | Delete bill |
| `POST` | `/bills/export` | Admin/Biller | Export Excel |
| `GET` | `/bills/invoice/{id}` | JWT | Download Invoice PDF |
| `GET` | `/bills/nf2/{id}` | JWT | Download NF2 PDF |
| `GET` | `/payments` | Admin/Poster | Paginated payments |
| `POST` | `/payments` | Admin/Poster | Post payment |
| `PUT` | `/payments/{id}` | Admin/Poster | Edit payment |
| `PATCH` | `/payments/{id}/refund` | Admin/Poster | Refund payment |
| `DELETE` | `/payments/{id}` | Admin | Delete payment |
| `POST` | `/payments/export` | Admin/Poster | Export Excel |
| `GET` | `/payments/receipt/{id}` | JWT | Download Receipt PDF |
| `GET` | `/patients` | Admin/Biller | Patient list |
| `GET` | `/patients/{id}` | Admin/Biller | Patient detail |
| `GET` | `/visits` | Admin/Biller | Visit list |
| `GET` | `/documents` | JWT | Document list |
| `GET` | `/documents/cheque/{id}` | JWT | Download Cheque |

### Standard Response Format

```json
{
  "success": true,
  "message": "Bills retrieved successfully",
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 47,
    "from": 1,
    "to": 10
  }
}
```

> `meta` is present only on paginated list responses.
> `data` is `null` on deletes and error responses.

---

## 🔄 Bill Lifecycle

```
       ┌─────────┐
       │  Draft  │ ← Saved, no PDF yet
       └────┬────┘
            │ Submit
            ▼
      ┌──────────┐
      │ Pending  │ ← Invoice PDF generated
      └────┬─────┘
  ┌────────┼──────────────┐
  │        │              │
Cancel*  Payment       Write Off†
  │      Posted           │
  ▼         │             ▼
Cancelled   ▼         Written Off
        ┌─────────┐
        │ Partial │ ← Some paid, balance remaining
        └────┬────┘
    ┌────────┴──────────┐
    │                   │
Full Payment         Write Off†
    │                   │
    ▼                   ▼
  Paid             Written Off
```

| Status | Rule |
|--------|------|
| **Cancel** | Only when `paid_amount = 0` |
| **Write Off** | Only from `Pending` or `Partial` |
| **Paid** | Auto-set when `outstanding_amount = 0` |
| **Partial** | Auto-set when `paid_amount > 0` and balance remains |

> Every status change regenerates the Invoice PDF.

---

## 💳 Payment Workflow

```
Bill: Pending or Partial
           │
           ▼
    Payment Submitted
           │
    ┌──────┴────────────────────┐
    │                           │
Pending / Failed            Completed
    │                           │
No effect on bill     ┌─────────▼──────────────────┐
                      │  paid_amount updated        │
                      │  outstanding recalculated   │
                      │  bill status auto-resolved  │
                      │  Invoice PDF regenerated    │
                      │  Receipt PDF created        │
                      └─────────┬──────────────────┘
                                │
                         [Refund]
                                │
                      ┌─────────▼──────────────────┐
                      │  payment → Refunded         │
                      │  bill amounts reversed      │
                      │  Refund Receipt generated   │
                      │  Invoice PDF regenerated    │
                      └────────────────────────────┘
```

**Payment modes:** `Cash` · `Cheque` · `Bank Transfer` · `Credit Card` · `Debit Card` · `Insurance` · `Online Payment`

---

## 🔐 Security

| Measure | Detail |
|---------|--------|
| **JWT Auth** | HS256 signed, 8-hour expiry, payload: `{ id, name, email, role }` |
| **Token Blacklisting** | Redis `setEx` on logout with exact remaining TTL |
| **Password Hashing** | bcryptjs — 10 salt rounds, excluded from all API responses |
| **Role Enforcement** | Angular guard → Laravel middleware → Node middleware (3 layers) |
| **Input Validation** | Reactive Forms (frontend) + Rules classes / express-validator (backend) |
| **File Validation** | MIME type + 5MB size limit — frontend and `filevalidation` middleware |
| **DB Transactions** | `DB::beginTransaction()` on every multi-step write — full rollback on failure |
| **Soft Deletes** | All models — no permanent data loss, full audit trail preserved |
| **Authenticated Downloads** | All PDF routes inside `firebasejwt` middleware — no bare public URLs |

---

## 💡 Key Design Decisions

**Dual-backend with shared JWT**
Node handles auth, Laravel handles billing. Both verify the same JWT using the same `JWT_SECRET`. No inter-service calls needed.

**JWT decoded client-side**
The user role is in the JWT payload. Angular decodes it with `atob()` — no extra API call to determine role or permissions.

**DB transactions on all writes**
Every controller wraps multi-table operations in `DB::beginTransaction()`. If PDF generation fails after a bill insert, everything rolls back cleanly.

**Soft deletes everywhere**
No row is ever hard-deleted. Deleted billers still appear on historical bills via `->withTrashed()` on the `creator()` relationship — the audit trail is always complete.

**Procedure codes as JSON snapshot**
Bills store procedure codes as a JSON snapshot at creation time. Master data changes never corrupt historical billing records.

**Authenticated blob downloads**
PDF routes are inside the `firebasejwt` middleware group. Angular uses `responseType: 'blob'` so the auth interceptor sends the Bearer token — no public storage, no bare links.

**Clone query for stats**
`getBillStats()` clones the base query before every aggregate. Eloquent builders are mutable — without cloning, `->sum()` and `->count()` calls compound each other and return wrong figures.

---

## 👨‍💻 Author

**Muhammad Bin Jabbar**
Bachelor of Science in Computer Science — Government College University, Faisalabad

---

<div align="center">

*Built with ❤️ as part of an internship project at Deline Media*

</div>
