# Laravel B2B Ecommerce Platform with Coin/Point System

## Overview

A comprehensive B2B ecommerce platform built with Laravel, featuring:
- **Vendor Management**: Multi-vendor marketplace with seller dashboards
- **Wholesale Platform**: B2B ordering, bulk pricing, and quote system
- **Coin/Point Loyalty System**: Earn, redeem, and transfer points/coins
- **Paytm Integration**: Secure payment processing and settlement
- **AI Features**: Product recommendations, search optimization
- **Advanced Analytics**: Business intelligence dashboards

## Tech Stack

- **Backend**: Laravel 11, PHP 8.3+
- **Database**: MySQL 8.0+, Redis
- **Frontend**: Blade Templates, Alpine.js, Tailwind CSS
- **Payments**: Paytm Payment Gateway API
- **Queue**: Laravel Queue for async processing
- **Cache**: Redis for performance optimization

## Project Structure

```
├── app/
│   ├── Console/          # Artisan commands
│   ├── Http/             # Controllers, Requests, Middleware
│   ├── Models/           # Eloquent models
│   ├── Services/         # Business logic services
│   ├── Repositories/     # Data access layer
│   ├── Jobs/             # Queued jobs
│   └── Events/           # Event listeners
├── database/
│   ├── migrations/       # Database schemas
│   └── seeders/          # Database seeds
├── resources/
│   ├── views/            # Blade templates
│   ├── css/              # Stylesheet
│   └── js/               # JavaScript assets
├── routes/               # API and web routes
├── config/               # Configuration files
└── tests/                # Test suite
```

## Features Implemented

### 1. User Management
- Admin, Vendor, Customer roles
- User authentication and authorization
- Profile management
- KYC verification for vendors

### 2. B2B Features
- Vendor registration and approval workflow
- Commission management
- Bulk order handling
- Wholesale pricing tiers
- Quote system

### 3. Coin/Point System
- Earn points on purchases
- Referral rewards
- Point redemption
- Coin transfers between users
- Transaction history and analytics

### 4. Payment Processing
- Paytm integration for customer payments
- Wallet system
- Payment reconciliation
- Settlement to vendors
- Transaction history

### 5. Product Management
- Multi-category support
- Inventory tracking
- Variant management
- AI-powered recommendations
- SEO optimization

### 6. Order Management
- Order creation and tracking
- Status management
- Return/refund system
- Shipping integration

### 7. Analytics & Reporting
- Sales dashboards
- Revenue analytics
- Vendor performance metrics
- Point system analytics
- Payment reconciliation reports

## Getting Started

### Prerequisites
- PHP 8.3+
- MySQL 8.0+
- Composer
- Node.js & npm
- Redis (optional but recommended)

### Installation

1. Clone the repository
```bash
git clone https://github.com/rchauhannodeawsreact/laravelEcommerce.git
cd laravelEcommerce
```

2. Install dependencies
```bash
composer install
npm install
```

3. Setup environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database and Paytm
```bash
# Update .env with your database and Paytm credentials
DB_HOST=localhost
DB_DATABASE=laravel_b2b
DB_USERNAME=root
DB_PASSWORD=

PAYTM_MERCHANT_ID=your_merchant_id
PAYTM_MERCHANT_KEY=your_merchant_key
PAYTM_MERCHANT_WEBSITE=WEBSTAGING
PAYTM_CHANNEL_ID=WEB
```

5. Run migrations and seed data
```bash
php artisan migrate
php artisan db:seed
```

6. Build assets
```bash
npm run dev
```

7. Start the development server
```bash
php artisan serve
```

Access the application at `http://localhost:8000`

## API Documentation

API endpoints are documented in `/docs/API.md`

## Testing

```bash
# Run tests
php artisan test

# Run with coverage
php artisan test --coverage
```

## Deployment

See `/docs/DEPLOYMENT.md` for production deployment guidelines.

## Contributing

Please read CONTRIBUTING.md for details on our code of conduct and the process for submitting pull requests.

## License

MIT License - see LICENSE file for details.
