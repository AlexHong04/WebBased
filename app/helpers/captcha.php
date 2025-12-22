<?php
/**
 * @param string 
 * @return bool
 */
function verifyRecaptcha($secretKey)
{
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (empty($recaptchaResponse)) {
        return false;
    }

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret'   => $secretKey,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'timeout' => 10
        ]
    ];

    $context = stream_context_create($options);
    
    $result = @file_get_contents($url, false, $context); 

    if ($result === FALSE) {
        return false; 
    }

    $responseKeys = json_decode($result, true);

    return isset($responseKeys["success"]) && $responseKeys["success"];
}
