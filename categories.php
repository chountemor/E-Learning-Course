<?php
require_once __DIR__ . '/config/database.php';
$page_title = 'All Categories';
require_once __DIR__ . '/includes/header.php';

$stmt = $pdo->query("\n    SELECT\n        c.id, c.name, c.name_kh, c.icon, c.description,\n        COUNT(co.id) AS course_count\n    FROM categories c\n    LEFT JOIN courses co\n        ON co.category_id = c.id\n        AND co.status = 'active'\n    WHERE c.status = 'active'\n    GROUP BY c.id, c.name, c.name_kh, c.icon, c.description\n    ORDER BY c.created_at DESC, c.id DESC\n");
$categories = $stmt->fetchAll();
?>

<section class="categories-page">
    <div class="container">
        <div class="categories-page-header">
            <span class="categories-eyebrow">
                <i class="fa-solid fa-layer-group"></i>
                <?=t('LEARNING SKILLS','ជំនាញសិក្សា')?>
            </span>
            <h1><?=t('All Categories','ជំនាញទាំងអស់')?></h1>
            <p><?=t(
                'Explore all learning skills and categories available on eLearning College.',
                'ស្វែងរកជំនាញ និងប្រភេទសិក្សាទាំងអស់ដែលមាននៅលើ eLearning College។'
            )?></p>
        </div>

        <?php if(!empty($categories)): ?>
            <div class="all-categories-grid">
                <?php foreach($categories as $cat): ?>
                    <article class="all-category-card">
                        <div class="all-category-icon"><?=htmlspecialchars($cat['icon'] ?: '📚')?></div>
                        <div class="all-category-content">
                            <h2><?=htmlspecialchars($lang === 'kh' && !empty($cat['name_kh']) ? $cat['name_kh'] : $cat['name'])?></h2>

                            <?php if(!empty($cat['name_kh']) && $lang !== 'kh'): ?>
                                <div class="category-kh-name"><?=htmlspecialchars($cat['name_kh'])?></div>
                            <?php endif; ?>

                            <p><?=htmlspecialchars($cat['description'] ?: t(
                                'Explore courses and lessons in this learning skill.',
                                'ស្វែងរកវគ្គសិក្សា និងមេរៀនក្នុងជំនាញនេះ។'
                            ))?></p>

                            <div class="all-category-footer">
                                <span class="course-count">
                                    <i class="fa-solid fa-book-open"></i>
                                    <?=number_format((int)$cat['course_count'])?>
                                    <?=t('Courses','វគ្គសិក្សា')?>
                                </span>
                                <a href="courses.php?cat=<?=urlencode($cat['name'])?>" class="category-explore-btn">
                                    <?=t('Explore','ស្វែងរក')?>
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-categories">
                <div class="empty-icon">📚</div>
                <h2><?=t('No Categories Available','មិនទាន់មានជំនាញ')?></h2>
                <p><?=t(
                    'Categories added by the administrator will appear here.',
                    'ជំនាញដែល Admin បានបញ្ចូល នឹងបង្ហាញនៅទីនេះ។'
                )?></p>
            </div>
        <?php endif; ?>

        <div class="categories-back">
            <a href="index.php" class="btn outline">
                <i class="fa-solid fa-arrow-left"></i>
                <?=t('Back to Home','ត្រឡប់ទៅទំព័រដើម')?>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
