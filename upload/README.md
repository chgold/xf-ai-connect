# AI Connect for XenForo

**WebMCP Protocol Bridge** - Connect AI agents to your XenForo forum using industry-standard authentication and REST APIs.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![XenForo](https://img.shields.io/badge/XenForo-2.2%2B-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.2%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--3.0-green.svg)

---

## 🚀 Quick Start

### Installation

1. **Upload addon files** to your XenForo installation:
   ```
   /src/addons/chgold/AIConnect/
   ```

2. **Install Composer dependencies**:
   ```bash
   cd /path/to/xenforo
   composer require firebase/php-jwt
   ```

3. **Install addon via XenForo Admin CP**:
   - Go to: Admin CP → Add-ons
   - Click "Install from archive/Install add-on"
   - Upload or select the AI Connect addon
   - Complete installation

4. **Done!** The addon is now active.

---

## 📖 Authentication

AI Connect uses **JWT (JSON Web Tokens)** for secure authentication.

### Login and Get Token

**Endpoint**: `POST /api/ai-connect/auth/login`

**Request**:
```json
{
  "username": "your_xenforo_username",
  "password": "your_xenforo_password"
}
```

**Response**:
```json
{
  "success": true,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user_id": 1,
  "username": "admin"
}
```

**cURL Example**:
```bash
curl -X POST "https://your-forum.com/api/ai-connect/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "your_password"
  }'
```

---

## 🛠️ Available Tools

### 1. xenforo.searchThreads

Search XenForo threads with filters.

**Endpoint**: `POST /api/ai-connect/v1/tools/xenforo.searchThreads`

**Parameters**:
- `search` (string, optional) - Search query
- `forum_id` (integer, optional) - Forum ID to filter by
- `limit` (integer, optional) - Max results (default: 10)

**Example**:
```bash
curl -X POST "https://your-forum.com/api/ai-connect/v1/tools/xenforo.searchThreads" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "search": "announcements",
    "forum_id": 2,
    "limit": 10
  }'
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "thread_id": 123,
      "title": "Important Announcement",
      "forum_id": 2,
      "forum_name": "Announcements",
      "user_id": 1,
      "username": "admin",
      "post_date": "2024-01-15T10:30:00+00:00",
      "reply_count": 15,
      "view_count": 250,
      "url": "https://your-forum.com/threads/important-announcement.123/"
    }
  ]
}
```

---

### 2. xenforo.getThread

Get a single thread by ID.

**Parameters**:
- `thread_id` (integer, required) - Thread ID

**Example**:
```bash
curl -X POST "https://your-forum.com/api/ai-connect/v1/tools/xenforo.getThread" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"thread_id": 123}'
```

---

### 3. xenforo.searchPosts

Search XenForo posts.

**Parameters**:
- `search` (string, optional) - Search query
- `thread_id` (integer, optional) - Thread ID to filter by
- `limit` (integer, optional) - Max results (default: 10)

**Example**:
```bash
curl -X POST "https://your-forum.com/api/ai-connect/v1/tools/xenforo.searchPosts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "search": "tutorial",
    "limit": 20
  }'
```

---

### 4. xenforo.getPost

Get a single post by ID.

**Parameters**:
- `post_id` (integer, required) - Post ID

---

### 5. xenforo.getCurrentUser

Get information about the authenticated user.

**Example**:
```bash
curl -X POST "https://your-forum.com/api/ai-connect/v1/tools/xenforo.getCurrentUser" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{}'
```

**Response**:
```json
{
  "success": true,
  "data": {
    "user_id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "is_admin": true,
    "is_moderator": true,
    "message_count": 1250,
    "register_date": 1609459200
  }
}
```

---

## 🔐 Admin Controls

### Settings

Navigate to **Admin CP → Options → AI Connect** to configure:

#### Rate Limiting
- **Requests per minute**: Default 50
- **Requests per hour**: Default 1,000

#### Security
- **JWT Secret Rotation**: Invalidate all tokens immediately
- **Blocked Users**: Prevent specific users from API access

### Block User Access

Via Admin CP:
1. Go to **AI Connect → Blocked Users**
2. Enter User ID
3. Click "Block"

Blocked users cannot authenticate or use existing tokens.

---

## 📊 Rate Limiting

Default limits (per user):
- **50 requests per minute**
- **1,000 requests per hour**

**Rate limit response**:
```json
{
  "success": false,
  "error": {
    "code": "rate_limit_exceeded",
    "message": "Rate limit exceeded: 50 requests per minute",
    "data": {
      "retry_after": 45,
      "limit": 50,
      "current": 51
    }
  }
}
```

---

## 🔧 Development

### Project Structure

```
xenforo-ai-connect/
├── addon.json                          # XenForo metadata
├── Setup.php                           # Installation/upgrade
├── composer.json                       # Dependencies
├── src/addons/chgold/AIConnect/
│   ├── Service/
│   │   ├── Auth.php                    # JWT authentication
│   │   ├── Manifest.php                # WebMCP manifest
│   │   └── RateLimiter.php             # Rate limiting
│   └── Module/
│       ├── ModuleBase.php              # Base class
│       └── CoreModule.php              # XenForo tools
```

### Add Custom Tools

Create a new module extending `ModuleBase`:

```php
<?php
namespace YourVendor\YourAddon\Module;

use chgold\AIConnect\Module\ModuleBase;

class CustomModule extends ModuleBase
{
    protected $moduleName = 'custom';

    protected function registerTools()
    {
        $this->registerTool('myTool', [
            'description' => 'My custom tool',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'param1' => [
                        'type' => 'string',
                        'description' => 'First parameter'
                    ]
                ],
                'required' => ['param1']
            ],
        ]);
    }

    public function execute_myTool($params)
    {
        return $this->success(['result' => 'Custom tool executed']);
    }
}
```

---

## 🐛 Troubleshooting

### "authentication_failed" Error
**Solution**: Verify XenForo credentials are correct

### "access_denied" Error
**Solution**: Check if user is blocked (Admin CP → AI Connect → Blocked Users)

### "Token expired"
**Solution**: Tokens expire after 1 hour. Re-authenticate to get a new token.

### "Rate limit exceeded"
**Solution**: 
- Wait for `retry_after` seconds
- Increase limits in Admin CP → Options → AI Connect

---

## 📄 License

GPL-3.0-or-later

---

## 🔗 Links

- **GitHub**: https://github.com/chgold/xenforo-ai-connect
- **Issues**: https://github.com/chgold/xenforo-ai-connect/issues

---

**Made with ❤️ for the XenForo community**
