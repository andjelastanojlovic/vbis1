<?php
require_once '../includes/db.php';

$sql = "SELECT u.username, COUNT(l.id) AS log_count
        FROM logs l
        JOIN users u ON u.id = l.user_id
        GROUP BY l.user_id
        ORDER BY log_count DESC";

$stmt = $pdo->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [
    "labels" => array_column($data, "username"),
    "counts" => array_column($data, "log_count")
];

header('Content-Type: application/json');
echo json_encode($result);
?>
