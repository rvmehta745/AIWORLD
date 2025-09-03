# API Logging System

This system automatically logs all API requests and responses to the `api_logs` table for monitoring and debugging purposes.

## Features

- **Automatic Logging**: All API requests are automatically logged except the `/register` endpoint
- **Comprehensive Data**: Logs include request/response data, user info, timing, and error details
- **Security**: Sensitive fields like passwords are automatically redacted
- **Performance Tracking**: Records API response times in milliseconds
- **Error Tracking**: Flags and tracks API errors (status codes >= 400)

## Database Schema

The `api_logs` table includes:
- User identification and authentication details
- HTTP method and endpoint information
- Request/response payloads (JSON format)
- Performance metrics (duration_ms)
- Error tracking and messages
- IP address and user agent for security auditing

## Usage

### Viewing API Logs

Access API logs via the authenticated endpoint:
```
GET /admin/api-logs
```

**Query Parameters:**
- `user_id`: Filter by specific user
- `endpoint`: Filter by API endpoint (partial match)
- `method`: Filter by HTTP method (GET, POST, etc.)
- `status_code`: Filter by response status code
- `is_error`: Filter by error status (true/false)
- `per_page`: Number of results per page (default: 50)

### Example Response
```json
{
  "status": "SUCCESS",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 123,
        "http_method": "POST",
        "api_endpoint": "/admin/login",
        "ip_address": "192.168.1.1",
        "response_status_code": 200,
        "duration_ms": 150,
        "is_error": false,
        "created_at": "2024-01-01T10:00:00Z",
        "user": {
          "id": 123,
          "first_name": "John",
          "last_name": "Doe",
          "email": "john@example.com"
        }
      }
    ]
  }
}
```

## Security Features

- Passwords and sensitive data are automatically redacted as `[REDACTED]`
- User agent and IP address tracking for security auditing
- Soft deletes support for data retention policies

## Performance Considerations

- The middleware is designed to have minimal impact on API performance
- Failed logging attempts are silently handled to avoid breaking the application
- Consider implementing log rotation and archival for high-traffic applications

## Migration

Run the migration to create the table:
```bash
php artisan migrate --path=database/migrations/2024_01_01_000000_create_api_logs_table.php
```

## Middleware Configuration

The `ApiLogger` middleware is automatically applied to all API routes through the `api` middleware group in `app/Http/Kernel.php`.