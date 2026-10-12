<div align="center">

# ⚡ NexusKey — Digital Product E-Commerce Platform

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.5%20-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![React](https://img.shields.io/badge/React-19-61DAFB?style=for-the-badge&logo=react&logoColor=black)](https://react.dev)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-3.x-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Filament](https://img.shields.io/badge/Filament-v5-F59E0B?style=for-the-badge&logo=filament&logoColor=white)](https://filamentphp.com)
[![Pest PHP](https://img.shields.io/badge/Pest-v5-FF4088?style=for-the-badge&logo=pest&logoColor=white)](https://pestphp.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

<p align="center">
  A high-performance, enterprise-grade digital marketplace engineered for selling software license keys, game panel access, and instant digital assets with automated fulfillment, customer wallet system, and dynamic reseller pricing.
</p>

[Explore Features](#-key-features) •
[Tech Stack](#-tech-stack) •
[Installation Guide](#-quick-start--installation) •
[Default Credentials](#-demo-credentials) •
[Admin Panel](#-admin-panel--filament) •
[Testing](#-testing--code-quality)

---

</div>

## 📖 Overview

**NexusKey** is a modern full-stack e-commerce solution tailored specifically for digital goods vendors, gaming communities, and software distributors. 

Unlike traditional physical e-commerce engines, NexusKey focuses entirely on the unique lifecycle of virtual goods: **instant key stock reservation, automated delivery upon payment confirmation, external game panel API synchronization, digital customer wallets, and tiered reseller margins.**

Powered by **Laravel**, **Inertia.js**, **React 19**, and **Tailwind CSS v4**, the customer portal provides a high-converting, cyber-themed glassmorphism aesthetic with seamless mobile-first responsiveness. Administrative workflows are powered by **Filament**, providing full control over catalog variants, stock pools, security logs, and sales analytics.

---

## 🌟 Key Features

### ⚡ Instant License Key Inventory & Fulfillment
- **Automated Delivery:** Instant digital key assignment to customer accounts as soon as a payment transaction confirms.
- **Race-Condition Safe:** Concurrency-safe stock reservation preventing duplicate sales during high-traffic drops.
- **Bulk Import Engine:** Easily upload hundreds of serial keys, activation codes, or panel credentials in bulk with duplicate detection.
- **Safety Guides & Setup Files:** Attach downloadable files, safety instructions, and maintenance advisories directly to product variants.

### 🎮 External Game Panel API Automation
- **API Auto-Provisioning:** Direct integration with external gaming panels and subscription servers (`ExternalPanelApiService`).
- **Dynamic Key Generation:** Request and provision fresh activation keys or accounts on-the-fly via external webhooks and REST endpoints.

### 💳 Customer Digital Wallet & Multi-Gateway Checkout
- **Instant Wallet System:** Customers can maintain a store balance (`WalletService`) with transparent audit logs of deposits, purchases, and bonus rewards.
- **1-Click Checkout:** Zero-friction purchases using pre-funded digital wallet balances.
- **Gateway Integration:** Integrated payment processing with webhook verification, payment verification callbacks, and error recovery.

### 👥 High-End Customer Portal & Mobile-First UX
- **Cyber-Gaming Dark Aesthetic:** Premium dark UI enriched with glassmorphism panels, subtle glow accents, and responsive typography.
- **1-Tap License Copier:** High-contrast key cards with 1-click clipboard copy and secret masking.
- **Interactive Order Tracking:** Accordion-style order history with real-time fulfillment status badges.
- **Live Helpdesk AI Chatbot:** Persistent, floating support assistant guiding users through common queries (orders, wallet top-up, key retrieval).
- **Referral & Affiliate System:** Built-in customer referral tracking and commission reward triggers.

### 🛡️ Filament Admin Dashboard & RBAC
- **Role-Based Access Control (RBAC):** Granular permissions and roles powered by Spatie Laravel Permission & Filament Shield.
- **Product & Variant Catalog:** Multi-tiered variants (e.g., 1-Day, 7-Day, 30-Day, Lifetime).
- **Customer Security & Login History:** Track IP addresses, devices, browsers, and timestamps to detect unauthorized account sharing or suspicious activity.
- **Discounts & Promo Codes:** Expiry dates, minimum spend thresholds, percentage or fixed discounts, and per-user usage limits.
- **Financial Analytics & Sales Reports:** Dedicated sales reports, revenue trends, and exportable financial summaries.

---

## 🛠️ Tech Stack

| Domain | Technology / Package | Description |
| :--- | :--- | :--- |
| **Backend Framework** | [Laravel 12 / 13](https://laravel.com) | Modern PHP framework offering expressive syntax and enterprise stability |
| **Language** | [PHP 8.3+](https://php.net) | Strong typing, promoted constructor properties, and high execution speed |
| **Frontend Framework** | [React 19](https://react.dev) + [Inertia.js](https://inertiajs.com) | Monolith SPA architecture without client-side routing complexity |
| **Styling & Icons** | [Tailwind CSS v4](https://tailwindcss.com) + [Lucide](https://lucide.dev) | Modern utility styling with bespoke dark-mode glassmorphic components |
| **Admin Suite** | [Filament v5](https://filamentphp.com) | Extensible, modern TALL-stack administrative control panel |
| **Authorization** | [Spatie Permission](https://spatie.be) + [Filament Shield](https://github.com/bezhanSalleh/filament-shield) | Comprehensive role and permission governance |
| **Testing** | [Pest PHP v5](https://pestphp.com) | Expressive testing framework covering feature & unit suites |
| **Code Formatter** | [Laravel Pint](https://laravel.com/docs/pint) | Zero-configuration code style standardizer |

---

## 📋 System Requirements

Ensure your server or development environment satisfies the following requirements:

- **PHP**: `^8.3`, `^8.4`, or `^8.5` (Extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`)
- **Composer**: `2.x`
- **Node.js**: `>= 20.x`
- **NPM / Yarn / Bun**: Latest stable version
- **Database**: SQLite (default for development), MySQL `8.0+`, or PostgreSQL `14+`

---

## 🚀 Quick Start / Installation

Follow these steps to set up the project on your local machine:

### 1. Clone the Repository
```bash
git clone https://github.com/rakibhossainporosh/Digital-product-ecommerce.git
cd Digital-product-ecommerce
```

### 2. Install PHP & JavaScript Dependencies
```bash
# Install PHP vendor packages
composer install

# Install Node modules
npm install
```

### 3. Configure the Environment
```bash
# Copy example environment configuration
cp .env.example .env

# Generate application encryption key
php artisan key:generate
```

> **Note:** Configure your database credentials inside `.env`. By default, the application is pre-configured to run with SQLite for zero-setup local development.

### 4. Run Migrations & Seed Sample Data
```bash
# Prepare database tables and populate sample catalog, admin users & keys
php artisan migrate --seed
```

### 5. Build Frontend Assets
```bash
# For development with Hot Module Replacement (HMR):
npm run dev

# Or build optimized production bundles:
npm run build
```

### 6. Serve the Application
```bash
php artisan serve
```

Access the application in your browser:
- **Storefront & Customer Portal:** [http://127.0.0.1:8000](http://127.0.0.1:8000)
- **Filament Admin Dashboard:** [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)

---

## 🔑 Demo Credentials

The database seeders provide pre-configured accounts for testing different user roles:

### 🛡️ Administrative Accounts
| Role | Email | Password | Access URL |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@example.com` | `password` | `/admin` |
| **Store Manager** | `manager@example.com` | `password` | `/admin` |

### 👤 Customer & Reseller Accounts
| Role | Email | Password | Pre-funded Wallet Balance |
| :--- | :--- | :--- | :--- |
| **VIP Reseller** | `reseller@example.com` | `password` | ৳ 3,550.00 (with active orders) |
| **Standard Customer** | `customer@example.com` | `password` | ৳ 600.00 |

---

## 🖥️ Admin Panel & Filament

The admin panel is accessible at `/admin`. It provides:

- **Dashboard:** At-a-glance revenue indicators, recent orders, and inventory health.
- **Product & Variant Catalog:** Manage digital items, set thumbnail galleries, upload instructions, and configure pricing tiers.
- **License Keys Pool:** Track keys by status (`Available`, `Sold`, `Reserved`), view serial details, and perform batch imports.
- **Orders & Fulfillment:** Review pending, completed, or failed orders, inspect associated keys, or manually re-dispatch licenses.
- **Customer Management:** View user balances, adjust wallet credits manually with audit trails, and inspect customer login logs.
- **Site Settings:** Change application title, logo, contact information, marquee notification banner, and payment gateway keys directly from the GUI.

---

## 🧪 Testing & Code Quality

The project adheres to rigorous testing standards powered by **Pest PHP**.

```bash
# Run all automated tests
php artisan test

# Or run tests using Pest directly
vendor/bin/pest

# Run specific feature tests (e.g., Order Fulfillment & Stock Engine)
php artisan test tests/Feature/OrderFulfillmentTest.php
php artisan test tests/Feature/LicenseKeyManagementTest.php
php artisan test tests/Feature/PaymentGatewayTest.php
```

### Code Formatting (Laravel Pint)
Ensure all PHP files adhere to clean PSR-12 coding guidelines:
```bash
vendor/bin/pint
```

---

## 📁 Project Architecture & Directory Layout

```
Digital-product-ecommerce/
├── app/
│   ├── Enums/                 # Application enums (OrderStatus, KeyStatus, etc.)
│   ├── Filament/              # Admin Panel Resources, Pages, and Widgets
│   │   ├── Pages/             # Custom pages (ManageSettings, SalesReportPage)
│   │   └── Resources/         # Filament CRUD modules (Products, Orders, Keys...)
│   ├── Http/
│   │   ├── Controllers/       # Customer, Storefront, and Checkout Controllers
│   │   └── Middleware/        # Guards, Inertia shared props, CSRF rules
│   ├── Models/                # Eloquent Models (Product, Order, LicenseKey, Wallet...)
│   └── Services/              # Core business services (Fulfillment, Wallet, API...)
├── database/
│   ├── factories/             # Model factories for testing
│   ├── migrations/            # Database schema blueprints
│   └── seeders/               # Realistic seeders for demonstration
├── resources/
│   ├── css/                   # Global CSS & Tailwind configuration
│   └── js/
│       ├── Components/        # Reusable React components (Navbar, Chatbot, etc.)
│       ├── Layouts/           # Application layout wrappers
│       └── Pages/             # Inertia Page views (Customer portal, Checkout...)
├── routes/
│   ├── web.php                # Storefront, customer, and webhook routes
│   └── console.php            # Artisan schedule & CLI commands
└── tests/
    └── Feature/               # Comprehensive Pest feature test suites
```

---

## 🤝 Contributing

Contributions make the open-source community an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 🔒 Security Vulnerabilities

If you discover a security vulnerability within this project, please open an issue or reach out directly to the maintainer via GitHub. All security disclosures will be handled promptly.

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

<div align="center">
  <sub>Developed with ❤️ by <a href="https://github.com/rakibhossainporosh">Rakib Hossain Porosh</a></sub>
</div>
