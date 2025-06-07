<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Preusmeri korisnika na stranicu za prijavu ako nije ulogovan.
 * Ako je $ajax = true, vrati JSON grešku sa statusom 401.
 *
 * @param bool $ajax
 * @return void
 */
function redirectIfNotLoggedIn($ajax = false) {
    if (!isLoggedIn()) {
        if ($ajax) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Niste ulogovani']);
            exit;
        } else {
            header("Location: ../pages/login.php");
            exit;
        }
    }
}
