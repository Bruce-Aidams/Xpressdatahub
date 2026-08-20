# Xpressdatahub API Documentation (v1)

Welcome to the Xpressdatahub API documentation. This guide is designed to help developers and agents integrate our mobile data provisioning services.

---

## 1. Authentication
All requests to the Xpressdatahub API must be authenticated using your unique API Key. You can pass the API key in one of three ways:

1. **HTTP Header (Recommended)**:
   ```http
   X-API-Key: your_api_key_here
   ```
2. **Query Parameter**:
   `?api_key=your_api_key_here`
3. **Request Body Parameter**:
   `"api_key": "your_api_key_here"`

---

## 2. API General Status Codes & Errors
Below are the standard HTTP status codes returned by the API along with their JSON payload structures:

### 401 Unauthorized
Returned when the API key is missing or invalid.
```json
{
  "success": false,
  "message": "API key is required"
}
```
or
```json
{
  "success": false,
  "message": "Invalid API key"
}
```

### 403 Forbidden
Returned if the API key is inactive, has expired, or if the agent account is disabled.
```json
{
  "success": false,
  "message": "API key is inactive"
}
```

### 429 Too Many Requests
Returned when you exceed your assigned rate limit. The response includes a `Retry-After` header indicating how many seconds you must wait.
```json
{
  "success": false,
  "message": "Rate limit exceeded. Try again in 15 seconds"
}
```

---

## 3. Endpoints

### 3.1 Get Wallet Balance
Retrieve your current wallet balance and currency.

- **URL**: `/api/v1/wallet/balance`
- **Method**: `GET`
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "balance": 150.75,
    "currency": "GH₵"
  }
  ```

---

### 3.2 List Available Packages
Get all active data bundle packages available for purchase.

- **URL**: `/api/v1/packages`
- **Method**: `GET`
- **Query Parameters**:
  - `network_type` *(Optional)*: Filter by network. Allowed values: `MTN`, `Telecel`, `AirtelTigo`
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "packages": [
      {
        "id": 12,
        "network_type": "MTN",
        "package_size": "1.5GB",
        "package_size_gb": 1.5,
        "selling_price": 10.00,
        "cost": 8.50
      },
      {
        "id": 15,
        "network_type": "Telecel",
        "package_size": "5GB",
        "package_size_gb": 5.0,
        "selling_price": 35.00,
        "cost": 31.00
      }
    ],
    "total": 2
  }
  ```

---

### 3.3 Create Data Purchase Order
Place a data purchase order. The cost of the package will be deducted from your wallet balance.

- **URL**: `/api/v1/orders`
- **Method**: `POST`
- **Request Body (JSON)**:
  ```json
  {
    "phone_number": "0241234567",
    "network_type": "MTN",
    "package_size": "1.5GB"
  }
  ```
- **Validation Rules**:
  - `phone_number` must be a valid Ghana phone number (digits from MTN, Telecel, or AirtelTigo).
  - `network_type` must be one of: `MTN`, `Telecel`, `AirtelTigo`.
  - `package_size` must match an active package exactly.

#### Responses & Specific Error Codes

- **200 OK (Success / Submitted to Provider)**:
  ```json
  {
    "success": true,
    "order_id": 482,
    "order_reference": "ORD-5F2A8D9B-1715892",
    "network_type": "MTN",
    "package_size": "1.5GB",
    "phone_number": "0241234567",
    "amount": 10.00,
    "new_balance": 140.75,
    "status": "processing",
    "external_transaction_id": "TXN-98471204",
    "message": "Order submitted successfully and is now being processed"
  }
  ```

- **402 Payment Required**:
  Wallet balance is too low for the transaction.
  ```json
  {
    "success": false,
    "message": "Insufficient wallet balance."
  }
  ```

- **422 Unprocessable Entity**:
  Invalid inputs (e.g. invalid phone format, phone prefix mismatch for the selected network, or unavailable package).
  ```json
  {
    "success": false,
    "message": "Invalid Ghana phone number."
  }
  ```
  or
  ```json
  {
    "success": false,
    "message": "Package not found for this network."
  }
  ```

- **500 Internal Server Error**:
  Order created locally, but the external provider's API returned an error.
  ```json
  {
    "success": false,
    "order_id": 482,
    "status": "failed",
    "error": "cURL Error: Connection timed out",
    "message": "Order created but failed to submit to external API"
  }
  ```

---

### 3.4 Check Order Status
Check the status of an order using your transaction reference or order reference.

- **URL**: `/api/v1/orders/status/{reference}`
- **Method**: `GET`
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "order": {
      "id": 482,
      "status": "processing",
      "phone_number": "0241234567",
      "network_type": "MTN",
      "package_size": "1.5GB",
      "amount": "10.00",
      "order_reference": "ORD-5F2A8D9B-1715892",
      "created_at": "2026-08-20 12:32:13",
      "updated_at": "2026-08-20 12:33:00"
    }
  }
  ```
- **Response (404 Not Found)**:
  ```json
  {
    "success": false,
    "message": "Order not found."
  }
  ```

---

### 3.5 List Orders
Retrieve a paginated list of your placed orders.

- **URL**: `/api/v1/orders`
- **Method**: `GET`
- **Query Parameters**:
  - `status` *(Optional)*: Filter by status. Allowed: `pending`, `processing`, `delivered`, `failed`, `cancelled`
  - `network_type` *(Optional)*: Filter by network: `MTN`, `Telecel`, `AirtelTigo`
  - `limit` *(Optional)*: Items per page (1 to 100, default: 50)
  - `offset` *(Optional)*: Pagination offset (default: 0)
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "orders": [
      {
        "id": 482,
        "phone_number": "0241234567",
        "network_type": "MTN",
        "package_size": "1.5GB",
        "amount": "10.00",
        "status": "processing",
        "payment_method": "wallet",
        "order_reference": "ORD-5F2A8D9B-1715892",
        "created_at": "2026-08-20 12:32:13",
        "updated_at": "2026-08-20 12:33:00"
      }
    ],
    "total": 1
  }
  ```

---

## 4. Order Status Flow
When you submit an order, it progresses through the following states:

1. **`pending`**: Order initialized and validated.
2. **`processing`**: Order successfully submitted to the external data provider.
3. **`delivered`**: Provisioning completed successfully.
4. **`failed`**: Submission or provisioning failed.
5. **`cancelled`**: Transaction cancelled.
