<?php

class TwilioSMS
{
    private $accountSid;
    private $authToken;
    private $from;

    public function __construct($sid, $token, $from)
    {
        $this->accountSid = $sid;
        $this->authToken  = $token;
        $this->from       = $from;
    }

    public function sendSMS($to, $message)
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";

        $data = http_build_query([
            'From' => $this->from,
            'To'   => $to,
            'Body' => $message
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => $this->accountSid . ':' . $this->authToken,
            CURLOPT_TIMEOUT        => 10
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode >= 200 && $httpCode < 300);
    }

    public function call($to, $message)
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Calls.json";

        // 这里的 XML 结构是正确的
        $twiml = "<Response><Say voice='alice'>{$message}</Say></Response>";

        $data = http_build_query([
            'From'  => $this->from,
            'To'    => $to,
            'Twiml' => $twiml
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => $this->accountSid . ':' . $this->authToken,
            // --- 新增/修改以下设置 ---
            CURLOPT_SSL_VERIFYPEER => false, // 修复本地开发环境无法验证 HTTPS 的问题
            CURLOPT_SSL_VERIFYHOST => 0,     // 修复本地开发环境无法验证 HTTPS 的问题
            // -----------------------
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // --- 新增错误捕获 ---
        if ($response === false) {
            $curlError = curl_error($ch);
            error_log("Twilio cURL Error: " . $curlError); // 查看 PHP 错误日志
        } else {
            error_log("Twilio API Response: " . $response); // 查看 Twilio 返回的具体报错信息
        }
        // ------------------

        curl_close($ch);

        return ($code >= 200 && $code < 300);
    }
}
