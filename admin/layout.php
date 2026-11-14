<?php

ob_start();

require_once __DIR__ . '/../config/auth.php';

require_admin();

/* ============================================================
   PAGE
============================================================ */

$page = basename($_SERVER['PHP_SELF']);


/* ============================================================
   PAGE GROUPS
============================================================ */

$coursePages = [
    'courses.php',
    'course_add.php',
    'course_edit.php',
    'course_media.php'
];

$lessonPages = [
    'lessons.php',
    'lesson_add.php',
    'lesson_edit.php'
];

$userPages = [
    'users.php',
    'user_add.php',
    'user_edit.php'
];

$contentPages = [
    'news.php',
    'news_add.php',
    'news_edit.php'
];


/* ============================================================
   PAGE HEADING
============================================================ */

if (
    in_array($page, $coursePages, true) &&
    empty($admin_heading)
) {
    $admin_heading = 'Course Management';
}

if (
    in_array($page, $lessonPages, true) &&
    empty($admin_heading)
) {
    $admin_heading = 'Lesson Management';
}

if (
    in_array($page, $userPages, true) &&
    empty($admin_heading)
) {
    $admin_heading = 'User Management';
}

if (
    in_array($page, $contentPages, true) &&
    empty($admin_heading)
) {
    $admin_heading = 'Website Content';
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

    <title>
        <?= htmlspecialchars($admin_title ?? 'Admin Dashboard') ?>
        — eLearning College
    </title>


    <!-- =====================================================
         GOOGLE FONT
    ====================================================== -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >


    <!-- =====================================================
         MAIN WEBSITE CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <!-- =====================================================
         ADMIN CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

    <!-- =====================================================
         ADMIN EXTRA / ACCOUNT UI
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../assets/css/admin-extra.css"
    >

    <!-- Final responsive rules for Computer / Tablet / Phone -->
    <link
        rel="stylesheet"
        href="../assets/css/responsive-all.css"
    >


    <!-- =====================================================
         DIRECT COURSE MEDIA CSS
         Course = Image + Video + PDF
    ====================================================== -->

    <?php if (in_array($page, $coursePages, true)): ?>

        <link
            rel="stylesheet"
            href="../assets/css/course-media.css"
        >

    <?php endif; ?>


    <!-- =====================================================
         LESSON CSS
         Only load on Lesson pages
    ====================================================== -->

    <?php if (in_array($page, $lessonPages, true)): ?>

        <link
            rel="stylesheet"
            href="../assets/css/lessons.css"
        >

    <?php endif; ?>


</head>


<body class="admin-body">

<div class="admin">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside class="admin-sidebar" id="adminSidebar">


        <!-- =================================================
             LOGO
        ================================================== -->

        <a class="admin-logo admin-logo-image" href="dashboard.php">
            <img src="../assets/images/logo.svg" alt="eLearning College">
        </a>


        <!-- =================================================
             MAIN MENU
        ================================================== -->

        <div class="side-label">
            MAIN MENU
        </div>


        <!-- DASHBOARD -->

        <a
            class="side-link <?= $page === 'dashboard.php' ? 'active' : '' ?>"
            href="dashboard.php"
        >

            <span class="side-icon">
                <i class="fa-solid fa-chart-pie"></i>
            </span>

            <span>
                Dashboard
            </span>

        </a>


        <!-- =================================================
             COURSE MANAGEMENT
        ================================================== -->

        <div class="side-label">
            COURSE MANAGEMENT
        </div>


        <!-- COURSES -->

        <a
            class="side-link <?= in_array($page, $coursePages, true) ? 'active' : '' ?>"
            href="courses.php"
        >

            <span class="side-icon">
                <i class="fa-solid fa-book-open"></i>
            </span>

            <span>
                Courses
            </span>

        </a>

<!-- CATEGORIES -->

        <a
            class="side-link <?= $page === 'categories.php' ? 'active' : '' ?>"
            href="categories.php"
        >

            <span class="side-icon">
                <i class="fa-solid fa-layer-group"></i>
            </span>

            <span>
                Categories
            </span>

        </a>


        <!-- =================================================
             USER MANAGEMENT
        ================================================== -->

        <div class="side-label">
            USER MANAGEMENT
        </div>


        <!-- ALL USERS -->

        <a
            class="side-link <?= in_array($page, $userPages, true) ? 'active' : '' ?>"
            href="users.php"
        >

            <span class="side-icon">
                <i class="fa-solid fa-users"></i>
            </span>

            <span>
                All Users
            </span>

        </a>


        <!-- TEACHERS -->

        <a
            class="side-link
            <?= (
                $page === 'people.php' &&
                ($_GET['type'] ?? '') === 'teachers'
            ) ? 'active' : '' ?>"
            href="people.php?type=teachers"
        >

            <span class="side-icon">
                <i class="fa-solid fa-chalkboard-user"></i>
            </span>

            <span>
                Teachers
            </span>

        </a>


        <!-- STAFF -->

        <a
            class="side-link
            <?= (
                $page === 'people.php' &&
                ($_GET['type'] ?? '') === 'staff'
            ) ? 'active' : '' ?>"
            href="people.php?type=staff"
        >

            <span class="side-icon">
                <i class="fa-solid fa-user-tie"></i>
            </span>

            <span>
                Staff
            </span>

        </a>


        <!-- =================================================
             WEBSITE CONTENT
        ================================================== -->

        <div class="side-label">
            WEBSITE CONTENT
        </div>


        <!-- NEWS -->

        <a
            class="side-link <?= in_array($page, $contentPages, true) ? 'active' : '' ?>"
            href="news.php"
        >

            <span class="side-icon">
                <i class="fa-solid fa-newspaper"></i>
            </span>

            <span>
                News
            </span>

        </a>




    </aside>

    <div
        class="admin-sidebar-backdrop"
        id="adminSidebarBackdrop"
        aria-hidden="true"
    ></div>


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">


        <!-- =================================================
             TOP BAR
        ================================================== -->

        <header class="adminbar">


            <!-- =================================================
                 PAGE HEADING
            ================================================== -->

            <button
                type="button"
                class="admin-mobile-toggle"
                id="adminMobileToggle"
                aria-label="Open Admin Menu"
                aria-controls="adminSidebar"
                aria-expanded="false"
            >
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="page-heading">

                <small>
                    ADMIN PANEL
                </small>

                <h1>
                    <?= htmlspecialchars(
                        $admin_heading ?? 'Dashboard'
                    ) ?>
                </h1>

            </div>


            <!-- =================================================
                 RIGHT SIDE
                 View Website + System Administrator
            ================================================== -->

            <div class="adminbar-right">


                <!-- =================================================
                     VIEW WEBSITE
                ================================================== -->

                <a
                    class="view-website-btn"
                    href="../index.php"
                    title="View Website"
                >

                    <i class="fa-solid fa-globe"></i>

                    <span>
                        View Website
                    </span>

                </a>


                <!-- =================================================
                     SYSTEM ADMINISTRATOR
                ================================================== -->

                <details class="admin-account">

                    <summary class="user-pill">

                        <span class="user-avatar">
                            <i class="fa-solid fa-user-shield"></i>
                        </span>

                        <span class="user-info">
                            <span class="user-name">
                                <?= htmlspecialchars(
                                    $_SESSION['user_name'] ??
                                    $_SESSION['admin_name'] ??
                                    'System Administrator'
                                ) ?>
                            </span>
                            <small>System Administrator</small>
                        </span>

                        <i class="fa-solid fa-chevron-down user-arrow"></i>

                    </summary>

                    <div class="account-menu">

                        <div class="account-menu-head">
                            <span class="account-menu-avatar">
                                <i class="fa-solid fa-user-shield"></i>
                            </span>
                            <span>
                                <strong>System Administrator</strong>
                                <small>Administrator Account</small>
                            </span>
                        </div>

                        <div class="account-menu-line"></div>

                        <a class="account-logout" href="../Login/logout.php">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </a>

                    </div>

                </details>


            </div>


        </header>



<script>
(function () {
    const body = document.body;
    const toggle = document.getElementById('adminMobileToggle');
    const backdrop = document.getElementById('adminSidebarBackdrop');

    function closeAdminMenu() {
        body.classList.remove('admin-menu-open');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }

    function openAdminMenu() {
        body.classList.add('admin-menu-open');
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
    }

    toggle?.addEventListener('click', function () {
        body.classList.contains('admin-menu-open')
            ? closeAdminMenu()
            : openAdminMenu();
    });

    backdrop?.addEventListener('click', closeAdminMenu);

    document.querySelectorAll('.admin-sidebar a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 900) closeAdminMenu();
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 900) closeAdminMenu();
    });
})();
</script>


        <!-- =================================================
             PAGE CONTENT START

             Your individual admin page content
             will appear here.
        ================================================== -->