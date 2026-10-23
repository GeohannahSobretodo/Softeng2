
<?php
require_once 'session.php';
require_once 'connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Inquiry.php');
    exit;
}

$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !hash_equals($sessionToken, $submittedToken)
) {
    $_SESSION['inquiry_flash'] = 'Security validation failed. Please try again.';
    header('Location: Inquiry.php');
    exit;
}

$inquiryId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$inquiryId || $inquiryId < 1) {
    $_SESSION['inquiry_flash'] = 'Invalid inquiry ID.';
    header('Location: Inquiry.php');
    exit;
}

try {
    $stmt = $conn->prepare('DELETE FROM inquiry WHERE id = ?');
    $stmt->bind_param('i', $inquiryId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['inquiry_flash'] = 'Inquiry deleted successfully.';
    } else {
        $_SESSION['inquiry_flash'] = 'Inquiry not found or already deleted.';
    }

    $stmt->close();
} catch (Throwable $e) {
    error_log('Delete inquiry error: ' . $e->getMessage());
    $_SESSION['inquiry_flash'] = 'Unable to delete the inquiry. Please try again.';
}

header('Location: Inquiry.php');
exit;
