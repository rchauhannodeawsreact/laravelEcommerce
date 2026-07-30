# API Documentation

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
All API endpoints require Bearer token authentication via JWT.

```bash
Authorization: Bearer {token}
Content-Type: application/json
```

## Response Format

All API responses follow this format:

### Success Response (200, 201)
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  },
  "timestamp": "2024-01-17T10:30:00Z"
}
```

### Error Response (400, 401, 403, 404, 500)
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Error message"]
  },
  "timestamp": "2024-01-17T10:30:00Z"
}
```

## Authentication Endpoints

### POST /auth/register
Register new user

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "user_type": "customer" // customer, vendor
}
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "user_type": "customer",
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

### POST /auth/login
Login user

**Request:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "user_type": "customer"
    }
  }
}
```

### POST /auth/logout
Logout user

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

## Product Endpoints

### GET /products
List all products with pagination and filters

**Query Parameters:**
- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (default: 15)
- `category_id` (integer): Filter by category
- `search` (string): Search term
- `sort` (string): Sort field (price, name, popularity)
- `order` (string): asc or desc

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Product Name",
        "description": "Product description",
        "price": 999.99,
        "discount_price": 799.99,
        "stock": 100,
        "rating": 4.5,
        "vendor_id": 1,
        "image": "url"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 150,
      "total_pages": 10
    }
  }
}
```

### GET /products/{id}
Get product details

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Product Name",
    "description": "Detailed description",
    "price": 999.99,
    "discount_price": 799.99,
    "stock": 100,
    "rating": 4.5,
    "reviews_count": 25,
    "vendor": {
      "id": 1,
      "name": "Vendor Name",
      "rating": 4.7
    },
    "images": [
      { "url": "image1.jpg" },
      { "url": "image2.jpg" }
    ],
    "variants": [
      {
        "id": 1,
        "name": "Color",
        "options": ["Red", "Blue", "Green"]
      }
    ]
  }
}
```

## Order Endpoints

### POST /orders
Create new order

**Request:**
```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "variant_options": {
        "color": "Red"
      }
    }
  ],
  "shipping_address_id": 1,
  "payment_method": "paytm",
  "coupon_code": "SAVE10"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 1,
    "order_number": "ORD-2024-001",
    "total_amount": 1799.98,
    "discount_amount": 179.99,
    "final_amount": 1619.99,
    "status": "pending_payment",
    "payment_url": "https://securegw.paytm.in/..."
  }
}
```

### GET /orders
Get user's orders

**Query Parameters:**
- `page` (integer): Page number
- `per_page` (integer): Items per page
- `status` (string): Filter by status

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "order_number": "ORD-2024-001",
        "total_amount": 1619.99,
        "status": "delivered",
        "created_at": "2024-01-17T10:30:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 5
    }
  }
}
```

### GET /orders/{id}
Get order details

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "order_number": "ORD-2024-001",
    "status": "delivered",
    "total_amount": 1619.99,
    "items": [
      {
        "product_id": 1,
        "product_name": "Product",
        "quantity": 2,
        "unit_price": 799.99,
        "total": 1599.98
      }
    ],
    "timeline": [
      {
        "status": "created",
        "timestamp": "2024-01-17T10:30:00Z"
      },
      {
        "status": "paid",
        "timestamp": "2024-01-17T10:35:00Z"
      },
      {
        "status": "shipped",
        "timestamp": "2024-01-18T14:00:00Z"
      },
      {
        "status": "delivered",
        "timestamp": "2024-01-20T16:00:00Z"
      }
    ]
  }
}
```

## Coin Endpoints

### GET /coins/balance
Get user's coin balance

**Response:**
```json
{
  "success": true,
  "data": {
    "total_balance": 5000,
    "available_balance": 4500,
    "locked_balance": 500,
    "pending_balance": 0,
    "expiring_soon": 100
  }
}
```

### GET /coins/transactions
Get coin transaction history

**Query Parameters:**
- `page` (integer): Page number
- `type` (string): earning, redemption, transfer
- `date_from` (string): YYYY-MM-DD
- `date_to` (string): YYYY-MM-DD

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "type": "earning",
        "amount": 500,
        "description": "Purchase reward for order ORD-2024-001",
        "reference_id": 1,
        "created_at": "2024-01-17T10:30:00Z"
      },
      {
        "id": 2,
        "type": "redemption",
        "amount": -100,
        "description": "Redeemed for discount",
        "reference_id": 2,
        "created_at": "2024-01-18T12:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 25
    }
  }
}
```

### POST /coins/redeem
Redeem coins for discount

**Request:**
```json
{
  "order_id": 1,
  "coins_to_redeem": 100
}
```

**Response:**
```json
{
  "success": true,
  "message": "Coins redeemed successfully",
  "data": {
    "coins_redeemed": 100,
    "discount_amount": 50,
    "new_balance": 4400,
    "order_total": 1569.99
  }
}
```

### POST /coins/transfer
Transfer coins to another user

**Request:**
```json
{
  "recipient_email": "recipient@example.com",
  "amount": 100,
  "message": "Gift coins"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Coins transferred successfully",
  "data": {
    "transfer_id": 1,
    "recipient": "recipient@example.com",
    "amount": 100,
    "new_balance": 4400,
    "created_at": "2024-01-17T10:30:00Z"
  }
}
```

## Payment Endpoints

### POST /payments/initiate
Initiate payment with Paytm

**Request:**
```json
{
  "order_id": 1,
  "amount": 1619.99,
  "payment_method": "paytm"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "transaction_id": "TXN-2024-001",
    "payment_url": "https://securegw.paytm.in/theia/api/v1/showPaymentPage?...",
    "order_id": 1
  }
}
```

### POST /payments/callback
Paytm payment callback

**Request:** (From Paytm)
```json
{
  "ORDERID": "ORD-2024-001",
  "TXNAMOUNT": "1619.99",
  "TXNID": "20240117101234567890",
  "TXNDATE": "2024-01-17 10:30:00",
  "STATUS": "TXN_SUCCESS",
  "CHECKSUMHASH": "checksum_hash_value"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment verified successfully",
  "data": {
    "order_id": 1,
    "transaction_id": "TXN-2024-001",
    "status": "success"
  }
}
```

## Vendor Endpoints

### GET /vendor/dashboard
Get vendor dashboard data

**Response:**
```json
{
  "success": true,
  "data": {
    "sales_today": 45000,
    "orders_today": 12,
    "products": 250,
    "active_products": 245,
    "total_coins_earned": 45000,
    "pending_commission": 12000,
    "available_withdrawal": 35000
  }
}
```

### POST /vendor/products
Create product (Vendor)

**Request:**
```json
{
  "name": "Product Name",
  "description": "Product description",
  "price": 999.99,
  "category_id": 1,
  "stock": 100,
  "images": ["image_base64_1", "image_base64_2"],
  "variants": [
    {
      "name": "Color",
      "options": ["Red", "Blue", "Green"]
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "name": "Product Name",
    "status": "pending_approval"
  }
}
```

## Admin Endpoints

### GET /admin/dashboard
Get admin dashboard

**Response:**
```json
{
  "success": true,
  "data": {
    "total_revenue": 5000000,
    "total_users": 1500,
    "total_orders": 2500,
    "active_vendors": 150,
    "pending_settlements": 500000,
    "today_orders": 45,
    "today_revenue": 125000
  }
}
```

### GET /admin/vendors
List vendors for approval

**Query Parameters:**
- `status` (string): pending, approved, rejected
- `page` (integer): Page number

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Vendor Name",
        "email": "vendor@example.com",
        "status": "pending",
        "created_at": "2024-01-17T10:30:00Z"
      }
    ]
  }
}
```

### POST /admin/vendors/{id}/approve
Approve vendor

**Response:**
```json
{
  "success": true,
  "message": "Vendor approved successfully",
  "data": {
    "id": 1,
    "status": "approved"
  }
}
```

## Error Codes

| Code | Message | Description |
|------|---------|-------------|
| 400 | Bad Request | Invalid request parameters |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Access denied |
| 404 | Not Found | Resource not found |
| 409 | Conflict | Resource conflict (e.g., duplicate email) |
| 422 | Unprocessable Entity | Validation error |
| 500 | Server Error | Internal server error |
| 503 | Service Unavailable | Service temporarily unavailable |

## Rate Limiting

API is rate limited to:
- **100 requests per minute** for authenticated users
- **20 requests per minute** for unauthenticated users

Headers:
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Requests remaining
- `X-RateLimit-Reset`: Unix timestamp when limit resets
