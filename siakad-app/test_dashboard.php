<?php
$baseUrl = 'http://127.0.0.1:8080';

// 1. Login
$ch = curl_init($baseUrl . '/backend/login');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => __DIR__ . '/cookies.txt',
    CURLOPT_COOKIEFILE => __DIR__ . '/cookies.txt',
]);
$response = curl_exec($ch);
preg_match('/name="_token"\s+value="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? '';
curl_close($ch);

$ch = curl_init($baseUrl . '/backend/login');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['email' => 'admin@siakad.test', 'password' => 'password123']),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-CSRF-TOKEN: ' . $csrfToken,
        'X-Requested-With: XMLHttpRequest',
    ],
    CURLOPT_COOKIEJAR => __DIR__ . '/cookies.txt',
    CURLOPT_COOKIEFILE => __DIR__ . '/cookies.txt',
]);
curl_exec($ch);
curl_close($ch);

// 2. Access dashboard with session cookies
$ch = curl_init($baseUrl . '/backend/dashboard');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => __DIR__ . '/cookies.txt',
    CURLOPT_COOKIEFILE => __DIR__ . '/cookies.txt',
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "GET /backend/dashboard => HTTP $httpCode" . PHP_EOL;
echo "Response length: " . strlen($response) . " chars" . PHP_EOL;

if ($httpCode == 200) {
    echo "DASHBOARD OK" . PHP_EOL;
} elseif ($httpCode == 302) {
    // Get redirect URL
    echo "REDIRECTED" . PHP_EOL;
} else {
    echo "ERROR: HTTP $httpCode" . PHP_EOL;
    // Show beginning of response
    echo substr($response, 0, 1000) . PHP_EOL;
}
