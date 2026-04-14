# XenForo AI Connect

[![Version](https://img.shields.io/badge/version-1.2.17-blue.svg)](https://github.com/chgold/xf-ai-connect/releases/latest)
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
| Translate content (30+ languages) | ✅ | ✅ |
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
- **12 Built-in Tools** — 7 free (search, read, translate) + 5 pro (create, reply, edit, message, browse)
- **Pre-configured AI Clients** — ChatGPT, Claude, Gemini, Copilot, Grok, DeepSeek, Perplexity, Meta AI
- **XenForo Permission Enforcement** — AI agents respect the same permission rules as human users
- **Rate Limiting** — Configurable per-user rate limits
- **Modular Architecture** — Extend with custom modules using a clean event-driven API

---

## Requirements

- **XenForo** 2.2.0 or higher
- **PHP** 7.2.0 or higher
- **MySQL** 5.5 or higher

> PHP dependencies (`firebase/php-jwt`) are bundled. No manual Composer step required.

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

### Core Module — `xenforo.*` (Free)

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

### Translation Module — `translation.*` (Free)

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

> **Pro Edition** adds 5 additional tools for content creation and management. See [ai-connect.gold-t.co.il](https://ai-connect.gold-t.co.il) for details.

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
- `allowUnauthenticatedRequest` explicitly declared on all API controllers — unauthenticated access to tool endpoints is blocked at the framework level

---

## Credits

- **[WebMCP Protocol](https://webmcp.org)** — The open protocol specification for AI-to-web integration
- **[XenForo](https://xenforo.com)** — The forum platform this addon extends

---

## License

This project is licensed under the GPL-3.0 License — see [LICENSE-GPL.txt](upload/src/addons/chgold/AIConnect/LICENSE-GPL.txt).

---

## Permission System

AI Connect implements a **3-tier permission system** managed entirely through XenForo's native Admin CP → User Groups → Permissions interface.

### Tier 1 — Master Switch

| Permission | Default | Description |
|---|---|---|
| `viewAiConnect` | Allow (Guests + Registered) | Controls visibility of navigation links and the info page |
| `useTools` | Allow (Registered only) | Master switch — deny blocks ALL tools for that group |

### Tier 2 — Package Permissions *(Pro addons)*

Automatically created when a Pro addon registers a package via the `ai_connect_sync_tool_permissions` code event.

| Permission | Format | Description |
|---|---|---|
| Package switch | `use_package_{id}` | Controls access to an entire group of Pro tools |

### Tier 3 — Per-Tool Permissions

One permission per tool, registered automatically on install/upgrade. Shown in Admin CP under **AI Connect — Tools**.

| Permission format | Example | Default |
|---|---|---|
| `tool_{module}_{tool}` | `tool_xenforo_searchThread` | Allow (Registered) |

All per-tool permissions depend on `useTools` — XenForo greys them out automatically in Admin CP when the master switch is set to Never.

### Permission-Aware Manifest

- **Anonymous requests** to `/api/aiconnect-manifest` → returns all tools (for AI client discovery before auth)
- **Authenticated requests** (Bearer token) → returns only tools the token owner is permitted to use

### Permission-Aware Prompt Generator

The token generator at `/ai-connect/generate-token` produces a **personalised prompt** containing only the tools the logged-in user is allowed to call. The MCP tool list and fallback URL examples are both filtered server-side.

New modules automatically participate in this filtering — no changes to the prompt generator are needed when adding tools.

---

## 📋 Changelog

### Version 1.2.17 - 2026-04-14
* **Added:** Generic prompt metadata system — each module declares its own `getToolPromptMeta()` so future tools are automatically reflected in the personalised prompt without touching `InfoPage`
* **Improved:** `buildPersonalizedPrompt()` now collects tool hints and URL examples from modules at runtime instead of a hardcoded lookup table

### Version 1.2.16 - 2026-04-14
* **Security:** Added `allowUnauthenticatedRequest()` returning `false` to `Api/Controller/Tools` — unauthenticated access to the tool execution endpoint is now explicitly blocked at the XenForo framework level
* **Fixed:** Admin CP permission JS file served from wrong path (`src/addons/…/js/`) — moved to `js/chgold/aiconnect/admin-permissions.js` per XenForo static file conventions

### Version 1.2.15 - 2026-04-13
* **Added:** Admin CP visual permission UI — per-tool permission rows are greyed out automatically when `useTools` is set to Never, matching XenForo's native `depend_permission_id` visual behaviour
* **Added:** `admin-permissions.js` injected via `template_modifications.xml` into XenForo's permission edit pages

### Version 1.2.14 - 2026-04-13
* **Added:** Permission-aware manifest — authenticated requests to `/api/aiconnect-manifest` return only tools the token owner is permitted to use; anonymous requests still return the full list for discovery
* **Added:** Permission-aware prompt generator — `/ai-connect/generate-token` returns a personalised prompt filtered to accessible tools only

### Version 1.2.13 - 2026-04-12
* **Added:** Full 3-tier permission system: `viewAiConnect` (page visibility), `useTools` (master switch), per-package permissions (`use_package_{id}`), per-tool permissions (`tool_{module}_{tool}`)
* **Added:** Two Admin CP permission interface groups: **AI Connect** (general) and **AI Connect — Tools** (per-tool, depends on master switch)
* **Added:** `syncToolPermissions()` and `syncPackagePermissions()` in `Setup.php` — permissions are registered automatically on install and upgrade; third-party addons can hook in via `ai_connect_sync_tool_permissions` code event
* **Default:** All per-tool permissions default to Allow for Registered users

### Version 1.2.10 - 2026-04-12
* **Added:** Permission `viewNavLink` — admins can control which user groups see the AI Connect navigation links (top nav and footer icon) via Admin CP → User Groups → Permissions → AI Connect
* **Default:** Allow for all users (guests and registered). Restrict to specific groups by setting "Not Set" in User Groups Permissions.
* **Fixed:** Footer template now uses `link('ai-connect')` and `base_url()` instead of hardcoded paths — works correctly when XenForo is installed in a subdirectory

### Version 1.2.9 - 2026-04-12
* **Docs:** Added Claude Desktop / webmcp-client MCP setup instructions to README

### Version 1.2.8 - 2026-03-31
* **Added:** `until=` parameter documentation in token prompt and manifest date filtering instructions

### Version 1.2.6 - 2026-03-30
* **Added:** Token generator endpoint (`/ai-connect/generate-token`) — logged-in users can generate Bearer tokens in one click
* **Added:** `?token=` query parameter authentication — AI agents that can't set HTTP headers can authenticate via URL parameter
* **Fixed:** Three critical bugs: rate limiter always returning 429, tools_endpoint pointing to wrong controller, ?token= auth in wrong middleware layer

### Version 1.2.2 - 2026-03-29
* **Added:** `until=` date filter for closed time windows (e.g. `since=1week&until=yesterday`)

### Version 1.2.0 - 2026-03-29
* **Improved:** Universal tool schemas — date filters accept natural language ("today", "1week"), dynamic durations ("3d", "6h"), ISO dates; added username/user_id filters; ISO language codes for translation

### Version 1.1.31 - 2026-03-19
* **Added:** Translation Provider Admin Setting (AI Self-Translate / MyMemory API / Disabled)
* **Added:** AI Connect info page (`/ai-connect/`) — public page with manifest URL, OAuth URL, and connection instructions
* **Added:** Footer icon and top navigation entry (configurable in Admin CP)
* **Fixed:** Fuzzy client_id matching — supports gemini_client, claude_ai, chatgpt_client variants automatically
* **Fixed:** Navigation template variable causing PHP warnings

---

## Support

- **Website**: [ai-connect.gold-t.co.il](https://ai-connect.gold-t.co.il)
- **Email**: [support@gold-t.co.il](mailto:support@gold-t.co.il)
- **Issues**: [GitHub Issues](https://github.com/chgold/xf-ai-connect/issues)

---

**Gold Technology** | [ai-connect.gold-t.co.il](https://ai-connect.gold-t.co.il)
