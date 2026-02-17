# XenForo AI Connect

[![Version](https://img.shields.io/badge/version-1.0.1-blue.svg)](releases/)
[![XenForo](https://img.shields.io/badge/XenForo-2.2.0+-orange.svg)](https://xenforo.com)
[![License](https://img.shields.io/badge/license-GPL--3.0-green.svg)](upload/src/addons/chgold/AIConnect/LICENSE-GPL.txt)

**Connect AI agents to your XenForo forum through a standardized WebMCP API.**

A WebMCP Protocol Bridge that allows AI agents to interact with XenForo forums through standardized API endpoints. Search threads, read posts, and manage forum content programmatically.

---

## 🚀 Features

- ✅ **WebMCP Protocol Compliant** - Standard manifest and tool execution endpoints
- ✅ **5 Core Tools** - Search threads, get thread, search posts, get post, get current user  
- ✅ **JWT Authentication** - Secure token-based authentication system
- ✅ **Rate Limiting** - Built-in configurable rate limits per user
- ✅ **Public Discovery** - Manifest endpoint accessible without authentication
- ✅ **Easy Integration** - Simple REST API for AI agents

---

## 📋 Requirements

- **XenForo** 2.2.0 or higher
- **PHP** 7.2.0 or higher
- **MySQL** 5.5 or higher

> PHP dependencies (`firebase/php-jwt`) are bundled inside the addon. No manual Composer step required.

---

## 📦 Installation

### Quick Install

1. **Download** the latest release
   ```
   releases/xenforo-ai-connect-v1.0.1-fixed.zip
   ```

2. **Install via Admin Panel**
   - Go to: Admin CP → Add-ons → Install add-on
   - Upload the zip file
   - Click "Install"

3. **Verify Installation**
   ```bash
   curl http://your-forum.com/api/aiconnect-manifest
   ```
   
   You should receive a JSON manifest response.

📖 **Detailed Instructions**: See [docs/INSTALLATION.md](docs/INSTALLATION.md)

---

## 🔌 API Endpoints

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/aiconnect-manifest` | GET | ❌ No | Get WebMCP manifest (public) |
| `/api/aiconnect-auth` | POST | ❌ No | Authenticate and get JWT token |
| `/api/aiconnect-tools` | POST | ✅ Yes | Execute tools with JWT |

---

## 🛠️ Available Tools

### 1. searchThreads
Search forum threads with filters

```json
{
  "search": "keyword",
  "forum_id": 5,
  "limit": 10
}
```

### 2. getThread
Get a specific thread by ID

```json
{
  "thread_id": 123
}
```

### 3. searchPosts
Search forum posts

```json
{
  "search": "keyword",
  "thread_id": 123,
  "limit": 10
}
```

### 4. getPost
Get a specific post by ID

```json
{
  "post_id": 456
}
```

### 5. getCurrentUser
Get current authenticated user information

```json
{}
```

---

## 📚 Quick Start

### 1. Get the Manifest
```bash
curl http://your-forum.com/api/aiconnect-manifest
```

### 2. Authenticate
```bash
curl -X POST http://your-forum.com/api/aiconnect-auth \
  -H "Content-Type: application/json" \
  -d '{
    "username": "your_username",
    "password": "your_password"
  }'
```

**Response:**
```json
{
  "success": true,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "expires_in": 86400
}
```

### 3. Use Tools
```bash
curl -X POST http://your-forum.com/api/aiconnect-tools \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tool": "xenforo.searchThreads",
    "input": {
      "search": "AI",
      "limit": 5
    }
  }'
```

---

## 📖 Documentation

- **[Installation Guide](docs/INSTALLATION.md)** - Detailed installation instructions
- **[Changelog](docs/CHANGELOG.md)** - Version history and updates
- **[Testing Summary](docs/TESTING_SUMMARY.md)** - Test results and verification

---

## 🔧 Configuration

After installation, the addon creates 4 database tables:

- `xf_ai_connect_api_keys` - API key storage
- `xf_ai_connect_blocked_users` - Blocked users list
- `xf_ai_connect_rate_limits` - Rate limit tracking
- `xf_ai_connect_settings` - Addon settings

### Default Settings

- **JWT Secret**: Auto-generated on installation
- **Token Expiry**: 86400 seconds (24 hours)
- **Rate Limit**: 100 requests per hour per user
- **Enabled**: Yes

---

## 🐛 Troubleshooting

### Routes Not Found (404 Error)

1. Disable and re-enable the addon in Admin CP
2. Clear cache:
   ```bash
   php cmd.php xf:rebuild-caches
   ```

### File Hash Mismatch

This means files were modified after installation. Reinstall to fix:
```bash
php cmd.php xf-addon:uninstall chgold/AIConnect
# Then reinstall via Admin CP
```

### API Key Errors

Make sure you're using the correct endpoints:
- ✅ `aiconnect-manifest` (with hyphens)
- ❌ `aiconnect/manifest` (slashes won't work)

---

## 🔐 Security

- JWT tokens expire after 24 hours (configurable)
- Rate limiting prevents abuse
- User permissions respected for all operations
- Password authentication only (API keys not stored in plain text)

---

## 📝 License

This project is licensed under the GPL-3.0 License - see the [LICENSE-GPL.txt](upload/src/addons/chgold/AIConnect/LICENSE-GPL.txt) file for details.

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit issues or pull requests.

---

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/yourusername/xenforo-ai-connect/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/xenforo-ai-connect/discussions)

---

## 🎯 Roadmap

- [ ] Add more tools (create thread, reply to post, etc.)
- [ ] OAuth2 support
- [ ] WebSocket support for real-time updates
- [ ] Admin panel for managing API keys
- [ ] Usage statistics and analytics

---

## 👏 Credits

Developed with ❤️ for the XenForo and AI communities.

---

**Made with XenForo 2.2.8** | **WebMCP Protocol** | **AI-Ready**
