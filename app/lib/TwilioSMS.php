<?php

class twilioSMS
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
            CURLOPT_SSL_VERIFYPEER => false, 
            CURLOPT_SSL_VERIFYHOST => 0,     
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch); 
        curl_close($ch);
        if ($code >= 200 && $code < 300) {
            return ['success' => true, 'message' => 'Call Initiated', 'debug' => $response];
        } else {
            return [
                'success' => false,
                'message' => "HTTP Code: $code. Error: " . ($curlError ?: $response)
            ];
        }


        return ($code >= 200 && $code < 300);
    }
}
