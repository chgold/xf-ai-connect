# XenForo AI Connect - Installation Guide

## Installation Steps

1. **Download the addon**
   - Download `xenforo-ai-connect-v1.0.1-fixed.zip`

2. **Upload to XenForo**
   - Extract the zip file
   - Upload the `upload/` directory contents to your XenForo root directory
   - The files should be placed in: `/path/to/xenforo/src/addons/chgold/AIConnect/`

3. **Install via Admin Panel**
   - Go to: Admin CP > Add-ons > Install add-on
   - Click "Install from archive" and upload the zip
   - OR click "Install add-on" if you already uploaded the files manually

4. **Verify Installation**
   - The addon should create 4 database tables:
     - `xf_ai_connect_api_keys`
     - `xf_ai_connect_blocked_users`
     - `xf_ai_connect_rate_limits`
     - `xf_ai_connect_settings`

5. **Test the Manifest Endpoint**
   ```bash
   curl http://your-forum.com/api/aiconnect-manifest
   ```
   
   You should receive a JSON response with the WebMCP manifest.

## API Endpoints

- **Manifest (Public)**: `GET /api/aiconnect-manifest`
- **Authentication**: `POST /api/aiconnect-auth`
- **Tools Execution**: `POST /api/aiconnect-tools`

## Important Notes

- Routes use hyphens (`aiconnect-manifest`) not slashes
- Manifest endpoint is publicly accessible (no API key required)
- Auth and Tools endpoints require authentication

## Troubleshooting

### Routes not found (404 error)
1. Go to Admin CP > Add-ons
2. Disable and re-enable the addon
3. Run: `php cmd.php xf:rebuild-caches`

### File hash mismatch errors
- This means the files were modified after installation
- Reinstall the addon to fix

### "Class 'Firebase\JWT\JWT' not found" error
The `vendor/` directory is missing from the addon. This happens if the addon was installed manually without the bundled vendor files. Re-install using the release zip from GitHub, or run `composer install --no-dev` inside the addon directory (`src/addons/chgold/AIConnect/`).

## Requirements

- XenForo 2.2.0+
- PHP 7.2.0+
- MySQL 5.5+

PHP dependencies (`firebase/php-jwt`) are bundled inside the addon and loaded automatically by XenForo. No Composer required.

## Support

For issues, please check the GitHub repository or forum thread.
