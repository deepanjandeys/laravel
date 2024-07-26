<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FunctionsController extends Controller
{
    function get_tiny_url($url)  {  
    $ch = curl_init();  
    $timeout = 5;  
    curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url);  
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);  
    $data = curl_exec($ch);  
    curl_close($ch);  
    return $data;  
}
function maskPhoneNumber($number){
   /* if(strlen($number)>0)
    $number=substr($number,1);*/
    $mask_number =  substr($number,0,2).str_repeat("*", strlen($number)-6) . substr($number, -4);
    
    return $mask_number;
}

function maskBankAccountNumber($ACnumber){
   /* if(strlen($number)>0)
    $number=substr($number,1);*/
    $mask_ACnumber =  substr($ACnumber,0,2).str_repeat("*", strlen($ACnumber)-6) . substr($ACnumber, -4);
    
    return $mask_ACnumber;
}
/*
function maskEmail($email){
 
   return $maskEmail=preg_replace('/(?<=.).(?=.*@)/', '*', $email);
}
*/
function mask($str, $first, $last) 
{
    $len = strlen($str);
    $toShow = $first + $last;
    return substr($str, 0, $len <= $toShow ? 0 : $first).str_repeat("*", $len - ($len <= $toShow ? 0 : $toShow)).substr($str, $len - $last, $len <= $toShow ? 0 : $last);
}

function maskEmail($email) {
    $mail_parts = explode("@", $email);
    $domain_parts = explode('.', $mail_parts[1]);

    $mail_parts[0] = $this->mask($mail_parts[0], 2, 1); // show first 2 letters and last 1 letter
    $domain_parts[0] = $this->mask($domain_parts[0], 2, 1); // same here
    $mail_parts[1] = implode('.', $domain_parts);

    return implode("@", $mail_parts);
}
function encrypt($string)
{
$ciphering_value = "AES-128-CTR";  
$encryption_key = "JavaTpoint"; 
$options = 0;
  
// Non-NULL Initialization Vector for encryption
$encryption_iv = '1234567891011121';
$encryption_value   = openssl_encrypt($string, $ciphering_value, $encryption_key,$options, $encryption_iv);

return $encryption_value;                   

}

function decrypt($encryption_value)
{
$ciphering_value = "AES-128-CTR";   
$decryption_key = "JavaTpoint";  
$decryption_iv = '1234567891011121';
$options = 0;
$decryption_value = openssl_decrypt($encryption_value, $ciphering_value, $decryption_key,$options, $decryption_iv);   
return $decryption_value;
}
}
