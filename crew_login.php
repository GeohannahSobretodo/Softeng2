<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';

if (
    ($_SESSION['role'] ?? '') === 'crew' &&
    isset($_SESSION['crew_id'])
) {
    header('Location: Crew_Dashboard.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['Password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, email, password, position, is_active
             FROM crew_members
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {
            $error = 'A system error occurred. Please try again.';
        } else {
            $stmt->bind_param('s', $email);
            $stmt->execute();

            $result = $stmt->get_result();
            $crew = $result->fetch_assoc();

            $stmt->close();

            if (
                !$crew ||
                !password_verify($password, $crew['password'])
            ) {
                $error = 'Invalid email or password.';
            } elseif ((int)$crew['is_active'] !== 1) {
                $error = 'Your account is inactive. Please contact your manager.';
            } else {
                session_regenerate_id(true);

                $_SESSION = [];

                $_SESSION['role'] = 'crew';
                $_SESSION['crew_id'] = (int)$crew['id'];
                $_SESSION['id'] = (int)$crew['id'];
                $_SESSION['name'] = $crew['name'];
                $_SESSION['email'] = $crew['email'];
                $_SESSION['position'] = $crew['position'];

                header('Location: Crew_Dashboard.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Crew Login - McSchedulEase</title>

    <link
        href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
        rel="stylesheet"
    >

    <style>
        * {
            box-sizing: border-box;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background-image: url('loginbg.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            background-attachment: fixed;
        }

        .box {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 380px;
            display: flex;
            flex-direction: column;
            padding: 25px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
        }

        header {
            color: #013133;
            font-size: 30px;
            font-weight: bold;
            text-shadow: 2px 2px 3px #fff;
            text-align: center;
            padding: 10px 0 25px;
        }

        .description {
            color: #fff;
            text-align: center;
            margin: 0 0 22px;
            font-size: 13px;
        }

        .input-field {
            position: relative;
            margin-bottom: 18px;
        }

        .input {
            height: 48px;
            width: 100%;
            border: 1px solid rgba(255,255,255,.4);
            outline: none;
            border-radius: 30px;
            color: #fff;
            padding: 0 18px 0 45px;
            background: rgba(0, 0, 0, 0.45);
        }

        .input::placeholder {
            color: #fff;
        }

        .input-field i {
            position: absolute;
            top: 14px;
            left: 16px;
            color: #fff;
            font-size: 20px;
        }

        .submit {
            border: none;
            border-radius: 30px;
            font-size: 15px;
            font-weight: bold;
            height: 46px;
            width: 100%;
            color: #fff;
            background: rgb(0, 130, 153);
            cursor: pointer;
            transition: .3s;
        }

        .submit:hover {
            background: rgb(0, 100, 120);
        }

        .error {
            background: #fff0f0;
            color: #a00000;
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 6px;
            text-align: center;
            font-size: 13px;
        }

        .login-footer {
            margin-top: 15px;
            color: #fff;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="box">
        <div class="container">
            <header>Crew Login</header>

            <p class="description">
                McSchedulEase Crew Portal
            </p>

            <?php if ($error !== ''): ?>
                <div class="error">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="crew_login.php" method="POST">
                <div class="input-field">
                    <input
                        type="email"
                        name="email"
                        placeholder="Crew Email Address"
                        class="input"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="username"
                        required
                    >
                    <i class="bx bx-envelope"></i>
                </div>

                <div class="input-field">
                    <input
                        type="password"
                        name="Password"
                        placeholder="Password"
                        class="input"
                        autocomplete="current-password"
                        required
                    >
                    <i class="bx bx-lock-alt"></i>
                </div>

                <div class="input-field">
                    <button type="submit" name="log" value="1" class="submit">
                        Log In as Crew
                    </button>
                </div>
            </form>

            <div class="login-footer">
                Authorized Crew Members Only
            </div>
        </div>
    </div>
</body>
</html>