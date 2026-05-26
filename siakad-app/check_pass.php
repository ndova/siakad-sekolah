<?php
$db = new PDO('sqlite:database/database.sqlite');

// Get an admin user
$stmt = $db->prepare("SELECT email, password FROM users WHERE email = :email");
$stmt->execute(['email' => 'admin@siakad.test']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "User found: " . $user['email'] . PHP_EOL;
    echo "Password hash: " . substr($user['password'], 0, 30) . "..." . PHP_EOL;
    
    // Test password verification
    $verified = password_verify('password123', $user['password']);
    echo "password_verify('password123'): " . ($verified ? 'MATCH' : 'NO MATCH') . PHP_EOL;
    
    // Check if already hashed (check for double hash)
    $reHashed = password_hash('password123', PASSWORD_BCRYPT);
    echo "Fresh hash of 'password123': " . substr($reHashed, 0, 30) . "..." . PHP_EOL;
    
    $isHashed = password_get_info($user['password'])['algo'] !== null;
    echo "Password algo: " . print_r(password_get_info($user['password']), true) . PHP_EOL;
} else {
    echo "User not found!" . PHP_EOL;
}
