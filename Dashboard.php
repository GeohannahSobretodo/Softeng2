<?php
require_once 'session.php';
require_once 'connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$managerName = $_SESSION['name'] ?? $_SESSION['email'] ?? 'Manager';

$stats = [
    'total_crew' => 0,
    'active_crew' => 0,
    'total_schedules' => 0,
    'published_schedules' => 0,
    'pending_inquiries' => 0
];

$upcomingSchedules = [];
$recentCrew = [];
$dashboardErrors = [];

try {
    $result = $conn->query("SELECT COUNT(*) AS total FROM crew_members");
    $stats['total_crew'] = (int)$result->fetch_assoc()['total'];

    $result = $conn->query(
        "SELECT COUNT(*) AS total FROM crew_members
         WHERE is_active = 1 AND work_status = 'Active'"
    );
    $stats['active_crew'] = (int)$result->fetch_assoc()['total'];
} catch (Throwable $e) {
    error_log('Dashboard crew statistics error: ' . $e->getMessage());
    $dashboardErrors[] = 'Crew statistics could not be loaded.';
}

try {
    $result = $conn->query("SELECT COUNT(*) AS total FROM schedules");
    $stats['total_schedules'] = (int)$result->fetch_assoc()['total'];

    $result = $conn->query(
        "SELECT COUNT(*) AS total FROM schedules WHERE status = 'published'"
    );
    $stats['published_schedules'] = (int)$result->fetch_assoc()['total'];
} catch (Throwable $e) {
    error_log('Dashboard schedule statistics error: ' . $e->getMessage());
    $dashboardErrors[] = 'Schedule statistics could not be loaded.';
}

try {
    $result = $conn->query(
        "SELECT s.id, s.shift_date, s.start_time, s.end_time,
                s.shift_type, s.status, c.name AS crew_name, c.position
         FROM schedules s
         LEFT JOIN crew_members c ON c.id = s.crew_id
         WHERE s.shift_date >= CURDATE()
         ORDER BY s.shift_date ASC, s.start_time ASC
         LIMIT 8"
    );

    while ($row = $result->fetch_assoc()) {
        $upcomingSchedules[] = $row;
    }
} catch (Throwable $e) {
    error_log('Dashboard upcoming schedules error: ' . $e->getMessage());
    $dashboardErrors[] = 'Upcoming schedules could not be loaded.';
}

try {
    $result = $conn->query(
        "SELECT id, name, email, position, work_status, created_at
         FROM crew_members ORDER BY id DESC LIMIT 5"
    );

    while ($row = $result->fetch_assoc()) {
        $recentCrew[] = $row;
    }
} catch (Throwable $e) {
    error_log('Dashboard recent crew error: ' . $e->getMessage());
}

try {
    $result = $conn->query("SELECT COUNT(*) AS total FROM inquiry");
    $stats['pending_inquiries'] = (int)$result->fetch_assoc()['total'];
} catch (Throwable $e) {
    error_log('Dashboard inquiry count error: ' . $e->getMessage());
}

function navActive(string $file): string {
    return basename($_SERVER['PHP_SELF']) === $file ? 'active' : '';
}

function displayTime($time): string {
    if (!$time) return '—';
    $timestamp = strtotime((string)$time);
    return $timestamp ? date('g:i A', $timestamp) : h($time);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard | McSchedulEase</title>
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

.main { margin-left: 68px; min-height: 100vh; }

.topbar {
    min-height: 65px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 15px 28px;
    background: rgba(245, 197, 66, 0.88);
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

/* Overview Banner Glassmorphism */
.overview {
    margin-bottom: 20px;
    padding: 23px;
    background: rgba(245, 197, 66, 0.88);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 5px solid var(--maroon);
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}
.overview small { font-size: 11px; font-weight: 800; letter-spacing: 2px; color: var(--maroon-dark); }
.overview h2 { margin: 10px 0 7px; font-size: 25px; color: var(--maroon-dark); }
.overview p { margin: 0; color: #3a0b15; font-size: 14px; line-height: 1.6; font-weight: 500; }

/* Stat Cards Glassmorphism */
.stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
.stat {
    padding: 20px;
    background: var(--white-glass);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--card-border);
    border-top: 4px solid var(--maroon);
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}
.stat-label { font-size: 12px; color: var(--muted); font-weight: 600; }
.stat-number { margin-top: 10px; font-size: 30px; font-weight: 800; color: var(--maroon); }
.stat-link { display: inline-block; margin-top: 13px; color: var(--maroon); font-size: 12px; font-weight: 700; text-decoration: none; }
.stat-link:hover { text-decoration: underline; }

.columns { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(280px, 1fr); gap: 20px; }

/* Dashboard Panels Glassmorphism */
.panel {
    background: var(--white-glass);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--card-border);
    border-radius: 13px;
    overflow: hidden;
    margin-bottom: 20px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}
.panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 20px 22px; border-bottom: 1px solid var(--border); }
.panel-head h2 { margin: 0; font-size: 18px; color: var(--maroon); }
.panel-head a { font-size: 12px; font-weight: 700; color: var(--maroon); text-decoration: none; }

.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; min-width: 520px; }
th, td { padding: 13px 15px; border-bottom: 1px solid var(--border); text-align: left; font-size: 12px; vertical-align: middle; }
th { background: rgba(245, 197, 66, 0.4); color: var(--maroon-dark); font-size: 10px; text-transform: uppercase; letter-spacing: .4px; font-weight: 800; }
tbody tr:hover { background: rgba(245, 197, 66, 0.15); }

.badge { display: inline-block; padding: 5px 8px; border-radius: 20px; font-size: 10px; font-weight: 800; white-space: nowrap; }
.badge-published, .badge-active { background: #1B5E20; color: #FFFFFF; }
.badge-draft { background: var(--yellow); color: var(--maroon-dark); }
.badge-other { background: #616161; color: #FFFFFF; }

.empty { padding: 25px; text-align: center; color: var(--muted); font-size: 13px; font-weight: 600; }

.quick-links { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; padding: 18px; }
.quick-link {
    padding: 16px;
    border: 1px solid var(--border);
    border-radius: 10px;
    text-decoration: none;
    color: var(--maroon-dark);
    background: rgba(255, 255, 255, 0.6);
    transition: all 0.2s ease;
}
.quick-link:hover { border-color: var(--maroon); background: rgba(245, 197, 66, 0.35); transform: translateY(-2px); }
.quick-link strong { display: block; font-size: 13px; color: var(--maroon); }
.quick-link span { display: block; margin-top: 6px; font-size: 11px; line-height: 1.5; color: var(--muted); }

.notice { padding: 13px 15px; margin-bottom: 18px; background: rgba(245, 197, 66, 0.9); border: 1px solid var(--maroon); border-radius: 8px; color: var(--maroon-dark); font-size: 13px; font-weight: 600; }

footer { text-align: center; padding: 18px; color: #FFFFFF; font-size: 12px; text-shadow: 0 1px 4px rgba(0,0,0,0.6); }

@media(max-width:1100px){ .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } .columns { grid-template-columns: 1fr; } }
@media(max-width:700px){ .sidebar { width: 0; } .sidebar:hover, .sidebar:focus-within { width: 250px; } .main { margin-left: 0; } .content { padding: 15px; } .topbar { padding: 15px; } .stats { grid-template-columns: 1fr 1fr; } .overview h2 { font-size: 21px; } h1 { font-size: 24px; } }
@media(max-width:420px){ .stats { grid-template-columns: 1fr; } .quick-links { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-brand"><div class="brand-icon">M</div><div class="brand-text"><strong>McSchedulEase</strong><small>MANAGER PORTAL</small></div></div>
    <div class="nav-label">Main Menu</div>
    <a class="nav-link <?= navActive('Dashboard.php') ?>" href="Dashboard.php"><span class="nav-icon">▦</span><span class="nav-text">Dashboard</span></a>
    <a class="nav-link <?= navActive('Crew_Management.php') ?>" href="Crew_Management.php"><span class="nav-icon">♙</span><span class="nav-text">Crew Management</span></a>
    <a class="nav-link <?= navActive('Schedule_Management.php') ?>" href="Schedule_Management.php"><span class="nav-icon">▣</span><span class="nav-text">Schedule Management</span></a>
    <a class="nav-link <?= navActive('Inquiry.php') ?>" href="Inquiry.php"><span class="nav-icon">✉</span><span class="nav-text">Inquiries / Feedback</span></a>
    <a class="nav-link <?= navActive('Admin Account.php') ?>" href="Admin%20Account.php"><span class="nav-icon">⚙</span><span class="nav-text">Admin Account</span></a>
    <div class="nav-label">Account</div>
    <a class="nav-link" href="logout.php"><span class="nav-icon">↪</span><span class="nav-text">Log Out</span></a>
</aside>

<main class="main">
<header class="topbar"><div class="topbar-title">McSchedulEase</div><div class="topbar-user">Welcome, <?= h($managerName) ?></div></header>
<div class="content">
    <h1>Dashboard</h1>
    <p class="subtitle">Monitor crew members, schedules, and crew feedback from one place.</p>

    <?php foreach ($dashboardErrors as $error): ?>
        <div class="notice"><?= h($error) ?></div>
    <?php endforeach; ?>

    <section class="overview">
        <small>MANAGER OVERVIEW</small>
        <h2>Welcome back, <?= h($managerName) ?>!</h2>
        <p>Manage your crew, organize work schedules, and review crew inquiries and feedback using the menu.</p>
    </section>

    <section class="stats">
        <div class="stat"><div class="stat-label">Total Crew Members</div><div class="stat-number"><?= $stats['total_crew'] ?></div><a class="stat-link" href="Crew_Management.php">Manage crew →</a></div>
        <div class="stat"><div class="stat-label">Active Crew Members</div><div class="stat-number"><?= $stats['active_crew'] ?></div><a class="stat-link" href="Crew_Management.php">View crew →</a></div>
        <div class="stat"><div class="stat-label">Total Schedules</div><div class="stat-number"><?= $stats['total_schedules'] ?></div><a class="stat-link" href="Schedule_Management.php">Manage schedules →</a></div>
        <div class="stat"><div class="stat-label">Crew Inquiries</div><div class="stat-number"><?= $stats['pending_inquiries'] ?></div><a class="stat-link" href="Inquiry.php">View inquiries →</a></div>
    </section>

    <div class="columns">
        <section class="panel">
            <div class="panel-head"><h2>Upcoming Schedules</h2><a href="Schedule_Management.php">View all →</a></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Crew Member</th><th>Date</th><th>Shift</th><th>Time</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (!$upcomingSchedules): ?>
                        <tr><td colspan="5" class="empty">No upcoming schedules found.</td></tr>
                    <?php else: foreach ($upcomingSchedules as $schedule): ?>
                        <tr>
                            <td><?= h($schedule['crew_name'] ?? 'Unassigned') ?></td>
                            <td><?= h(date('M j, Y', strtotime($schedule['shift_date']))) ?></td>
                            <td><?= h($schedule['shift_type'] ?? '—') ?></td>
                            <td><?= h(displayTime($schedule['start_time'])) ?> – <?= h(displayTime($schedule['end_time'])) ?></td>
                            <td><span class="badge <?= strtolower((string)$schedule['status']) === 'published' ? 'badge-published' : 'badge-draft' ?>"><?= h(ucfirst((string)$schedule['status'])) ?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <div class="panel">
                <div class="panel-head"><h2>Quick Actions</h2></div>
                <div class="quick-links">
                    <a class="quick-link" href="Crew_Management.php"><strong>Crew Management</strong><span>Add crew accounts or update work status.</span></a>
                    <a class="quick-link" href="Schedule_Management.php"><strong>Manage Schedules</strong><span>Create, edit, and publish shifts.</span></a>
                    <a class="quick-link" href="Inquiry.php"><strong>Inquiries / Feedback</strong><span>Review crew concerns and feedback.</span></a>
                    <a class="quick-link" href="Admin%20Account.php"><strong>Admin Account</strong><span>View administrator accounts.</span></a>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head"><h2>Recently Added Crew</h2><a href="Crew_Management.php">View all →</a></div>
                <?php if (!$recentCrew): ?>
                    <div class="empty">No crew members found.</div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Name</th><th>Position</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($recentCrew as $crew): ?>
                                <tr>
                                    <td><?= h($crew['name']) ?></td>
                                    <td><?= h($crew['position']) ?></td>
                                    <td><span class="badge <?= ($crew['work_status'] ?? '') === 'Active' ? 'badge-active' : 'badge-other' ?>"><?= h($crew['work_status'] ?? 'Unknown') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <footer>McSchedulEase © <?= date('Y') ?> | Manager Portal</footer>
</div>
</main>
</body>
</html>