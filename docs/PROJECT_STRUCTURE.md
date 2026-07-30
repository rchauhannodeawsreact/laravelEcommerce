# Project Structure Documentation

## Overview

This document describes the directory structure and organization of the Laravel B2B Ecommerce Platform.

## Directory Structure

### `/app`
Main application code organized by responsibility:

- **`Console/`** - Artisan commands for background tasks, data processing
  - `Commands/` - Custom CLI commands

- **`Http/`** - HTTP layer handling
  - `Controllers/` - Request handlers organized by feature
    - `Admin/` - Admin dashboard controllers
    - `Vendor/` - Vendor/seller controllers
    - `Customer/` - Customer controllers
    - `Api/` - REST API controllers
  - `Requests/` - Form request validation
  - `Middleware/` - HTTP middleware
  - `Resources/` - API response transformers

- **`Models/`** - Eloquent models
  - User.php, Vendor.php, Customer.php
  - Product.php, Category.php, Inventory.php
  - Order.php, OrderItem.php
  - Coin.php, CoinTransaction.php, CoinRedemption.php
  - Payment.php, Transaction.php
  - Commission.php, Settlement.php

- **`Services/`** - Business logic layer
  - `PaymentService.php` - Paytm integration
  - `CoinService.php` - Coin/point system logic
  - `OrderService.php` - Order processing
  - `VendorService.php` - Vendor management
  - `CommissionService.php` - Commission calculations
  - `ReportingService.php` - Analytics and reporting

- **`Repositories/`** - Data access layer
  - `UserRepository.php`
  - `ProductRepository.php`
  - `OrderRepository.php`
  - `CoinRepository.php`
  - `PaymentRepository.php`

- **`Jobs/`** - Queued jobs
  - `ProcessPaymentCallback.php`
  - `SettleVendorCommission.php`
  - `SendOrderNotification.php`
  - `GenerateReport.php`

- **`Events/`** - Event classes
  - `OrderCreated.php`
  - `PaymentProcessed.php`
  - `CoinEarned.php`

- **`Listeners/`** - Event listeners
  - `SendOrderNotification.php`
  - `AwardReferralCoin.php`
  - `GenerateCommission.php`

- **`Exceptions/`** - Custom exceptions
  - `PaymentException.php`
  - `CoinException.php`
  - `InsufficientInventoryException.php`

### `/database`
Database-related files:

- **`migrations/`** - Schema definitions
  - `2024_*_create_users_table.php`
  - `2024_*_create_vendors_table.php`
  - `2024_*_create_products_table.php`
  - `2024_*_create_orders_table.php`
  - `2024_*_create_coin_system_tables.php`
  - `2024_*_create_payment_tables.php`
  - `2024_*_create_commission_tables.php`

- **`seeders/`** - Database seeders
  - `DatabaseSeeder.php`
  - `AdminSeeder.php`
  - `ProductSeeder.php`
  - `VendorSeeder.php`

### `/resources`
Frontend assets:

- **`views/`** - Blade templates
  - `layouts/` - Base templates
    - `app.blade.php` - Main layout
    - `admin.blade.php` - Admin layout
    - `vendor.blade.php` - Vendor layout
  - `auth/` - Authentication pages
  - `admin/` - Admin dashboards
    - `dashboard.blade.php`
    - `users/` - User management
    - `vendors/` - Vendor management
    - `payments/` - Payment management
  - `vendor/` - Vendor dashboards
    - `dashboard.blade.php`
    - `products/` - Product management
    - `orders/` - Order management
    - `coins/` - Coin system
  - `customer/` - Customer pages
    - `home.blade.php`
    - `products/` - Product browsing
    - `cart.blade.php`
    - `checkout.blade.php`
    - `account/` - Account management
    - `coins/` - Coin redemption
  - `components/` - Reusable components

- **`css/`** - Stylesheets
  - `app.css` - Main stylesheet with Tailwind

- **`js/`** - JavaScript
  - `app.js` - Main JavaScript
  - `components/` - Vue components if needed

### `/routes`
Route definitions:

- `web.php` - Web routes
- `api.php` - API routes
- `admin.php` - Admin routes
- `vendor.php` - Vendor routes
- `customer.php` - Customer routes

### `/config`
Configuration files:

- `app.php` - Application config
- `database.php` - Database config
- `paytm.php` - Paytm configuration
- `coin-system.php` - Coin system configuration
- `commission.php` - Commission configuration
- `filesystems.php` - File storage config

### `/tests`
Test suite:

- `Unit/` - Unit tests
  - `Services/` - Service tests
  - `Models/` - Model tests
- `Feature/` - Feature tests
  - `Auth/` - Authentication tests
  - `Orders/` - Order tests
  - `Payments/` - Payment tests
  - `Coins/` - Coin system tests
- `Pest.php` - Test configuration

### `/storage`
Application storage:

- `app/` - Application files
- `logs/` - Application logs
- `framework/` - Framework cache

### `/bootstrap`
Bootstrap files:

- `app.php` - Application bootstrapper
- `cache/` - Bootstrap cache

## Key Design Patterns

### Service Layer
Business logic is encapsulated in services to keep controllers lean:
- `PaymentService` handles all payment operations
- `CoinService` manages coin transactions
- `OrderService` processes orders

### Repository Pattern
Data access is abstracted through repositories:
- Controllers and services use repositories
- Easy to switch implementations (e.g., database to cache)
- Easier testing with mock repositories

### Events & Listeners
Event-driven architecture for loose coupling:
- `OrderCreated` event fires when order is created
- Listeners handle coin awards, notifications, commission generation
- Easy to add new behaviors without modifying core logic

### Jobs & Queues
Asynchronous processing for long-running tasks:
- Payment callbacks processed in queue
- Settlement calculations run in background
- Notifications sent asynchronously

## Data Flow

### Customer Checkout Flow
```
1. Customer adds items to cart
2. Customer initiates checkout
3. OrderController receives checkout request
4. OrderService validates inventory
5. OrderService creates Order and OrderItems
6. Order saved to database
7. PaymentService initiates Paytm transaction
8. Customer redirected to Paytm payment gateway
9. Paytm calls callback URL after payment
10. PaymentController handles callback
11. PaymentService verifies signature
12. ProcessPaymentCallback job queued
13. Job updates order status
14. CoinService awards purchase coins
15. CommissionService generates vendor commission
16. Settlement calculated for vendor
```

### Vendor Settlement Flow
```
1. Vendor initiates withdrawal request
2. WithdrawalController receives request
3. WithdrawalService validates eligibility
4. Settlement record created with pending status
5. SettleVendorCommission job queued
6. Job processes settlement via Paytm
7. Settlement marked as completed
8. Notification sent to vendor
```

### Coin Redemption Flow
```
1. Customer selects coins to redeem
2. CoinController receives redemption request
3. CoinService validates coin balance
4. CoinService converts coins to discount amount
5. Discount applied to order
6. Order processed with reduced amount
7. CoinTransaction record created
```
