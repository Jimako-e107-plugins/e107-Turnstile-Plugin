# e107 Turnstile Plugin

Cloudflare Turnstile captcha for e107 — a drop-in replacement for the core captcha, making your site more user-friendly and secure.

# Description

This plugin replaces the built-in e107 captcha (the distorted-characters image) with [Cloudflare Turnstile](https://developers.cloudflare.com/turnstile/), a free, privacy-friendly captcha alternative. It hooks into the core `secure_image` class via e107 overrides, so it works everywhere the core captcha appears — signup, login, contact forms, password reset — with no template changes.

# Requirements

* PHP 7.4 – 8.4
* e107 v2.4
* A Cloudflare account with a Turnstile widget (free) — you need its **Site Key** and **Secret Key**
* curl extension or `allow_url_fopen` enabled (curl is preferred and used automatically when available)

# Installation

1. Upload the plugin directory and install it via the e107 admin plugin manager.
2. Create a Turnstile widget at [Cloudflare Turnstile](https://dash.cloudflare.com/?to=/:account/turnstile) for your domain.
3. Enter the **Site Key** and **Secret Key** under *Admin → Turnstile Captcha → Preferences* and set the plugin to *Active*.

# Preferences

| Preference | Description |
|---|---|
| Active | Master switch. When off, the core e107 captcha is used. |
| Hide from Members | Logged-in members get no captcha widget; verification passes automatically for them. |
| Site Key | Public key rendered into the widget. |
| Secret Key | Private key used for server-side verification. Never displayed on the site. |

# How it works

* The widget is rendered in place of the core captcha image; Turnstile's script injects a `cf-turnstile-response` token into the form.
* On submit, the token is verified server-side against Cloudflare's `siteverify` endpoint (10s timeout).
* Verification **fails closed**: a missing token, network failure or unexpected response rejects the submission. Failed verification requests are recorded in the system log (`TURNSTILE_01`).

# Troubleshooting

* If forms are rejected with "Incorrect code entered", check *Admin → Tools → System Logs* for `TURNSTILE_01` entries (network/endpoint problems) and verify the Secret Key.
* If the widget doesn't appear, verify the Site Key and that the page can load `challenges.cloudflare.com`.
