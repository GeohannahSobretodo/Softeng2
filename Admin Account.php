 <?php
require_once 'session.php';
require_once 'connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function navActive(string $file): string
{
    return strcasecmp(basename($_SERVER['PHP_SELF']), $file) === 0 ? 'active' : '';
}

$adminAccounts = [];
$totalAdmins = 0;
$errorMessage = '';

try {
    $result = $conn->query(
        "SELECT id, Name, email, Contact
         FROM admin
         ORDER BY id DESC"
    );

    while ($row = $result->fetch_assoc()) {
        $adminAccounts[] = $row;
    }

    $totalAdmins = count($adminAccounts);
} catch (Throwable $e) {
    error_log('Admin Account page error: ' . $e->getMessage());
    $errorMessage = 'Unable to load admin accounts. Please check the database connection.';
}

$managerName = $_SESSION['Name']
    ?? $_SESSION['name']
    ?? $_SESSION['email']
    ?? 'Manager';

$flashMessage = $_SESSION['admin_accounts_flash'] ?? '';
unset($_SESSION['admin_accounts_flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Account | McSchedulEase</title>

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
    font-family: Arial, Helvetica, sans-serif;
    color: var(--text);
    background:
        linear-gradient(rgba(20, 10, 15, 0.45), rgba(20, 10, 15, 0.45)),
        url('loginbg.jpg') center/cover fixed no-repeat;
    min-height: 100vh;
}

/* SIDEBAR */
.sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    z-index: 1000;
    width: 68px;
    height: 100vh;
    overflow-x: hidden;
    overflow-y: auto;
    background: rgba(245, 197, 66, 0.88);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    box-shadow: 3px 0 20px rgba(0, 0, 0, 0.15);
    transition: width 0.25s ease;
}

.sidebar:hover,
.sidebar:focus-within,
.sidebar.expanded {
    width: 250px;
}

.sidebar-brand {
    height: 70px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 15px;
    white-space: nowrap;
    border-bottom: 1px solid rgba(77, 16, 29, 0.15);
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
.nav-label,
.manager-details {
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.15s ease;
}

.sidebar:hover .brand-text,
.sidebar:hover .nav-text,
.sidebar:hover .nav-label,
.sidebar:hover .manager-details,
.sidebar:focus-within .brand-text,
.sidebar:focus-within .nav-text,
.sidebar:focus-within .nav-label,
.sidebar:focus-within .manager-details,
.sidebar.expanded .brand-text,
.sidebar.expanded .nav-text,
.sidebar.expanded .nav-label,
.sidebar.expanded .manager-details {
    opacity: 1;
    visibility: visible;
}

.brand-text strong {
    display: block;
    color: var(--maroon-dark);
    font-size: 16px;
}

.brand-text small {
    display: block;
    margin-top: 4px;
    font-size: 10px;
    letter-spacing: 1px;
    color: var(--maroon-dark);
}

.manager-info {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 68px;
    padding: 12px 10px;
    border-bottom: 1px solid rgba(77, 16, 29, 0.15);
    white-space: nowrap;
}

.manager-icon {
    width: 38px;
    height: 38px;
    min-width: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--maroon);
    color: var(--yellow);
    font-weight: bold;
}

.manager-details {
    font-size: 13px;
    color: var(--maroon-dark);
}

.manager-details small {
    display: block;
    margin-top: 4px;
    font-size: 11px;
}

.nav-label {
    margin: 25px 18px 10px;
    font-size: 10px;
    letter-spacing: 1px;
    white-space: nowrap;
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
    transition: background 0.2s ease, color 0.2s ease;
}

.nav-link:hover {
    background: rgba(255, 229, 138, 0.95);
    color: var(--maroon);
}

.nav-link.active {
    background: var(--maroon);
    color: #FFFFFF;
    border-left-color: #FFFFFF;
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
    font-size: 14px;
}

.sidebar-bottom {
    margin-top: 25px;
    padding-top: 12px;
    border-top: 1px solid rgba(77, 16, 29, 0.16);
}

/* MAIN CONTENT */
.main {
    margin-left: 68px;
    min-width: 0;
    min-height: 100vh;
    transition: margin-left 0.25s ease;
}

.topbar {
    min-height: 70px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 15px 28px;
    background: rgba(245, 197, 66, 0.86);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.4);
}

.topbar-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--maroon);
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.topbar-user {
    font-size: 13px;
    color: var(--maroon-dark);
}

.content {
    max-width: 1600px;
    margin: auto;
    padding: 28px;
}

/* BUTTONS */
.logout-btn,
.create-btn {
    display: inline-block;
    padding: 11px 16px;
    border: 0;
    border-radius: 8px;
    background: var(--maroon);
    color: #FFFFFF;
    text-decoration: none;
    font-size: 13px;
    font-weight: bold;
    white-space: nowrap;
    transition: 0.2s ease;
}

.logout-btn:hover {
    background: var(--maroon-dark);
    color: var(--yellow);
}

.create-btn {
    background: var(--maroon);
    color: #FFFFFF;
}

.create-btn:hover {
    background: var(--maroon-dark);
    color: var(--yellow);
}

/* STATISTICS */
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    padding: 23px;
    background: var(--yellow-light);
    border: 1px solid var(--card-border);
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.stat-label {
    color: var(--maroon-dark);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.5px;
}

.stat-number {
    margin-top: 10px;
    color: var(--maroon);
    font-size: 34px;
    font-weight: 800;
}

/* ADMIN TABLE PANEL */
.panel {
    padding: 24px;
    margin-bottom: 24px;
    background: var(--white-glass);
    border: 1px solid var(--card-border);
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.14);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
}

.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
}

.panel-header h2 {
    margin: 0;
    color: var(--maroon);
    font-size: 20px;
}

.panel-header p {
    margin: 7px 0 0;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.5;
}

.search {
    width: 280px;
    max-width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--border);
    border-radius: 9px;
    outline: none;
    background: rgba(255, 255, 255, 0.9);
    font-size: 13px;
}

.search:focus {
    border-color: var(--maroon);
    box-shadow: 0 0 0 3px rgba(112, 28, 44, 0.1);
}

.table-wrap {
    width: 100%;
    overflow-x: auto;
    border-radius: 9px;
}

table {
    width: 100%;
    min-width: 650px;
    border-collapse: collapse;
    background: rgba(255, 255, 255, 0.45);
}

thead {
    background: var(--maroon);
    color: #FFFFFF;
}

th,
td {
    padding: 14px 12px;
    text-align: left;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
    vertical-align: middle;
}

th {
    font-size: 11px;
    letter-spacing: 0.4px;
    white-space: nowrap;
}

tbody tr {
    transition: background 0.15s ease;
}

tbody tr:hover {
    background: rgba(255, 244, 199, 0.8);
}

.admin-name {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: bold;
}

.avatar {
    width: 36px;
    height: 36px;
    min-width: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--yellow-light);
    color: var(--maroon);
    border: 1px solid rgba(112, 28, 44, 0.12);
    font-weight: bold;
}

.actions {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-block;
    padding: 8px 11px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: bold;
    text-decoration: none;
    white-space: nowrap;
    transition: opacity 0.2s ease;
}

.edit-btn {
    background: var(--yellow);
    color: var(--maroon-dark);
}

.view-btn {
    background: #F3E7E9;
    color: var(--maroon);
}

.delete-btn {
    background: var(--maroon);
    color: #FFFFFF;
}

.action-btn:hover {
    opacity: 0.78;
}

.message {
    padding: 14px 16px;
    margin-bottom: 20px;
    border: 1px solid rgba(245, 197, 66, 0.8);
    border-radius: 9px;
    background: var(--yellow-light);
    color: var(--maroon-dark);
    font-size: 13px;
    line-height: 1.5;
}

.error-message {
    background: rgba(251, 232, 232, 0.95);
    border-color: #F2CACA;
    color: #991B1B;
}

.empty {
    padding: 35px 15px;
    text-align: center;
    color: var(--muted);
}

.footer-note {
    margin-top: 18px;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.6;
}

footer {
    padding: 18px;
    text-align: center;
    color: #FFFFFF;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.65);
    font-size: 12px;
}

/* RESPONSIVE */
@media (max-width: 800px) {
    .content {
        padding: 17px;
    }

    .topbar {
        padding: 15px;
    }

    .topbar-user {
        display: none;
    }
}

@media (max-width: 700px) {
    .sidebar {
        width: 0;
    }

    .sidebar:hover,
    .sidebar:focus-within,
    .sidebar.expanded {
        width: 250px;
    }

    .main {
        margin-left: 0;
    }

    .content {
        padding: 15px;
    }

    .panel {
        padding: 16px;
    }

    .panel-header {
        align-items: stretch;
        flex-direction: column;
    }

    .search {
        width: 100%;
    }

    .topbar-title {
        font-size: 17px;
    }

    .create-btn {
        padding: 10px 12px;
        font-size: 12px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        transition: none !important;
    }
}
</style>
</head>

<body>

<aside class="sidebar" id="managerSidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">M</div>
        <div class="brand-text">
            <strong>McSchedulEase</strong>
            <small>MANAGER PORTAL</small>
        </div>
    </div>

    <div class="manager-info">
        <div class="manager-icon">
            <?= h(strtoupper(substr(trim((string)$managerName), 0, 1) ?: 'M')) ?>
        </div>

        <div class="manager-details">
            <?= h($managerName) ?>
            <small>Manager Account</small>
        </div>
    </div>

    <div class="nav-label">MAIN MENU</div>

    <a class="nav-link <?= navActive('Dashboard.php') ?>"
       href="Dashboard.php" title="Dashboard">
        <span class="nav-icon">▦</span>
        <span class="nav-text">Dashboard</span>
    </a>

    <a class="nav-link <?= navActive('Crew_Management.php') ?>"
       href="Crew_Management.php" title="Crew Management">
        <span class="nav-icon">♙</span>
        <span class="nav-text">Crew Management</span>
    </a>

    <a class="nav-link <?= navActive('Schedule_Management.php') ?>"
       href="Schedule_Management.php" title="Schedule Management">
        <span class="nav-icon">▣</span>
        <span class="nav-text">Schedule Management</span>
    </a>

    <a class="nav-link <?= navActive('Inquiry.php') ?>"
       href="Inquiry.php" title="Inquiries / Feedback">
        <span class="nav-icon">✉</span>
        <span class="nav-text">Inquiries / Feedback</span>
    </a>

    <a class="nav-link <?= navActive('Admin Account.php') ?>"
       href="Admin%20Account.php" title="Admin Account">
        <span class="nav-icon">⚙</span>
        <span class="nav-text">Admin Account</span>
    </a>

    <div class="sidebar-bottom">
        <a href="logout.php" class="nav-link" title="Log Out">
            <span class="nav-icon">↪</span>
            <span class="nav-text">Log Out</span>
        </a>
    </div>
</aside>

<main class="main">
    <header class="topbar">
        <div class="topbar-title">Admin Account</div>

        <div class="topbar-right">
            <span class="topbar-user">
                Welcome, <?= h($managerName) ?>
            </span>

            <a href="CreateAccount.php" class="create-btn">
                + Create Admin
            </a>
        </div>
    </header>

    <div class="content">

        <?php if ($errorMessage !== ''): ?>
            <div class="message error-message">
                <?= h($errorMessage) ?>
            </div>
        <?php endif; ?>

        <?php if ($flashMessage !== ''): ?>
            <div class="message">
                <?= h($flashMessage) ?>
            </div>
        <?php endif; ?>

        <section class="stats">
            <div class="stat-card">
                <div class="stat-label">TOTAL ADMIN ACCOUNTS</div>
                <div class="stat-number"><?= (int)$totalAdmins ?></div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>Administrator List</h2>
                    <p>View and manage registered administrator accounts.</p>
                </div>

                <input
                    type="search"
                    id="adminSearch"
                    class="search"
                    placeholder="Search name, email, or contact..."
                    aria-label="Search admin accounts"
                >
            </div>

            <div class="table-wrap">
                <table id="adminTable">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Email Address</th>
                            <th>Contact Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($adminAccounts): ?>
                        <?php foreach ($adminAccounts as $admin): ?>
                            <?php
                            $adminId = (int)$admin['id'];
                            $adminName = (string)($admin['Name'] ?? '');
                            $initial = strtoupper(
                                substr(trim($adminName) !== '' ? trim($adminName) : 'A', 0, 1)
                            );
                            ?>

                            <tr>
                                <td>
                                    <div class="admin-name">
                                        <span class="avatar"><?= h($initial) ?></span>

                                        <span>
                                            <?= h($adminName) ?>
                                            <br>
                                            <small style="color:var(--muted);font-weight:normal">
                                                ID: <?= $adminId ?>
                                            </small>
                                        </span>
                                    </div>
                                </td>

                                <td><?= h($admin['email'] ?? '') ?></td>
                                <td><?= h($admin['Contact'] ?? '') ?></td>

                                <td>
                                    <div class="actions">
                                        <a
                                            class="action-btn edit-btn"
                                            href="UpdateAdmin.php?updatesadmin=<?= $adminId ?>"
                                        >Edit</a>

                                        <a
                                            class="action-btn view-btn"
                                            href="view_accounts.php?updateadmin=<?= $adminId ?>"
                                        >View</a>

                                        <a
                                            class="action-btn delete-btn"
                                            href="delete_admin.php?deleteaccount=<?= $adminId ?>"
                                            onclick="return confirm('Are you sure you want to delete this admin account?');"
                                        >Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty">
                                No administrator accounts were found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="footer-note">
                For crew inquiries, concerns, and satisfaction feedback, select
                <strong>Inquiries / Feedback</strong> from the sidebar.
                Administrator passwords are not displayed on this page.
            </p>
        </section>

        <footer>
            McSchedulEase © <?= date('Y') ?> | Manager Portal
        </footer>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('adminSearch');
    const tableRows = document.querySelectorAll('#adminTable tbody tr');
    const sidebar = document.getElementById('managerSidebar');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();

            tableRows.forEach(function (row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    if (sidebar && window.innerWidth <= 700) {
        sidebar.addEventListener('click', function (event) {
            if (!event.target.closest('a')) {
                sidebar.classList.toggle('expanded');
            }
        });
    }
});
</script>

</body>
</html>