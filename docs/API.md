# Apileon API Documentation

## Quick Start Examples

### Basic Routes

#### Hello World
```bash
curl -X GET http://localhost:8000/hello
```
Response:
```json
{
  "message": "Hello from Apileon!"
}
```

#### Hello with Parameter
```bash
curl -X GET http://localhost:8000/hello/John
```
Response:
```json
{
  "message": "Hello John!",
  "timestamp": "2025-08-19 12:00:00"
}
```

### User Management API

#### Get All Users
```bash
curl -X GET http://localhost:8000/api/users
```

#### Get User by ID
```bash
curl -X GET http://localhost:8000/api/users/1
```

#### Create User
```bash
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com"}'
```

#### Update User
```bash
curl -X PUT http://localhost:8000/api/users/1 \
  -H "Content-Type: application/json" \
  -d '{"name": "John Smith"}'
```

#### Delete User
```bash
curl -X DELETE http://localhost:8000/api/users/1
```

### Protected Routes (with Authentication)

#### Get Profile
```bash
curl -X GET http://localhost:8000/api/v1/profile \
  -H "Authorization: Bearer your-token-here"
```

#### Update Profile
```bash
curl -X PUT http://localhost:8000/api/v1/profile \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -d '{"bio": "Updated bio"}'
```

### Public Routes with CORS

#### Get Status
```bash
curl -X GET http://localhost:8000/public/status
```

### Rate Limited Routes

#### Contact Form
```bash
curl -X POST http://localhost:8000/api/contact \
  -H "Content-Type: application/json" \
  -d '{"name": "John", "email": "john@example.com", "message": "Hello!"}'
```

## Available Endpoints

| Method | Endpoint | Description | Middleware |
|--------|----------|-------------|------------|
| GET | `/hello` | Basic hello world | - |
| GET | `/hello/{name}` | Hello with parameter | - |
| GET | `/api/users` | List all users | - |
| GET | `/api/users/{id}` | Get user by ID | - |
| POST | `/api/users` | Create new user | - |
| PUT | `/api/users/{id}` | Update user | - |
| DELETE | `/api/users/{id}` | Delete user | - |
| GET | `/api/v1/profile` | Get user profile | auth |
| PUT | `/api/v1/profile` | Update profile | auth |
| GET | `/public/status` | API status | cors |
| POST | `/api/contact` | Contact form | throttle |

## Middleware

### CORS Middleware
Automatically adds CORS headers to responses and handles preflight requests.

### Auth Middleware
Validates Bearer tokens in the Authorization header.

### Throttle Middleware
Rate limits requests (default: 60 requests per minute per IP).

## Error Responses

All errors follow a consistent format:

```json
{
  "error": "Error type",
  "message": "Detailed error message",
  "code": 400
}
```

Common HTTP status codes:
- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `404` - Not Found
- `422` - Unprocessable Entity
- `429` - Too Many Requests
- `500` - Internal Server Error
