<?php
require_once 'session.php';
require_once 'connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (empty($_SESSION['crew_csrf'])) {
    $_SESSION['crew_csrf'] = bin2hex(random_bytes(32));
}

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function crewRedirect($message, $type = 'success') {
    $_SESSION['crew_message'] = $message;
    $_SESSION['crew_message_type'] = $type;
    header('Location: Crew_Management.php');
    exit;
}

$message = $_SESSION['crew_message'] ?? '';
$messageType = $_SESSION['crew_message_type'] ?? 'success';
unset($_SESSION['crew_message'], $_SESSION['crew_message_type']);

$positions = ['Crew Member', 'Team Leader', 'Supervisor'];
$workStatuses = ['Active', 'Absent', 'On Leave', 'Deactivated'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['crew_csrf'], $_POST['csrf_token'] ?? '')) {
        crewRedirect('Security verification failed. Please try again.', 'error');
    }

    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_crew') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $contact = trim($_POST['contact'] ?? '');
            $position = trim($_POST['position'] ?? 'Crew Member');
            $password = $_POST['password'] ?? '';

            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                crewRedirect('Enter a valid name and email address.', 'error');
            }

            if (strlen($password) < 8) {
                crewRedirect('Password must be at least 8 characters long.', 'error');
            }

            if (strlen($name) > 150 || strlen($email) > 255 ||
                strlen($contact) > 20 || !in_array($position, $positions, true)) {
                crewRedirect('Please check the entered information.', 'error');
            }

            $check = $conn->prepare('SELECT id FROM crew_members WHERE email = ? LIMIT 1');
            $check->bind_param('s', $email);
            $check->execute();
            $existing = $check->get_result()->fetch_assoc();
            $check->close();

            if ($existing) {
                crewRedirect('That email address is already registered.', 'error');
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "INSERT INTO crew_members
                (name, email, password, contact, position, is_active, work_status)
                VALUES (?, ?, ?, ?, ?, 1, 'Active')"
            );
            $stmt->bind_param('sssss', $name, $email, $hash, $contact, $position);
            $stmt->execute();
            $stmt->close();

            crewRedirect('Crew account created successfully.');
        }

        if ($action === 'edit_crew') {
            $id = (int)($_POST['crew_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $contact = trim($_POST['contact'] ?? '');
            $position = trim($_POST['position'] ?? '');
            $workStatus = trim($_POST['work_status'] ?? '');

            if ($id <= 0 || $name === '' ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                !in_array($position, $positions, true) ||
                !in_array($workStatus, $workStatuses, true)) {
                crewRedirect('Please provide valid crew information.', 'error');
            }

            $check = $conn->prepare(
                'SELECT id FROM crew_members WHERE email = ? AND id <> ? LIMIT 1'
            );
            $check->bind_param('si', $email, $id);
            $check->execute();
            $duplicate = $check->get_result()->fetch_assoc();
            $check->close();

            if ($duplicate) {
                crewRedirect('That email address is already used by another crew member.', 'error');
            }

            $isActive = ($workStatus === 'Deactivated') ? 0 : 1;
            $stmt = $conn->prepare(
                'UPDATE crew_members
                 SET name=?, email=?, contact=?, position=?, work_status=?, is_active=?
                 WHERE id=?'
            );
            $stmt->bind_param(
                'sssssii',
                $name, $email, $contact, $position, $workStatus, $isActive, $id
            );
            $stmt->execute();
            $stmt->close();

            crewRedirect('Crew member information updated successfully.');
        }

        if ($action === 'change_status') {
            $id = (int)($_POST['crew_id'] ?? 0);
            $workStatus = trim($_POST['work_status'] ?? '');

            if ($id <= 0 || !in_array($workStatus, $workStatuses, true)) {
                crewRedirect('Invalid crew status.', 'error');
            }

            $isActive = ($workStatus === 'Deactivated') ? 0 : 1;
            $stmt = $conn->prepare(
                'UPDATE crew_members SET work_status=?, is_active=? WHERE id=?'
            );
            $stmt->bind_param('sii', $workStatus, $isActive, $id);
            $stmt->execute();
            $stmt->close();

            crewRedirect('Crew work status updated.');
        }

        crewRedirect('Unknown action.', 'error');
    } catch (mysqli_sql_exception $e) {
        error_log('Crew Management error: ' . $e->getMessage());
        crewRedirect(
            (int)$e->getCode() === 1062
                ? 'That email address is already registered.'
                : 'A database error occurred. Check your PHP error log.',
            'error'
        );
    }
}

$editCrew = null;

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];

    if ($editId > 0) {
        $stmt = $conn->prepare(
            'SELECT id, name, email, contact, position, is_active, work_status
             FROM crew_members WHERE id=? LIMIT 1'
        );
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $editCrew = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($editCrew && empty($editCrew['work_status'])) {
            $editCrew['work_status'] = ((int)$editCrew['is_active'] === 1)
                ? 'Active' : 'Deactivated';
        }

        if (!$editCrew) {
            $message = 'Crew member not found.';
            $messageType = 'error';
        }
    }
}

$crewMembers = [];
$result = $conn->query(
    'SELECT id, name, email, contact, position, is_active, work_status, created_at
     FROM crew_members ORDER BY id DESC'
);

while ($row = $result->fetch_assoc()) {
    if (empty($row['work_status'])) {
        $row['work_status'] = ((int)$row['is_active'] === 1)
            ? 'Active' : 'Deactivated';
    }
    $crewMembers[] = $row;
}

$totalCrew = count($crewMembers);
$activeCount = 0;

foreach ($crewMembers as $crew) {
    if ($crew['work_status'] === 'Active' && (int)$crew['is_active'] === 1) {
        $activeCount++;
    }
}

$managerName = $_SESSION['name'] ?? $_SESSION['email'] ?? 'Manager';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Crew Management | McSchedulEase</title>
<style>
:root {
    --maroon: #701C2C;
    --maroon-dark: #4D101D;
    --yellow: #F5C542;
    --yellow-light: rgba(255, 244, 199, 0.85);
    --white-glass: rgba(255, 255, 255, 0.82);
    --card-border: rgba(255, 255, 255, 0.5);
    --text: #30262A;
    --muted: #5C5255;
    --border: rgba(112, 28, 44, 0.15);
}

* { box-sizing: border-box; }

body {
    margin: 0;
    min-height: 100vh;
    font-family: Arial, Helvetica, sans-serif;
    color: var(--text);
    background: linear-gradient(rgba(20, 10, 15, 0.45), rgba(20, 10, 15, 0.45)), 
                url('loginbg.jpg') center/cover fixed no-repeat;
}

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    width: 68px;
    height: 100vh;
    overflow-x: hidden;
    overflow-y: auto;
    background: rgba(245, 197, 66, 0.9);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.25);
    transition: width .25s;
}
.sidebar:hover, .sidebar:focus-within { width: 250px; }
.sidebar-brand {
    height: 65px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 15px;
    white-space: nowrap;
    border-bottom: 1px solid rgba(77, 16, 29, 0.2);
}
.brand-icon {
    width: 38px;
    height: 38px;
    min-width: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--maroon);
    color: var(--yellow);
    font-size: 20px;
    font-weight: 800;
}
.brand-text, .nav-text, .nav-label { opacity: 0; visibility: hidden; transition: opacity .15s; }
.sidebar:hover .brand-text, .sidebar:hover .nav-text, .sidebar:hover .nav-label,
.sidebar:focus-within .brand-text, .sidebar:focus-within .nav-text, .sidebar:focus-within .nav-label {
    opacity: 1; visibility: visible;
}
.brand-text strong { display: block; font-size: 16px; color: var(--maroon-dark); }
.brand-text small { display: block; margin-top: 4px; font-size: 10px; letter-spacing: 1px; }
.nav-label { margin: 25px 18px 10px; font-size: 10px; letter-spacing: 1px; white-space: nowrap; font-weight: bold; color: var(--maroon-dark); }
.nav-link {
    height: 47px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin: 4px 8px;
    padding: 0 12px;
    border-left: 3px solid transparent;
    border-radius: 8px;
    color: var(--maroon-dark);
    text-decoration: none;
    white-space: nowrap;
    font-weight: 600;
}
.nav-link:hover { background: rgba(255, 255, 255, 0.5); color: var(--maroon); }
.nav-link.active { background: var(--maroon); color: white; border-left-color: var(--yellow); font-weight: 700; }
.nav-icon { width: 26px; min-width: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 21px; }
.nav-text { font-size: 13px; }

.main { margin-left: 68px; min-width: 0; min-height: 100vh; }

.topbar {
    min-height: 65px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 15px 28px;
    background: rgba(245, 197, 66, 0.9);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
.topbar-title { font-size: 18px; font-weight: 800; color: var(--maroon); }
.topbar-user { font-size: 13px; color: var(--maroon-dark); font-weight: 600; }

.content { max-width: 1600px; margin: auto; padding: 28px; }

h1 { margin: 0 0 8px; color: #FFFFFF; font-size: 28px; text-shadow: 0 2px 6px rgba(0,0,0,0.5); }
.subtitle { margin: 0 0 24px; color: #FFF4C7; font-size: 14px; line-height: 1.6; text-shadow: 0 1px 4px rgba(0,0,0,0.5); }

/* Stat Cards Glassmorphism */
.stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
.stat, .panel {
    background: var(--white-glass);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--card-border);
    border-radius: 13px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}
.stat { padding: 21px; border-top: 4px solid var(--maroon); }
.stat-label { color: var(--muted); font-size: 13px; font-weight: 600; }
.stat-number { margin-top: 10px; color: var(--maroon); font-size: 29px; font-weight: 800; }

.panel { padding: 24px; margin-bottom: 24px; }
.panel h2 { margin: 0 0 20px; font-size: 20px; color: var(--maroon); }

.form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.field label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; color: var(--maroon-dark); }
.field input, .field select {
    width: 100%;
    padding: 12px;
    border: 1px solid rgba(112, 28, 44, 0.25);
    border-radius: 7px;
    background: rgba(255, 255, 255, 0.9);
    color: var(--text);
    font-size: 14px;
}
.field input:focus, .field select:focus { outline: 2px solid var(--yellow); border-color: var(--maroon); }
.full { grid-column: 1 / -1; }
.help { margin-top: 7px; color: var(--muted); font-size: 12px; line-height: 1.6; }

.btn { display: inline-block; border: 0; border-radius: 7px; padding: 10px 13px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; transition: all 0.2s ease; }
.btn-primary { background: var(--maroon); color: white; }
.btn-primary:hover { background: var(--maroon-dark); color: var(--yellow); }
.btn-edit { background: var(--yellow); color: var(--maroon-dark); }
.btn-edit:hover { background: #E6B435; }
.btn-cancel { background: #E2D9D7; color: var(--text); }
.btn-cancel:hover { background: #D1C6C4; }

.notice { padding: 13px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; line-height: 1.5; font-weight: 600; }
.notice.success { background: rgba(245, 197, 66, 0.9); border: 1px solid var(--maroon); color: var(--maroon-dark); }
.notice.error { background: rgba(254, 226, 226, 0.95); color: #991B1B; border: 1px solid #F87171; }

.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; min-width: 950px; }
th, td { padding: 13px 11px; border-bottom: 1px solid var(--border); text-align: left; font-size: 13px; vertical-align: middle; }
th { background: rgba(245, 197, 66, 0.4); color: var(--maroon-dark); font-size: 11px; text-transform: uppercase; letter-spacing: .4px; font-weight: 800; }
tbody tr:hover { background: rgba(245, 197, 66, 0.15); }

.badge { display: inline-block; padding: 6px 9px; border-radius: 20px; font-size: 11px; font-weight: 800; white-space: nowrap; }
.badge-active { background: #1B5E20; color: #FFFFFF; }
.badge-absent { background: #C62828; color: #FFFFFF; }
.badge-leave { background: #4A148C; color: #FFFFFF; }
.badge-deactivated { background: #616161; color: #FFFFFF; }

.actions { display: flex; gap: 7px; flex-wrap: wrap; align-items: center; }
.inline-form { display: inline-flex; gap: 6px; align-items: center; }
.inline-form select { padding: 7px; border: 1px solid var(--border); border-radius: 6px; max-width: 130px; background: rgba(255, 255, 255, 0.9); }

.empty { text-align: center; padding: 30px; color: var(--muted); font-weight: 600; }
footer { text-align: center; padding: 18px; color: #FFFFFF; font-size: 12px; text-shadow: 0 1px 4px rgba(0,0,0,0.6); }

@media(max-width:800px){ .content { padding: 17px; } .topbar { padding: 15px; } .form-grid { grid-template-columns: 1fr; } .full { grid-column: auto; } }
@media(max-width:700px){ .sidebar { width: 0; } .sidebar:hover, .sidebar:focus-within { width: 250px; } .main { margin-left: 0; } .stats { grid-template-columns: 1fr; } .content { padding: 15px; } h1 { font-size: 24px; } }
</style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-brand"><div class="brand-icon">M</div><div class="brand-text"><strong>McSchedulEase</strong><small>MANAGER PORTAL</small></div></div>
    <div class="nav-label">Main Menu</div>
    <a class="nav-link" href="Dashboard.php"><span class="nav-icon">▦</span><span class="nav-text">Dashboard</span></a>
    <a class="nav-link" href="Crew_Management.php"><span class="nav-icon">♙</span><span class="nav-text">Crew Management</span></a>
    <a class="nav-link" href="Schedule_Management.php"><span class="nav-icon">▣</span><span class="nav-text">Schedule Management</span></a>
    <a class="nav-link" href="Inquiry.php"><span class="nav-icon">✉</span><span class="nav-text">Inquiries / Feedback</span></a>
    <a class="nav-link" href="Admin%20Account.php"><span class="nav-icon">⚙</span><span class="nav-text">Admin Account</span></a>
    <div class="nav-label">Account</div>
    <a class="nav-link" href="logout.php"><span class="nav-icon">↪</span><span class="nav-text">Log Out</span></a>
</aside>

<main class="main">
<header class="topbar"><div class="topbar-title">McSchedulEase</div><div class="topbar-user">Welcome, <?= h($managerName) ?></div></header>
<div class="content">
    <h1>Crew Management</h1>
    <p class="subtitle">Register crew members, edit their information, and manage their work status.</p>

    <?php if ($message !== ''): ?><div class="notice <?= h($messageType) ?>"><?= h($message) ?></div><?php endif; ?>

    <section class="stats">
        <div class="stat"><div class="stat-label">Total Crew Members</div><div class="stat-number"><?= $totalCrew ?></div></div>
        <div class="stat"><div class="stat-label">Active Crew Members</div><div class="stat-number"><?= $activeCount ?></div></div>
    </section>

    <section class="panel">
        <h2><?= $editCrew ? 'Edit Crew Member' : 'Create Crew Account' ?></h2>
        <form method="POST" action="Crew_Management.php">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['crew_csrf']) ?>">
            <input type="hidden" name="action" value="<?= $editCrew ? 'edit_crew' : 'add_crew' ?>">
            <?php if ($editCrew): ?><input type="hidden" name="crew_id" value="<?= (int)$editCrew['id'] ?>"><?php endif; ?>
            <div class="form-grid">
                <div class="field"><label for="name">Full Name *</label><input id="name" name="name" maxlength="150" required value="<?= h($editCrew['name'] ?? '') ?>"></div>
                <div class="field"><label for="email">Email Address *</label><input type="email" id="email" name="email" maxlength="255" required value="<?= h($editCrew['email'] ?? '') ?>"></div>
                <div class="field"><label for="contact">Contact Number</label><input id="contact" name="contact" maxlength="20" value="<?= h($editCrew['contact'] ?? '') ?>"></div>
                <div class="field"><label for="position">Position *</label><select id="position" name="position" required><?php foreach ($positions as $position): ?><option value="<?= h($position) ?>" <?= ($editCrew['position'] ?? 'Crew Member') === $position ? 'selected' : '' ?>><?= h($position) ?></option><?php endforeach; ?></select></div>
                <?php if (!$editCrew): ?>
                    <div class="field full"><label for="password">Temporary Password *</label><input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required><div class="help">At least 8 characters. Share the password securely with the crew member.</div></div>
                <?php else: ?>
                    <div class="field full"><label for="work_status">Work Status *</label><select id="work_status" name="work_status" required><?php foreach ($workStatuses as $status): ?><option value="<?= h($status) ?>" <?= ($editCrew['work_status'] ?? 'Active') === $status ? 'selected' : '' ?>><?= h($status) ?></option><?php endforeach; ?></select><div class="help">Only Active and enabled crew members are available for new schedules.</div></div>
                <?php endif; ?>
                <div class="full actions"><button class="btn btn-primary" type="submit"><?= $editCrew ? 'Save Changes' : 'Create Crew Account' ?></button><?php if ($editCrew): ?><a class="btn btn-cancel" href="Crew_Management.php">Cancel Edit</a><?php endif; ?></div>
            </div>
        </form>
    </section>

    <section class="panel">
        <h2>Registered Crew Members</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Contact</th><th>Position</th><th>Work Status</th><th>Registered</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$crewMembers): ?><tr><td colspan="8" class="empty">No crew members registered yet.</td></tr>
                <?php else: foreach ($crewMembers as $crew):
                    $statusClass = match ($crew['work_status']) {
                        'Active' => 'badge-active',
                        'Absent' => 'badge-absent',
                        'On Leave' => 'badge-leave',
                        default => 'badge-deactivated'
                    };
                ?>
                    <tr>
                        <td><?= (int)$crew['id'] ?></td><td><?= h($crew['name']) ?></td><td><?= h($crew['email']) ?></td><td><?= h($crew['contact']) ?></td><td><?= h($crew['position']) ?></td>
                        <td><span class="badge <?= $statusClass ?>"><?= h($crew['work_status']) ?></span></td><td><?= h($crew['created_at'] ?? '—') ?></td>
                        <td><div class="actions">
                            <a class="btn btn-edit" href="Crew_Management.php?edit=<?= (int)$crew['id'] ?>">Edit</a>
                            <form class="inline-form" method="POST" action="Crew_Management.php">
                                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['crew_csrf']) ?>">
                                <input type="hidden" name="action" value="change_status">
                                <input type="hidden" name="crew_id" value="<?= (int)$crew['id'] ?>">
                                <select name="work_status" aria-label="Change work status"><?php foreach ($workStatuses as $status): ?><option value="<?= h($status) ?>" <?= $crew['work_status'] === $status ? 'selected' : '' ?>><?= h($status) ?></option><?php endforeach; ?></select>
                                <button class="btn btn-primary" type="submit">Update</button>
                            </form>
                        </div></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <footer>McSchedulEase © <?= date('Y') ?> | Manager Portal</footer>
</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const current = location.pathname.split('/').pop().toLowerCase();
    document.querySelectorAll('.nav-link').forEach(function (link) {
        const href = link.getAttribute('href').split('/').pop().toLowerCase();
        link.classList.toggle('active', href === current);
    });
});
</script>
</body>
</html>