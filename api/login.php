<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Get the posted data.
$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (isset($users[$username]) && password_verify($password, $users[$username]['password_hash'])) {
    // Password is correct, so start a new session
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $users[$username]['role'];

    echo json_encode(['success' => true, 'role' => $_SESSION['role']]);
} else {
    // Incorrect username or password
    echo json_encode(['success' => false, 'message' => 'Hatalı kullanıcı adı veya şifre!']);
}
?>
