# API Reference

Complete API documentation for XenForo AI Connect.

## Base URL

```
http://your-forum.com/api
```

## Endpoints

### 1. Get Manifest (Public)

**Endpoint**: `GET /aiconnect-manifest`  
**Authentication**: Not required  
**Description**: Returns the WebMCP protocol manifest

**Response**:
```json
{
  "schema_version": "1.0",
  "name": "xenforo-ai-connect",
  "version": "1.0.0",
  "description": "WebMCP bridge for XenForo",
  "capabilities": {
    "tools": true,
    "resources": false,
    "prompts": false
  },
  "tools": [...]
}
```

---

### 2. Authenticate

**Endpoint**: `POST /aiconnect-auth`  
**Authentication**: Not required  
**Content-Type**: `application/json`

**Request Body**:
```json
{
  "username": "your_username",
  "password": "your_password"
}
```

**Response (Success)**:
```json
{
  "success": true,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "expires_in": 86400,
  "user": {
    "user_id": 1,
    "username": "your_username"
  }
}
```

**Response (Error)**:
```json
{
  "success": false,
  "error": "Invalid credentials"
}
```

---

### 3. Execute Tools

**Endpoint**: `POST /aiconnect-tools`  
**Authentication**: Required (JWT Bearer token)  
**Content-Type**: `application/json`

**Headers**:
```
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: application/json
```

**Request Body**:
```json
{
  "tool": "xenforo.searchThreads",
  "input": {
    "search": "keyword",
    "limit": 10
  }
}
```

---

## Tools

### searchThreads

Search forum threads with filters.

**Tool Name**: `xenforo.searchThreads`

**Input Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search query |
| `forum_id` | integer | No | Filter by forum ID |
| `limit` | integer | No | Max results (default: 10) |

**Example Request**:
```json
{
  "tool": "xenforo.searchThreads",
  "input": {
    "search": "AI technology",
    "forum_id": 5,
    "limit": 20
  }
}
```

**Example Response**:
```json
{
  "success": true,
  "data": {
    "threads": [
      {
        "thread_id": 123,
        "title": "Discussion about AI",
        "post_count": 45,
        "view_count": 1200,
        "created_at": "2024-01-15T10:30:00Z"
      }
    ],
    "total": 1
  }
}
```

---

### getThread

Get a specific thread by ID.

**Tool Name**: `xenforo.getThread`

**Input Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `thread_id` | integer | Yes | Thread ID |

**Example Request**:
```json
{
  "tool": "xenforo.getThread",
  "input": {
    "thread_id": 123
  }
}
```

**Example Response**:
```json
{
  "success": true,
  "data": {
    "thread_id": 123,
    "title": "Discussion about AI",
    "post_count": 45,
    "view_count": 1200,
    "first_post": {
      "post_id": 456,
      "message": "Thread content...",
      "author": "username"
    }
  }
}
```

---

### searchPosts

Search forum posts.

**Tool Name**: `xenforo.searchPosts`

**Input Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search query |
| `thread_id` | integer | No | Filter by thread ID |
| `limit` | integer | No | Max results (default: 10) |

**Example Request**:
```json
{
  "tool": "xenforo.searchPosts",
  "input": {
    "search": "machine learning",
    "limit": 10
  }
}
```

---

### getPost

Get a specific post by ID.

**Tool Name**: `xenforo.getPost`

**Input Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `post_id` | integer | Yes | Post ID |

**Example Request**:
```json
{
  "tool": "xenforo.getPost",
  "input": {
    "post_id": 789
  }
}
```

---

### getCurrentUser

Get current authenticated user information.

**Tool Name**: `xenforo.getCurrentUser`

**Input Parameters**: None

**Example Request**:
```json
{
  "tool": "xenforo.getCurrentUser",
  "input": {}
}
```

**Example Response**:
```json
{
  "success": true,
  "data": {
    "user_id": 1,
    "username": "your_username",
    "email": "user@example.com",
    "message_count": 150,
    "register_date": "2020-01-01T00:00:00Z"
  }
}
```

---

## Error Responses

All endpoints return errors in this format:

```json
{
  "errors": [
    {
      "code": "error_code",
      "message": "Human readable error message",
      "params": {}
    }
  ]
}
```

### Common Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `no_api_key_in_request` | 400 | No authentication provided |
| `api_key_not_found` | 401 | Invalid JWT token |
| `api_key_inactive` | 403 | Token expired or revoked |
| `endpoint_not_found` | 404 | Invalid endpoint |
| `rate_limit_exceeded` | 429 | Too many requests |

---

## Rate Limits

Default rate limits:
- **100 requests per hour** per authenticated user
- **10 requests per minute** for manifest endpoint (unauthenticated)

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1640000000
```

---

## Authentication Flow

1. **Get Manifest** (optional, for discovery)
   ```bash
   curl http://your-forum.com/api/aiconnect-manifest
   ```

2. **Authenticate**
   ```bash
   curl -X POST http://your-forum.com/api/aiconnect-auth \
     -H "Content-Type: application/json" \
     -d '{"username":"user","password":"pass"}'
   ```

3. **Use Tools**
   ```bash
   curl -X POST http://your-forum.com/api/aiconnect-tools \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"tool":"xenforo.getCurrentUser","input":{}}'
   ```

---

## WebMCP Protocol

This addon implements the WebMCP (Web Model Context Protocol) specification for AI agent integration.

**Key Features**:
- Standard manifest discovery
- Tool-based architecture
- JSON-based request/response format
- JWT authentication
- Rate limiting

**Learn more**: [WebMCP Specification](https://github.com/webmcp/specification)
