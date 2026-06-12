<?php
/*
 * Turnstile Captcha for e107 - replaces the core captcha with
 * Cloudflare Turnstile (https://developers.cloudflare.com/turnstile/).
 */

if (!defined('e107_INIT'))
{
	exit;
}

if (e107::pref('turnstile', 'active'))
{
	class e107turnstile
	{

		/**
		 * True when the captcha should not be shown to (and not verified for)
		 * the current, logged-in user.
		 *
		 * @return bool
		 */
		static function hiddenForCurrentUser()
		{
			return USER && e107::pref('turnstile', 'hidefrommembers');
		}


		static function blank()
		{
			return '';
		}


		static function input()
		{
			if (self::hiddenForCurrentUser())
			{
				return '';
			}

			$sitekey = e107::getParser()->toAttribute(e107::pref('turnstile', 'sitekey'));

			return '<div class="cf-turnstile" data-sitekey="' . $sitekey . '"></div>';
		}


		static function hiddeninput()
		{
			// The Turnstile widget injects its own hidden 'cf-turnstile-response'
			// input into the form; the core text input is suppressed.
			return '';
		}


		/**
		 * Verifies the Turnstile response token against Cloudflare's API.
		 * Fails closed: any missing token, network failure or unexpected
		 * response means the captcha is NOT accepted.
		 *
		 * @param mixed $code  unused - kept for override signature
		 * @param mixed $other unused - kept for override signature
		 * @return bool
		 */
		static function verify($code = null, $other = null)
		{
			if ($_SERVER['REQUEST_METHOD'] !== 'POST')
			{
				return false;
			}

			// widget is hidden for members - there is no token to verify
			if (self::hiddenForCurrentUser())
			{
				return true;
			}

			$secretKey = e107::pref('turnstile', 'secretkey');
			$token = isset($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : '';

			if (empty($secretKey) || empty($token) || !is_string($token))
			{
				return false;
			}

			$url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
			$data = array(
				'secret'   => $secretKey,
				'response' => $token,
				'remoteip' => e107::getIPHandler()->getIP(false),
			);

			$result = self::request($url, $data);

			if ($result === false)
			{
				e107::getLog()->add('Turnstile verification request failed',
					'Could not reach the Cloudflare siteverify endpoint.',
					E_LOG_WARNING, 'TURNSTILE_01');

				return false;
			}

			$response = json_decode($result, true);

			return !empty($response['success']);
		}


		/**
		 * POSTs $data to $url; uses curl when available, falls back to
		 * a stream context. Returns the response body or false.
		 *
		 * @param string $url
		 * @param array  $data
		 * @return string|false
		 */
		static function request($url, $data)
		{
			if (function_exists('curl_init'))
			{
				$ch = curl_init($url);
				curl_setopt_array($ch, array(
					CURLOPT_POST           => true,
					CURLOPT_POSTFIELDS     => http_build_query($data),
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT        => 10,
					CURLOPT_CONNECTTIMEOUT => 5,
				));
				$result = curl_exec($ch);
				curl_close($ch);

				return $result;
			}

			$options = array(
				'http' => array(
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => 'POST',
					'content' => http_build_query($data),
					'timeout' => 10,
				),
			);

			return @file_get_contents($url, false, stream_context_create($options));
		}


		/**
		 * Returns an error message (truthy) if the check fails,
		 * otherwise returns false.
		 *
		 * @param mixed $code
		 * @param mixed $other
		 * @return bool|string
		 */
		static function invalid($code = null, $other = null)
		{
			if (self::verify($code, $other))
			{
				return false;
			}

			return defined('LAN_INVALID_CODE') ? LAN_INVALID_CODE : 'You did not pass the robot test.';
		}

	}

	/* replace original captcha */
	e107::getOverride()->replace('secure_image::r_image',     'e107turnstile::input');
	e107::getOverride()->replace('secure_image::renderInput', 'e107turnstile::hiddeninput');
	e107::getOverride()->replace('secure_image::invalidCode', 'e107turnstile::invalid');
	e107::getOverride()->replace('secure_image::renderLabel', 'e107turnstile::blank');
	e107::getOverride()->replace('secure_image::verify_code', 'e107turnstile::verify');
}
