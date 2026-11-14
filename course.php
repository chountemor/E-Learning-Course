<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/config/auth.php';
require_once __DIR__.'/includes/lang.php';

$id=(int)($_GET['id']??0);
$s=$pdo->prepare('SELECT * FROM courses WHERE id=? AND status="active"');
$s->execute([$id]);
$c=$s->fetch();

if(!$c){
    http_response_code(404);
    require_once __DIR__.'/includes/header.php';
    echo '<main class="container section"><div class="empty-state"><div class="empty-icon">📚</div><h1>'.t('Course not found','រកមិនឃើញវគ្គសិក្សាទេ').'</h1><a class="btn" href="'.url('courses.php').'">'.t('Back to Courses','ត្រឡប់ទៅវគ្គសិក្សា').'</a></div></main>';
    require __DIR__.'/includes/footer.php'; exit;
}

// Load active lessons uploaded by Teachers/Admin for this course.
$lessonStmt = $pdo->prepare("
    SELECT id, title, title_kh, description, video_url, video_file, document_url, document_file, duration, lesson_order, created_at
    FROM lessons
    WHERE course_id = ? AND status = 'active'
    ORDER BY lesson_order ASC, id ASC
");
$lessonStmt->execute([$id]);
$course_lessons = $lessonStmt->fetchAll(PDO::FETCH_ASSOC);

$isLoggedIn=!empty($_SESSION['user_id']);
if(!$isLoggedIn){ $_SESSION['login_redirect']=$_SERVER['REQUEST_URI']??(app_base_url().'/course.php?id='.$id); }

function course_safe_url(?string $url): string { $url=trim((string)$url); if($url===''||!filter_var($url,FILTER_VALIDATE_URL))return ''; $scheme=strtolower((string)parse_url($url,PHP_URL_SCHEME)); return in_array($scheme,['http','https'],true)?$url:''; }
function course_youtube_embed(string $url): string { $host=strtolower((string)parse_url($url,PHP_URL_HOST)); $path=trim((string)parse_url($url,PHP_URL_PATH),'/'); parse_str((string)parse_url($url,PHP_URL_QUERY),$query); if($host==='youtu.be'){ $id=explode('/',$path)[0]??''; return $id!==''?'https://www.youtube.com/embed/'.rawurlencode($id).'?rel=0':''; } if(str_contains($host,'youtube.com')){ if(!empty($query['v']))return 'https://www.youtube.com/embed/'.rawurlencode((string)$query['v']).'?rel=0'; if(str_starts_with($path,'shorts/')){ $id=substr($path,7); return $id!==''?'https://www.youtube.com/embed/'.rawurlencode($id).'?rel=0':''; } if(str_starts_with($path,'embed/'))return 'https://www.youtube.com/'.$path; } return ''; }
function course_vimeo_embed(string $url): string { $host=strtolower((string)parse_url($url,PHP_URL_HOST)); $path=trim((string)parse_url($url,PHP_URL_PATH),'/'); if(!str_contains($host,'vimeo.com'))return ''; foreach(explode('/',$path) as $part)if(ctype_digit($part))return 'https://player.vimeo.com/video/'.rawurlencode($part); return ''; }
function course_is_direct_video(string $url): bool { return (bool)preg_match('/\.(mp4|webm|ogg|mov)$/i',(string)parse_url($url,PHP_URL_PATH)); }

$video_url=course_safe_url($c['video_url']??''); $pdf_url=course_safe_url($c['pdf_url']??'');
$uploaded_video=!empty($c['video_file'])?'media.php?course_id='.$id.'&type=course_video':'';
$uploaded_pdf=!empty($c['pdf_file'])?'media.php?course_id='.$id.'&type=course_pdf':'';
$youtube=$video_url?course_youtube_embed($video_url):''; $vimeo=$video_url?course_vimeo_embed($video_url):''; $video_embed=$youtube?:$vimeo; $direct_video=$video_url&&course_is_direct_video($video_url);
$has_video=(bool)($uploaded_video||$video_embed||$direct_video); $has_pdf=(bool)($uploaded_pdf||$pdf_url);
$page_title=course_title($c).' — eLearning College';
require __DIR__.'/includes/header.php';
?>
<main class="course-direct-page">
<section class="container section">
<a class="course-back" href="<?=url('courses.php')?>"><i class="fa-solid fa-arrow-left"></i> <?=t('Back to Courses','ត្រឡប់ទៅវគ្គសិក្សា')?></a>
<div class="course-direct-hero">
<div class="course-direct-cover"><?php if(!empty($c['image'])): ?><img src="<?=url('uploads/'.rawurlencode($c['image']))?>" alt="<?=htmlspecialchars(course_title($c))?>"><?php else: ?><div class="course-direct-placeholder"><i class="fa-solid fa-book-open"></i></div><?php endif; ?></div>
<div class="course-direct-info"><span class="eyebrow"><?=htmlspecialchars($c['category']??t('Course','វគ្គសិក្សា'))?></span><h1><?=htmlspecialchars(course_title($c))?></h1><?php if(!empty($c['title_kh'])&&$lang==='en'): ?><div class="course-kh-title"><?=htmlspecialchars($c['title_kh'])?></div><?php endif; ?><p><?=nl2br(htmlspecialchars(course_description($c)))?></p>
<div class="course-direct-meta"><span><i class="fa-regular fa-clock"></i> <?=htmlspecialchars($c['duration']?:t('Self-paced','រៀនតាមល្បឿនខ្លួនឯង'))?></span><span><i class="fa-solid fa-lock"></i> <?=t('Login required','ត្រូវចូលគណនីដើម្បីរៀន')?></span><?php if($has_video): ?><span class="media-badge video"><i class="fa-solid fa-video"></i> Video</span><?php endif;?><?php if($has_pdf): ?><span class="media-badge pdf"><i class="fa-solid fa-file-pdf"></i> PDF</span><?php endif;?></div>
</div></div>

<section class="course-lesson-library">
    <div class="course-lesson-library-head">
        <div>
            <span class="eyebrow">LESSON LIBRARY</span>
            <h2><?=t('Course Lessons','មេរៀនក្នុងវគ្គសិក្សា')?></h2>
            <p><?=t('Video and PDF materials uploaded by teachers appear here.','Video និង PDF ដែលគ្រូបានបញ្ចូល នឹងបង្ហាញនៅទីនេះ។')?></p>
        </div>
        <span class="lesson-count-badge"><?=count($course_lessons)?> <?=t('Lessons','មេរៀន')?></span>
    </div>

    <?php if($course_lessons): ?>
        <div class="course-lesson-list">
            <?php foreach($course_lessons as $index=>$cl): ?>
                <article class="course-lesson-item">
                    <div class="course-lesson-number"><?=sprintf('%02d',$index+1)?></div>
                    <div class="course-lesson-main">
                        <h3><?=htmlspecialchars($cl['title'])?></h3>
                        <?php if(!empty($cl['title_kh'])): ?><p class="course-lesson-kh"><?=htmlspecialchars($cl['title_kh'])?></p><?php endif; ?>
                        <div class="course-lesson-meta">
                            <?php if(!empty($cl['duration'])): ?><span><i class="fa-regular fa-clock"></i> <?=htmlspecialchars($cl['duration'])?></span><?php endif; ?>
                            <?php if(!empty($cl['video_file']) || !empty($cl['video_url'])): ?><span class="lesson-media video"><i class="fa-solid fa-video"></i> Video</span><?php endif; ?>
                            <?php if(!empty($cl['document_file']) || !empty($cl['document_url'])): ?><span class="lesson-media pdf"><i class="fa-solid fa-file-pdf"></i> PDF</span><?php endif; ?>
                        </div>
                    </div>
                    <a class="course-lesson-open" href="lesson.php?id=<?= (int)$cl['id']?>">
                        <i class="fa-solid fa-play"></i> <?=t('Open Lesson','បើកមេរៀន')?>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="course-no-lessons"><i class="fa-regular fa-folder-open"></i><strong><?=t('No lessons uploaded yet','មិនទាន់មានមេរៀនត្រូវបានបញ្ចូលនៅឡើយទេ')?></strong><span><?=t('Teachers can upload Video and PDF materials from the Teacher panel.','គ្រូអាចបញ្ចូល Video និង PDF ពី Teacher Panel បាន។')?></span></div>
    <?php endif; ?>
</section>

<?php if(!$isLoggedIn): ?>
<div class="course-lock-panel"><div class="course-lock-icon"><i class="fa-solid fa-lock"></i></div><div><span class="eyebrow">MEMBERS ONLY</span><h2><?=t('Login to unlock this course','ចូលគណនី ដើម្បីបើកមេរៀននេះ')?></h2><p><?=t('Video and PDF lessons are protected. Sign in or create a free account to continue learning.','វីដេអូ និង PDF ត្រូវបានការពារ។ សូមចូលគណនី ឬបង្កើតគណនីឥតគិតថ្លៃ ដើម្បីបន្តការសិក្សា។')?></p><div class="course-lock-actions"><a href="<?=url('Login/login.php')?>" data-auth-modal="login" class="btn"><i class="fa-solid fa-right-to-bracket"></i> <?=t('Sign In','ចូលគណនី')?></a><a href="<?=url('Login/register.php')?>" data-auth-modal="register" class="btn outline"><i class="fa-solid fa-user-plus"></i> <?=t('Create Free Account','បង្កើតគណនីឥតគិតថ្លៃ')?></a></div></div></div>
<?php elseif($has_video||$has_pdf): ?>
<div class="direct-content-grid">
<?php if($has_video): ?><section class="direct-media-card direct-video-card"><div class="direct-media-header"><div><span class="eyebrow">COURSE VIDEO</span><h2><?=t('Watch Video','មើលវីដេអូ')?></h2></div><?php if($video_url): ?><a class="media-open-btn" href="<?=htmlspecialchars($video_url)?>" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-up-right-from-square"></i> <?=t('Open','បើក')?></a><?php endif;?></div><div class="course-video-viewer"><?php if($uploaded_video): ?><video controls playsinline preload="metadata" class="direct-video" controlsList="nodownload"><source src="<?=htmlspecialchars($uploaded_video)?>">Your browser does not support HTML5 video.</video><?php elseif($video_embed): ?><iframe src="<?=htmlspecialchars($video_embed)?>" title="<?=htmlspecialchars(course_title($c))?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe><?php elseif($direct_video): ?><video controls playsinline preload="metadata" class="direct-video"><source src="<?=htmlspecialchars($video_url)?>"></video><?php endif;?></div></section><?php endif;?>
<?php if($has_pdf): $pdf_src=$uploaded_pdf?:$pdf_url; ?><section class="direct-media-card direct-pdf-card"><div class="direct-media-header"><div><span class="eyebrow">COURSE PDF</span><h2><?=t('Read PDF','អាន PDF')?></h2></div><a class="media-open-btn" href="<?=htmlspecialchars($pdf_src)?>" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-up-right-from-square"></i> <?=t('Open PDF','បើក PDF')?></a></div><div class="course-pdf-viewer"><iframe src="<?=htmlspecialchars($pdf_src)?>" title="Course PDF" loading="lazy"></iframe></div></section><?php endif;?>
</div>
<?php else: ?><div class="course-no-media"><div class="no-media-icon"><i class="fa-solid fa-photo-film"></i></div><h2><?=t('Course content is coming soon','មាតិកាវគ្គសិក្សានឹងមានឆាប់ៗនេះ')?></h2><p><?=t('The administrator has not added a video or PDF to this course yet.','អ្នកគ្រប់គ្រងមិនទាន់បានបន្ថែម Video ឬ PDF សម្រាប់វគ្គនេះនៅឡើយទេ។')?></p></div><?php endif; ?>
<div class="course-direct-actions"><a class="btn" href="<?=url('courses.php')?>"><i class="fa-solid fa-graduation-cap"></i> <?=t('Explore More Courses','ស្វែងរកវគ្គផ្សេងទៀត')?></a></div>
</section></main>
<?php require_once __DIR__.'/includes/footer.php'; ?>
