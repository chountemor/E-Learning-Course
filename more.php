<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/lang.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug !== '') {
    $st = $pdo->prepare("SELECT * FROM more_information WHERE slug=? AND status='published' LIMIT 1");
    $st->execute([$slug]);
    $item = $st->fetch();
} else {
    $item = $pdo->query("SELECT * FROM more_information WHERE status='published' ORDER BY sort_order ASC, id ASC LIMIT 1")->fetch();
}
if (!$item) {
    http_response_code(404);
    $page_title = 'Information Not Found';
    require __DIR__.'/includes/header.php';
    echo '<main class="container section"><div class="more-not-found"><div class="more-not-found-icon"><i class="fa-solid fa-circle-info"></i></div><h1>'.t('Information not found','រកមិនឃើញព័ត៌មានទេ').'</h1><a class="btn" href="'.url('index.php').'">'.t('Back Home','ត្រឡប់ទៅទំព័រដើម').'</a></div></main>';
    require __DIR__.'/includes/footer.php'; exit;
}

$page_title = ($lang==='kh' && !empty($item['title_kh']) ? $item['title_kh'] : $item['title_en']).' — eLearning College';
require __DIR__.'/includes/header.php';
$title = $lang==='kh' && !empty($item['title_kh']) ? $item['title_kh'] : $item['title_en'];
$eyebrow = $lang==='kh' && !empty($item['eyebrow_kh']) ? $item['eyebrow_kh'] : ($item['eyebrow_en'] ?? 'eLEARNING COLLEGE');
$desc = $lang==='kh' && !empty($item['description_kh']) ? $item['description_kh'] : ($item['description_en'] ?? '');
$content = $lang==='kh' && !empty($item['content_kh']) ? $item['content_kh'] : ($item['content_en'] ?? '');
?>
<main class="more-detail-page">
    <section class="more-detail-hero">
        <div class="container">
            <a class="more-breadcrumb" href="<?=url('index.php')?>"><i class="fa-solid fa-arrow-left"></i> <?=t('Back to Home','ត្រឡប់ទៅទំព័រដើម')?></a>
            <div class="more-detail-card">
                <div class="more-detail-visual">
                    <?php if(!empty($item['image'])): ?>
                        <img src="<?=url('uploads/'.rawurlencode($item['image']))?>" alt="<?=htmlspecialchars($title)?>">
                    <?php else: ?>
                        <div class="more-detail-icon"><i class="<?=htmlspecialchars($item['icon'] ?: 'fa-solid fa-circle-info')?>"></i></div>
                    <?php endif; ?>
                </div>
                <div class="more-detail-copy">
                    <span class="more-eyebrow"><?=htmlspecialchars($eyebrow)?></span>
                    <h1><?=htmlspecialchars($title)?></h1>
                    <p class="more-detail-lead"><?=nl2br(htmlspecialchars($desc))?></p>
                    <div class="more-detail-meta">
                        <span><i class="fa-solid fa-circle-check"></i> <?=t('Official College Information','ព័ត៌មានផ្លូវការរបស់មហាវិទ្យាល័យ')?></span>
                        <span><i class="fa-solid fa-graduation-cap"></i> eLearning College</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container more-detail-content-wrap">
        <article class="more-detail-content">
            <div class="more-content-heading">
                <span><?=t('DETAILS','ព័ត៌មានលម្អិត')?></span>
                <h2><?=htmlspecialchars($title)?></h2>
            </div>
            <div class="more-rich-content"><?=nl2br(htmlspecialchars($content))?></div>
        </article>

        <aside class="more-detail-sidebar">
            <div class="more-side-card">
                <div class="more-side-icon"><i class="fa-solid fa-book-open-reader"></i></div>
                <h3><?=t('Continue Learning','បន្តការសិក្សា')?></h3>
                <p><?=t('Explore free courses and continue building your skills.','ស្វែងរកវគ្គសិក្សាឥតគិតថ្លៃ និងបន្តបង្កើនជំនាញរបស់អ្នក។')?></p>
                <a class="btn" href="<?=url('courses.php')?>"><?=t('Browse Courses','មើលវគ្គសិក្សា')?> <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="more-side-card soft">
                <div class="more-side-icon"><i class="fa-solid fa-circle-question"></i></div>
                <h3><?=t('Need more information?','ត្រូវការព័ត៌មានបន្ថែម?')?></h3>
                <p><?=t('Return to the home page to explore more sections of eLearning College.','ត្រឡប់ទៅទំព័រដើម ដើម្បីស្វែងរកផ្នែកផ្សេងៗរបស់ eLearning College។')?></p>
                <a class="more-side-link" href="<?=url('index.php')?>"><?=t('Back Home','ត្រឡប់ទៅទំព័រដើម')?> <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </aside>
    </section>
</main>
<?php require __DIR__.'/includes/footer.php'; ?>
