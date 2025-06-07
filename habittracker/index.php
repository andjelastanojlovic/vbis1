<?php
require 'includes/functions.php';

if (isLoggedIn()) {
    header("Location: pages/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Habit Tracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tvoj custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4c6ef5, #15aabf);
            color: white;
            min-height: 100vh;
            margin: 0;
        }
        .welcome-box {
            background-color: rgba(0, 0, 0, 0.25);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 90%;
        }
        .btn-light {
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        .btn-light:hover {
            background-color: #ffe066;
            color: #212529;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="text-center welcome-box">
        <h1 class="display-5 fw-bold">Dobrodošli u <span style="color: #ffe066;">Habit Tracker</span></h1>
        <p class="lead mb-4">Pratite i poboljšajte navike vašeg tima na jednostavan način.</p>
        <a href="pages/login.php" class="btn btn-light btn-lg px-4">🔐 Prijavi se</a>
    </div>

    <!-- Bootstrap JS Bundle CDN (opciono ako ti treba za neke interakcije) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
