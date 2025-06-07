<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Niste ulogovani']);
    exit;
}

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$habit_id = $_POST['habit_id'] ?? null;

if (!$habit_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nije prosleđen ID navike']);
    exit;
}

// Provera da li korisnik ima tu naviku (sigurnosna provera)
$stmt = $pdo->prepare("SELECT * FROM habits WHERE id = ? AND user_id = ?");
$stmt->execute([$habit_id, $user_id]);
$habit = $stmt->fetch();

if (!$habit) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nemate pristup toj navici']);
    exit;
}

// Provera da li je već logovano za danas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM habit_logs WHERE habit_id = ? AND log_date = CURDATE()");
$stmt->execute([$habit_id]);
$alreadyLogged = $stmt->fetchColumn();

if ($alreadyLogged) {
    echo json_encode(['success' => false, 'message' => 'Već ste logovali ovu naviku danas']);
    exit;
}

// Ubacivanje novog loga
$stmt = $pdo->prepare("INSERT INTO habit_logs (habit_id, log_date) VALUES (?, CURDATE())");
if ($stmt->execute([$habit_id])) {
    // Uzmi novi broj logova za tu naviku
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM habit_logs WHERE habit_id = ?");
    $stmt->execute([$habit_id]);
    $logCount = (int) $stmt->fetchColumn();

    echo json_encode(['success' => true, 'logCount' => $logCount]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Greška pri dodavanju loga']);
}
