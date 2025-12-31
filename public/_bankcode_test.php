<?php
/**
 * BankCode-JP v3 freeword API サーバーサイドテスト
 */

$apiKey = 'gsSkVJXNDk3C8m9Fba7s15ZrMQF4Z5'; // ← APIキーをここに入れる
$bankName = 'ちゅうおう';       // ← 検索したい銀行名
$limit = 5;                     // ← 最大取得件数
$businessTypeCodes = ['00700', '01100']; // ← 任意の業種コード

// API URL
$apiUrl = 'https://apis.bankcode-jp.com/v3/freeword/banks';

// 配列パラメータを含めてクエリ文字列を作成
$query = [
    'freeword' => $bankName,
    'limit' => $limit,
];

foreach ($businessTypeCodes as $code) {
    $query['businessTypeCode[]'][] = $code; // key[]形式で複数指定
}

// cURLでGETリクエスト
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl . '?' . http_build_query($query));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "cURL error: " . curl_error($ch) . PHP_EOL;
    exit;
}

curl_close($ch);

// 結果を表示
echo "HTTP Status: $httpCode\n";
echo "Response:\n$response\n";

// JSONを整形表示したい場合
// echo "Formatted:\n";
// print_r(json_decode($response, true));
