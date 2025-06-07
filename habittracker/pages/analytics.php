<?php
require 'includes/db.php';
require 'includes/functions.php';

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Broj navika po korisniku (za pie chart)
$stmt = $pdo->query("SELECT u.username, COUNT(h.id) AS habit_count
                     FROM users u
                     LEFT JOIN habits h ON u.id = h.user_id
                     GROUP BY u.id");
$habit_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Broj logova po navici (za bar chart)
$stmt2 = $pdo->prepare("SELECT h.name, COUNT(l.id) AS logs FROM habits h
                        LEFT JOIN habit_logs l ON h.id = l.habit_id
                        WHERE h.user_id = ? GROUP BY h.id");
$stmt2->execute([$user_id]);
$habit_logs = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Analitika</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container mt-4">
    <a href="dashboard.php" class="btn btn-secondary mb-3">Nazad na Dashboard</a>
    <h4>Grafički prikaz navika</h4>

    <div class="row mt-4">
        <div class="col-md-6">
            <h5>Raspodela navika po korisnicima</h5>
            <canvas id="pieChart"></canvas>
        </div>
        <div class="col-md-6">
            <h5>Logovi navika (samo tvoje)</h5>
            <canvas id="barChart"></canvas>
        </div>
    </div>
</div>

<script>
    // Pie chart - navike po korisniku
    const pieData = {
        labels: <?= json_encode(array_column($habit_counts, 'username')) ?>,
        datasets: [{
            label: 'Broj navika',
            data: <?= json_encode(array_column($habit_counts, 'habit_count')) ?>,
            backgroundColor: ['#f94144','#f3722c','#f9844a','#f9c74f','#90be6d','#43aa8b','#577590']
        }]
    };
    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: pieData
    });

    // Bar chart - tvoje navike i logovi
    const barData = {
        labels: <?= json_encode(array_column($habit_logs, 'name')) ?>,
        datasets: [{
            label: 'Broj logovanja',
            data: <?= json_encode(array_column($habit_logs, 'logs')) ?>,
            backgroundColor: '#4c6ef5'
        }]
    };
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: barData,
        options: { scales: { y: { beginAtZero: true } } }
    });
</script>
</body>
</html>



<?php
require_once 'includes/db.php';

// Dohvati najaktivnije korisnike po broju navika i logova
$sql = "
    SELECT u.username,
           COUNT(DISTINCT h.id) AS habit_count,
           COUNT(l.id) AS log_count
    FROM users u
    LEFT JOIN habits h ON h.user_id = u.id
    LEFT JOIN logs l ON l.user_id = u.id
    GROUP BY u.id
    ORDER BY log_count DESC, habit_count DESC
    LIMIT 10
";

$stmt = $pdo->query($sql);
$users = $stmt->fetchAll();
?>

<h2>Najaktivniji korisnici</h2>
<table class="table table-bordered">
  <thead>
    <tr>
      <th>Korisničko ime</th>
      <th>Broj navika</th>
      <th>Broj logova</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $user): ?>
      <tr>
        <td><?= htmlspecialchars($user['username']) ?></td>
        <td><?= $user['habit_count'] ?></td>
        <td><?= $user['log_count'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
