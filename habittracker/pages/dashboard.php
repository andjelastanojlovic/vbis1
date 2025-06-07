<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn('../pages/login.php');

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$is_admin = isAdmin();

// Uzmi navike (admin vidi sve)
if ($is_admin) {
    $stmt = $pdo->query("SELECT habits.*, users.username FROM habits JOIN users ON habits.user_id = users.id ORDER BY habits.created_at DESC");
    $habits = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT habits.*, users.username FROM habits JOIN users ON habits.user_id = users.id WHERE habits.user_id = ? ORDER BY habits.created_at DESC");
    $stmt->execute([$user_id]);
    $habits = $stmt->fetchAll();
}

// Funkcija za broj logova po navici
function getLogCount($pdo, $habit_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM habit_logs WHERE habit_id = ?");
    $stmt->execute([$habit_id]);
    return (int) $stmt->fetchColumn();
}

// Funkcija proverava da li je danas već logovana navika
function isLoggedToday($pdo, $habit_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM habit_logs WHERE habit_id = ? AND DATE(log_date) = CURDATE()");
    $stmt->execute([$habit_id]);
    return ((int) $stmt->fetchColumn()) > 0;
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - Habit Tracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="../css/dashboard.css" rel="stylesheet" />
    <style>
        /* --- Navbar --- */
        .navbar {
            background: #fff;
            box-shadow: 0 2px 6px rgb(0 0 0 / 0.08);
            font-weight: 600;
            user-select: none;
            padding: 0.4rem 1.5rem; /* tanji padding za manje visine */
            transition: box-shadow 0.3s ease;
        }
        .navbar:hover {
            box-shadow: 0 3px 12px rgb(0 0 0 / 0.12);
        }
        .navbar-brand {
            font-size: 1.7rem;
            color: #4c6ef5 !important;
            font-weight: 700;
            letter-spacing: 0.06em;
            transition: color 0.3s ease;
        }
        .navbar-brand:hover {
            color: #3b5bdb !important;
            text-decoration: none;
        }
        .navbar-nav .nav-link {
            color: #495057;
            font-size: 1.05rem;
            padding: 0.45rem 1rem;
            border-radius: 8px;
            transition: background-color 0.25s ease, color 0.25s ease;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            background-color: #4c6ef5;
            color: white !important;
            text-decoration: none;
        }
        .navbar-toggler {
            border: none;
            color: #4c6ef5;
            font-size: 1.4rem;
        }
        .navbar-toggler:focus {
            outline: none;
            box-shadow: none;
        }
        .btn-logout {
            background-color: #f03e3e;
            color: white;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.4rem 1rem;
            border: none;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.95rem;
        }
        .btn-logout:hover {
            background-color: #c92a2a;
            color: white;
            text-decoration: none;
        }

        /* --- Habit Card --- */
        .habit-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.2rem 1.75rem;
            margin-bottom: 12px; /* manji razmak između kartica */
            box-shadow: 0 6px 15px rgb(0 0 0 / 0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: box-shadow 0.3s ease;
            font-weight: 600;
            color: #2b2d42;
        }
        .habit-card:hover {
            box-shadow: 0 10px 26px rgb(0 0 0 / 0.10);
        }
        .habit-info {
            flex: 1;
        }
        .habit-name {
            font-size: 1.25rem;
            margin-bottom: 0.15rem;
            user-select: none;
        }
        .habit-user {
            font-size: 0.88rem;
            color: #6c757d;
            font-weight: 500;
            user-select: none;
        }
        .habit-log-count {
            font-size: 0.88rem;
            color: #495057;
            margin-top: 0.25rem;
            user-select: none;
        }
        .log-button {
            min-width: 140px;
            font-weight: 700;
            border-radius: 12px;
            padding: 0.45rem 1.2rem;
            font-size: 1rem;
            transition: background-color 0.3s ease, color 0.3s ease;
            user-select: none;
        }
        .log-button:disabled {
            background-color: #adb5bd !important;
            cursor: default;
            color: #f8f9fa !important;
            box-shadow: none !important;
            pointer-events: none;
        }

        /* Uklanjamo višak prostora ispod dugmadi */
        #habitsList {
            margin-bottom: 0; /* nema prevelikog praznog prostora ispod liste */
        }
        /* Glavni container manje vertikalnog margina */
        .container > h1 {
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="../pages/dashboard.php">
            <i class="fa-solid fa-check-to-slot"></i> Habit Tracker
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a href="../pages/dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-house"></i> Početna
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../pages/habits.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'habits.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-list-check"></i> Moje navike
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../pages/logs.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-clipboard-list"></i> Logovi
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../pages/statistics.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'statistics.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-chart-simple"></i> Statistika
                    </a>
                </li>
                <li class="nav-item d-lg-none">
                    <a href="../pages/logout.php" class="nav-link btn-logout w-100 text-center mt-3">
                        <i class="fa-solid fa-right-from-bracket"></i> Odjavi se
                    </a>
                </li>
            </ul>

            <!-- Logout button za desktop -->
            <a href="../pages/logout.php" class="btn-logout d-none d-lg-flex ms-3">
                <i class="fa-solid fa-right-from-bracket"></i> Odjavi se
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <h1>Dobrodošli, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>

    <?php if ($is_admin): ?>
        <div class="alert alert-info">Admin režim: prikazane su sve navike svih korisnika.</div>
    <?php endif; ?>

    <section id="habitsList">
        <?php if (empty($habits)): ?>
            <p class="text-muted">Još uvek nemate nijednu naviku.</p>
        <?php endif; ?>

        <?php foreach ($habits as $habit):
            $count = getLogCount($pdo, $habit['id']);
            $loggedToday = isLoggedToday($pdo, $habit['id']);
        ?>
        <div class="habit-card">
            <div class="habit-info">
                <div class="habit-name"><?= htmlspecialchars($habit['name']) ?></div>
                <div class="habit-user">Korisnik: <?= htmlspecialchars($habit['username']) ?></div>
                <div class="habit-log-count">Ukupno logova: <strong><?= $count ?></strong></div>
            </div>
            <button
                class="btn btn-primary log-button"
                data-habit-id="<?= $habit['id'] ?>"
                <?= $loggedToday ? 'disabled' : '' ?>
            >
                <?= $loggedToday ? 'Već logovano danas' : 'Loguj za danas' ?>
            </button>
        </div>
        <?php endforeach; ?>
    </section>
</div>

<!-- Bootstrap i jQuery -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(function() {
    const buttons = $('.log-button');

    buttons.on('click', function() {
        const button = $(this);
        const habitId = button.data('habit-id');
        const logCountElem = button.siblings('.habit-info').find('.habit-log-count strong');

        if(button.prop('disabled')) return; // sigurnosna provera

        button.prop('disabled', true).text('Logovanje...');

        fetch('../scripts/log_habit.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'habit_id=' + encodeURIComponent(habitId)
        })
        .then(res => {
            if(!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if(data.success){
                logCountElem.text(data.logCount);
                button.text('Već logovano danas');
                // Dugme ostaje disabled, ne može se kliknuti ponovo
            } else {
                throw new Error(data.message || 'Greška pri logovanju navike.');
            }
        })
        .catch(err => {
            alert(err.message || 'Greška pri komunikaciji sa serverom.');
            button.prop('disabled', false).text('Loguj za danas');
        });
    });
});
</script>

</body>
</html>
