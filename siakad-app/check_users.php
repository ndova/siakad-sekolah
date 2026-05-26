<?php
$db = new PDO('sqlite:database/database.sqlite');
$stmt = $db->query("SELECT email, role, is_active FROM users LIMIT 10");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['email'] . ' | ' . $row['role'] . ' | active=' . $row['is_active'] . PHP_EOL;
}
