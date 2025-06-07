<?php
require_once '../includes/db.php';
session_start();

$query = $_GET['query'] ?? '';
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$sql = "SELECT * FROM habits WHERE user_id = ? AND name LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, "%$query%", $limit, $offset]);

while ($habit = $stmt->fetch()) {
    echo "<div class='habit-item'>";
    echo "<strong>" . htmlspecialchars($habit['name']) . "</strong><br>";
    echo "<span>" . htmlspecialchars($habit['description']) . "</span>";
    echo "</div><hr>";
}
?>
