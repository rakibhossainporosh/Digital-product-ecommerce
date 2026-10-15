<div align="center">

# ⚡ NexusKey — Digital Product & Game Panel E-Commerce Platform

[![Laravel](https://img.shields.io/badge/Laravel-12%2F13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%20|%208.4%20|%208.5-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
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
- **Race-Condition Safe:** Concurrency-safe stock reservation preventing duplicate sales during high-traffic drops (`LicenseKeyStockService`).
- **Bulk Import Engine:** Easily upload hundreds of serial keys, activation codes, or panel credentials in bulk with duplicate detection (`LicenseKeyImportService`).
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
- **Live Helpdesk Chatbot:** Persistent, floating support assistant guiding users through common queries (orders, wallet top-up, key retrieval).
- **Referral & Affiliate System:** Built-in customer referral tracking and commission reward triggers.

### 🛡️ Filament Admin Dashboard & RBAC
- **Role-Based Access Control (RBAC):** Granular permissions and roles powered by Spatie Laravel Permission & Filament Shield.
- **Product & Variant Catalog:** Multi-tiered variants (e.g., 1-Day, 7-Day, 30-Day, Lifetime).
- **Customer Security & Login History:** Track IP addresses, devices, browsers, and timestamps (`LoginHistories`, `UserAgentParser`) to detect unauthorized account sharing or suspicious activity.
- **Discounts & Promo Codes:** Expiry dates, minimum spend thresholds, percentage or fixed discounts, and per-user usage limits (`PromoCodeService`).
- **Financial Analytics & Sales Reports:** Dedicated sales reports, revenue trends, and exportable financial summaries (`SalesReportPage`).

---

## 🛠️ Tech Stack

| Domain | Technology / Package | Description |
| :--- | :--- | :--- |
| **Backend Framework** | [Laravel 12 / 13](https://laravel.com) | Modern PHP framework offering expressive syntax and enterprise stability |
| **Language** | [PHP 8.3+](https://php.net) | Strong typing, promoted constructor properties, and high execution speed |
| **Frontend Framework** | [React 19](https://react.dev) + [Inertia.js](https://inertiajs.com) | Monolith SPA architecture without client-side routing complexity |
| **Styling & Icons** | [Tailwind CSS v4](https://tailwindcss.com) + [Lucide](https://lucide.dev) | Modern utility styling with bespoke dark-mode glassmorphic components |
| **Admin Suite** | [Filament v5](https://filamentphp.com) | Extensible, modern administrative control panel |
| **Authorization** | [Spatie Permission](https://spatie.be) + [Filament Shield](https://github.com/bezhanSalleh/filament-shield) | Comprehensive role and permission governance |
| **Testing** | [Pest PHP v5](https://pestphp.com) | Expressive testing framework covering feature & unit suites |
| **Code Formatter** | [Laravel Pint](https://laravel.com/docs/pint) | Zero-configuration code style standardizer |

---

## 📋 System Requirements

- **PHP**: `^8.3`, `^8.4`, or `^8.5`
- **Composer**: `2.x`
- **Node.js**: `>= 20.x`
- **Database**: SQLite (default for development), MySQL `8.0+`, or PostgreSQL `14+`

---

## 🚀 Quick Start / Installation

```bash
# 1. Clone repository
git clone https://github.com/rakibhossainporosh/Digital-product-ecommerce.git
cd Digital-product-ecommerce

# 2. Install dependencies
composer install
npm install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Database migration & sample seeder
php artisan migrate --seed

# 5. Build assets
npm run build
# OR for hot development:
npm run dev

# 6. Run local server
php artisan serve
