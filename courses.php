<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$isFeaturedPage = isset($_GET['featured']) && $_GET['featured'] === '1';
$page_title = $isFeaturedPage ? 'Featured Courses' : 'Courses';
$q = trim($_GET['q'] ?? '');
$cat = trim($_GET['cat'] ?? '');
$term = "%{$q}%";

$where = ["c.status='active'"];
$params = [];
if ($isFeaturedPage) $where[] = 'c.featured=1';
if ($q !== '') {
    $where[] = '(c.title LIKE ? OR c.title_kh LIKE ? OR c.category LIKE ? OR c.description LIKE ? OR c.description_kh LIKE ?)';
    array_push($params,$term,$term,$term,$term,$term);
}
if ($cat !== '') {
    $where[] = 'c.category = ?';
    $params[] = $cat;
}
$stmt = $pdo->prepare('SELECT c.* FROM courses c WHERE '.implode(' AND ',$where).' ORDER BY c.created_at DESC');
$stmt->execute($params);
$courses = $stmt->fetchAll();
?>
<section class="page">
    <div class="container">
        <span class="eyebrow"><?= t('LEARNING LIBRARY','បណ្ណាល័យសិក្សា') ?></span>
        <h1><?= $isFeaturedPage ? t('Featured Courses','វគ្គសិក្សាណែនាំ') : t('Explore Courses','ស្វែងរកវគ្គសិក្សា') ?></h1>
        <p><?= $isFeaturedPage ? t('Explore all courses selected as Featured by the Administrator.','ស្វែងរកវគ្គសិក្សាទាំងអស់ដែល Admin បានជ្រើសជាវគ្គណែនាំ។') : t('Find the right course for your future.','ស្វែងរកវគ្គសិក្សាដែលសមស្របសម្រាប់អនាគតរបស់អ្នក។') ?></p>
    </div>
</section>

<main class="container section">
    <form class="filter course-search" method="get">
        <?php if($cat !== ''): ?><input type="hidden" name="cat" value="<?=htmlspecialchars($cat)?>"><?php endif; ?>
        <?php if($isFeaturedPage): ?><input type="hidden" name="featured" value="1"><?php endif; ?>
        <i class="fa-solid fa-magnifying-glass"></i>
        <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="<?= t('Search by course, category or topic...','ស្វែងរកតាមឈ្មោះវគ្គ ប្រភេទ ឬមេរៀន...') ?>">
        <?php if($q !== '' || $cat !== ''): ?><a class="search-clear" href="courses.php"><?= t('Clear','សម្អាត') ?></a><?php endif; ?>
        <button class="btn" type="submit"><?= t('Search','ស្វែងរក') ?></button>
    </form>

    <div class="course-results-head">
        <div><b><?= count($courses) ?></b> <?= t('courses available','វគ្គកំពុងមាន') ?></div>
        <span><?= $lang === 'kh' ? 'ប្តូរភាសាដោយចុច ខ្មែរ / EN ខាងលើ' : 'Switch language using Khmer / EN above' ?></span>
    </div>

    <div class="grid">
        <?php if ($courses): foreach ($courses as $c): ?>
            <article class="course">
                <?php if (!empty($c['image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($c['image']) ?>" alt="<?= htmlspecialchars(course_title($c)) ?>">
                <?php else: ?><div class="placeholder">📚</div><?php endif; ?>
                <div class="body">
                    <small><?= htmlspecialchars($c['category'] ?? t('General','ទូទៅ')) ?></small>
                    <h3><?= htmlspecialchars(course_title($c)) ?></h3>
                    <p><?= htmlspecialchars(mb_strimwidth(course_description($c), 0, 130, '...')) ?></p>
                    <div class="course-meta-mini">
                        <span>⏱ <?= htmlspecialchars($c['duration'] ?: t('Self-paced','រៀនតាមល្បឿនខ្លួនឯង')) ?></span>
                        <span class="free-badge">FREE</span>
                    </div>
                    <a class="btn small" href="course.php?id=<?= (int)$c['id'] ?>"><?= t('View Course','មើលវគ្គសិក្សា') ?> →</a>
                </div>
            </article>
        <?php endforeach; else: ?>
            <div class="empty-state" style="grid-column:1/-1">
                <div class="empty-icon">🔎</div>
                <h3><?= t('No courses found','រកមិនឃើញវគ្គសិក្សាទេ') ?></h3>
                <p><?= t('Try another keyword or clear your search.','សូមសាកល្បងពាក្យផ្សេង ឬសម្អាតការស្វែងរក។') ?></p>
                <a class="btn outline" href="courses.php"><?= t('View all courses','មើលវគ្គទាំងអស់') ?></a>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
