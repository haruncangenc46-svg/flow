<?php
session_start();

// Unset all of the session variables
$_SESSION = [];

// Destroy the session.
session_destroy();

header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
