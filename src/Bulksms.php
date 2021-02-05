<?php

namespace Abedin99\Bulksms;

class Bulksms
{
    
    public static function send($numbers, $text)
    {
        $url = config('bulksms.url');

        $data= array(
            'username'=> config('bulksms.username'),
            'password'=>config('bulksms.password'),
            'number'=>"$numbers",
            'message'=>"$text"
        );
        
        $ch = curl_init(); // Initialize cURL
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $smsresult = curl_exec($ch);
        $p = explode("|",$smsresult);
        $sendstatus = $p[0];

        $msg = [
            1000 => "Invalid user or Password",
            1002 => "Empty Number",
            1003 => "Invalid message or empty message",
            1004 => "Invalid number",
            1005 => "All Number is Invalid ",
            1006 => "insufficient Balance ",
            1009 => "Inactive Account",
            1010 => "Max number limit exceeded",
            1101 => "Success",
        ];

        return $msg[$sendstatus];
    }
    
}