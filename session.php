<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (
    !isset($_SESSION['id']) ||
    trim((string) $_SESSION['id']) === ''
) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'] ?? '';

if ($role !== 'manager') {
    if ($role === 'crew') {
        header('Location: Crew_Dashboard.php');
    } else {
        $_SESSION = [];
        session_destroy();

        header('Location: login.php');
    }

    exit();
}

$session_id = $_SESSION['id'];
$session_email = $_SESSION['email'] ?? '';
?>