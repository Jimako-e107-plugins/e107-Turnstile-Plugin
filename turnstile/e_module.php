<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * 
 */

$turnstileActive = e107::pref('turnstile', 'active');
$turnstileSiteKey = e107::pref('turnstile', 'sitekey');
$turnstileSecretKey = e107::pref('turnstile', 'secretkey');
 
if($turnstileActive)
{	 	 
	class e107turnstile 
	{
	
		public function __construct()
	    {
		    e107::lan('turnstile', false, true);
	  	}
	    
		static function blank()
		{
			return ;
		}
	 
		static function input()
		{
 			$text = '<div class="cf-turnstile" data-sitekey="' . e107::pref('turnstile', 'sitekey') . '"></div>';
			return $text;
		}
		
		static function hiddeninput()
		{	 
			$frm = e107::getForm();	
            $element = '<input type="hidden" name="cf-turnstile-response" value="your-verification-token">';
            $element .= $frm->hidden("rand_num", 'google' );
			return "";
		}
		
		static function verify($code, $other)
		{

			if ($_SERVER['REQUEST_METHOD'] === 'POST')
			{
				$secretKey = e107::pref('turnstile', 'secretkey'); // Replace with your Secret Key
				$token = $_POST['cf-turnstile-response']; // The response token from Turnstile
				$ip = $_SERVER['REMOTE_ADDR']; // User's IP address

				// Send a POST request to Cloudflare's Turnstile API
				$url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
				$data = [
					'secret' => $secretKey,
					'response' => $token,
					'remoteip' => $ip,
				];

				$options = [
					'http' => [
						'header' => "Content-type: application/x-www-form-urlencoded\r\n",
						'method' => 'POST',
						'content' => http_build_query($data),
					],
				];

				$context = stream_context_create($options);
				$result = file_get_contents($url, false, $context);
				$response = json_decode($result, true);

				if ($response['success'])
				{
					// CAPTCHA validation succeeded
					// Process the form submission
					return true;
				}
				else
				{
					// CAPTCHA validation failed
					// Handle the error (e.g., show an error message)
					$error = 'CAPTCHA validation failed. Please try again.';
					return false;
				}
			}
		}

		// Return an Error message (true) if check fails, otherwise return false. 
		/**
		 * @param $rec_num
		 * @param $checkstr
		 * @return bool|mixed|string
		 */
		function invalidinvalid($code, $other)
		{
	 	
			if(self::verify($rec_num,$checkstr))
			{
				return false;	
			}
			else
			{
				return 'You did not pass robot test';	
			}		
	
		}
		  
	}
	/* remove original captcha */
	e107::getOverride()->replace('secure_image::r_image',     'e107turnstile::blank');
	e107::getOverride()->replace('secure_image::renderInput', 'e107turnstile::hiddeninput');
	e107::getOverride()->replace('secure_image::invalidCode', 'e107turnstile::invalid');
	e107::getOverride()->replace('secure_image::renderLabel', 'e107turnstile::blank');
	e107::getOverride()->replace('secure_image::verify_code', 'e107turnstile::verify'); 
}
