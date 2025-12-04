# API Documentation

## Overview

The TrackTik Employee Sync API provides endpoints to synchronize employee data from identity providers to TrackTik's REST API. The API supports two different provider schemas with automatic data transformation and validation.

## Base URL

```
http://localhost:8000/api/v1
```

## Authentication

All API endpoints require Bearer token authentication. Each provider has a unique API token.

**Request Header:**
```
Authorization: Bearer {API_TOKEN}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Invalid API token",
  "error": "Unauthorized"
}
```

---

## Endpoints

### POST /employees/provider1

Syncs employee data from Provider 1 format.

**Authentication:** Required (`PROVIDER1_API_TOKEN`)

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| emp_id | string | Yes | Employee ID from provider |
| first_name | string | Yes | Employee first name |
| last_name | string | Yes | Employee last name |
| email_address | string | Yes | Valid email address |
| phone | string | No | Phone number |
| job_title | string | No | Job title |
| dept | string | No | Department name |
| hire_date | date | No | Hire date (YYYY-MM-DD) |
| employment_status | string | No | active, inactive, or terminated |

**Example Request:**
```bash
curl -X POST http://localhost:8000/api/v1/employees/provider1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your_provider1_token" \
  -d '{
    "emp_id": "P1_001",
    "first_name": "Alice",
    "last_name": "Johnson",
    "email_address": "alice.johnson@example.com",
    "phone": "+1-555-0101",
    "job_title": "Security Officer",
    "dept": "Security Operations",
    "hire_date": "2024-01-15",
    "employment_status": "active"
  }'
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Employee synced successfully",
  "data": {
    "id": 1,
    "provider": "provider1",
    "provider_employee_id": "P1_001",
    "tracktik_employee_id": "TT_123456789",
    "sync_status": "success",
    "error_message": null,
    "created_at": "2024-12-04T10:30:00.000000Z",
    "updated_at": "2024-12-04T10:30:00.000000Z"
  }
}
```

**Validation Error (422):**
```json
{
  "message": "The email address field must be a valid email address.",
  "errors": {
    "email_address": [
      "The email address field must be a valid email address."
    ]
  }
}
```

---

### POST /employees/provider2

Syncs employee data from Provider 2 format.

**Authentication:** Required (`PROVIDER2_API_TOKEN`)

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| employee_number | string | Yes | Employee ID from provider |
| personal_info | object | Yes | Personal information object |
| personal_info.given_name | string | Yes | First name |
| personal_info.family_name | string | Yes | Last name |
| personal_info.email | string | Yes | Valid email address |
| personal_info.mobile | string | No | Mobile phone number |
| work_info | object | Yes | Work information object |
| work_info.role | string | Yes | Job role/title |
| work_info.division | string | No | Division/department |
| work_info.start_date | date | No | Start date (YYYY-MM-DD) |
| work_info.current_status | string | No | employed, on_leave, or terminated |

**Example Request:**
```bash
curl -X POST http://localhost:8000/api/v1/employees/provider2 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your_provider2_token" \
  -d '{
    "employee_number": "P2_001",
    "personal_info": {
      "given_name": "Carol",
      "family_name": "Davis",
      "email": "carol.davis@example.com",
      "mobile": "+1-555-0201"
    },
    "work_info": {
      "role": "Security Guard",
      "division": "Night Shift Security",
      "start_date": "2024-02-01",
      "current_status": "employed"
    }
  }'
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Employee synced successfully",
  "data": {
    "id": 2,
    "provider": "provider2",
    "provider_employee_id": "P2_001",
    "tracktik_employee_id": "TT_987654321",
    "sync_status": "success",
    "error_message": null,
    "created_at": "2024-12-04T10:31:00.000000Z",
    "updated_at": "2024-12-04T10:31:00.000000Z"
  }
}
```

---

## Response Codes

| Code | Description |
|------|-------------|
| 201 | Employee synced successfully |
| 401 | Unauthorized - Invalid or missing API token |
| 422 | Validation Error - Invalid request data |
| 500 | Server Error - TrackTik API unavailable or internal error |

---

## Error Handling

### Validation Errors (422)

Returned when request data fails validation rules.

**Example:**
```json
{
  "message": "The first name field is required.",
  "errors": {
    "first_name": ["The first name field is required."],
    "last_name": ["The last name field is required."]
  }
}
```

### API Errors (500)

Returned when TrackTik API is unavailable or returns an error.

**Example:**
```json
{
  "success": false,
  "message": "Failed to sync employee",
  "error": "Failed to create employee in TrackTik"
}
```

---

## Idempotency

The API is **idempotent** - sending the same employee data multiple times will not create duplicates. If an employee with the same `emp_id` or `employee_number` already exists and has been successfully synced, the API returns the existing record.

**Example:**
```bash
# First request - creates employee
POST /api/v1/employees/provider1
{"emp_id": "P1_001", ...}
→ 201 Created, tracktik_employee_id: "TT_123"

# Second request - returns existing employee
POST /api/v1/employees/provider1
{"emp_id": "P1_001", ...}
→ 201 OK, tracktik_employee_id: "TT_123" (same as before)
```

---

## Rate Limiting

Currently no rate limiting is enforced. For production use, implement rate limiting to prevent abuse.

---

## Testing

Import the provided Postman collection (`TrackTik-Employee-Sync.postman_collection.json`) to test all endpoints with sample data.

**Collection includes:**
- Authentication tests
- Provider 1 employee sync (6 scenarios)
- Provider 2 employee sync (4 scenarios)
- Validation error tests
- Idempotency tests
