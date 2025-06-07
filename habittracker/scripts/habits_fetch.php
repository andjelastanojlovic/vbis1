<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Niste ulogovani']);
    exit;
}

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$search = $_GET['search'] ?? '';
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

$sql = "SELECT habits.* FROM habits";
$params = [];

if (!$is_admin) {
    $sql .= " WHERE user_id = ?";
    $params[] = $user_id;

    if ($search !== '') {
        $sql .= " AND name LIKE ?";
        $params[] = "%$search%";
    }
} else {
    if ($search !== '') {
        $sql .= " WHERE name LIKE ?";
        $params[] = "%$search%";
    }
}

$sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$habits = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($habits, JSON_UNESCAPED_UNICODE);
exit;
