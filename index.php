<?php
require_once __DIR__ . '/config/database.php';
$page_title = 'Learn Today. Lead Tomorrow.';
require_once __DIR__ . '/includes/header.php';

if (!empty($FEATURED_SUPPORTED)) {
    $courses = $pdo->query("SELECT * FROM courses WHERE status='active' AND featured=1 ORDER BY updated_at DESC, created_at DESC, id DESC LIMIT 3")->fetchAll();
} else {
    // Older databases without ALTER permission still load safely.
    $courses = $pdo->query("SELECT * FROM courses WHERE status='active' ORDER BY updated_at DESC, created_at DESC, id DESC LIMIT 3")->fetchAll();
}

$categories = $pdo->query("
    SELECT id, name, name_kh, icon, description
    FROM categories
    WHERE status='active'
    ORDER BY created_at DESC, id DESC
    LIMIT 7
")->fetchAll();

// Homepage shows only 6 categories. If there are more, show View All Categories.
$has_more_categories = count($categories) > 6;
$popular_categories = array_slice($categories, 0, 6);

$moreItems = [];
try {
    $moreItems = $pdo->query("
        SELECT *
        FROM more_information
        WHERE status='published'
        ORDER BY sort_order ASC, id ASC
        LIMIT 6
    ")->fetchAll();
} catch (Throwable $e) {
    $moreItems = [];
}


/* Display cards for Diploma & Certificate paths.
   These are presentation cards and can later be connected to a database table. */
$credentials = [
    [
        'icon' => '🎓',
        'type_en' => 'DIPLOMA',
        'type_kh' => 'សញ្ញាបត្រ',
        'title_en' => 'Professional Diploma',
        'title_kh' => 'សញ្ញាបត្រវិជ្ជាជីវៈ',
        'desc_en' => 'Build job-ready knowledge through a structured learning path and complete a professional diploma.',
        'desc_kh' => 'បង្កើនចំណេះដឹងសម្រាប់ការងារ តាមរយៈកម្មវិធីសិក្សាដែលមានរបៀបរៀបរយ និងទទួលបានសញ្ញាបត្រវិជ្ជាជីវៈ។',
        'meta_en' => 'Structured Program',
        'meta_kh' => 'កម្មវិធីសិក្សាមានរបៀបរៀបរយ'
    ],
    [
        'icon' => '📜',
        'type_en' => 'CERTIFICATE',
        'type_kh' => 'វិញ្ញាបនបត្រ',
        'title_en' => 'Professional Certificate',
        'title_kh' => 'វិញ្ញាបនបត្រវិជ្ជាជីវៈ',
        'desc_en' => 'Complete focused courses and earn a certificate that highlights your new skills and knowledge.',
        'desc_kh' => 'បញ្ចប់វគ្គសិក្សាដែលផ្តោតលើជំនាញជាក់លាក់ និងទទួលបានវិញ្ញាបនបត្របង្ហាញពីសមត្ថភាពរបស់អ្នក។',
        'meta_en' => 'Skill Focused',
        'meta_kh' => 'ផ្តោតលើជំនាញ'
    ],
    [
        'icon' => '🏆',
        'type_en' => 'ACHIEVEMENT',
        'type_kh' => 'សមិទ្ធផល',
        'title_en' => 'Course Completion',
        'title_kh' => 'ការបញ្ចប់វគ្គសិក្សា',
        'desc_en' => 'Track your progress, finish your learning goals and celebrate your course completion.',
        'desc_kh' => 'តាមដានវឌ្ឍនភាព បញ្ចប់គោលដៅសិក្សា និងអបអរសាទរការបញ្ចប់វគ្គសិក្សារបស់អ្នក។',
        'meta_en' => 'Learn & Complete',
        'meta_kh' => 'រៀន និងបញ្ចប់'
    ],
];
?>
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-content text-center mx-auto">

            <span class="hero-kicker">
                <i class="fa-solid fa-graduation-cap"></i>
                <?=t('MODERN ONLINE LEARNING','ការសិក្សាតាមអនឡាញទំនើប')?>
            </span>

            <h1>
                <?=t('Start your Free.','ចាប់ផ្តើមការសិក្សា')?>
                <br>
                <span><?=t('Online Course.','វគ្គសិក្សាអនឡាញ')?></span>
            </h1>

            <p class="mx-auto">
                <?=t(
                    'Discover professional online courses designed to build your skills and grow your career.',
                    'ស្វែងរកវគ្គសិក្សាតាមអនឡាញ ដែលជួយបង្កើនជំនាញ និងឱកាសការងាររបស់អ្នក។'
                )?>
            </p>

            <form class="search mx-auto" action="courses.php" method="get">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input
                    name="q"
                    type="search"
                    autocomplete="off"
                    placeholder="<?=t('What do you want to learn?','តើអ្នកចង់រៀនអ្វី?')?>"
                    aria-label="<?=t('What do you want to learn?','តើអ្នកចង់រៀនអ្វី?')?>"
                >
                <button class="btn" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <?=t('Search Courses','ស្វែងរកវគ្គសិក្សា')?>
                </button>
            </form>

            <div class="hero-badges">
                <span><i class="fa-solid fa-circle-check"></i> <?=t('100% Free','ឥតគិតថ្លៃ 100%')?></span>
                <span><i class="fa-solid fa-clock"></i> <?=t('Learn Anytime','រៀនបានគ្រប់ពេល')?></span>
                <span><i class="fa-solid fa-mobile-screen-button"></i> <?=t('Learn Anywhere','រៀនបានគ្រប់ទីកន្លែង')?></span>
            </div>

        </div>
    </div>
</section>

<section class="container section popular-categories-section">
    <div class="title">
        <small><?=t('EXPLORE CATEGORIES','ស្វែងរកតាមប្រភេទ')?></small>
        <h2><?=t('Popular Categories','ប្រភេទវគ្គពេញនិយម')?></h2>
    </div>

    <div class="category-grid">
        <?php foreach($popular_categories as $cat): ?>
            <article class="cat-card">
                <span class="cat-icon"><?=htmlspecialchars($cat['icon'] ?: '📚')?></span>
                <div class="cat-content">
                    <h4><?=htmlspecialchars($lang==='kh' && !empty($cat['name_kh']) ? $cat['name_kh'] : $cat['name'])?></h4>
                    <?php if(!empty($cat['description'])): ?>
                        <p class="cat-description"><?=htmlspecialchars($cat['description'])?></p>
                    <?php endif; ?>
                    <a href="courses.php?cat=<?=urlencode($cat['name'])?>" class="cat-link">
                        <?=t('View Courses →','មើលវគ្គសិក្សា →')?>
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if($has_more_categories): ?>
        <div class="categories-view-all">
            <a href="categories.php" class="btn categories-view-all-btn">
                <i class="fa-solid fa-layer-group"></i>
                <?=t('View All Categories','មើលជំនាញទាំងអស់')?>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    <?php endif; ?>
</section>

<section class="section bg-light" id="courses">
    <div class="container">
        <div class="title">
            <small><?=t('POPULAR COURSES','វគ្គសិក្សាពេញនិយម')?></small>
            <div class="featured-title-row"><div><h2><?=t('Featured Courses','វគ្គសិក្សាណែនាំ')?></h2></div><a class="featured-all-link" href="<?=url('featured.php')?>"><?=t('View Featured Courses','មើលវគ្គសិក្សាណែនាំទាំងអស់')?> <i class="fa-solid fa-arrow-right"></i></a></div>
            <p class="featured-courses-subtitle">
                <?=t('Click a course to watch the video and read the PDF directly — no Lessons page needed.','ចុចលើវគ្គសិក្សា ដើម្បីមើលវីដេអូ និងអាន PDF ដោយផ្ទាល់ — មិនចាំបាច់មានទំព័រ Lessons ទៀតទេ។')?>
            </p>
        </div>

        <div class="grid featured-course-grid">
            <?php if(!$courses): ?><div class="empty-state featured-empty" style="grid-column:1/-1"><div class="empty-icon">⭐</div><h3><?=t('Featured courses are being prepared','វគ្គសិក្សាណែនាំកំពុងរៀបចំ')?> </h3><p><?=t('The Administrator can select up to three featured courses.','Admin អាចជ្រើសរើសវគ្គសិក្សាណែនាំបានតែ ៣ វគ្គ។')?></p><a class="btn outline" href="<?=url('courses.php')?>"><?=t('Browse All Courses','មើលវគ្គសិក្សាទាំងអស់')?></a></div><?php endif; ?>
            <?php foreach($courses as $c): ?>
                <?php
                    $hasVideo = !empty($c['video_file']) || !empty($c['video_url']);
                    $hasPdf = !empty($c['pdf_file']) || !empty($c['pdf_url']);
                ?>
                <article class="course featured-course-card">
                    <div class="featured-course-image">
                        <?php if(!empty($c['image'])): ?>
                            <img src="uploads/<?=htmlspecialchars($c['image'])?>" alt="<?=htmlspecialchars(course_title($c))?>">
                        <?php else: ?>
                            <div class="placeholder">📚</div>
                        <?php endif; ?>
                        <div class="featured-media-badges">
                            <?php if($hasVideo): ?><span><i class="fa-solid fa-video"></i> Video</span><?php endif; ?>
                            <?php if($hasPdf): ?><span><i class="fa-solid fa-file-pdf"></i> PDF</span><?php endif; ?>
                        </div>
                    </div>

                    <div class="body">
                        <small><?=htmlspecialchars($c['category']??t('General','ទូទៅ'))?></small>
                        <h3><?=htmlspecialchars(course_title($c))?></h3>
                        <p><?=htmlspecialchars(mb_strimwidth(course_description($c),0,110,'...'))?></p>
                        <div class="featured-course-meta">
                            <span><i class="fa-regular fa-clock"></i> <?=htmlspecialchars($c['duration'] ?: t('Self-paced','រៀនតាមល្បឿនខ្លួនឯង'))?></span>
                            <span class="free-badge">FREE</span>
                        </div>
                        <a class="btn small featured-view-btn" href="course.php?id=<?=$c['id']?>">
                            <i class="fa-solid fa-play"></i>
                            <?=t('View Course','មើលវគ្គសិក្សា')?> →
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- =========================================================
     DIPLOMAS & CERTIFICATES — PREMIUM SECTION
========================================================= -->
<section class="section credentials-section" id="diplomas-certificates">
    <div class="container">
        <div class="credentials-heading">
            <div class="credentials-heading-copy">
                <span class="credentials-eyebrow">
                    <i class="fa-solid fa-award"></i>
                    <?=t('DIPLOMAS & CERTIFICATES','សញ្ញាបត្រ និងវិញ្ញាបនបត្រ')?>
                </span>
                <h2><?=t('Learn with purpose.','រៀនដោយមានគោលដៅ។')?><br><span><?=t('Show what you can do.','បង្ហាញពីសមត្ថភាពរបស់អ្នក។')?></span></h2>
                <p><?=t('Complete a learning path, build practical skills, and keep a clear record of your achievements.','បញ្ចប់ផ្លូវសិក្សា បង្កើនជំនាញជាក់ស្តែង និងរក្សាទុកសមិទ្ធផលសិក្សារបស់អ្នកឱ្យបានច្បាស់លាស់។')?></p>
            </div>

            <div class="credentials-proof">
                <div class="proof-icon"><i class="fa-solid fa-certificate"></i></div>
                <div>
                    <strong><?=t('Recognition that matters','ការទទួលស្គាល់ដែលមានតម្លៃ')?></strong>
                    <small><?=t('Track your progress from learning to completion.','តាមដានវឌ្ឍនភាពពីការរៀនរហូតដល់បញ្ចប់។')?></small>
                </div>
            </div>
        </div>

        <div class="credentials-grid">
            <?php foreach($credentials as $index => $item): ?>
                <article class="credential-card credential-card-<?=($index + 1)?>">
                    <div class="credential-card-glow"></div>
                    <div class="credential-top">
                        <div class="credential-icon"><span><?=$item['icon']?></span></div>
                        <span class="credential-type">
                            <?=htmlspecialchars($lang === 'kh' ? $item['type_kh'] : $item['type_en'])?>
                        </span>
                    </div>

                    <div class="credential-body">
                        <h3><?=htmlspecialchars($lang === 'kh' ? $item['title_kh'] : $item['title_en'])?></h3>
                        <p><?=htmlspecialchars($lang === 'kh' ? $item['desc_kh'] : $item['desc_en'])?></p>
                    </div>

                    <div class="credential-meta">
                        <span><i class="fa-solid fa-circle-check"></i><?=htmlspecialchars($lang === 'kh' ? $item['meta_kh'] : $item['meta_en'])?></span>
                        <a href="<?=url('courses.php')?>"><?=t('Explore','ស្វែងរក')?> <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="credentials-note">
            <div class="credentials-note-icon"><i class="fa-solid fa-lightbulb"></i></div>
            <p><?=t('Tip: Start with a certificate-focused course, then continue into a structured diploma path as your skills grow.','គន្លឹះ៖ ចាប់ផ្តើមពីវគ្គដែលផ្តោតលើវិញ្ញាបនបត្រ ហើយបន្តទៅកម្មវិធីសញ្ញាបត្រដែលមានរបៀបរៀបរយ នៅពេលជំនាញរបស់អ្នកកើនឡើង។')?></p>
            <a href="<?=url('courses.php')?>"><?=t('Start Learning','ចាប់ផ្តើមរៀន')?> <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>


<!-- =========================================================
     MORE FROM eLEARNING COLLEGE
========================================================= -->
<?php if($moreItems): ?>
<section class="section more-college-section">
    <div class="container">
        <div class="more-section-heading">
            <div>
                <span class="more-eyebrow"><i class="fa-solid fa-sparkles"></i> <?=t('MORE FROM eLEARNING COLLEGE','ព័ត៌មានបន្ថែមពី eLearning College')?></span>
                <h2><?=t('Discover more about your learning journey.','ស្វែងយល់បន្ថែមអំពីដំណើរសិក្សារបស់អ្នក។')?></h2>
                <p><?=t('Explore the college, learning process, achievements and latest information in one place.','ស្វែងយល់អំពីមហាវិទ្យាល័យ របៀបសិក្សា សមិទ្ធផល និងព័ត៌មានថ្មីៗនៅកន្លែងតែមួយ។')?></p>
            </div>
            <a class="more-heading-link" href="<?=url('more.php')?>"><?=t('View all','មើលទាំងអស់')?> <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="more-college-grid">
            <?php foreach($moreItems as $mi): ?>
                <article class="more-college-card">
                    <div class="more-card-media">
                        <?php if(!empty($mi['image'])): ?>
                            <img src="<?=url('uploads/'.rawurlencode($mi['image']))?>" alt="<?=htmlspecialchars($lang==='kh' && $mi['title_kh'] ? $mi['title_kh'] : $mi['title_en'])?>">
                        <?php else: ?>
                            <div class="more-card-icon"><i class="<?=htmlspecialchars($mi['icon'] ?: 'fa-solid fa-circle-info')?>"></i></div>
                        <?php endif; ?>
                        <span class="more-card-number"><?=str_pad((string)$mi['sort_order'],2,'0',STR_PAD_LEFT)?></span>
                    </div>
                    <div class="more-card-body">
                        <span><?=htmlspecialchars($lang==='kh' && $mi['eyebrow_kh'] ? $mi['eyebrow_kh'] : ($mi['eyebrow_en'] ?? 'eLEARNING COLLEGE'))?></span>
                        <h3><?=htmlspecialchars($lang==='kh' && $mi['title_kh'] ? $mi['title_kh'] : $mi['title_en'])?></h3>
                        <p><?=htmlspecialchars(mb_strimwidth($lang==='kh' && $mi['description_kh'] ? $mi['description_kh'] : ($mi['description_en'] ?? ''),0,135,'...'))?></p>
                        <a class="more-detail-link" href="<?=url('more.php?slug='.rawurlencode($mi['slug']))?>">
                            <?=t('View Detail','មើលព័ត៌មានលម្អិត')?> <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="container section"><div class="title"><small><?=t('WHY CHOOSE US','ហេតុអ្វីជ្រើសរើសយើង')?></small><h2><?=t('Why Choose eLearning College?','ហេតុអ្វីជ្រើសរើស eLearning College?')?></h2></div>
<div class="features-grid">
<div class="feature-card"><div class="badge-icon">🏷️</div><h3><?=t('100% Free Learning','ការសិក្សាឥតគិតថ្លៃ 100%')?></h3><p><?=t('All courses are completely free of charge and accessible from anywhere.','វគ្គសិក្សាទាំងអស់ឥតគិតថ្លៃ និងអាចចូលរៀនបានពីគ្រប់ទីកន្លែង។')?></p></div>
<div class="feature-card"><div class="badge-icon">🎓</div><h3><?=t('Flexible Online Learning','ការសិក្សាតាមពេលវេលាដែលអ្នកចង់')?></h3><p><?=t('Study in your own time and at your own pace without deadlines.','អ្នកអាចរៀនតាមពេលវេលា និងល្បឿនរបស់ខ្លួនដោយមិនមានកាលកំណត់។')?></p></div>
<div class="feature-card"><div class="badge-icon">📜</div><h3><?=t('Endorsed Certificates','វិញ្ញាបនបត្រ')?></h3><p><?=t('Complete your course and strengthen your learning profile with certificates.','បញ្ចប់វគ្គសិក្សា និងបង្កើនប្រវត្តិសិក្សារបស់អ្នកជាមួយវិញ្ញាបនបត្រ។')?></p></div>
</div></section>

<section class="section bg-light"><div class="container"><div class="title"><small><?=t('QUESTIONS & ANSWERS','សំណួរ និងចម្លើយ')?></small><h2><?=t('Frequently Asked Questions','សំណួរដែលសួរញឹកញាប់')?></h2></div>
<div class="faq-wrapper">
<details class="faq-item"><summary><?=t('What is eLearning?','តើ eLearning ជាអ្វី?')?></summary><p><?=t('eLearning is learning delivered digitally through computers, tablets or phones.','eLearning គឺជាការសិក្សាតាមប្រព័ន្ធឌីជីថល តាមរយៈកុំព្យូទ័រ ថេប្លេត ឬទូរស័ព្ទ។')?></p></details>
<details class="faq-item"><summary><?=t('How effective is online learning?','តើការរៀនតាមអនឡាញមានប្រសិទ្ធភាពដែរឬទេ?')?></summary><p><?=t('Online learning lets students learn at their own speed and review materials anytime.','ការរៀនតាមអនឡាញអនុញ្ញាតឱ្យសិស្សរៀនតាមល្បឿនរបស់ខ្លួន និងមើលមេរៀនឡើងវិញបានគ្រប់ពេល។')?></p></details>
<details class="faq-item"><summary><?=t('Can I study more than one course?','តើខ្ញុំអាចរៀនច្រើនជាងមួយវគ្គបានទេ?')?></summary><p><?=t('Yes. You can study multiple courses based on your available time.','បាន។ អ្នកអាចចុះឈ្មោះ និងរៀនច្រើនវគ្គតាមពេលវេលាដែលអ្នកមាន។')?></p></details>
</div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
