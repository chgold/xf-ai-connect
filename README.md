# XenForo AI Connect

[![Version](https://img.shields.io/badge/version-1.1.6-blue.svg)](releases/)
[![XenForo](https://img.shields.io/badge/XenForo-2.2.0+-orange.svg)](https://xenforo.com)
[![License](https://img.shields.io/badge/license-GPL--3.0-green.svg)](upload/src/addons/chgold/AIConnect/LICENSE-GPL.txt)

**Connect AI agents to your XenForo forum through the WebMCP Protocol.**

A WebMCP Protocol Bridge that allows AI agents (ChatGPT, Claude, Gemini, Copilot, etc.) to interact with XenForo forums through standardized API endpoints using OAuth 2.0 with PKCE for secure authentication.

---

## Editions

| Feature | Free | Pro |
|---------|------|-----|
| Core tools (search, read) | ✅ | ✅ |
| OAuth 2.0 + PKCE | ✅ | ✅ |
| Translation tools | ✅ | ✅ |
| Write tools (create thread, reply) | — | ✅ |
| Admin tools (manage users, moderate) | — | ✅ |
| Custom modules & hooks | — | ✅ |
| Priority support | — | ✅ |

---

## Features

- **WebMCP Protocol Compliant** — Standard manifest and tool execution endpoints
- **OAuth 2.0 + PKCE** — Secure authentication flow, no passwords stored
- **7 Built-in Tools** — Search threads, read posts, translate content, and more
- **Pre-configured AI Clients** — ChatGPT, Claude, Gemini, Copilot, Grok, DeepSeek, Perplexity, Meta AI
- **Rate Limiting** — Configurable per-user rate limits
- **Translation Module** — Translate content between 30+ languages
- **Public Discovery** — Manifest endpoint for automatic AI agent configuration

---

## Requirements

- **XenForo** 2.2.0 or higher
- **PHP** 7.2.0 or higher
- **MySQL** 5.5 or higher

> PHP dependencies (`firebase/php-jwt`) are bundled. No manual Composer step required.

---

## Installation

### Quick Install

1. **Download** the latest release from [Releases](releases/)

2. **Extract** the `upload/` directory into your XenForo root

3. **Install via CLI** (recommended):
   ```bash
   php cmd.php xf:addon-install chgold/AIConnect
   ```
   Or via Admin CP → Add-ons → Install add-on

4. **Copy `oauth.php`** to your XenForo root directory (if not already there)

5. **Add to `.htaccess`** (Apache only — required for Bearer token auth):
   ```apache
   RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
   ```
   Add this before the existing RewriteRule lines.

6. **Verify**:
   ```bash
   curl https://your-forum.com/api/aiconnect-manifest
   ```

---

## API Endpoints

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/aiconnect-manifest` | GET | No | WebMCP manifest (public discovery) |
| `/oauth.php` | GET | No | OAuth 2.0 consent screen |
| `/api/aiconnect-oauth` | POST | No | Token exchange (authorization_code, refresh_token) |
| `/api/aiconnect-tools/` | POST | Bearer | Execute tools |

---

## Authentication (OAuth 2.0 + PKCE)

AI Connect uses the OAuth 2.0 Authorization Code flow with PKCE (Proof Key for Code Exchange) — no client secrets needed.

### Flow

```
1. AI Agent → GET /oauth.php?client_id=chatgpt&response_type=code
                             &scope=read+write
                             &code_challenge=...&code_challenge_method=S256
                             &redirect_uri=urn:ietf:wg:oauth:2.0:oob

2. User approves → receives authorization code

3. AI Agent → POST /api/aiconnect-oauth
              { grant_type, code, client_id, code_verifier, redirect_uri }

4. Response → { access_token, refresh_token, expires_in, scope }

5. AI Agent → POST /api/aiconnect-tools/
              Authorization: Bearer <access_token>
              { "name": "xenforo.searchThreads", "arguments": {...} }
```

### Pre-configured Clients

The addon ships with OAuth clients for major AI platforms:

| Client ID | Platform |
|-----------|----------|
| `chatgpt` | ChatGPT (OpenAI) |
| `claude-ai` | Claude (Anthropic) |
| `gemini` | Gemini (Google) |
| `copilot` | Microsoft Copilot |
| `grok` | Grok (xAI) |
| `deepseek` | DeepSeek AI |
| `perplexity` | Perplexity AI |
| `meta-ai` | Meta AI |

---

## Available Tools

### Core Module (`xenforo.*`)

#### searchThreads
Search forum threads with filters.
```json
{ "name": "xenforo.searchThreads", "arguments": { "search": "keyword", "forum_id": 5, "limit": 10 } }
```

#### getThread
Get a specific thread by ID.
```json
{ "name": "xenforo.getThread", "arguments": { "thread_id": 123 } }
```

#### searchPosts
Search post content.
```json
{ "name": "xenforo.searchPosts", "arguments": { "search": "keyword", "thread_id": 123, "limit": 10 } }
```

#### getPost
Get a specific post by ID.
```json
{ "name": "xenforo.getPost", "arguments": { "post_id": 456 } }
```

#### getCurrentUser
Get the authenticated user's information.
```json
{ "name": "xenforo.getCurrentUser", "arguments": {} }
```

### Translation Module (`translation.*`)

#### translate
Translate text between languages.
```json
{ "name": "translation.translate", "arguments": { "text": "Hello world", "target_lang": "he", "source_lang": "en" } }
```

#### getSupportedLanguages
List all supported language codes.
```json
{ "name": "translation.getSupportedLanguages", "arguments": {} }
```

---

## Database Tables

The addon creates 7 tables on installation:

| Table | Purpose |
|-------|---------|
| `xf_ai_connect_oauth_clients` | Registered OAuth clients |
| `xf_ai_connect_oauth_codes` | Authorization codes (temporary) |
| `xf_ai_connect_oauth_tokens` | Access & refresh tokens |
| `xf_ai_connect_api_keys` | API key storage |
| `xf_ai_connect_blocked_users` | Blocked users list |
| `xf_ai_connect_rate_limits` | Rate limit tracking |
| `xf_ai_connect_settings` | Addon settings |

---

## Troubleshooting

### Bearer Token Not Working (403)

Apache strips the `Authorization` header by default. Add to `.htaccess`:
```apache
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
```

### Routes Not Found (404)

```bash
php cmd.php xf:rebuild-caches
```

### "Unexpected Content" Warning on Install

Rebuild the addon to regenerate `hashes.json`:
```bash
php cmd.php xf-addon:build-release chgold/AIConnect
```

---

## Security

- OAuth 2.0 with PKCE — no client secrets, no stored passwords
- Access tokens expire after 1 hour (configurable)
- Refresh tokens expire after 30 days
- Rate limiting prevents abuse
- Token revocation support

---

## Credits

- **[WebMCP Protocol](https://webmcp.org)** — The open protocol specification that makes AI-to-web integration possible. AI Connect implements the WebMCP standard for XenForo.
- **[XenForo](https://xenforo.com)** — The forum platform this addon extends.
- **[Firebase PHP-JWT](https://github.com/firebase/php-jwt)** — JWT token handling library.

---

## License

This project is licensed under the GPL-3.0 License — see [LICENSE-GPL.txt](upload/src/addons/chgold/AIConnect/LICENSE-GPL.txt).

---

## Support

- **Issues**: [GitHub Issues](https://github.com/chgold/xf-ai-connect/issues)
- **Pro Edition**: Contact for licensing and priority support

---

**XenForo AI Connect** | **WebMCP Protocol** | **Free & Pro Editions Available**
