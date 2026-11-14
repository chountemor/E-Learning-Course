<?php
require_once __DIR__ . '/../config/database.php';

$title = 'Teacher Dashboard';
$heading = 'Teacher Dashboard';

$uid = (int)($_SESSION['user_id'] ?? 0);

$st = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE created_by = ?");
$st->execute([$uid]);
$lessonCount = (int)$st->fetchColumn();

$st = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE created_by = ? AND status = 'active'");
$st->execute([$uid]);
$activeCount = (int)$st->fetchColumn();

$st = $pdo->prepare("SELECT COUNT(DISTINCT course_id) FROM lessons WHERE created_by = ?");
$st->execute([$uid]);
$courseCount = (int)$st->fetchColumn();

$recent = $pdo->prepare("
    SELECT l.id, l.title, l.title_kh, l.status, l.duration, l.created_at, c.title AS course_title
    FROM lessons l
    INNER JOIN courses c ON c.id = l.course_id
    WHERE l.created_by = ?
    ORDER BY l.id DESC
    LIMIT 5
");
$recent->execute([$uid]);
$recentRows = $recent->fetchAll(PDO::FETCH_ASSOC);

require 'layout.php';
?>

<section class="teacher-welcome">
    <h2>Welcome, <?= e($_SESSION['name'] ?? 'Teacher') ?> 👋</h2>
    <p>Manage your lessons, learning materials and course content from one clean workspace.</p>
</section>

<section class="teacher-grid">
    <div class="teacher-stat">
        <div class="stat-icon"><i class="fa-solid fa-book-open"></i></div>
        <div class="stat-value"><?= $lessonCount ?></div>
        <div class="stat-label">My Lessons</div>
    </div>

    <div class="teacher-stat">
        <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-value"><?= $activeCount ?></div>
        <div class="stat-label">Active Lessons</div>
    </div>

    <div class="teacher-stat">
        <div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div>
        <div class="stat-value"><?= $courseCount ?></div>
        <div class="stat-label">Courses With My Lessons</div>
    </div>
</section>

<section class="teacher-card">
    <div class="teacher-card-head">
        <div>
            <h2>Recent Lessons</h2>
            <p>Your latest learning content.</p>
        </div>
        <div class="teacher-actions">
            <a class="btn secondary" href="lessons.php"><i class="fa-solid fa-list"></i> View All</a>
            <a class="btn" href="lesson_add.php"><i class="fa-solid fa-plus"></i> Add Lesson</a>
        </div>
    </div>

    <?php if (!$recentRows): ?>
        <div class="teacher-empty">
            <div><i class="fa-regular fa-folder-open"></i></div>
            <strong>No lessons yet</strong>
            <div class="muted">Create your first lesson to start building learning content.</div>
        </div>
    <?php else: ?>
        <div class="teacher-table-wrap">
            <table class="teacher-table">
                <thead>
                    <tr>
                        <th>Lesson</th>
                        <th>Course</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentRows as $r): ?>
                    <tr>
                        <td>
                            <div class="lesson-title">
                                <span class="lesson-icon"><i class="fa-solid fa-file-lines"></i></span>
                                <span>
                                    <strong><?= e($r['title']) ?></strong>
                                    <small><?= e($r['title_kh'] ?? '') ?></small>
                                </span>
                            </div>
                        </td>
                        <td><?= e($r['course_title']) ?></td>
                        <td><?= e($r['duration'] ?: '—') ?></td>
                        <td><span class="status <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
                        <td><a class="btn secondary" href="../lesson.php?id=<?= (int)$r['id'] ?>" target="_blank"><i class="fa-solid fa-eye"></i> View</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

</main>
</div>
</body>
</html>
