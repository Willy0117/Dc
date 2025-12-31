<?php
// bank_test.php

// BankCode-JP v3 APIキー
$apiKey = 'gsSkVJXNDk3C8m9Fba7s15ZrMQF4Z5';  // ここにキーを入れる
$apiUrl = 'https://apis.bankcode-jp.com/v3/freeword/banks';

$params = [
    'freeword' => 'ちゅうおう',
    'businessTypeCode' => ['00700', '01100'], // 配列対応
    'limit' => 2
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl . '?' . http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response:\n$response\n";

