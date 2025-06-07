<?php
require 'includes/db.php';
require 'includes/functions.php';

redirectIfNotLoggedIn();
if (!isAdmin()) {
    die("Samo admin može registrovati korisnike.");
}

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $passwordInput = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'employee';

    if (strlen($username) < 3 || strlen($passwordInput) < 4) {
        $message = "Korisničko ime ili lozinka su prekratki.";
        $messageType = 'danger';
    } else {
        $password = password_hash($passwordInput, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");

        try {
            if ($stmt->execute([$username, $password, $role])) {
                $message = "✅ Korisnik uspešno dodat.";
                $messageType = 'success';
                // Reset vrednosti
                $username = '';
                $role = 'employee';
            } else {
                $message = "Greška pri dodavanju korisnika.";
                $messageType = 'danger';
            }
        } catch (PDOException $e) {
            $message = "Korisničko ime već postoji.";
            $messageType = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Registracija korisnika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f3f5;
        }
        .card {
            max-width: 500px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            background-color: white;
        }
    </style>
</head>
<body>
<div class="card">
    <h4 class="mb-4 text-center">Dodaj korisnika</h4>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3">
            <label class="form-label">Korisničko ime</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Lozinka</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Uloga</label>
            <select name="role" class="form-select">
                <option value="employee" <?= (isset($role) && $role === 'employee') ? 'selected' : '' ?>>Zaposleni</option>
                <option value="admin" <?= (isset($role) && $role === 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-success">➕ Dodaj korisnika</button>
        </div>
    </form>
</div>
</body>
</html>
