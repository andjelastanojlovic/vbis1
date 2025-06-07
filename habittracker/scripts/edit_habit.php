<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Niste ulogovani']);
    exit;
}

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$habit_id = $_POST['habit_id'] ?? null;
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');

if (!$habit_id || $name === '') {
    echo json_encode(['success' => false, 'message' => 'Nevalidni podaci']);
    exit;
}

// Proveri vlasništvo nad navikom
$stmt = $pdo->prepare("SELECT * FROM habits WHERE id = ? AND user_id = ?");
$stmt->execute([$habit_id, $user_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Nemate pristup ovoj navici']);
    exit;
}

// Ažuriraj naviku
$stmt = $pdo->prepare("UPDATE habits SET name = ?, description = ? WHERE id = ? AND user_id = ?");
$success = $stmt->execute([$name, $description, $habit_id, $user_id]);

echo json_encode(['success' => $success]);
exit;
