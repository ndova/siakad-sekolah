<?php
// Script to test login via cURL

$baseUrl = 'http://127.0.0.1:8080';

// 1. GET login page and extract CSRF token
$ch = curl_init($baseUrl . '/backend/login');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => __DIR__ . '/cookies.txt',
    CURLOPT_COOKIEFILE => __DIR__ . '/cookies.txt',
    CURLOPT_HEADER => false,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "GET /backend/login => HTTP $httpCode" . PHP_EOL;

// Extract CSRF token
preg_match('/name="_token"\s+value="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? null;
echo "CSRF Token: " . ($csrfToken ? 'Found' : 'NOT FOUND') . PHP_EOL;

if (!$csrfToken) {
    echo "Could not find CSRF token. Response excerpt:" . PHP_EOL;
    echo substr($response, 0, 500) . PHP_EOL;
    exit(1);
}

// 2. POST login
$ch = curl_init($baseUrl . '/backend/login');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'email' => 'admin@siakad.test',
        'password' => 'password123',
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-CSRF-TOKEN: ' . $csrfToken,
        'X-Requested-With: XMLHttpRequest',
    ],
    CURLOPT_COOKIEJAR => __DIR__ . '/cookies.txt',
    CURLOPT_COOKIEFILE => __DIR__ . '/cookies.txt',
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "POST /backend/login => HTTP $httpCode" . PHP_EOL;
echo "Response: " . $response . PHP_EOL;
