<?php
// ImportDemoData.php

require_once 'includes/db.php'; // poveži sa tvojim PDO konektorom

// Putanja do JSON fajla (prilagodi ako treba)
$jsonFile = __DIR__ . '/demo_data.json';

if (!file_exists($jsonFile)) {
    die("JSON fajl '$jsonFile' ne postoji.\n");
}

$jsonData = file_get_contents($jsonFile);
$data = json_decode($jsonData, true);

if (!$data) {
    die("Greška pri čitanju ili parsiranju JSON fajla.\n");
}

try {
    // Isključi foreign key checks za čišćenje tabela
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("TRUNCATE TABLE habit_logs");
    $pdo->exec("TRUNCATE TABLE habits");
    $pdo->exec("TRUNCATE TABLE users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    // Ubacivanje korisnika
    $stmtUser = $pdo->prepare("INSERT INTO users (id, username, password, role, created_at) VALUES (?, ?, ?, ?, ?)");
    foreach ($data['users'] as $user) {
        $stmtUser->execute([
            $user['id'],
            $user['username'],
            $user['password'],
            $user['role'],
            $user['created_at'] ?? date('Y-m-d H:i:s')
        ]);
    }

    // Ubacivanje navika
    $stmtHabit = $pdo->prepare("INSERT INTO habits (id, user_id, name, description, created_at) VALUES (?, ?, ?, ?, ?)");
    foreach ($data['habits'] as $habit) {
        $stmtHabit->execute([
            $habit['id'],
            $habit['user_id'],
            $habit['name'],
            $habit['description'],
            $habit['created_at'] ?? date('Y-m-d H:i:s')
        ]);
    }

    // Ubacivanje logova
    $stmtLog = $pdo->prepare("INSERT INTO habit_logs (id, habit_id, log_date, note, created_at) VALUES (?, ?, ?, ?, ?)");
    foreach ($data['habit_logs'] as $log) {
        $stmtLog->execute([
            $log['id'],
            $log['habit_id'],
            $log['log_date'],
            $log['note'],
            $log['created_at'] ?? date('Y-m-d H:i:s')
        ]);
    }

    echo "Import podataka uspešno završen.\n";

} catch (PDOException $e) {
    echo "Greška prilikom ubacivanja podataka: " . $e->getMessage() . "\n";
}
