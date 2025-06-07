<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Niste ulogovani']);
    exit;
}

require_once '../includes/db.php';

$userFilter = $_GET['user'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$sql = "SELECT l.*, u.username, h.name AS habit_name
        FROM habit_logs l
        JOIN habits h ON l.habit_id = h.id
        JOIN users u ON h.user_id = u.id
        WHERE 1=1";

$params = [];

if (!empty($userFilter)) {
    $sql .= " AND u.username LIKE ?";
    $params[] = "%$userFilter%";
}
if (!empty($dateFrom)) {
    $sql .= " AND DATE(l.log_date) >= ?";
    $params[] = $dateFrom;
}
if (!empty($dateTo)) {
    $sql .= " AND DATE(l.log_date) <= ?";
    $params[] = $dateTo;
}

// Ubacujemo limit i offset direktno (da izbegnemo PDO problem sa stringovima u LIMIT)
$sql .= " ORDER BY l.log_date DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
