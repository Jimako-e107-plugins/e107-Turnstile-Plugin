<?php
$turnstileActive = e107::pref('turnstile', 'active');
$turnstileSiteKey = e107::pref('turnstile', 'sitekey');

if ($turnstileActive)
{

	e107::js("footer-inline", '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>');


	e107::js("footer-inline", $script, 'jquery');
}
