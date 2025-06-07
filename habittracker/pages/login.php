<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        // Ovde samo dashboard.php jer smo već u folderu pages/
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "❌ Pogrešno korisničko ime ili lozinka.";
    }
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Prijava</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f3f5;
        }
        .login-card {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
<div class="login-card">
    <h4 class="mb-4 text-center">Prijava na sistem</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <div class="mb-3">
            <label class="form-label">Korisničko ime</label>
            <input type="text" name="username" class="form-control" required autofocus>
        </div>

        <div class="mb-3">
            <label class="form-label">Lozinka</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="d-grid">
            <button class="btn btn-primary">🔐 Prijavi se</button>
        </div>
    </form>

    <div class="mt-3 text-center">
        <small>Nemate nalog? <a href="register.php">Zatražite od admina da vas doda</a></small><br>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="register.php" class="btn btn-link mt-2">➕ Registruj novog korisnika</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
