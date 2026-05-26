<?php
$db = new PDO('sqlite:database/database.sqlite');

echo "=== STAFF ===" . PHP_EOL;
$stmt = $db->query("SELECT nama_lengkap, jabatan, is_active FROM staff LIMIT 10");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['nama_lengkap'] . ' | ' . $row['jabatan'] . ' | active=' . $row['is_active'] . PHP_EOL;
}

echo PHP_EOL . "=== STAFF ATTENDANCE ===" . PHP_EOL;
$stmt = $db->query("SELECT COUNT(*) as cnt FROM staff_attendances");
echo "Total records: " . $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] . PHP_EOL;

$stmt = $db->query("SELECT tanggal, status, COUNT(*) as cnt FROM staff_attendances GROUP BY status LIMIT 10");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['status'] . ': ' . $row['cnt'] . PHP_EOL;
}
