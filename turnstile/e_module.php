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
        $secretKey = e107::pref('turnstile', 'secretkey');
        $token = $_POST['cf-turnstile-response'];
        $ip = $_SERVER['REMOTE_ADDR'];

        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

        $data = [
            'secret'   => $secretKey,
            'response' => $token,
            'remoteip' => $ip,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result, true);

        if (!empty($response['success']) && $response['success'] === true)
        {
            return true;
        }
        else
        {
            // Puedes registrar $response['error-codes'] para depuración
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
		static function invalid($rec_num, $checkstr)
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
	//if ($user_func = e107::getOverride()->check($this,'r_image'))
	e107::getOverride()->replace('secure_image::r_image',     'e107turnstile::input');
	e107::getOverride()->replace('secure_image::renderInput', 'e107turnstile::hiddeninput');
	e107::getOverride()->replace('secure_image::invalidCode', 'e107turnstile::invalid');
	e107::getOverride()->replace('secure_image::renderLabel', 'e107turnstile::blank');
	e107::getOverride()->replace('secure_image::verify_code', 'e107turnstile::verify');
	 
	
}
