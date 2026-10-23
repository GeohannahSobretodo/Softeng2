<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['Password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $conn->prepare(
            'SELECT id, Name, email, Password
             FROM admin
             WHERE email = ?
             LIMIT 1'
        );

        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if ($row) {
                $storedPassword = (string) $row['Password'];

                $passwordMatches = password_verify(
                    $password,
                    $storedPassword
                );

                $legacyPassword = false;

                if (
                    !$passwordMatches &&
                    hash_equals($storedPassword, $password)
                ) {
                    $passwordMatches = true;
                    $legacyPassword = true;
                }

                if ($passwordMatches) {
                    if ($legacyPassword) {
                        $newHash = password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );

                        $update = $conn->prepare(
                            'UPDATE admin SET Password = ? WHERE id = ?'
                        );

                        if ($update) {
                            $adminId = (int) $row['id'];
                            $update->bind_param(
                                'si',
                                $newHash,
                                $adminId
                            );
                            $update->execute();
                            $update->close();
                        }
                    }

                    session_regenerate_id(true);

                    $_SESSION['id'] = $row['id'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['name'] = $row['Name'];
                    $_SESSION['role'] = 'manager';

                    header('Location: Dashboard.php');
                    exit();
                }
            }

            $error = 'Incorrect email or password.';
        } else {
            $error = 'Login could not be processed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login - Manager/Admin</title>

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
            font-size: 34px;
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
            <header>Manager Login</header>

            <p class="description">
                McSchedule AI Administration Panel
            </p>

            <?php if ($error !== ''): ?>
                <div class="error">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="input-field">
                    <input
                        type="email"
                        name="email"
                        placeholder="Admin Email"
                        class="input"
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="username"
                        required
                    >
                    <i class="bx bx-user"></i>
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
                        Login
                    </button>
                </div>
            </form>

            <div class="login-footer">
                Authorized Manager/Admin Accounts Only
            </div>
        </div>
    </div>
</body>
</html>
