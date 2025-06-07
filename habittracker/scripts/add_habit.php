<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();

header('Content-Type: application/json');

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');

if (strlen($name) < 2 || strlen($name) > 100) {
    echo json_encode(['success' => false, 'message' => 'Naziv navike mora imati između 2 i 100 karaktera.']);
    exit;
}
if (strlen($description) > 500) {
    echo json_encode(['success' => false, 'message' => 'Opis ne sme biti duži od 500 karaktera.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO habits (user_id, name, description, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, $name, $description]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Greška pri čuvanju navike: ' . $e->getMessage()]);
}
