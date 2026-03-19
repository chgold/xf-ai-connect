# XenForo AI Connect

[![Version](https://img.shields.io/badge/version-1.1.31-blue.svg)](https://github.com/chgold/xf-ai-connect/releases/latest)
[![XenForo](https://img.shields.io/badge/XenForo-2.2.0+-orange.svg)](https://xenforo.com)
[![License](https://img.shields.io/badge/license-GPL--3.0-green.svg)](upload/src/addons/chgold/AIConnect/LICENSE-GPL.txt)

**Connect AI agents to your XenForo forum through the WebMCP Protocol.**

A WebMCP Protocol Bridge that allows AI agents (ChatGPT, Claude, Gemini, Copilot, etc.) to interact with XenForo forums through standardized API endpoints using OAuth 2.0 with PKCE for secure authentication.

**Website**: [ai-connect.gold-t.co.il](https://ai-connect.gold-t.co.il)

---

## Editions

| Feature | Free | Pro |
|---------|------|-----|
| Search threads and posts | ✅ | ✅ |
| Read thread and post content | ✅ | ✅ |
| Get current user info | ✅ | ✅ |
| Translation (AI self-translate or MyMemory) | ✅ | ✅ |
| OAuth 2.0 + PKCE | ✅ | ✅ |
| XenForo permission enforcement | ✅ | ✅ |
| Browse and list forums | — | ✅ |
| Create threads | — | ✅ |
| Reply to threads | — | ✅ |
| Edit posts | — | ✅ |
| Send private conversations | — | ✅ |
| Priority support | — | ✅ |

---

## Features

- **WebMCP Protocol Compliant** — Standard manifest and tool execution endpoints
- **OAuth 2.0 + PKCE** — Secure authentication flow, no passwords stored
- **11 Built-in Tools** — 5 free core + optional translation module + 5 pro (create, reply, edit, message, browse)
- **Pre-configured AI Clients** — ChatGPT, Claude, Gemini, Copilot, Grok, DeepSeek, Perplexity, Meta AI
- **XenForo Permission Enforcement** — AI agents respect the same permission rules as human users
- **Rate Limiting** — Configurable per-user rate limits
- **Modular Architecture** — Extend with custom modules using a clean event-driven API

---

## Requirements

- **XenForo** 2.2.0 or higher
- **PHP** 8.0+ or higher
- **MySQL** 5.5 or higher

> No external PHP dependencies required.

---

## Installation

### Quick Install

1. **Download** the latest release from [Releases](https://github.com/chgold/xf-ai-connect/releases/latest)

2. **Extract** the `upload/` directory into your XenForo root

3. **Install via CLI** (recommended):
   ```bash
   php cmd.php xf:addon-install chgold/AIConnect
   ```
   Or via Admin CP → Add-ons → Install add-on

4. **Verify**:
   ```bash
   curl https://your-forum.com/api/aiconnect-manifest
   ```

### Pro Edition

1. Install the free AI Connect addon first (required dependency)
2. Extract the Pro addon `upload/` directory into your XenForo root
3. Install:
   ```bash
   php cmd.php xf:addon-install chgold/AIConnectPro
   ```

---

## Connecting AI Agents

### The Right Way: MCP (Recommended)

AI Connect implements the WebMCP protocol. Connect AI agents via **MCP** for full, reliable access:

| Client | How to connect |
|--------|----------------|
| Claude Desktop | Add to `claude_desktop_config.json` as MCP server |
| OpenCode / CLI | Configure MCP server endpoint |
| Claude.ai (web) | Remote MCP connection (Settings → Integrations) |
| ChatGPT | Custom Action with manifest URL |

**MCP endpoint:** `https://your-forum.com/api/aiconnect-manifest`

When connected via MCP, the AI agent receives all tool definitions with full schemas and can call any tool with any parameters freely.

### Quick Start: Token Prompt (for web interfaces)

For AI web interfaces that support web browsing (Grok, Claude.ai chat, etc.), AI Connect provides a **token-based system prompt** that lets the agent call tools via GET requests.

Generate a token at: `https://your-forum.com/ai-connect/generate-token`

**Limitations of the web_fetch approach:**
- Some AI platforms restrict which URLs the agent can fetch based on conversation context
- Works reliably for common patterns; edge cases may require MCP
- Not all parameters may work in all web interfaces

For best results, use **MCP** with a proper MCP-compatible client.

### Info Page

AI Connect provides a public info page at `/ai-connect/` with:
- Quick start prompt with connection details
- Manifest and OAuth URLs
- Step-by-step instructions for connecting AI agents

---

## API Endpoints

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/aiconnect-manifest` | GET | No | WebMCP manifest (public discovery) |
| `/api/ai-connect/manifest` | GET | No | WebMCP manifest (alternative URL) |
| `/oauth.php` | GET | No | OAuth 2.0 consent screen |
| `/api/aiconnect-oauth` | POST | No | Token exchange (authorization_code, refresh_token) |
| `/api/aiconnect-oauth` | GET | No | Token exchange via query params (for web agents) |
| `/api/aiconnect-oauth/start` | GET | No | Session-based auth flow — returns `{session_id, auth_url, poll_url}` |
| `/api/aiconnect-oauth/poll` | GET | No | Poll for token after user approval |
| `/ai-connect/generate-token` | POST | Session | Generate token for logged-in user (web UI) |
| `/api/aiconnect-tools` | POST | Bearer | Execute tools |
| `/api/aiconnect-tools` | GET | Bearer or `?token=` | Execute read-only tools via query params |

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

### Core Module — `xenforo.*` (Free)

#### searchThreads
Search forum threads. All parameters are optional — call with `{}` to get latest threads.

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Keyword to search in thread titles (optional) |
| `forum_id` | integer | Filter by forum ID (optional) |
| `since` | string | Time filter: presets (`today`, `yesterday`, `1hour`, `1week`, `1month`), dynamic (`3d`, `6h`, `2w`, `1y`, `2years`), ISO date (`2026-03-15`), or `all` for all history |
| `date_from` | string | Start of date range: Unix timestamp or `YYYY-MM-DD` string |
| `date_to` | string | End of date range: Unix timestamp or `YYYY-MM-DD` string |
| `limit` | integer | Max results (default: 10) |

```json
{ "name": "xenforo.searchThreads", "arguments": { "since": "today", "limit": 20 } }
{ "name": "xenforo.searchThreads", "arguments": { "search": "keyword", "forum_id": 5 } }
{ "name": "xenforo.searchThreads", "arguments": {} }
```

#### getThread
Get a specific thread by ID, including the content of the opening post.

| Parameter | Type | Description |
|-----------|------|-------------|
| `thread_id` | integer | Thread ID (required) |

Returns: `thread_id`, `title`, `forum_id`, `forum_name`, `username`, `post_date`, `reply_count`, `view_count`, `first_post_id`, `first_post_message`, `url`

```json
{ "name": "xenforo.getThread", "arguments": { "thread_id": 123 } }
```

#### searchPosts
Search posts. All parameters are optional — call with `{}` to get latest posts.

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Keyword to search in post content (optional) |
| `thread_id` | integer | Filter by thread ID (optional) |
| `since` | string | Time filter: presets (`today`, `yesterday`, `1hour`, `1week`, `1month`), dynamic (`3d`, `6h`, `2w`, `1y`, `2years`), ISO date (`2026-03-15`), or `all` for all history |
| `date_from` | string | Start of date range: Unix timestamp or `YYYY-MM-DD` string |
| `date_to` | string | End of date range: Unix timestamp or `YYYY-MM-DD` string |
| `limit` | integer | Max results (default: 10) |

```json
{ "name": "xenforo.searchPosts", "arguments": { "since": "today", "limit": 20 } }
{ "name": "xenforo.searchPosts", "arguments": { "limit": 20 } }
{ "name": "xenforo.searchPosts", "arguments": { "search": "keyword", "thread_id": 123 } }
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

### Translation Module — `translation.*` (Configurable)

Translation is configurable via **Admin CP → Options → AI Connect — Settings**:

| Provider | Behavior |
|----------|----------|
| **AI Self-Translate** (default) | AI agent translates using its own abilities. No external API, no limits. |
| **MyMemory API** | Uses [MyMemory](https://mymemory.translated.net/) free API. Limited to ~5,000 chars/day. |
| **Disabled** | No translation capability. |

When set to **MyMemory**, two tools are registered:

#### translate
Translate text between languages. Supports text of **any length** (auto-chunked). Source language is auto-detected if omitted.

> **POST only** — not available via GET.

| Parameter | Type | Description |
|-----------|------|-------------|
| `text` | string | Text to translate (any length) |
| `target_lang` | string | Target language code (e.g. `"he"`, `"en"`, `"es"`) |
| `source_lang` | string | Source language code (optional — auto-detected) |

#### getSupportedLanguages
List all supported language codes.

> **Pro Edition** adds 5 additional tools for content creation and management. See [ai-connect.gold-t.co.il](https://ai-connect.gold-t.co.il) for details.

---

## Admin CP Settings

### AI Connect — Settings

| Option | Values | Default |
|--------|--------|---------|
| Translation provider | AI Self-Translate, MyMemory API, Disabled | AI Self-Translate |

### AI Connect — Navigation

| Option | Description | Default |
|--------|-------------|---------|
| Show in top navigation | Adds "AI Connect" link to main nav bar | On |
| Show next to RSS (footer) | Adds AI Connect icon next to RSS in footer | On |

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
- XenForo permission enforcement — AI agents see only what the user can see
- Access tokens expire after 1 hour
- Refresh tokens expire after 30 days
- Rate limiting prevents abuse
- Token revocation support

---

## Credits

- **[WebMCP Protocol](https://webmcp.org)** — The open protocol specification for AI-to-web integration
- **[XenForo](https://xenforo.com)** — The forum platform this addon extends

---

## License

This project is licensed under the GPL-3.0 License — see [LICENSE-GPL.txt](upload/src/addons/chgold/AIConnect/LICENSE-GPL.txt).

---

## Support

- **Website**: [ai-connect.gold-t.co.il](https://ai-connect.gold-t.co.il)
- **Email**: [support@gold-t.co.il](mailto:support@gold-t.co.il)
- **Issues**: [GitHub Issues](https://github.com/chgold/xf-ai-connect/issues)

---

**Gold Technology** | [ai-connect.gold-t.co.il](https://ai-connect.gold-t.co.il)
