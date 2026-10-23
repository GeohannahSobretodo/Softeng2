<?php
require_once 'session.php';
require_once 'connect.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (empty($_SESSION['sched_csrf'])) {
    $_SESSION['sched_csrf'] = bin2hex(random_bytes(32));
}
function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function schedRedirect(string $message, string $type = 'success'): void {
    $_SESSION['sched_message'] = $message;
    $_SESSION['sched_message_type'] = $type;
    header('Location: Schedule_Management.php');
    exit;
}
function validDate(string $date): bool {
    $d = DateTime::createFromFormat('!Y-m-d', $date);
    return $d !== false && $d->format('Y-m-d') === $date;
}
function shiftInterval(string $date, string $start, string $end): array {
    $startAt = new DateTimeImmutable($date . ' ' . $start);
    $endAt = new DateTimeImmutable($date . ' ' . $end);

    if ($endAt <= $startAt) {
        $endAt = $endAt->modify('+1 day');
    }

    return [$startAt, $endAt];
}
function logScheduleAction(
    mysqli $conn,
    ?int $adminId,
    string $action,
    string $details
): void {
    $stmt = $conn->prepare(
        'INSERT INTO schedule_audit_log
        (actor_admin_id, action, details)
        VALUES (?, ?, ?)'
    );

    $stmt->bind_param('iss', $adminId, $action, $details);
    $stmt->execute();
    $stmt->close();
}
function validateSchedule(
    mysqli $conn,
    int $crewId,
    ?int $stationId,
    string $shiftDate,
    string $startTime,
    string $endTime,
    string $shiftType,
    int $excludeId = 0
): ?string {

    $allowedShifts = [
        'Graveyard',
        'Morning',
        'Mid',
        'Closing'
    ];

    if ($crewId <= 0 || !validDate($shiftDate)) {
        return 'Please select a valid crew member and shift date.';
    }

    if (!in_array($shiftType, $allowedShifts, true)) {
        return 'Please select a valid shift category.';
    }

    if (
        !preg_match('/^\d{2}:\d{2}$/', $startTime) ||
        !preg_match('/^\d{2}:\d{2}$/', $endTime)
    ) {
        return 'Please enter valid start and end times.';
    }

    if ($startTime === $endTime) {
        return 'Start time and end time cannot be identical.';
    }

    $stmt = $conn->prepare(
        'SELECT id
         FROM crew_members
         WHERE id = ? AND is_active = 1'
    );

    $stmt->bind_param('i', $crewId);
    $stmt->execute();

    $crewExists = $stmt->get_result()->num_rows === 1;
    $stmt->close();

    if (!$crewExists) {
        return 'The selected crew member is not active or does not exist.';
    }

    if ($stationId !== null) {

        $stmt = $conn->prepare(
            'SELECT id
             FROM stations
             WHERE id = ? AND is_active = 1'
        );

        $stmt->bind_param('i', $stationId);
        $stmt->execute();

        $stationExists = $stmt->get_result()->num_rows === 1;
        $stmt->close();

        if (!$stationExists) {
            return 'The selected station is not active or does not exist.';
        }

        $stmt = $conn->prepare(
            'SELECT id
             FROM crew_stations
             WHERE crew_id = ?
               AND station_id = ?
               AND is_trained = 1
             LIMIT 1'
        );

        $stmt->bind_param('ii', $crewId, $stationId);
        $stmt->execute();

        $isTrained = $stmt->get_result()->num_rows === 1;
        $stmt->close();

        if (!$isTrained) {
            return 'This crew member is not marked as trained for the selected station. Update Crew Stations first, or choose no station.';
        }
    }

    [$newStart, $newEnd] = shiftInterval(
        $shiftDate,
        $startTime,
        $endTime
    );

    $fromDate = (new DateTimeImmutable($shiftDate))
        ->modify('-1 day')
        ->format('Y-m-d');

    $toDate = (new DateTimeImmutable($shiftDate))
        ->modify('+1 day')
        ->format('Y-m-d');

    $stmt = $conn->prepare(
        'SELECT id, shift_date, start_time, end_time
         FROM schedules
         WHERE crew_id = ?
           AND shift_date BETWEEN ? AND ?
           AND id <> ?'
    );

    $stmt->bind_param(
        'issi',
        $crewId,
        $fromDate,
        $toDate,
        $excludeId
    );

    $stmt->execute();
    $result = $stmt->get_result();

    while ($existing = $result->fetch_assoc()) {

        if (
            empty($existing['start_time']) ||
            empty($existing['end_time'])
        ) {
            continue;
        }
        [$existingStart, $existingEnd] = shiftInterval(
            $existing['shift_date'],
            substr($existing['start_time'], 0, 5),
            substr($existing['end_time'], 0, 5)
        );
        if (
            $newStart < $existingEnd &&
            $newEnd > $existingStart
        ) {
            $stmt->close();
            return 'This crew member already has a schedule that overlaps the selected time.';
        }
    }
    $stmt->close();
    return null;
}
$message = $_SESSION['sched_message'] ?? '';
$messageType = $_SESSION['sched_message_type'] ?? 'success';
unset(
    $_SESSION['sched_message'],
    $_SESSION['sched_message_type']
);
$shiftTypes = [
    'Graveyard',
    'Morning',
    'Mid',
    'Closing'
];
$statuses = [
    'draft',
    'published'
];
$editSchedule = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !hash_equals(
            $_SESSION['sched_csrf'],
            $_POST['csrf_token'] ?? ''
        )
    ) {
        schedRedirect(
            'Security verification failed. Please try again.',
            'error'
        );
    }
    $action = $_POST['action'] ?? '';
    try {
        if (
            $action === 'add_schedule' ||
            $action === 'update_schedule'
        ) {
            $id = (int)($_POST['schedule_id'] ?? 0);
            $crewId = (int)($_POST['crew_id'] ?? 0);
            $stationInput = trim(
                (string)($_POST['station_id'] ?? '')
            );
            $stationId = $stationInput === ''
                ? null
                : (int)$stationInput;
            $shiftDate = trim(
                (string)($_POST['shift_date'] ?? '')
            );
            $startTime = trim(
                (string)($_POST['start_time'] ?? '')
            );
            $endTime = trim(
                (string)($_POST['end_time'] ?? '')
            );
            $shiftType = trim(
                (string)($_POST['shift_type'] ?? 'Morning')
            );
            $status = trim(
                (string)($_POST['status'] ?? 'draft')
            );
            $notes = trim(
                (string)($_POST['schedule_notes'] ?? '')
            );
            if (
                $action === 'update_schedule' &&
                $id <= 0
            ) {
                schedRedirect(
                    'Invalid schedule selected.',
                    'error'
                );
            }
            if (!in_array($status, $statuses, true)) {
                schedRedirect(
                    'Please select a valid schedule status.',
                    'error'
                );
            }
            if (
                $stationInput !== '' &&
                $stationId <= 0
            ) {
                schedRedirect(
                    'Please select a valid station.',
                    'error'
                );
            }
            if (strlen($notes) > 500) {
                schedRedirect(
                    'Notes must be 500 characters or fewer.',
                    'error'
                );
            }
            $validationError = validateSchedule(
                $conn,
                $crewId,
                $stationId,
                $shiftDate,
                $startTime,
                $endTime,
                $shiftType,
                $id
            );
            if ($validationError !== null) {
                schedRedirect($validationError, 'error');
            }
            if ($action === 'add_schedule') {
                $stmt = $conn->prepare(
                    'INSERT INTO schedules
                    (
                        crew_id,
                        station_id,
                        shift_date,
                        start_time,
                        end_time,
                        shift_type,
                        status,
                        schedule_notes
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param(
                    'iissssss',
                    $crewId,
                    $stationId,
                    $shiftDate,
                    $startTime,
                    $endTime,
                    $shiftType,
                    $status,
                    $notes
                );
                $stmt->execute();
                $newId = (int)$conn->insert_id;
                $stmt->close();
                $adminId = isset($_SESSION['admin_id'])
                    ? (int)$_SESSION['admin_id']
                    : null;
                logScheduleAction(
                    $conn,
                    $adminId,
                    'schedule_created',
                    'Created schedule #' . $newId .
                    ' for crew #' . $crewId .
                    ' on ' . $shiftDate .
                    ' (' . $shiftType . ', ' . $status . ').'
                );
                schedRedirect(
                    'Schedule created successfully.'
                );
            }
            $stmt = $conn->prepare(
                'UPDATE schedules
                 SET crew_id = ?,
                     station_id = ?,
                     shift_date = ?,
                     start_time = ?,
                     end_time = ?,
                     shift_type = ?,
                     status = ?,
                     schedule_notes = ?,
                     published_at = CASE
                         WHEN ? = "published"
                              AND published_at IS NULL
                         THEN NOW()
                         WHEN ? = "draft"
                         THEN NULL
                         ELSE published_at
                     END
                 WHERE id = ?'
            );
            $stmt->bind_param(
                'iissssssssi',
                $crewId,
                $stationId,
                $shiftDate,
                $startTime,
                $endTime,
                $shiftType,
                $status,
                $notes,
                $status,
                $status,
                $id
            );

            $stmt->execute();
            $stmt->close();
            $adminId = isset($_SESSION['admin_id'])
                ? (int)$_SESSION['admin_id']
                : null;

            logScheduleAction(
                $conn,
                $adminId,
                'schedule_updated',
                'Updated schedule #' . $id .
                ' for crew #' . $crewId .
                ' on ' . $shiftDate .
                ' (' . $shiftType . ', ' . $status . ').'
            );

            schedRedirect(
                'Schedule updated successfully.'
            );
        }
        if ($action === 'delete_schedule') {

            $id = (int)($_POST['schedule_id'] ?? 0);

            if ($id <= 0) {
                schedRedirect(
                    'Invalid schedule selected.',
                    'error'
                );
            }
            $stmt = $conn->prepare(
                'SELECT crew_id, shift_date, shift_type
                 FROM schedules
                 WHERE id = ?'
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $record = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$record) {
                schedRedirect(
                    'Schedule not found.',
                    'error'
                );
            }
            $stmt = $conn->prepare(
                'DELETE FROM schedules WHERE id = ?'
            );

            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $adminId = isset($_SESSION['admin_id'])
                ? (int)$_SESSION['admin_id']
                : null;

            logScheduleAction(
                $conn,
                $adminId,
                'schedule_deleted',
                'Deleted schedule #' . $id .
                ' for crew #' . $record['crew_id'] .
                ' on ' . $record['shift_date'] .
                ' (' . $record['shift_type'] . ').'
            );

            schedRedirect(
                'Schedule deleted successfully.'
            );
        }

    } catch (mysqli_sql_exception $e) {

        error_log(
            'Schedule management database error: ' .
            $e->getMessage()
        );
        if ((int)$e->getCode() === 1062) {
            schedRedirect(
                'This crew member already has a schedule for that date. The current database may permit only one schedule per crew member per day.',
                'error'
            );
        }

        schedRedirect(
            'A database error occurred. Check that the database structure matches this code.',
            'error'
        );
    } catch (Throwable $e) {

        error_log(
            'Schedule management error: ' .
            $e->getMessage()
        );

        schedRedirect(
            'An unexpected error occurred. Please try again.',
            'error'
        );
    }
}

if (isset($_GET['edit'])) {

    $editId = (int)$_GET['edit'];

    if ($editId > 0) {

        $stmt = $conn->prepare(
            'SELECT * FROM schedules WHERE id = ?'
        );
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $editSchedule = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        if (!$editSchedule) {
            $message = 'Schedule not found.';
            $messageType = 'error';
        }
    }
}
$crewList = [];
$crewRes = $conn->query(
    'SELECT id, name, position
     FROM crew_members
     WHERE is_active = 1
     ORDER BY name ASC'
);

while ($row = $crewRes->fetch_assoc()) {
    $crewList[] = $row;
}
$stationList = [];
$stationRes = $conn->query(
    'SELECT id, station_code, station_name, department
     FROM stations
     WHERE is_active = 1
     ORDER BY department, station_name'
);

while ($row = $stationRes->fetch_assoc()) {
    $stationList[] = $row;
}

$schedules = [];
$schedRes = $conn->query(
    'SELECT
        s.*,
        c.name AS crew_name,
        c.position,
        st.station_name,
        st.station_code
     FROM schedules s
     LEFT JOIN crew_members c ON c.id = s.crew_id
     LEFT JOIN stations st ON st.id = s.station_id
     ORDER BY s.shift_date DESC, s.start_time ASC'
);

while ($row = $schedRes->fetch_assoc()) {
    $schedules[] = $row;
}

$managerName = $_SESSION['name']
    ?? $_SESSION['email']
    ?? 'Manager';
$form = $editSchedule ?? [
    'id' => '',
    'crew_id' => '',
    'station_id' => '',
    'shift_date' => '',
    'start_time' => '',
    'end_time' => '',
    'shift_type' => 'Morning',
    'status' => 'draft',
    'schedule_notes' => ''
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Schedule Management | McSchedulEase</title>

<style>
:root {
    --maroon: #701C2C;
    --maroon-dark: #4D101D;
    --yellow: #F5C542;
    --white-glass: rgba(255,255,255,.88);
    --card-border: rgba(255,255,255,.5);
    --text: #30262A;
    --muted: #5C5255;
    --border: rgba(112,28,44,.15);
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
        linear-gradient(
            rgba(20,10,15,.45),
            rgba(20,10,15,.45)
        ),
        url('images/mcd-bg.jpg') center/cover fixed no-repeat;
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
    background: rgba(245,197,66,.9);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 4px 0 20px rgba(0,0,0,.25);
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
    border-bottom: 1px solid rgba(77,16,29,.2);
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
    background: rgba(255,255,255,.5);
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
.main {
    margin-left: 68px;
    min-width: 0;
    min-height: 100vh;
}
.topbar {
    min-height: 65px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 15px 28px;
    background: rgba(255,255,255,.88);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border);
    box-shadow: 0 2px 10px rgba(0,0,0,.1);
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
    color: #fff;
    font-size: 28px;
    text-shadow: 0 2px 6px rgba(0,0,0,.5);
}
.subtitle {
    margin: 0 0 24px;
    color: #FFF4C7;
    font-size: 14px;
    line-height: 1.6;
    text-shadow: 0 1px 4px rgba(0,0,0,.5);
}
.panel {
    background: var(--white-glass);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--card-border);
    border-radius: 13px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 8px 25px rgba(0,0,0,.15);
}
.panel h2 {
    margin: 0 0 20px;
    font-size: 20px;
    color: var(--maroon);
}
.form-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}
.field label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 700;
    color: var(--maroon-dark);
}
.field input,
.field select,
.field textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid rgba(112,28,44,.25);
    border-radius: 7px;
    background: rgba(255,255,255,.95);
    color: var(--text);
    font-size: 14px;
}

.field input:focus,
.field select:focus,
.field textarea:focus {
    outline: 2px solid var(--yellow);
    border-color: var(--maroon);
}

.field small {
    display: block;
    margin-top: 6px;
    color: var(--muted);
    font-size: 11px;
}

.full {
    grid-column: 1 / -1;
}

.btn {
    display: inline-block;
    border: 0;
    border-radius: 7px;
    padding: 10px 15px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: all .2s;
}

.btn-primary {
    background: var(--maroon);
    color: white;
}

.btn-primary:hover {
    background: var(--maroon-dark);
    color: var(--yellow);
}

.btn-danger {
    background: #C62828;
    color: white;
}

.btn-danger:hover {
    background: #8E0000;
}

.btn-secondary {
    background: #e8e1e3;
    color: var(--maroon-dark);
}

.notice {
    padding: 13px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 1.5;
    font-weight: 600;
}

.notice.success {
    background: rgba(245,197,66,.92);
    border: 1px solid var(--maroon);
    color: var(--maroon-dark);
}

.notice.error {
    background: rgba(254,226,226,.96);
    color: #991B1B;
    border: 1px solid #F87171;
}

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1050px;
}

th,
td {
    padding: 12px 10px;
    border-bottom: 1px solid var(--border);
    text-align: left;
    font-size: 13px;
    vertical-align: middle;
}

th {
    background: rgba(245,197,66,.4);
    color: var(--maroon-dark);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .4px;
    font-weight: 800;
}

tbody tr:hover {
    background: rgba(245,197,66,.15);
}

.badge {
    display: inline-block;
    padding: 6px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

.badge-published {
    background: #1B5E20;
    color: white;
}

.badge-draft {
    background: var(--yellow);
    color: var(--maroon-dark);
}

.empty {
    text-align: center;
    padding: 30px;
    color: var(--muted);
    font-weight: 600;
}

.actions {
    display: flex;
    gap: 6px;
    align-items: center;
}

.actions form {
    display: inline;
}

footer {
    text-align: center;
    padding: 18px;
    color: white;
    font-size: 12px;
    text-shadow: 0 1px 4px rgba(0,0,0,.6);
}

@media(max-width:900px) {
    .form-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media(max-width:700px) {
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

    h1 {
        font-size: 24px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .topbar {
        padding: 15px;
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

    <a class="nav-link" href="Dashboard.php">
        <span class="nav-icon">▦</span>
        <span class="nav-text">Dashboard</span>
    </a>

    <a class="nav-link" href="Crew_Management.php">
        <span class="nav-icon">♙</span>
        <span class="nav-text">Crew Management</span>
    </a>

    <a class="nav-link" href="Schedule_Management.php">
        <span class="nav-icon">▣</span>
        <span class="nav-text">Schedule Management</span>
    </a>

    <a class="nav-link" href="Inquiry.php">
        <span class="nav-icon">✉</span>
        <span class="nav-text">Inquiries / Feedback</span>
    </a>

    <a class="nav-link" href="Admin%20Account.php">
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

        <div class="topbar-user">
            Welcome, <?= h($managerName) ?>
        </div>
    </header>

    <div class="content">

        <h1>Schedule Management</h1>

        <p class="subtitle">
            Create, assign, edit, and publish crew schedules.
            Available shift categories: Graveyard, Morning, Mid, and Closing.
        </p>

        <?php if ($message !== ''): ?>

            <div class="notice <?= h($messageType === 'error' ? 'error' : 'success') ?>">
                <?= h($message) ?>
            </div>

        <?php endif; ?>

        <section class="panel" id="schedule-form">

            <h2>
                <?= $editSchedule ? 'Edit Shift Schedule' : 'Create Shift Schedule' ?>
            </h2>

            <form method="POST" action="Schedule_Management.php">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= h($_SESSION['sched_csrf']) ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="<?= $editSchedule ? 'update_schedule' : 'add_schedule' ?>"
                >

                <?php if ($editSchedule): ?>

                    <input
                        type="hidden"
                        name="schedule_id"
                        value="<?= (int)$form['id'] ?>"
                    >

                <?php endif; ?>

                <div class="form-grid">

                    <div class="field">

                        <label for="crew_id">Crew Member *</label>

                        <select id="crew_id" name="crew_id" required>

                            <option value="">-- Select Crew Member --</option>

                            <?php foreach ($crewList as $c): ?>

                                <option
                                    value="<?= (int)$c['id'] ?>"
                                    <?= (int)$form['crew_id'] === (int)$c['id'] ? 'selected' : '' ?>
                                >
                                    <?= h($c['name']) ?>
                                    (<?= h($c['position']) ?>)
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="field">

                        <label for="station_id">Station (optional)</label>

                        <select id="station_id" name="station_id">

                            <option value="">-- No station assigned --</option>

                            <?php foreach ($stationList as $st): ?>

                                <option
                                    value="<?= (int)$st['id'] ?>"
                                    <?= (string)$form['station_id'] === (string)$st['id'] ? 'selected' : '' ?>
                                >
                                    <?= h($st['station_name']) ?>
                                    — <?= h($st['department']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="field">

                        <label for="shift_date">Shift Date *</label>

                        <input
                            type="date"
                            id="shift_date"
                            name="shift_date"
                            required
                            value="<?= h($form['shift_date']) ?>"
                        >

                    </div>

                    <div class="field">

                        <label for="shift_type">Shift Category *</label>

                        <select id="shift_type" name="shift_type" required>

                            <?php foreach ($shiftTypes as $st): ?>

                                <option
                                    value="<?= h($st) ?>"
                                    <?= $form['shift_type'] === $st ? 'selected' : '' ?>
                                >
                                    <?= h($st) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="field">

                        <label for="start_time">Start Time *</label>

                        <input
                            type="time"
                            id="start_time"
                            name="start_time"
                            required
                            value="<?= h($form['start_time'] ? substr($form['start_time'], 0, 5) : '') ?>"
                        >

                    </div>

                    <div class="field">

                        <label for="end_time">End Time *</label>

                        <input
                            type="time"
                            id="end_time"
                            name="end_time"
                            required
                            value="<?= h($form['end_time'] ? substr($form['end_time'], 0, 5) : '') ?>"
                        >

                        <small>
                            For overnight shifts, choose an end time earlier than the start time.
                        </small>

                    </div>

                    <div class="field">

                        <label for="status">Schedule Status *</label>

                        <select id="status" name="status" required>

                            <?php foreach ($statuses as $st): ?>

                                <option
                                    value="<?= h($st) ?>"
                                    <?= $form['status'] === $st ? 'selected' : '' ?>
                                >
                                    <?= h(ucfirst($st)) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="field full">

                        <label for="schedule_notes">
                            Notes / Special Instructions
                        </label>

                        <textarea
                            id="schedule_notes"
                            name="schedule_notes"
                            rows="2"
                            maxlength="500"
                            placeholder="Optional notes for crew member..."
                        ><?= h($form['schedule_notes']) ?></textarea>

                    </div>

                    <div class="full">

                        <button class="btn btn-primary" type="submit">
                            <?= $editSchedule ? 'Save Changes' : 'Create Schedule' ?>
                        </button>

                        <?php if ($editSchedule): ?>

                            <a
                                class="btn btn-secondary"
                                href="Schedule_Management.php"
                            >
                                Cancel Edit
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            </form>

        </section>

        <section class="panel">

            <h2>All Work Schedules</h2>

            <div class="table-wrap">

                <table>

                    <thead>
                        <tr>
                            <th>Crew Member</th>
                            <th>Position</th>
                            <th>Shift Date</th>
                            <th>Shift</th>
                            <th>Station</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (!$schedules): ?>

                        <tr>
                            <td colspan="8" class="empty">
                                No schedules found.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($schedules as $s): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?= h($s['crew_name'] ?? 'Unknown crew') ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= h($s['position'] ?? '—') ?>
                                </td>

                                <td>
                                    <?= h(date('M j, Y', strtotime($s['shift_date']))) ?>
                                </td>

                                <td>
                                    <?= h($s['shift_type']) ?>
                                </td>

                                <td>
                                    <?= h($s['station_name'] ?: '—') ?>
                                </td>

                                <td>
                                    <?= h(date('g:i A', strtotime($s['start_time']))) ?>
                                    –
                                    <?= h(date('g:i A', strtotime($s['end_time']))) ?>

                                    <?= $s['end_time'] <= $s['start_time'] ? ' (+1 day)' : '' ?>
                                </td>

                                <td>

                                    <span class="badge <?= $s['status'] === 'published' ? 'badge-published' : 'badge-draft' ?>">
                                        <?= h(ucfirst($s['status'])) ?>
                                    </span>

                                </td>

                                <td>

                                    <div class="actions">

                                        <a
                                            class="btn btn-secondary"
                                            href="Schedule_Management.php?edit=<?= (int)$s['id'] ?>#schedule-form"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="Schedule_Management.php"
                                            onsubmit="return confirm('Delete this schedule? This cannot be undone.');"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= h($_SESSION['sched_csrf']) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="delete_schedule"
                                            >

                                            <input
                                                type="hidden"
                                                name="schedule_id"
                                                value="<?= (int)$s['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-danger"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <footer>
            McSchedulEase © <?= date('Y') ?> | Manager Portal
        </footer>

    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const current = location.pathname
        .split('/')
        .pop()
        .toLowerCase();

    document.querySelectorAll('.nav-link').forEach(function (link) {

        const href = link.getAttribute('href')
            .split('/')
            .pop()
            .toLowerCase();

        link.classList.toggle('active', href === current);

    });

});
</script>

</body>
</html>