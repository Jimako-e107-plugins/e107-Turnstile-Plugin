<?php

if (!defined('e107_INIT'))
{
	exit;
}

if (e107::pref('turnstile', 'active'))
{
	// no need to load the Cloudflare script for users who never see the widget
	if (USER && e107::pref('turnstile', 'hidefrommembers'))
	{
		return;
	}

	e107::js("footer-inline", '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>');
}
