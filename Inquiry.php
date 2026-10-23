<?php
require_once 'session.php';
require_once 'connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function navActive(string $file): string {
    return basename($_SERVER['PHP_SELF']) === $file ? 'active' : '';
}

$managerName = $_SESSION['name'] ?? $_SESSION['email'] ?? 'Manager';
$search = trim($_GET['search'] ?? '');
$inquiries = [];
$pageError = '';

try {
    if ($search !== '') {
        $term = '%' . $search . '%';

        $stmt = $conn->prepare(
            "SELECT id, FNAME, LNAME, PNUM, EMAIL, INQUIRY, `Date`
             FROM inquiry
             WHERE FNAME LIKE ?
                OR LNAME LIKE ?
                OR PNUM LIKE ?
                OR EMAIL LIKE ?
                OR INQUIRY LIKE ?
             ORDER BY id DESC"
        );

        $stmt->bind_param('sssss', $term, $term, $term, $term, $term);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query(
            "SELECT id, FNAME, LNAME, PNUM, EMAIL, INQUIRY, `Date`
             FROM inquiry
             ORDER BY id DESC"
        );
    }

    while ($row = $result->fetch_assoc()) {
        $inquiries[] = $row;
    }

    if (isset($stmt)) {
        $stmt->close();
    }
} catch (Throwable $e) {
    error_log('Inquiry page error: ' . $e->getMessage());
    $pageError = 'Inquiry records could not be loaded. Please check the database.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inquiries / Feedback | McSchedulEase</title>

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

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    font-family: Arial, Helvetica, sans-serif;
    color: var(--text);
    background:
        linear-gradient(rgba(20, 10, 15, 0.45), rgba(20, 10, 15, 0.45)),
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

.sidebar:hover,
.sidebar:focus-within {
    width: 250px;
}

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

.brand-text,
.nav-text,
.nav-label {
    opacity: 0;
    visibility: hidden;
    transition: opacity .15s;
}

.sidebar:hover .brand-text,
.sidebar:hover .nav-text,
.sidebar:hover .nav-label,
.sidebar:focus-within .brand-text,
.sidebar:focus-within .nav-text,
.sidebar:focus-within .nav-label {
    opacity: 1;
    visibility: visible;
}

.brand-text strong {
    display: block;
    font-size: 16px;
    color: var(--maroon-dark);
}

.brand-text small {
    display: block;
    margin-top: 4px;
    font-size: 10px;
    letter-spacing: 1px;
}

.nav-label {
    margin: 25px 18px 10px;
    font-size: 10px;
    letter-spacing: 1px;
    white-space: nowrap;
    font-weight: bold;
    color: var(--maroon-dark);
}

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

.nav-link:hover {
    background: rgba(255, 255, 255, 0.5);
    color: var(--maroon);
}

.nav-link.active {
    background: var(--maroon);
    color: white;
    border-left-color: var(--yellow);
    font-weight: 700;
}

.nav-icon {
    width: 26px;
    min-width: 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
}

.nav-text {
    font-size: 13px;
}

/* MAIN CONTENT */
.main {
    margin-left: 68px;
    min-height: 100vh;
}

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

.topbar-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--maroon);
}

.topbar-user {
    font-size: 13px;
    color: var(--maroon-dark);
    font-weight: 600;
}

.content {
    max-width: 1600px;
    margin: auto;
    padding: 28px;
}

h1 {
    margin: 0 0 8px;
    color: #FFFFFF;
    font-size: 28px;
    text-shadow: 0 2px 6px rgba(0,0,0,0.5);
}

.subtitle {
    margin: 0 0 24px;
    color: #FFF4C7;
    font-size: 14px;
    line-height: 1.6;
    text-shadow: 0 1px 4px rgba(0,0,0,0.5);
}

/* OVERVIEW BANNER */
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

.overview small {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    color: var(--maroon-dark);
}

.overview h2 {
    margin: 10px 0 7px;
    font-size: 25px;
    color: var(--maroon-dark);
}

.overview p {
    margin: 0;
    color: #3a0b15;
    font-size: 14px;
    line-height: 1.6;
    font-weight: 500;
}

/* GLASS PANELS */
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

.panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 20px 22px;
    border-bottom: 1px solid var(--border);
}

.panel-head h2 {
    margin: 0;
    font-size: 18px;
    color: var(--maroon);
}

.panel-body {
    padding: 22px;
}

/* SEARCH */
.search-form {
    display: flex;
    gap: 10px;
    margin-bottom: 18px;
}

.search-form input {
    flex: 1;
    min-width: 0;
    padding: 12px 14px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.85);
    color: var(--text);
    font: inherit;
    outline: none;
}

.search-form input:focus {
    border-color: var(--maroon);
    box-shadow: 0 0 0 3px rgba(112, 28, 44, 0.1);
}

.btn {
    display: inline-block;
    padding: 11px 16px;
    border: none;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}

.btn-primary {
    background: var(--maroon);
    color: white;
}

.btn-primary:hover {
    background: var(--maroon-dark);
    color: white;
}

.btn-reset {
    background: rgba(245, 197, 66, 0.6);
    color: var(--maroon-dark);
}

.btn-reset:hover {
    background: var(--yellow);
    color: var(--maroon-dark);
    text-decoration: none;
}

.result-count {
    margin-bottom: 12px;
    color: var(--muted);
    font-size: 12px;
    font-weight: 600;
}

/* TABLE */
.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 850px;
}

th,
td {
    padding: 13px 15px;
    border-bottom: 1px solid var(--border);
    text-align: left;
    font-size: 12px;
    vertical-align: middle;
}

th {
    background: rgba(245, 197, 66, 0.4);
    color: var(--maroon-dark);
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .4px;
    font-weight: 800;
    white-space: nowrap;
}

tbody tr:hover {
    background: rgba(245, 197, 66, 0.15);
}

.inquiry-text {
    min-width: 200px;
    max-width: 320px;
    overflow-wrap: anywhere;
    line-height: 1.5;
}

.action-link {
    display: inline-block;
    padding: 7px 10px;
    border-radius: 6px;
    background: var(--maroon);
    color: white;
    text-decoration: none;
    font-size: 11px;
    font-weight: 700;
}

.action-link:hover {
    background: var(--maroon-dark);
    color: white;
    text-decoration: none;
}

.empty {
    padding: 25px;
    text-align: center;
    color: var(--muted);
    font-size: 13px;
    font-weight: 600;
}

.notice {
    padding: 13px 15px;
    margin-bottom: 18px;
    background: rgba(245, 197, 66, 0.9);
    border: 1px solid var(--maroon);
    border-radius: 8px;
    color: var(--maroon-dark);
    font-size: 13px;
    font-weight: 600;
}

.notice.error {
    background: rgba(255, 235, 238, 0.95);
    color: #7f1d1d;
    border-color: #b91c1c;
}

footer {
    text-align: center;
    padding: 18px;
    color: #FFFFFF;
    font-size: 12px;
    text-shadow: 0 1px 4px rgba(0,0,0,0.6);
}

@media(max-width: 700px) {
    .sidebar {
        width: 0;
    }

    .sidebar:hover,
    .sidebar:focus-within {
        width: 250px;
    }

    .main {
        margin-left: 0;
    }

    .content {
        padding: 15px;
    }

    .topbar {
        padding: 15px;
    }

    h1 {
        font-size: 24px;
    }

    .overview h2 {
        font-size: 21px;
    }

    .search-form {
        flex-wrap: wrap;
    }

    .search-form input {
        flex-basis: 100%;
    }
}
</style>
</head>

<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">M</div>
        <div class="brand-text">
            <strong>McSchedulEase</strong>
            <small>MANAGER PORTAL</small>
        </div>
    </div>

    <div class="nav-label">Main Menu</div>

    <a class="nav-link <?= navActive('Dashboard.php') ?>" href="Dashboard.php">
        <span class="nav-icon">▦</span>
        <span class="nav-text">Dashboard</span>
    </a>

    <a class="nav-link <?= navActive('Crew_Management.php') ?>" href="Crew_Management.php">
        <span class="nav-icon">♙</span>
        <span class="nav-text">Crew Management</span>
    </a>

    <a class="nav-link <?= navActive('Schedule_Management.php') ?>" href="Schedule_Management.php">
        <span class="nav-icon">▣</span>
        <span class="nav-text">Schedule Management</span>
    </a>

    <a class="nav-link <?= navActive('Inquiry.php') ?>" href="Inquiry.php">
        <span class="nav-icon">✉</span>
        <span class="nav-text">Inquiries / Feedback</span>
    </a>

    <a class="nav-link <?= navActive('Admin Account.php') ?>" href="Admin%20Account.php">
        <span class="nav-icon">⚙</span>
        <span class="nav-text">Admin Account</span>
    </a>

    <div class="nav-label">Account</div>

    <a class="nav-link" href="logout.php">
        <span class="nav-icon">↪</span>
        <span class="nav-text">Log Out</span>
    </a>
</aside>

<main class="main">

    <header class="topbar">
        <div class="topbar-title">McSchedulEase</div>
        <div class="topbar-user">Welcome, <?= h($managerName) ?></div>
    </header>

    <div class="content">

        <h1>Inquiries / Feedback</h1>
        <p class="subtitle">
            Review customer inquiries and feedback from one place.
        </p>

        <?php if ($pageError !== ''): ?>
            <div class="notice error"><?= h($pageError) ?></div>
        <?php endif; ?>

        <section class="panel">
            <div class="panel-head">
                <h2>All Inquiries</h2>
            </div>

            <div class="panel-body">

                <form method="GET" action="Inquiry.php" class="search-form">
                    <input
                        type="search"
                        name="search"
                        placeholder="Search name, phone, email, or message..."
                        value="<?= h($search) ?>"
                    >

                    <button type="submit" class="btn btn-primary">
                        Search
                    </button>

                    <?php if ($search !== ''): ?>
                        <a href="Inquiry.php" class="btn btn-reset">Reset</a>
                    <?php endif; ?>
                </form>

                <div class="result-count">
                    <?= count($inquiries) ?> record(s) found
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Phone Number</th>
                                <th>Email</th>
                                <th>Inquiry / Feedback</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (!$pageError && !$inquiries): ?>
                            <tr>
                                <td colspan="7" class="empty">
                                    No inquiry records found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inquiries as $inquiry): ?>
                                <tr>
                                    <td><?= h($inquiry['id']) ?></td>
                                    <td>
                                        <?= h($inquiry['FNAME']) ?>
                                        <?= h($inquiry['LNAME']) ?>
                                    </td>
                                    <td><?= h($inquiry['PNUM']) ?></td>
                                    <td><?= h($inquiry['EMAIL']) ?></td>
                                    <td class="inquiry-text">
                                        <?= h($inquiry['INQUIRY']) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $date = $inquiry['Date'] ?? '';
                                        echo (
                                            $date !== '' &&
                                            $date !== '0000-00-00' &&
                                            strtotime($date)
                                        ) ? h(date('M j, Y', strtotime($date))) : '—';
                                        ?>
                                    </td>
                                    <td>
                                        <a
                                            class="action-link"
                                            href="inquiry%20view.php?viewmsg=<?= urlencode((string)$inquiry['id']) ?>"
                                        >View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>

        <footer>
            McSchedulEase © <?= date('Y') ?> | Manager Portal
        </footer>

    </div>
</main>

</body>
</html>