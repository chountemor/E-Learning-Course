<?php

session_start();

require_once __DIR__ . '/../config/database.php';

$isPopup = isset($_GET['popup']) && $_GET['popup'] === '1';

$error = '';
$ok    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data
    $name    = trim($_POST['name'] ?? '');
    $email   = strtolower(trim($_POST['email'] ?? ''));
    $phone   = trim($_POST['phone'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validate name & email
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid name and email address.';

    // Validate phone
    } elseif (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {

        $error = 'Please enter a valid phone number.';

    // Validate password
    } elseif (strlen($pass) < 6 || $pass !== $confirm) {

        $error = 'Password must be at least 6 characters and both passwords must match.';

    } else {

        // Check existing email or phone
        $stmt = $pdo->prepare(
            'SELECT id, email, phone
             FROM users
             WHERE email = ? OR phone = ?
             LIMIT 1'
        );

        $stmt->execute([$email, $phone]);

        $old = $stmt->fetch();

        if ($old) {

            if (strcasecmp($old['email'], $email) === 0) {

                $error = 'This email already exists.';

            } else {

                $error = 'This phone number already exists.';
            }

        } else {

            // Create new student account
            $stmt = $pdo->prepare(
                "INSERT INTO users
                    (name, email, phone, password, role, status)
                 VALUES
                    (?, ?, ?, ?, 'student', 'active')"
            );

            $stmt->execute([
                $name,
                $email,
                $phone,
                password_hash($pass, PASSWORD_DEFAULT)
            ]);

            $ok = 'Account created successfully. You can now sign in.';
        }
    }
}

?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Create Free Account — eLearning College</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >

    <!-- Login CSS -->
    <link
        rel="stylesheet"
        href="../assets/css/login.css"
    >

</head>

<body
    class="login-page register-page premium-register <?= $isPopup ? 'login-popup-page' : '' ?>"
>

    <div class="login-background">

        <!-- Background Glow -->
        <div class="login-glow login-glow-one"></div>
        <div class="login-glow login-glow-two"></div>

        <main class="login-container">

            <section class="login-card">
                <!-- Register Hero -->
                <div class="register-hero">

                    <div class="register-icon">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>

                    <div>

                        <span>START YOUR LEARNING JOURNEY</span>

                        <h1>Create Free Account</h1>

                        <p>
                            Join eLearning College for free and start learning online.
                        </p>

                    </div>

                </div>

                <!-- Benefits -->
                <div class="register-benefits">

                    <span>
                        <i class="fa-solid fa-check"></i>
                        Free access
                    </span>

                    <span>
                        <i class="fa-solid fa-check"></i>
                        Learn anytime
                    </span>

                    <span>
                        <i class="fa-solid fa-check"></i>
                        Save your progress
                    </span>

                </div>

                <!-- Error Message -->
                <?php if ($error): ?>

                    <div class="login-alert error-alert">

                        <span class="alert-icon">
                            <i class="fa-solid fa-xmark"></i>
                        </span>

                        <span>
                            <?= htmlspecialchars($error) ?>
                        </span>

                    </div>

                <?php endif; ?>

                <!-- Success Message -->
                <?php if ($ok): ?>

                    <div class="login-alert success-alert">

                        <span class="alert-icon">
                            <i class="fa-solid fa-check"></i>
                        </span>

                        <span>
                            <?= htmlspecialchars($ok) ?>
                        </span>

                    </div>

                <?php endif; ?>

                <!-- Register Form -->
                <form
                    method="post"
                    class="login-form"
                    autocomplete="off"
                >

                    <!-- Full Name -->
                    <div class="form-group">

                        <label>Full Name</label>

                        <div class="input-wrapper">

                            <span class="input-icon">
                                <i class="fa-regular fa-user"></i>
                            </span>

                            <input
                                name="name"
                                type="text"
                                placeholder="Your full name"
                                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                required
                            >

                        </div>

                    </div>

                    <!-- Email -->
                    <div class="form-group">

                        <label>Email Address</label>

                        <div class="input-wrapper">

                            <span class="input-icon">
                                <i class="fa-regular fa-envelope"></i>
                            </span>

                            <input
                                name="email"
                                type="email"
                                placeholder="you@example.com"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                required
                            >

                        </div>

                    </div>

                    <!-- Phone -->
                    <div class="form-group">

                        <label>Phone Number</label>

                        <div class="input-wrapper">

                            <span class="input-icon">
                                <i class="fa-solid fa-phone"></i>
                            </span>

                            <input
                                name="phone"
                                type="tel"
                                placeholder="012 345 678"
                                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                required
                            >

                        </div>

                    </div>

                    <!-- Passwords -->
                    <div class="two">

                        <!-- Password -->
                        <div class="form-group">

                            <label>Password</label>

                            <div class="input-wrapper password-wrapper">

                                <span class="input-icon">
                                    <i class="fa-solid fa-key"></i>
                                </span>

                                <input
                                    id="regPassword"
                                    name="password"
                                    type="password"
                                    minlength="6"
                                    placeholder="At least 6 characters"
                                    required
                                >

                                <button
                                    class="password-toggle"
                                    type="button"
                                    data-target="regPassword"
                                    aria-label="Show password"
                                >
                                    <i class="fa-regular fa-eye"></i>
                                </button>

                            </div>

                            <!-- Password Strength -->
                            <div
                                class="password-strength"
                                id="strength"
                            >

                                <span class="strength-bar"></span>
                                <span class="strength-bar"></span>
                                <span class="strength-bar"></span>

                                <small class="strength-label">
                                    Use 6+ characters
                                </small>

                            </div>

                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group">

                            <label>Confirm Password</label>

                            <div class="input-wrapper password-wrapper">

                                <span class="input-icon">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </span>

                                <input
                                    id="confirmPassword"
                                    name="confirm_password"
                                    type="password"
                                    minlength="6"
                                    placeholder="Repeat password"
                                    required
                                >

                                <button
                                    class="password-toggle"
                                    type="button"
                                    data-target="confirmPassword"
                                    aria-label="Show password"
                                >
                                    <i class="fa-regular fa-eye"></i>
                                </button>

                            </div>

                            <!-- Password Match Status -->
                            <div
                                class="password-status"
                                id="matchStatus"
                            >

                                <span class="status-dot"></span>

                                <span>
                                    Repeat your password
                                </span>

                            </div>

                        </div>

                    </div>

                    <!-- Submit -->
                    <button
                        class="login-button"
                        type="submit"
                    >

                        <span>Create Free Account</span>

                        <i class="fa-solid fa-arrow-right button-arrow"></i>

                    </button>

                </form>

                <!-- Login Link -->
                <div class="register-area">

                    Already have an account?

                    <a
                        href="login.php?popup=<?= $isPopup ? '1' : '0' ?>"
                        data-switch-login
                    >
                        Sign In
                    </a>

                </div>

                <!-- Footer -->
                <div class="login-footer">

                    <span>Free account</span>

                    <span class="footer-dot">•</span>

                    <span>Free learning</span>

                </div>

            </section>

        </main>

    </div>

    <!-- Auth JavaScript -->
    <script src="../assets/js/auth.js"></script>

</body>

</html>