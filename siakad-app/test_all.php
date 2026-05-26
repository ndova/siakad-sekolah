<?php
$baseUrl = 'http://127.0.0.1:8080';

// Login
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
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json', 'X-CSRF-TOKEN: ' . $csrfToken, 'X-Requested-With: XMLHttpRequest'],
    CURLOPT_COOKIEJAR => __DIR__ . '/cookies.txt',
    CURLOPT_COOKIEFILE => __DIR__ . '/cookies.txt',
]);
curl_exec($ch);
curl_close($ch);

// Test all staff pages
$pages = [
    '/backend/staff' => 'Staff Index',
    '/backend/staff/attendance' => 'Attendance Grid',
    '/backend/staff/attendance/recap' => 'Attendance Recap',
];

foreach ($pages as $path => $label) {
    $ch = curl_init($baseUrl . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => __DIR__ . '/cookies.txt',
        CURLOPT_COOKIEFILE => __DIR__ . '/cookies.txt',
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $status = $code === 200 ? 'OK' : ($code === 302 ? 'REDIR' : 'ERROR');
    echo "$status ($code) - $label (" . strlen($resp) . " chars)\n";
}
