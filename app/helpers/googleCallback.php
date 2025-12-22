<?php
function handleGoogleCallback($client_id, $client_secret, $redirect_uri)
{
    // 1. Check authorization code
    if (!isset($_GET['code'])) {
        return ["error" => "Google login failed"];
    }

    $code = $_GET['code'];

    // 2. Exchange code for access token
    $token_url = "https://oauth2.googleapis.com/token";

    $data = [
        "code" => $code,
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "redirect_uri" => $redirect_uri,
        "grant_type" => "authorization_code"
    ];

    $options = [
        "http" => [
            "method" => "POST",
            "header" => "Content-Type: application/x-www-form-urlencoded",
            "content" => http_build_query($data)
        ]
    ];

    $response = file_get_contents($token_url, false, stream_context_create($options));
    if (!$response) {
        return ["error" => "Failed to get access token"];
    }

    $token = json_decode($response, true);
    if (!isset($token['access_token'])) {
        return ["error" => "Access token not found"];
    }

    $access_token = $token['access_token'];

    // 3. Get user info
    $user_info = file_get_contents(
        "https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $access_token
    );

    if (!$user_info) {
        return ["error" => "Failed to get user info"];
    }

    $user = json_decode($user_info, true);

    // 4. Store in session
    $_SESSION['email'] = $user['email'];
    $_SESSION['name']  = $user['name'];

    return $user; // return user info array
}
