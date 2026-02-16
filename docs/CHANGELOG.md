# Changelog

## v1.0.1 - 2026-02-16

### Fixed
- Fixed `assertRequiredApiInput()` visibility (must be `public` not `protected`)
- Added `allowUnauthenticatedRequest()` to Manifest controller to allow public access
- Changed API route format from `aiconnect/manifest` to `aiconnect-manifest` (hyphens instead of slashes)
- Updated routes.xml with correct route prefixes

### Technical Details
- Route prefixes: `aiconnect-manifest`, `aiconnect-auth`, `aiconnect-tools`
- Manifest endpoint is now publicly accessible without API key
- All controllers properly extend XenForo AbstractController with correct method signatures

## v1.0.0 - Initial Release
- WebMCP Protocol Bridge for XenForo 2.2.8+
- 5 core tools: searchThreads, getThread, searchPosts, getPost, getCurrentUser
- JWT authentication system
- Rate limiting
