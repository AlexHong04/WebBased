<?php
// 开启所有错误显示，这对于调试至关重要
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Twilio Call Debugger</h1>";

// --- 配置区域 (请确保这里是你最新的 Token) ---
// ⚠️ 注意：为了安全，测试完后请删除此文件或撤销 Token
$sid    = "AC691f78ade95d9649a59a8e5c7a431e7a"; 
$token  = "242e93ca44f709be3f93cd4ea0bd012a";
$from   = "+14199241697"; // 你的 Twilio 购买的号码
$to     = "+60176265778"; // 🔴 替换成你要拨打的真实马来西亚手机号 (格式: +601xxxx)
$message = "This is a test call from the debugging script.";

// --- 准备请求 ---
$url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Calls.json";

// 构建 TwiML (告诉 Twilio 接通后说什么)
$twiml = "<Response><Say voice='alice'>$message</Say></Response>";

$data = [
    'From'  => $from,
    'To'    => $to,
    'Twiml' => $twiml
];

echo "<p>正在尝试呼叫: <strong>$to</strong> ...</p>";
echo "<p>使用主叫号码: <strong>$from</strong></p>";

// --- 初始化 cURL ---
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD        => "$sid:$token",
    CURLOPT_TIMEOUT        => 30,
    // 🔴 强制关闭 SSL 验证 (仅用于排查是否是 SSL 证书问题)
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
    // 打印详细的调试信息
    CURLOPT_VERBOSE        => true 
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

// --- 分析结果 ---
echo "<hr>";
if ($curlError) {
    echo "<h3 style='color:red'>❌ cURL 连接错误 (网络/SSL问题)</h3>";
    echo "<pre>$curlError</pre>";
    echo "<p><strong>分析：</strong> 如果这里有字，说明请求根本没发出去。通常是 SSL 证书问题。</p>";
} else {
    echo "<h3>API HTTP 状态码: $httpCode</h3>";
    
    $json = json_decode($response, true);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "<h3 style='color:green'>✅ 请求成功发送给 Twilio!</h3>";
        echo "<p>如果手机还没响，请检查 SID 的通话日志状态 (Status)。</p>";
        echo "<strong>Call SID:</strong> " . ($json['sid'] ?? '未知') . "<br>";
        echo "<strong>Status:</strong> " . ($json['status'] ?? '未知');
    } else {
        echo "<h3 style='color:red'>❌ Twilio 拒绝了请求</h3>";
        echo "<strong>错误代码 (Code):</strong> " . ($json['code'] ?? '无') . "<br>";
        echo "<strong>错误信息 (Message):</strong> " . ($json['message'] ?? '无') . "<br>";
        
        // 自动诊断常见错误
        if (isset($json['code'])) {
            echo "<br><strong>🤖 AI 诊断建议:</strong><br>";
            switch ($json['code']) {
                case 21214:
                case 21217:
                case 21219:
                    echo "👉 <strong>‘To’ 号码未验证。</strong> 因为你是试用账号 (Trial)，你只能给‘Verified Caller IDs’列表里的号码打电话。请去 Twilio 后台验证这个手机号。";
                    break;
                case 21408:
                    echo "👉 <strong>地理位置权限被锁。</strong> 你的账号未开启拨打马来西亚 (+60) 的权限。请去 Twilio Console > Voice > Geographic Permissions 开启 Malaysia。";
                    break;
                case 20003:
                    echo "👉 <strong>账户余额不足或被封锁。</strong> 请检查 Twilio 首页余额。";
                    break;
                case 21212:
                    echo "👉 <strong>手机号格式错误。</strong> 确保号码格式是 +60123456789。";
                    break;
                default:
                    echo "👉 请根据上面的 Message 信息去 Google 搜索 Twilio Error " . $json['code'];
            }
        }
    }
    
    echo "<h4>完整响应内容:</h4>";
    echo "<pre>" . htmlspecialchars(print_r($json, true)) . "</pre>";
}
?>