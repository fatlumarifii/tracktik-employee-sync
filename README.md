# TrackTik Employee Sync API

Laravel API that receives employee data from identity providers and syncs them to TrackTik's REST API.

## Requirements

- PHP 8.2+ / 8.3
- Composer
- MySQL 5.7+ or MariaDB 10.3+

## Installation

```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create database
mysql -u root -p -e "CREATE DATABASE tracktik_employee_sync;"

# Run migrations
php artisan migrate
```

## Configuration

Update `.env` with the required configuration:

```
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tracktik_employee_sync
DB_USERNAME=root
DB_PASSWORD=your_password

# TrackTik API credentials
TRACKTIK_CLIENT_ID=your_client_id
TRACKTIK_CLIENT_SECRET=your_client_secret
TRACKTIK_TOKEN_URL=https://cnvc2vp9q8.execute-api.us-east-2.amazonaws.com/prod/oauth/token
TRACKTIK_API_BASE_URL=https://cnvc2vp9q8.execute-api.us-east-2.amazonaws.com/prod/v1

# Provider API tokens for authentication
PROVIDER1_API_TOKEN=your_provider1_token
PROVIDER2_API_TOKEN=your_provider2_token
```

## Documentation

- **[API Documentation](API.md)** - Complete API reference with examples
- **[Postman Collection](TrackTik-Employee-Sync.postman_collection.json)** - Import into Postman for testing

## API Endpoints

### Provider 1
```bash
POST /api/v1/employees/provider1
Content-Type: application/json
Authorization: Bearer {PROVIDER1_API_TOKEN}

{
  "emp_id": "P1_001",
  "first_name": "John",
  "last_name": "Doe",
  "email_address": "john.doe@example.com",
  "phone": "+1234567890",
  "job_title": "Security Guard",
  "dept": "Security Operations",
  "hire_date": "2024-01-15",
  "employment_status": "active"
}
```

### Provider 2
```bash
POST /api/v1/employees/provider2
Content-Type: application/json
Authorization: Bearer {PROVIDER2_API_TOKEN}

{
  "employee_number": "P2_001",
  "personal_info": {
    "given_name": "Jane",
    "family_name": "Doe",
    "email": "jane.doe@example.com",
    "mobile": "+1234567890"
  },
  "work_info": {
    "role": "Security Guard",
    "division": "Night Shift Security",
    "start_date": "2024-02-01",
    "current_status": "employed"
  }
}
```

## Testing

```bash
php artisan test
```

## Architecture

- **Actions Pattern**: Business logic encapsulated in action classes
- **Form Requests**: Input validation
- **API Resources**: Response formatting
- **Service Layer**: External API integration
- **Custom Exceptions**: Error handling

## Tech Stack

- Laravel 12
- PHP 8.2
- MySQL 5.7
