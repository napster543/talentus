<?php 
//https://anexsoft.com/implementacion-de-json-web-token-con-php

namespace App\models;

use Firebase\JWT\JWT;


class Auth{
	//$time+(86400 * 30); // Access_Token Time, aquí durante 30 días
    private static $secret_key = 'Sdw1s9x8@';
    private static $encrypt = ['HS256'];
    private static $aud = null;

    public $Mtoken;
    public $mensaje;
    public $success = false;

    public static function SignIn($data)
    {
        $time = time();

        $token = array(
            'exp' => $time + (99910 * 2),
            'aud' => self::Aud(),
            'data' => $data
        );

        $MyToken = JWT::encode($token, self::$secret_key);

        return self::TokenResult($MyToken, "Token Generado con exito", true);
        
    }

    public static function Check($token)
    {
        try {
            if(empty($token))
            {
                throw new Exception("Invalid token supplied.");
            }

            $decode = JWT::decode(
                $token,
                self::$secret_key,
                self::$encrypt
            );
            
            if($decode->aud !== self::Aud())
            {
                throw new Exception("Invalid user logged in.");
            }
            return $decode;
        } catch (\Firebase\JWT\ExpiredException $e){
            return $e->getMessage();
           
        }


    }

    public static function GetData($token)
    {

        
        try {

            $mytoken = JWT::decode(
                $token,
                self::$secret_key,
                self::$encrypt
            )->data;    
            
            return self::TokenResult($mytoken, "acceso correcto", true);
        
        }catch(\Firebase\JWT\ExpiredException $e){
                              
            return self::TokenResult("", $e->getMessage(), false);
            
       }
    
    }
    private static function TokenResult($token, $mensaje, $success){
        return array(
                    "token"=>$token, 
                    "mensaje" => $mensaje, 
                    "success" => boolval($success)
                );
    }
    

    private static function Aud()
    {
        $aud = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }

        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();

        return sha1($aud);
    }
	
}
 
?>