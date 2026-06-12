# Changelog

## 2.0 (2026-06-12)

Update for PHP 7.4 – 8.4 and e107 2.4.

### Fixed
- `verify()` raised an "undefined index" warning when the `cf-turnstile-response` token was missing; a missing token now fails validation cleanly
- A network failure or non-JSON response from Cloudflare no longer raises warnings — verification fails closed and the problem is written to the system log (`TURNSTILE_01`)
- Undefined variable `$script` in `e_header.php` removed
- `e107_INIT` guards added to `e_module.php` and `e_header.php`

### Added
- The *Hide from Members* preference is now actually implemented: logged-in members don't get the widget, the Cloudflare script isn't loaded for them, and verification auto-passes for them
- Verification request uses curl when available (stream context with a timeout as fallback) — works on hostings with `allow_url_fopen` disabled, and a slow/unreachable Cloudflare can no longer hang the page (10s timeout)
- The user's IP is taken from e107's IP handler (proxy-aware) instead of raw `REMOTE_ADDR`

### Security
- Site key is escaped with `toAttribute()` when rendered into the widget markup
- Verification fails closed in all error cases (missing secret key, missing token, network failure, unexpected response)

### Changed
- Invalid-captcha message uses the core `LAN_INVALID_CODE` language string
- Leftover dead code removed (unused hidden inputs copied from documentation examples)
- `plugin.xml`: version 2.0, compatibility 2.4
