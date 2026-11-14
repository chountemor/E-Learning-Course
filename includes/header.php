<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/lang.php';

$projectPath  = str_replace('\\', '/', dirname(__DIR__));
$documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
$baseUrl = '';
if ($documentRoot !== '' && strpos($projectPath, $documentRoot) === 0) {
    $baseUrl = substr($projectPath, strlen($documentRoot));
}
$baseUrl = '/' . trim($baseUrl, '/');
if ($baseUrl === '/') $baseUrl = '';

function url($path = '') {
    global $baseUrl;
    return $baseUrl . '/' . ltrim($path, '/');
}
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'kh' ? 'km' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="eLearning College - Free Online Learning Platform">
    <meta name="theme-color" content="#f5a623">
    <title><?= htmlspecialchars($page_title ?? 'eLearning College') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Bootstrap 5 — responsive utilities, grid and modern components -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    <!-- eLearning College custom styles load AFTER Bootstrap -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/header.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/footer.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/custom-modern.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/course-media.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/auth-modal.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/responsive-all.css') ?>">
</head>
<body>

<header class="header">
    <div class="container nav">

        <!-- Logo -->
        <a class="brand brand-logo-link" href="<?= url('index.php') ?>" aria-label="eLearning College">
            <img class="site-logo" src="<?= url('assets/images/logo.svg') ?>" alt="eLearning College">
        </a>

        <!-- Main navigation -->
        <nav class="main-nav" id="mainNav">
            <a href="<?= url('index.php') ?>" class="nav-link home-nav-link">
                <i class="fa-solid fa-house"></i>
                <?= t('Home','ទំព័រដើម') ?>
            </a>
            <a href="<?= url('courses.php') ?>" class="nav-link">
                <i class="fa-solid fa-book-open"></i>
                <?= t('Courses','វគ្គសិក្សា') ?>
            </a>
            <a href="<?= url('index.php#diplomas-certificates') ?>" class="nav-link">
                <i class="fa-solid fa-graduation-cap"></i>
                <?= t('Diplomas & Certificates','សញ្ញាបត្រ និងវិញ្ញាបនបត្រ') ?>
            </a>
            <!-- Admin Dashboard is intentionally kept OUT of the main navigation.
                 This prevents the public header from shifting/overlapping after admin login. -->
        </nav>

        <!-- Notifications are ALWAYS available, including for logged-in users. -->
        <div class="header-tools">
            <div class="notification-wrap">
                <button class="header-tool notification-btn" type="button" id="notificationToggle"
                        aria-label="Notifications"
                        title="<?= t('Notifications','ការជូនដំណឹង') ?>"
                        aria-expanded="false">
                    <i class="fa-regular fa-bell"></i>
                    <span class="notification-dot"></span>
                </button>

                <div class="notification-panel" id="notificationPanel" hidden>
                    <div class="notification-head">
                        <div>
                            <strong><?= t('Notifications','ការជូនដំណឹង') ?></strong>
                            <small><?= t('Stay updated with eLearning College','ទទួលព័ត៌មានថ្មីៗពី eLearning College') ?></small>
                        </div>
                        <span class="notification-count">3</span>
                    </div>

                    <a href="<?= url('courses.php') ?>" class="notification-item">
                        <span class="notification-icon"><i class="fa-solid fa-book-open"></i></span>
                        <span>
                            <b><?= t('New courses available','មានវគ្គសិក្សាថ្មី') ?></b>
                            <small><?= t('Explore free learning materials','ស្វែងរកមេរៀនឥតគិតថ្លៃ') ?></small>
                        </span>
                    </a>

                    <a href="<?= url('index.php#diplomas-certificates') ?>" class="notification-item">
                        <span class="notification-icon"><i class="fa-solid fa-award"></i></span>
                        <span>
                            <b><?= t('Certificates','វិញ្ញាបនបត្រ') ?></b>
                            <small><?= t('Discover certificates and diplomas','ស្វែងយល់អំពីសញ្ញាបត្រ និងវិញ្ញាបនបត្រ') ?></small>
                        </span>
                    </a>

                    <a href="<?= url('Login/register.php') ?>" class="notification-item" data-auth-modal="register">
                        <span class="notification-icon"><i class="fa-solid fa-user-plus"></i></span>
                        <span>
                            <b><?= t('Start learning','ចាប់ផ្តើមសិក្សា') ?></b>
                            <small><?= t('Create your free account','បង្កើតគណនីរបស់អ្នកដោយឥតគិតថ្លៃ') ?></small>
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Account -->
        <div class="nav-user">
            <?php if (!empty($_SESSION['user_id'])): ?>

                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a
                        class="admin-dashboard-btn"
                        href="<?= url('admin/dashboard.php') ?>"
                        title="<?= t('Admin Dashboard','ផ្ទាំងគ្រប់គ្រង Admin') ?>"
                    >
                        <i class="fa-solid fa-chart-line"></i>
                        <span><?= t('Admin','Admin') ?></span>
                    </a>
                <?php endif; ?>

                <div class="user-pill">
                    <span class="user-avatar"><i class="fa-solid fa-user"></i></span>
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
                </div>
                <a class="btn outline" href="<?= url('Login/logout.php') ?>">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <?= t('Logout','ចាកចេញ') ?>
                </a>
            <?php else: ?>
                <a class="signin-link" href="<?= url('Login/login.php') ?>" data-auth-modal="login">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <?= t('Sign In','ចូលប្រព័ន្ធ') ?>
                </a>
                <a class="btn" href="<?= url('Login/register.php') ?>" data-auth-modal="register">
                    <i class="fa-solid fa-user-plus"></i>
                    <?= t('Get Started','ចាប់ផ្តើមសិក្សា') ?>
                </a>
            <?php endif; ?>
        </div>

        <button class="mobile-menu-btn" type="button" aria-label="Open Menu" onclick="toggleMobileMenu()" aria-expanded="false" aria-controls="mainNav">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</header>

<!-- Login / Register popup -->
<div class="auth-modal" id="authModal" data-base="<?= htmlspecialchars($baseUrl) ?>" hidden aria-hidden="true">
    <div class="auth-modal-dialog" role="dialog" aria-modal="true" aria-label="Account access">
        <div class="auth-modal-top">
            <button class="auth-modal-close" id="authModalClose" type="button" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <iframe class="auth-modal-frame" id="authModalFrame" title="Account Login"></iframe>
        
    </div>
</div>

<!-- Bootstrap JS bundle -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"
></script>

<script src="<?= url('assets/js/app.js') ?>"></script>
<script src="<?= url('assets/js/auth-modal.js') ?>"></script>
