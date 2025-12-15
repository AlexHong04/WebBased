<?php


function base64UrlEncode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data)
{
    return base64_decode(strtr($data, '-_', '+/'));
}

function createJWT($payload, $secret)
{
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $payload = json_encode($payload);

    $base64Header = base64UrlEncode($header);
    $base64Payload = base64UrlEncode($payload);

    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
    $base64Signature = base64UrlEncode($signature);

    return "$base64Header.$base64Payload.$base64Signature";
}

function verifyJWT($jwt, $secret)
{
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return false;

    list($header, $payload, $signature) = $parts;

    $expectedSignature = base64UrlEncode(
        hash_hmac('sha256', "$header.$payload", $secret, true)
    );

    if ($signature !== $expectedSignature) return false;

    return json_decode(base64UrlDecode($payload), true);
}


$SECRET = "Lovine";

function authenticate() {
    global $SECRET;

    // 1. session token exists
    if (!empty($_SESSION['token'])) {
        $data = verifyJWT($_SESSION['token'], $SECRET);
        if ($data) {
            return $data;
        }
    }

    // 2. token in cookie
    if (!empty($_COOKIE['remember_token'])) {
        $data = verifyJWT($_COOKIE['remember_token'], $SECRET);
        if ($data) {
            // write back to session
            $_SESSION['token'] = $_COOKIE['remember_token'];
            $_SESSION['customerId'] = $data['customerId'];
            return $data;
        }
    }

    // 3. no token → force login
    header("Location: /app/views/security/signIn.php");
    exit;
}
