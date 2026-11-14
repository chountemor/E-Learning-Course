<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/config/auth.php';
require_once __DIR__.'/includes/lang.php';
$page_title='Featured Courses — eLearning College';
require __DIR__.'/includes/header.php';
$courses=[];
if (!empty($FEATURED_SUPPORTED)) {
    $courses=$pdo->query("SELECT * FROM courses WHERE status='active' AND featured=1 ORDER BY updated_at DESC, created_at DESC, id DESC")->fetchAll();
} else {
    $courses=$pdo->query("SELECT * FROM courses WHERE status='active' ORDER BY updated_at DESC, created_at DESC, id DESC LIMIT 3")->fetchAll();
}
?>
<main class="container section">
    <div class="section-head"><div><span class="eyebrow">FEATURED COURSES</span><h1><?=t('Featured Courses','វគ្គសិក្សាណែនាំ')?></h1><p><?=t('Courses selected by the Administrator for the homepage.','វគ្គសិក្សាដែល Admin បានជ្រើសរើសសម្រាប់បង្ហាញនៅទំព័រដើម។')?></p></div></div>
    <div class="grid course-grid">
    <?php if(!$courses): ?><div class="empty-state" style="grid-column:1/-1"><div class="empty-icon">⭐</div><h2><?=t('No featured courses yet','មិនទាន់មានវគ្គសិក្សាណែនាំនៅឡើយទេ')?></h2></div>
    <?php else: foreach($courses as $c): ?>
        <article class="course"><div class="course-media"><?php if(!empty($c['image'])):?><img src="<?=url('uploads/'.rawurlencode($c['image']))?>" alt="<?=e($c['title'])?>"><?php else:?><div class="course-placeholder">🎓</div><?php endif;?></div><div class="course-body"><span class="eyebrow"><?=e($c['category']??'Course')?></span><h3><?=e($c['title'])?></h3><p><?=e($c['description'])?></p><a class="btn small" href="<?=url('course.php?id='.(int)$c['id'])?>"><?=t('View Course','មើលវគ្គសិក្សា')?> →</a></div></article>
    <?php endforeach; endif; ?>
    </div>
</main>
<?php require __DIR__.'/includes/footer.php'; ?>
