# AI Connect Pro for XenForo

**Write tools extension for [AI Connect (free)](https://github.com/chgold/xf-ai-connect)** — lets AI agents create threads, post replies, edit content, and send private messages on your XenForo forum.

**Price: $29 one-time** · [Purchase on XenForo Resources](https://xenforo.com/community/resources/)

---

## What's Included

| Tool | Description |
|---|---|
| `xenforo_pro.getForumList` | List all accessible forums/nodes |
| `xenforo_pro.createThread` | Create a new thread in any forum |
| `xenforo_pro.replyToThread` | Post a reply to an existing thread |
| `xenforo_pro.editPost` | Edit an existing post |
| `xenforo_pro.sendConversation` | Send a private message (conversation) to a user |

## Requirements

- **XenForo** 2.2.0+
- **PHP** 7.2+
- **AI Connect (free)** v1.1.9+ — [GitHub](https://github.com/chgold/xf-ai-connect) · [XenForo Resources](https://xenforo.com/community/resources/ai-connect-for-xenforo-webmcp-bridge.10336/)

## Installation

1. Install and configure the free **AI Connect** addon first
2. Upload the `upload/` directory contents to your XenForo root
3. Install **AI Connect Pro** in Admin CP → Add-ons
4. Grant the `use_package_pro` permission to the desired user groups

## Use Cases

- **AI support bot** — automatically answers and creates threads in support forums
- **Content automation** — AI drafts and posts announcements, news, or daily discussions
- **Community engagement** — AI responds to unanswered threads to keep engagement high
- **Translation assistant** — AI posts translated versions of content in multiple languages

## Security & Permissions

- All write operations require the `write` OAuth scope
- XenForo's full permission system is enforced — AI acts as the authenticated user
- If a user can't post, their AI agent can't post either
- Pro tools require the `use_package_pro` permission group
- Rate limiting (50 req/min, 1000 req/hour) applies to all operations

## Compatible AI Agents

Claude · ChatGPT · Gemini · Grok · Copilot · Perplexity · Meta AI · DeepSeek

## Architecture

Pro registers itself via the `ai_connect_modules_init` hook fired by the base addon — identical to how WordPress Pro plugins extend free plugins. No core file modifications.

## Pricing

- **$29 one-time** — includes all current Pro tools and future patch updates
- Additional tool bundles (e.g., Resource Manager, Media Gallery) will be sold separately

## License

Commercial. Single-site license per purchase. Not for redistribution.
For support: [ai-connect.gold-t.co.il](https://ai-connect.gold-t.co.il/)
