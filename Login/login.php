<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

$isPopup = isset($_GET['popup']) && $_GET['popup'] === '1';

if (!empty($_SESSION['user_id'])) {
    $redirect_url = match ($_SESSION['role'] ?? '') {
        'admin' => '../admin/dashboard.php',
        'teacher' => '../teacher/dashboard.php',
        default => '../index.php'
    };
    if ($isPopup) {
        ?><!doctype html><html><body><script>window.top.location.href=<?=json_encode($redirect_url)?>;</script></body></html><?php
        exit;
    }
    header('Location: ' . $redirect_url);
    exit;
}

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) die('Invalid request submission.');

    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR phone = ?) AND role IN ('admin','teacher','student') LIMIT 1");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'Incorrect email/phone or password.';
    } elseif (($user['status'] ?? '') === 'blocked' || !empty($user['locked_at'])) {
        $error = 'This account is locked. Please contact support.';
    } elseif (($user['status'] ?? '') !== 'active') {
        $error = 'This account is not active.';
    } elseif (password_verify($password, $user['password'])) {
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $rehash = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
            $rehash->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
        }
        session_regenerate_id(true);
        $pdo->prepare("UPDATE users SET failed_login_attempts = 0, last_login_at = NOW() WHERE id = ?")->execute([$user['id']]);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        $redirect = $_SESSION['login_redirect'] ?? '';
        unset($_SESSION['login_redirect']);
        if ($user['role'] === 'admin') $redirectTarget = '../admin/dashboard.php';
        elseif ($user['role'] === 'teacher') $redirectTarget = '../teacher/dashboard.php';
        elseif ($redirect && str_starts_with($redirect, '/') && !str_starts_with($redirect, '//')) $redirectTarget = $redirect;
        else $redirectTarget = '../index.php';

        if ($isPopup) {
            ?><!doctype html><html lang="en"><head><meta charset="utf-8"><title>Signed in</title><style>body{font-family:Inter,Arial;display:grid;place-items:center;height:100vh;margin:0;background:#f6f9ff;color:#102f61}.ok{padding:28px;text-align:center}.ok .mark{width:58px;height:58px;border-radius:18px;background:#fff3e5;color:#f47c0a;display:grid;place-items:center;font-size:28px;margin:auto}.ok h2{margin:14px 0 6px}</style></head><body><div class="ok"><div class="mark">✓</div><h2>Login successful</h2><p>Opening your learning page…</p></div><script>window.top.location.href=<?=json_encode($redirectTarget)?>;</script></body></html><?php
            exit;
        }
        header('Location: ' . $redirectTarget);
        exit;
    } else {
        $attempts = (int)($user['failed_login_attempts'] ?? 0) + 1;
        if ($attempts >= 5) {
            $pdo->prepare("UPDATE users SET failed_login_attempts = 5, status = 'blocked', locked_at = NOW() WHERE id = ?")->execute([$user['id']]);
            $error = 'Too many incorrect attempts. This account is now locked.';
        } else {
            $pdo->prepare("UPDATE users SET failed_login_attempts = ? WHERE id = ?")->execute([$attempts, $user['id']]);
            $error = 'Incorrect password. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Student Login — eLearning College</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/login.css">
</head>
<body class="login-page <?= $isPopup ? 'login-popup-page' : '' ?>">
<div class="login-background">
    <div class="login-shell">
        <section class="login-visual" aria-label="eLearning College learning scene">
            <div class="login-visual-overlay"></div>
        </section>

        <section class="login-card">
            <div class="login-brand-wrap">
                <a>
                    <img class="login-brand-logo" src="../assets/images/logo.svg" alt="eLearning College">
                </a>
            </div>
            <div class="login-heading">
                <h1>Student <span>Login</span></h1>
                <p>Sign in to continue your learning journey.</p>
            </div>

            <?php if ($error): ?>
                <div class="login-alert error-alert"><span class="alert-icon"><i class="fa-solid fa-xmark"></i></span><span><?=htmlspecialchars($error)?></span></div>
            <?php endif; ?>

            <form method="post" action="?popup=<?= $isPopup ? '1' : '0' ?>" class="login-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>">

                <div class="form-group">
                    <label for="identifier">Email or Phone</label>
                    <div class="input-wrapper">
                        <span class="input-icon"><i class="fa-regular fa-envelope"></i></span>
                        <input id="identifier" name="identifier" type="text" placeholder="Email or Phone" autocomplete="username" required value="<?=htmlspecialchars($_POST['identifier'] ?? '')?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper password-wrapper">
                        <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                        <input id="password" name="password" type="password" placeholder="Password" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" data-target="password" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
                    </div>
                </div>

                <div class="login-options">
                    <label class="remember"><input type="checkbox" name="remember" value="1"><span></span> Remember me</label>
                    <a href="register.php?popup=<?= $isPopup ? '1' : '0' ?>">Need help?</a>
                </div>

                <button class="login-button" type="submit"><span>Sign In</span><i class="fa-solid fa-arrow-right"></i></button>
            </form>

            <div class="register-divider"><span></span><b>New student?</b><span></span></div>
            <a class="register-button" href="register.php?popup=<?= $isPopup ? '1' : '0' ?>"><i class="fa-solid fa-user-plus"></i> Create a free account</a>
            <div class="login-footer"><span><i class="fa-solid fa-graduation-cap"></i> Free learning</span><i>•</i><span>Learn at your own pace</span></div>
        </section>
    </div>
</div>
<script src="../assets/js/auth.js"></script>
</body>
</html>
