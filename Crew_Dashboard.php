<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['id']) ||
    ($_SESSION['role'] ?? '') !== 'crew' ||
    !isset($_SESSION['crew_id'])
) {
    if (($_SESSION['role'] ?? '') === 'manager') {
        header('Location: Dashboard.php');
    } else {
        header('Location: crew_login.php');
    }
    exit();
}

require_once 'connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function crewDate($date) {
    return $date ? date('M d, Y', strtotime($date)) : '—';
}

function crewTime($time) {
    return $time ? date('h:i A', strtotime($time)) : '—';
}

$crewId = (int)$_SESSION['crew_id'];
$crew = null;
$upcomingSchedules = [];
$totalUpcoming = 0;
$nextShift = null;
$pageError = '';

try {
    // Get the latest account and work status from the database.
    $stmt = $conn->prepare("
        SELECT id, name, email, contact, position,
               is_active, work_status, created_at
        FROM crew_members
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param('i', $crewId);
    $stmt->execute();
    $crew = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Immediately block a deactivated account or inactive login account.
    if (
        !$crew ||
        (int)$crew['is_active'] !== 1 ||
        ($crew['work_status'] ?? '') === 'Deactivated'
    ) {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: crew_login.php?deactivated=1');
        exit();
    }

    // Retrieve the crew member's published upcoming schedules.
    $stmt = $conn->prepare("
        SELECT id, shift_date, start_time, end_time,
               shift_type, status
        FROM schedules
        WHERE crew_id = ?
          AND status = 'published'
          AND shift_date >= CURDATE()
        ORDER BY shift_date ASC, start_time ASC, id ASC
    ");

    $stmt->bind_param('i', $crewId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $upcomingSchedules[] = $row;
    }

    $stmt->close();

    $totalUpcoming = count($upcomingSchedules);

    if ($totalUpcoming > 0) {
        $nextShift = $upcomingSchedules[0];
    }

} catch (mysqli_sql_exception $e) {
    error_log('Crew dashboard error: ' . $e->getMessage());

    $pageError = 'Some dashboard information could not be loaded. Please refresh the page or contact your manager.';
}

$crewName = $crew['name'] ?? ($_SESSION['name'] ?? 'Crew Member');
$crewEmail = $crew['email'] ?? ($_SESSION['email'] ?? '');
$crewContact = $crew['contact'] ?? '';
$crewPosition = $crew['position'] ?? 'Crew Member';
$workStatus = $crew['work_status'] ?? 'Active';
$isActive = isset($crew['is_active']) && (int)$crew['is_active'] === 1;
$joinedDate = $crew['created_at'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crew Dashboard | McSchedule AI</title>

<style>
:root {
    --maroon:#701C2C;
    --maroon-dark:#4D101D;
    --maroon-light:#8D293D;
    --yellow:#F5C542;
    --yellow-light:#FFF4C7;
    --bg:#F5F1EE;
    --text:#30262A;
    --muted:#82777B;
    --border:#E8DFDC;
    --white:#FFFFFF;
}

* {
    box-sizing:border-box;
}

body {
    margin:0;
    font-family:Arial,Helvetica,sans-serif;
    background:var(--bg);
    color:var(--text);
}

.layout {
    display:flex;
    min-height:100vh;
}

.sidebar {
    width:250px;
    flex-shrink:0;
    padding:27px 17px;
    background:var(--maroon-dark);
    color:var(--white);
}

.brand {
    font-size:23px;
    font-weight:800;
}

.brand span {
    color:var(--yellow);
}

.brand-sub {
    color:var(--yellow-light);
    font-size:11px;
    margin:7px 0 35px;
    letter-spacing:1px;
}

.nav-label {
    color:#D9B7BD;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:1px;
    margin:23px 10px 12px;
}

.nav-link {
    display:block;
    color:#F5E9EB;
    text-decoration:none;
    padding:13px 12px;
    margin-bottom:6px;
    border-radius:9px;
    font-size:14px;
}

.nav-link:hover,
.nav-link.active {
    background:var(--maroon-light);
    color:white;
    border-left:4px solid var(--yellow);
}

.sidebar-footer {
    border-top:1px solid #824452;
    margin-top:35px;
    padding:17px 10px;
    font-size:12px;
    color:#F5E9EB;
    line-height:1.8;
}

.main {
    flex:1;
    min-width:0;
}

.topbar {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    padding:19px 30px;
    background:white;
    border-bottom:1px solid var(--border);
}

.topbar-title {
    font-size:18px;
    font-weight:700;
    color:var(--maroon-dark);
}

.topbar-user {
    color:var(--muted);
    font-size:13px;
}

.logout {
    display:inline-block;
    text-decoration:none;
    background:var(--yellow-light);
    color:var(--maroon-dark);
    padding:10px 13px;
    border-radius:8px;
    font-size:13px;
    font-weight:700;
}

.logout:hover {
    background:var(--yellow);
}

.content {
    padding:30px;
    max-width:1500px;
    margin:auto;
}

.welcome {
    background:linear-gradient(120deg,var(--maroon-dark),var(--maroon-light));
    color:white;
    padding:29px;
    border-radius:17px;
    margin-bottom:24px;
    border-bottom:5px solid var(--yellow);
}

.welcome small {
    color:var(--yellow);
    font-weight:700;
    letter-spacing:1px;
}

.welcome h1 {
    font-size:27px;
    margin:12px 0 8px;
}

.welcome p {
    color:#F8EDEF;
    margin:0;
    line-height:1.7;
}

.stats {
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:16px;
    margin-bottom:25px;
}

.stat-card,
.panel {
    background:white;
    border:1px solid var(--border);
    border-radius:13px;
    padding:21px;
    box-shadow:0 3px 12px rgba(77,16,29,.05);
}

.stat-top {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:8px;
}

.stat-label {
    color:var(--muted);
    font-size:12px;
    line-height:1.5;
}

.stat-icon {
    display:flex;
    align-items:center;
    justify-content:center;
    width:39px;
    height:39px;
    background:var(--yellow-light);
    color:var(--maroon);
    border-radius:10px;
    font-size:19px;
}

.stat-number {
    font-size:27px;
    font-weight:800;
    margin-top:14px;
    color:var(--maroon-dark);
}

.stat-note {
    color:var(--muted);
    font-size:12px;
    margin-top:7px;
}

.panel {
    margin-bottom:24px;
}

.panel-heading {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:19px;
}

.panel-heading h2 {
    font-size:19px;
    margin:0;
    color:var(--maroon-dark);
}

.table-wrap {
    overflow-x:auto;
}

table {
    width:100%;
    border-collapse:collapse;
    min-width:600px;
}

th,
td {
    padding:14px 12px;
    text-align:left;
    border-bottom:1px solid #F0E8E5;
    font-size:13px;
}

th {
    background:var(--yellow-light);
    color:var(--maroon-dark);
    text-transform:uppercase;
    font-size:11px;
    letter-spacing:.4px;
}

.badge {
    display:inline-block;
    padding:6px 9px;
    border-radius:20px;
    font-size:11px;
    font-weight:800;
}

.badge.published,
.badge.active {
    background:#E3F4E8;
    color:#216638;
}

.badge.absent {
    background:#FCE8D5;
    color:#8A4D0F;
}

.badge.leave {
    background:var(--yellow-light);
    color:#765600;
}

.badge.inactive {
    background:#F8E1E4;
    color:#962638;
}

.profile-grid {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:16px;
}

.profile-item {
    background:#FCFAF9;
    border:1px solid var(--border);
    border-radius:10px;
    padding:15px;
    min-width:0;
}

.profile-label {
    color:var(--muted);
    font-size:12px;
    margin-bottom:7px;
}

.profile-value {
    font-size:14px;
    font-weight:700;
    overflow-wrap:anywhere;
}

.next-shift {
    padding:20px;
    border-radius:12px;
    background:var(--yellow-light);
    border:1px solid #F0D777;
}

.next-shift h3 {
    margin:0 0 10px;
    font-size:19px;
    color:var(--maroon-dark);
}

.next-shift p {
    margin:7px 0;
    color:var(--text);
    font-size:14px;
}

.empty {
    padding:30px 15px;
    text-align:center;
    color:var(--muted);
    background:#FCFAF9;
    border:1px dashed #D7C7C5;
    border-radius:10px;
    font-size:13px;
    line-height:1.7;
}

.notice {
    background:var(--yellow-light);
    color:#765600;
    border:1px solid #F0D777;
    border-radius:10px;
    padding:14px 16px;
    margin-bottom:20px;
    font-size:13px;
    line-height:1.6;
}

footer {
    text-align:center;
    color:var(--muted);
    padding:22px 10px;
    font-size:12px;
}

@media(max-width:1000px) {
    .stats {
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
}

@media(max-width:700px) {
    .layout {
        display:block;
    }

    .sidebar {
        width:100%;
        padding:18px;
    }

    .brand-sub {
        margin-bottom:15px;
    }

    .nav-label,
    .sidebar-footer {
        display:none;
    }

    .nav-link {
        display:inline-block;
        padding:10px;
        margin:8px 4px 0 0;
    }

    .topbar {
        padding:16px;
    }

    .topbar-user {
        display:none;
    }

    .content {
        padding:16px;
    }

    .stats {
        grid-template-columns:1fr 1fr;
        gap:10px;
    }

    .stat-card {
        padding:15px;
    }

    .stat-number {
        font-size:23px;
    }

    .welcome {
        padding:23px;
    }

    .welcome h1 {
        font-size:23px;
    }

    .profile-grid {
        grid-template-columns:1fr;
    }
}
</style>
</head>

<body>
<div class="layout">

    <aside class="sidebar">
        <div class="brand">McSchedule <span>AI</span></div>
        <div class="brand-sub">CREW PORTAL</div>

        <div class="nav-label">Main Menu</div>

        <a class="nav-link active" href="Crew_Dashboard.php">
            ▦ &nbsp; Dashboard
        </a>

        <a class="nav-link" href="#my-schedule">
            ▣ &nbsp; My Schedule
        </a>

        <a class="nav-link" href="#my-profile">
            ♙ &nbsp; My Profile
        </a>

        <div class="nav-label">Account</div>

        <a class="nav-link" href="crew_logout.php">
            ↪ &nbsp; Log Out
        </a>

        <div class="sidebar-footer">
            <strong>McSchedule AI</strong><br>
            Your Crew Scheduling Portal
        </div>
    </aside>

    <main class="main">

        <header class="topbar">
            <div class="topbar-title">Crew Dashboard</div>

            <div class="topbar-user">
                Welcome, <?= h($crewName) ?>
            </div>

            <a class="logout" href="crew_logout.php">Log Out</a>
        </header>

        <div class="content">

            <section class="welcome">
                <small>YOUR CREW WORKSPACE</small>

                <h1>Welcome, <?= h($crewName) ?>!</h1>

                <p>
                    View your published shifts, check your next duty,
                    and review your crew account information here.
                </p>
            </section>

            <?php if ($pageError !== ''): ?>
                <div class="notice">
                    <?= h($pageError) ?>
                </div>
            <?php endif; ?>

            <section class="stats">

                <div class="stat-card">
                    <div class="stat-top">
                        <div class="stat-label">
                            Upcoming Published Shifts
                        </div>
                        <div class="stat-icon">▣</div>
                    </div>

                    <div class="stat-number">
                        <?= $totalUpcoming ?>
                    </div>

                    <div class="stat-note">
                        Your scheduled shifts from today onward
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-top">
                        <div class="stat-label">Next Shift</div>
                        <div class="stat-icon">◷</div>
                    </div>

                    <div class="stat-number" style="font-size:20px">
                        <?= $nextShift
                            ? h(crewDate($nextShift['shift_date']))
                            : 'Not assigned' ?>
                    </div>

                    <div class="stat-note">
                        <?= $nextShift
                            ? h($nextShift['shift_type'])
                            : 'Check again after your manager publishes a schedule' ?>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-top">
                        <div class="stat-label">Work Status</div>
                        <div class="stat-icon">✓</div>
                    </div>

                    <div class="stat-number" style="font-size:23px">
                        <?= h($workStatus) ?>
                    </div>

                    <div class="stat-note">
                        Your current status set by the manager
                    </div>
                </div>

            </section>

            <section class="panel" id="my-schedule">
                <div class="panel-heading">
                    <h2>My Upcoming Schedule</h2>
                </div>

                <?php if (empty($upcomingSchedules)): ?>

                    <div class="empty">
                        <strong>No published shifts yet.</strong><br>
                        Your schedule will appear here after your manager
                        assigns and publishes a shift.
                    </div>

                <?php else: ?>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Duty Date</th>
                                    <th>Shift Type</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($upcomingSchedules as $shift): ?>
                                <tr>
                                    <td>
                                        <?= h(crewDate($shift['shift_date'])) ?>
                                    </td>

                                    <td><?= h($shift['shift_type']) ?></td>

                                    <td>
                                        <?= h(crewTime($shift['start_time'])) ?>
                                    </td>

                                    <td>
                                        <?= h(crewTime($shift['end_time'])) ?>
                                    </td>

                                    <td>
                                        <span class="badge published">
                                            Published
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>
            </section>

            <?php if ($nextShift): ?>
                <section class="panel">
                    <div class="panel-heading">
                        <h2>Next Shift Details</h2>
                    </div>

                    <div class="next-shift">
                        <h3><?= h($nextShift['shift_type']) ?></h3>

                        <p>
                            <strong>Date:</strong>
                            <?= h(crewDate($nextShift['shift_date'])) ?>
                        </p>

                        <p>
                            <strong>Start:</strong>
                            <?= h(crewTime($nextShift['start_time'])) ?>
                        </p>

                        <p>
                            <strong>End:</strong>
                            <?= h(crewTime($nextShift['end_time'])) ?>
                        </p>
                    </div>
                </section>
            <?php endif; ?>

            <section class="panel" id="my-profile">
                <div class="panel-heading">
                    <h2>My Profile</h2>

                    <?php
                    $statusClass = 'active';

                    if ($workStatus === 'Absent') {
                        $statusClass = 'absent';
                    } elseif ($workStatus === 'On Leave') {
                        $statusClass = 'leave';
                    } elseif (
                        $workStatus === 'Deactivated' ||
                        !$isActive
                    ) {
                        $statusClass = 'inactive';
                    }
                    ?>

                    <span class="badge <?= h($statusClass) ?>">
                        <?= h($workStatus) ?>
                    </span>
                </div>

                <div class="profile-grid">

                    <div class="profile-item">
                        <div class="profile-label">Full Name</div>
                        <div class="profile-value">
                            <?= h($crewName) ?>
                        </div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-label">Email Address</div>
                        <div class="profile-value">
                            <?= h($crewEmail) ?>
                        </div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-label">Contact Number</div>
                        <div class="profile-value">
                            <?= h($crewContact ?: 'Not provided') ?>
                        </div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-label">Position</div>
                        <div class="profile-value">
                            <?= h($crewPosition) ?>
                        </div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-label">Crew ID</div>
                        <div class="profile-value">
                            <?= h($crewId) ?>
                        </div>
                    </div>

                    <div class="profile-item">
                        <div class="profile-label">Account Created</div>
                        <div class="profile-value">
                            <?= $joinedDate
                                ? h(crewDate($joinedDate))
                                : '—' ?>
                        </div>
                    </div>

                </div>
            </section>

            <footer>
                McSchedule AI © <?= date('Y') ?> |
                Crew Scheduling Portal
            </footer>

        </div>
    </main>
</div>
</body>
</html>