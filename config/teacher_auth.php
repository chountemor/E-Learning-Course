<?php
require_once __DIR__.'/auth.php';
function is_teacher(): bool { return !empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'teacher'; }
function require_teacher(): void { if (!is_teacher()) { header('Location: ../Login/login.php'); exit; } }
function require_teacher_or_admin(): void { if (!is_teacher() && !is_admin()) { header('Location: ../Login/login.php'); exit; } }
