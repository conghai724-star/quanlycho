<?php
// Test: simulate loading the market_edit page (GET), extract CSRF token, then submit (POST)
require_once __DIR__ . '/application/security.class.php';

// Simulate GET request - generate a token
session_start();
$token = security::getToken();
echo "=== STEP 1: GET page ===\n";
echo "Generated token: $token\n";
echo "Session token: " . $_SESSION['csrf_token'] . "\n\n";

// Now simulate POST request with that token
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['csrf_token'] = $token;

echo "=== STEP 2: POST submit ===\n";
echo "POST csrf_token: " . $_POST['csrf_token'] . "\n";
echo "Session csrf_token: " . $_SESSION['csrf_token'] . "\n";

$valid = security::validateToken();
echo "Validation result: " . ($valid ? 'PASS' : 'FAIL') . "\n";
echo "hash_equals: " . (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']) ? 'TRUE' : 'FALSE') . "\n";
