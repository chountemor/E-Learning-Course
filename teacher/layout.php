<?php
require_once __DIR__ . '/../config/teacher_auth.php';
require_teacher();

$page = basename($_SERVER['PHP_SELF']);
$title = $title ?? 'Teacher Portal';
$heading = $heading ?? 'Teacher Dashboard';
$teacherName = $_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Teacher';
$teacherSkill = 'Not assigned';
$teacherPhoto = '';
try {
    $st = $pdo->prepare('SELECT c.name, t.photo FROM teachers t LEFT JOIN categories c ON c.id=t.category_id WHERE t.email=(SELECT email FROM users WHERE id=? LIMIT 1) LIMIT 1');
    $st->execute([(int)$_SESSION['user_id']]);
    $teacherRow = $st->fetch() ?: [];
     $teacherSkill = (string)($teacherRow['name'] ?? 'Not assigned');
     $teacherPhoto = (string)($teacherRow['photo'] ?? '');
} catch (Throwable $e) {}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — eLearning College</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/teacher/style.css">
</head>
<body>
<div class="teacher-shell">
<aside class="teacher-sidebar">
    <a class="teacher-brand teacher-logo-brand" href="dashboard.php"><img src="../assets/images/logo.svg" alt="eLearning College"></a>
    <div class="teacher-portal-chip"><i class="fa-solid fa-chalkboard-user"></i><span>Teacher Workspace</span></div>

    <div class="teacher-side-label">MAIN</div>
    <nav class="teacher-nav">
        <a class="<?= $page === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php"><span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span><span>Dashboard</span></a>
        <a class="<?= $page === 'lessons.php' ? 'active' : '' ?>" href="lessons.php"><span class="nav-icon"><i class="fa-solid fa-book-open"></i></span><span>My Lessons</span></a>
        <a class="<?= $page === 'lesson_add.php' ? 'active' : '' ?>" href="lesson_add.php"><span class="nav-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span><span>Upload Lesson</span></a>
    </nav>
    <div class="teacher-sidebar-footer">
        <a class="teacher-logout teacher-logout-card" href="../Login/logout.php"><span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span><span><strong>Logout</strong>  </span></a>
    </div>
</aside>

<main class="teacher-main">
<header class="teacher-topbar">
    <div><span class="teacher-eyebrow">TEACHER WORKSPACE</span><h1 class="teacher-heading"><?= e($heading) ?></h1><p class="teacher-subheading">Upload learning content for your assigned skill.</p></div>
    <div class="teacher-profile"><span class="teacher-avatar"><?php if($teacherPhoto): ?><img src="../uploads/<?=e($teacherPhoto)?>" alt=""><?php else: ?><i class="fa-solid fa-chalkboard-user"></i><?php endif; ?></span><span><strong><?= e($teacherName) ?></strong><small><?= e($teacherSkill) ?></small></span></div>
</header>
