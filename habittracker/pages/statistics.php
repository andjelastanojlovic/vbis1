<?php
require '../includes/db.php';
require '../includes/functions.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$is_admin = isAdmin();

// Logovi po navikama
if ($is_admin) {
    $stmt = $pdo->query("SELECT h.name, COUNT(hl.id) AS total_logs
                         FROM habit_logs hl
                         JOIN habits h ON hl.habit_id = h.id
                         GROUP BY hl.habit_id");
} else {
    $stmt = $pdo->prepare("SELECT h.name, COUNT(hl.id) AS total_logs
                           FROM habit_logs hl
                           JOIN habits h ON hl.habit_id = h.id
                           WHERE h.user_id = ?
                           GROUP BY hl.habit_id");
    $stmt->execute([$user_id]);
}
$logs_per_habit = $stmt->fetchAll();

// Logovi po danima
if ($is_admin) {
    $stmt = $pdo->query("SELECT log_date, COUNT(*) AS total
                         FROM habit_logs
                         GROUP BY log_date
                         ORDER BY log_date ASC");
} else {
    $stmt = $pdo->prepare("SELECT log_date, COUNT(*) AS total
                           FROM habit_logs
                           JOIN habits ON habits.id = habit_logs.habit_id
                           WHERE habits.user_id = ?
                           GROUP BY log_date
                           ORDER BY log_date ASC");
    $stmt->execute([$user_id]);
}
$logs_per_day = $stmt->fetchAll();

// Procenat logovanja po habit_id (primer za dodatni graf)
if ($is_admin) {
    $stmt = $pdo->query("SELECT h.name, ROUND( (COUNT(hl.id)*100) / (SELECT COUNT(*) FROM habit_logs), 2) AS percent
                         FROM habit_logs hl
                         JOIN habits h ON hl.habit_id = h.id
                         GROUP BY hl.habit_id");
} else {
    $stmt = $pdo->prepare("SELECT h.name, ROUND( (COUNT(hl.id)*100) / (SELECT COUNT(*) FROM habit_logs JOIN habits ON habits.id = habit_logs.habit_id WHERE habits.user_id = ?), 2) AS percent
                           FROM habit_logs hl
                           JOIN habits h ON hl.habit_id = h.id
                           WHERE h.user_id = ?
                           GROUP BY hl.habit_id");
    $stmt->execute([$user_id, $user_id]);
}
$habit_log_percent = $stmt->fetchAll();

// Ukupni broj logova po korisniku
if ($is_admin) {
    $stmt = $pdo->query("SELECT u.username, COUNT(hl.id) AS total_logs
                         FROM habit_logs hl
                         JOIN habits h ON hl.habit_id = h.id
                         JOIN users u ON h.user_id = u.id
                         GROUP BY u.id");
} else {
    $stmt = $pdo->prepare("SELECT u.username, COUNT(hl.id) AS total_logs
                           FROM habit_logs hl
                           JOIN habits h ON hl.habit_id = h.id
                           JOIN users u ON h.user_id = u.id
                           WHERE u.id = ?
                           GROUP BY u.id");
    $stmt->execute([$user_id]);
}
$logs_per_user = $stmt->fetchAll();

// Logovi po mesecima (prvih 6 meseci npr.)
if ($is_admin) {
    $stmt = $pdo->query("SELECT DATE_FORMAT(log_date, '%Y-%m') AS month, COUNT(*) AS total
                         FROM habit_logs
                         WHERE log_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                         GROUP BY month
                         ORDER BY month ASC");
} else {
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(log_date, '%Y-%m') AS month, COUNT(*) AS total
                           FROM habit_logs
                           JOIN habits ON habits.id = habit_logs.habit_id
                           WHERE habits.user_id = ? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                           GROUP BY month
                           ORDER BY month ASC");
    $stmt->execute([$user_id]);
}
$logs_per_month = $stmt->fetchAll();

// Prosečni logovi po danu u poslednjih 7 dana (primer)
if ($is_admin) {
    $stmt = $pdo->query("SELECT log_date, COUNT(*) AS total
                         FROM habit_logs
                         WHERE log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                         GROUP BY log_date
                         ORDER BY log_date ASC");
} else {
    $stmt = $pdo->prepare("SELECT log_date, COUNT(*) AS total
                           FROM habit_logs
                           JOIN habits ON habits.id = habit_logs.habit_id
                           WHERE habits.user_id = ? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                           GROUP BY log_date
                           ORDER BY log_date ASC");
    $stmt->execute([$user_id]);
}
$logs_last_7_days = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8" />
    <title>Statistika navika</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            color: #343a40;
        }
        h4 {
            margin-top: 30px;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 20px;
            background: white;
        }
        .container {
            max-width: 1100px;
            margin-top: 40px;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="dashboard.php" class="btn btn-outline-primary mb-4">&larr; Nazad na Dashboard</a>
    <h4 class="text-center">Statistika navika <?= $is_admin ? '(Svi korisnici)' : '' ?></h4>

    <!-- Sad je pie chart i percent chart u jednom redu -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card">
                <h5>Broj logova po navikama (pie chart)</h5>
                <canvas id="pieChart"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <h5>Procenat logova po navikama (%)</h5>
                <canvas id="percentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bar chart ide ispod u zasebnom redu -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <h5>Logovi po danima (bar chart)</h5>
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card">
                <h5>Ukupno logova po korisniku</h5>
                <canvas id="userChart"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <h5>Logovi po mesecima (poslednjih 6 meseci)</h5>
                <canvas id="monthChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <h5>Logovi u poslednjih 7 dana (line chart)</h5>
                <canvas id="last7daysChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Pie chart - logovi po navikama
const pieCtx = document.getElementById('pieChart').getContext('2d');
new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($logs_per_habit, 'name')) ?>,
        datasets: [{
            label: 'Broj logova',
            data: <?= json_encode(array_column($logs_per_habit, 'total_logs')) ?>,
            backgroundColor: ['#4c6ef5', '#63e6be', '#ff8787', '#ffa94d', '#845ef7', '#51cf66', '#fab005', '#f783ac']
        }]
    }
});

// Percent chart - procenat logova po navikama
const percentCtx = document.getElementById('percentChart').getContext('2d');
new Chart(percentCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($habit_log_percent, 'name')) ?>,
        datasets: [{
            label: 'Procenat logova (%)',
            data: <?= json_encode(array_column($habit_log_percent, 'percent')) ?>,
            backgroundColor: ['#ff922b', '#ff6b6b', '#51cf66', '#339af0', '#845ef7', '#f03e3e']
        }]
    },
    options: {
        responsive: true
    }
});

// Bar chart - logovi po danima
const barCtx = document.getElementById('barChart').getContext('2d');
new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($logs_per_day, 'log_date')) ?>,
        datasets: [{
            label: 'Logovi po danima',
            data: <?= json_encode(array_column($logs_per_day, 'total')) ?>,
            backgroundColor: '#339af0'
        }]
    },
    options: {
        scales: { y: { beginAtZero: true } },
        responsive: true
    }
});

// User chart - ukupno logova po korisniku
const userCtx = document.getElementById('userChart').getContext('2d');
new Chart(userCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($logs_per_user, 'username')) ?>,
        datasets: [{
            label: 'Ukupno logova',
            data: <?= json_encode(array_column($logs_per_user, 'total_logs')) ?>,
            backgroundColor: '#7950f2'
        }]
    },
    options: {
        scales: { y: { beginAtZero: true } },
        responsive: true
    }
});

// Month chart - logovi po mesecima
const monthCtx = document.getElementById('monthChart').getContext('2d');
new Chart(monthCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($logs_per_month, 'month')) ?>,
        datasets: [{
            label: 'Logovi po mesecima',
            data: <?= json_encode(array_column($logs_per_month, 'total')) ?>,
            fill: false,
            borderColor: '#228be6',
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});

// Last 7 days chart - logovi u poslednjih 7 dana
const last7Ctx = document.getElementById('last7daysChart').getContext('2d');
new Chart(last7Ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($logs_last_7_days, 'log_date')) ?>,
        datasets: [{
            label: 'Logovi u poslednjih 7 dana',
            data: <?= json_encode(array_column($logs_last_7_days, 'total')) ?>,
            fill: false,
            borderColor: '#15aabf',
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>
