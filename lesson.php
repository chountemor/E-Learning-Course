<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/includes/lang.php';

/*
|--------------------------------------------------------------------------
| Get Lesson ID
|--------------------------------------------------------------------------
*/
$id = (int) ($_GET['id'] ?? 0);

/*
|--------------------------------------------------------------------------
| Get Lesson Data
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare(
    "SELECT
        l.*,
        c.title AS course_title,
        c.id AS course_id
     FROM lessons l
     JOIN courses c ON c.id = l.course_id
     WHERE l.id = ?
       AND l.status = 'active'
       AND c.status = 'active'
     LIMIT 1"
);

$stmt->execute([$id]);

$lesson = $stmt->fetch();

/*
|--------------------------------------------------------------------------
| Lesson Not Found
|--------------------------------------------------------------------------
*/
if (!$lesson) {

    http_response_code(404);

    require __DIR__ . '/includes/header.php';
    ?>

    <main class="container section">

        <div class="empty-state">

            <h1>Lesson not found</h1>

        </div>

    </main>

    <?php

    require __DIR__ . '/includes/footer.php';

    exit;
}

/*
|--------------------------------------------------------------------------
| Login Status
|--------------------------------------------------------------------------
*/
$isLoggedIn = !empty($_SESSION['user_id']);

/*
|--------------------------------------------------------------------------
| Save Current Page for Login Redirect
|--------------------------------------------------------------------------
*/
if (!$isLoggedIn) {

    $_SESSION['login_redirect'] =
        $_SERVER['REQUEST_URI']
        ?? ('lesson.php?id=' . $id);
}

/*
|--------------------------------------------------------------------------
| Safe Media URL
|--------------------------------------------------------------------------
*/
function safe_media_url(?string $url): string
{
    $url = trim((string) $url);

    if (
        $url === ''
        || !filter_var($url, FILTER_VALIDATE_URL)
    ) {
        return '';
    }

    $scheme = strtolower(
        (string) parse_url($url, PHP_URL_SCHEME)
    );

    if (!in_array($scheme, ['http', 'https'], true)) {
        return '';
    }

    return $url;
}

/*
|--------------------------------------------------------------------------
| YouTube Embed URL
|--------------------------------------------------------------------------
*/
function youtube_embed_url(string $url): string
{
    $host = strtolower(
        (string) parse_url($url, PHP_URL_HOST)
    );

    $path = trim(
        (string) parse_url($url, PHP_URL_PATH),
        '/'
    );

    parse_str(
        (string) parse_url($url, PHP_URL_QUERY),
        $query
    );

    /*
    |--------------------------------------------------------------------------
    | youtu.be/VIDEO_ID
    |--------------------------------------------------------------------------
    */
    if ($host === 'youtu.be') {

        $id = explode('/', $path)[0] ?? '';

        if ($id === '') {
            return '';
        }

        return 'https://www.youtube.com/embed/'
            . rawurlencode($id)
            . '?rel=0';
    }

    /*
    |--------------------------------------------------------------------------
    | youtube.com
    |--------------------------------------------------------------------------
    */
    if (str_contains($host, 'youtube.com')) {

        /*
        | Normal URL
        | https://www.youtube.com/watch?v=VIDEO_ID
        */
        if (!empty($query['v'])) {

            return 'https://www.youtube.com/embed/'
                . rawurlencode((string) $query['v'])
                . '?rel=0';
        }

        /*
        | YouTube Shorts
        | https://www.youtube.com/shorts/VIDEO_ID
        */
        if (str_starts_with($path, 'shorts/')) {

            $id = substr($path, 7);

            if ($id === '') {
                return '';
            }

            return 'https://www.youtube.com/embed/'
                . rawurlencode($id)
                . '?rel=0';
        }

        /*
        | Already Embed URL
        */
        if (str_starts_with($path, 'embed/')) {

            return 'https://www.youtube.com/' . $path;
        }
    }

    return '';
}

/*
|--------------------------------------------------------------------------
| Vimeo Embed URL
|--------------------------------------------------------------------------
*/
function vimeo_embed_url(string $url): string
{
    $host = strtolower(
        (string) parse_url($url, PHP_URL_HOST)
    );

    $path = trim(
        (string) parse_url($url, PHP_URL_PATH),
        '/'
    );

    if (!str_contains($host, 'vimeo.com')) {
        return '';
    }

    foreach (explode('/', $path) as $part) {

        if (ctype_digit($part)) {

            return 'https://player.vimeo.com/video/'
                . rawurlencode($part);
        }
    }

    return '';
}

/*
|--------------------------------------------------------------------------
| Check Direct Video URL
|--------------------------------------------------------------------------
*/
function is_direct_video_url(string $url): bool
{
    return (bool) preg_match(
        '/\.(mp4|webm|ogg|mov)(?:$|\?)/i',
        (string) parse_url($url, PHP_URL_PATH)
    );
}

/*
|--------------------------------------------------------------------------
| Prepare Media URLs
|--------------------------------------------------------------------------
*/
$video_url = safe_media_url(
    $lesson['video_url'] ?? ''
);

$pdf_url = safe_media_url(
    $lesson['document_url'] ?? ''
);

$youtube = $video_url
    ? youtube_embed_url($video_url)
    : '';

$vimeo = $video_url
    ? vimeo_embed_url($video_url)
    : '';

$external_video_embed = $youtube ?: $vimeo;

$direct_video = (
    $video_url !== ''
    && is_direct_video_url($video_url)
);

$file_video = !empty($lesson['video_file']);

$file_pdf = !empty($lesson['document_file']);

/*
|--------------------------------------------------------------------------
| Page Title
|--------------------------------------------------------------------------
*/
$page_title =
    $lesson['title']
    . ' — eLearning College';

/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/
require __DIR__ . '/includes/header.php';

?>

<main class="container section">

    <!-- Back to Course -->
    <a
        class="back"
        href="course.php?id=<?= (int) $lesson['course_id'] ?>"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Back to
        <?= htmlspecialchars($lesson['course_title']) ?>

    </a>


    <div class="lesson-page">

        <!-- =========================================================
             LESSON HEADER
             ========================================================= -->
        <div class="lesson-top">

            <span class="eyebrow">
                LESSON
            </span>

            <h1>
                <?= htmlspecialchars($lesson['title']) ?>
            </h1>

            <?php if (!empty($lesson['duration'])): ?>

                <div class="lesson-duration">

                    <i class="fa-regular fa-clock"></i>

                    <?= htmlspecialchars($lesson['duration']) ?>

                </div>

            <?php endif; ?>

            <?php if (!empty($lesson['description'])): ?>

                <p>
                    <?= nl2br(
                        htmlspecialchars(
                            $lesson['description']
                        )
                    ) ?>
                </p>

            <?php endif; ?>

        </div>


        <?php if (!$isLoggedIn): ?>

            <!-- =====================================================
                 LOGIN REQUIRED
                 ===================================================== -->
            <div class="lesson-lock-card">

                <div class="lesson-lock-icon">

                    <i class="fa-solid fa-lock"></i>

                </div>

                <div>

                    <span class="eyebrow">
                        LOGIN REQUIRED
                    </span>

                    <h2>
                        <?= t(
                            'Login to view this lesson',
                            'ចូលគណនី ដើម្បីមើលមេរៀននេះ'
                        ) ?>
                    </h2>

                    <p>
                        <?= t(
                            'Please sign in before accessing course video and PDF files.',
                            'សូមចូលគណនីជាមុនសិន មុនពេលចូលមើលវីដេអូ និង PDF។'
                        ) ?>
                    </p>

                    <div class="course-lock-actions">

                        <!-- Sign In -->
                        <a
                            class="btn"
                            href="Login/login.php"
                            data-auth-modal="login"
                        >

                            <i class="fa-solid fa-right-to-bracket"></i>

                            Sign In

                        </a>

                        <!-- Register -->
                        <a
                            class="btn outline"
                            href="Login/register.php"
                            data-auth-modal="register"
                        >

                            <i class="fa-solid fa-user-plus"></i>

                            Create a free account

                        </a>

                    </div>

                </div>

            </div>


        <?php else: ?>


            <!-- =====================================================
                 VIDEO SECTION
                 ===================================================== -->
            <?php if (
                $file_video
                || $external_video_embed
                || $direct_video
            ): ?>

                <section class="media-card video-card">

                    <!-- Video Header -->
                    <div class="media-head dark-head">

                        <div>

                            <span class="eyebrow">
                                COURSE VIDEO
                            </span>

                            <b>
                                🎬 Lesson Video
                            </b>

                        </div>

                        <?php if ($video_url): ?>

                            <a
                                class="btn small outline-light"
                                href="<?= htmlspecialchars($video_url) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >

                                Open Video ↗

                            </a>

                        <?php endif; ?>

                    </div>


                    <!-- Uploaded Video -->
                    <?php if ($file_video): ?>

                        <video
                            controls
                            playsinline
                            preload="metadata"
                            controlsList="nodownload"
                            class="lesson-video"
                        >

                            <source
                                src="media.php?id=<?= (int) $lesson['id'] ?>&type=video"
                                type="video/mp4"
                            >

                            Your browser does not support video playback.

                        </video>


                    <!-- YouTube / Vimeo -->
                    <?php elseif ($external_video_embed): ?>

                        <div class="video-embed">

                            <iframe
                                src="<?= htmlspecialchars($external_video_embed) ?>"
                                title="<?= htmlspecialchars($lesson['title']) ?>"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>

                        </div>


                    <!-- Direct Video -->
                    <?php elseif ($direct_video): ?>

                        <video
                            controls
                            playsinline
                            preload="metadata"
                            class="lesson-video"
                        >

                            <source
                                src="<?= htmlspecialchars($video_url) ?>"
                            >

                            Your browser does not support video playback.

                        </video>

                    <?php endif; ?>

                </section>

            <?php endif; ?>


            <!-- =====================================================
                 PDF SECTION
                 ===================================================== -->
            <?php if ($file_pdf || $pdf_url): ?>

                <?php

                if ($file_pdf) {

                    $pdf_src =
                        'media.php?id='
                        . (int) $lesson['id']
                        . '&type=pdf';

                } else {

                    $pdf_src = $pdf_url;
                }

                ?>

                <section class="media-card pdf-card">

                    <!-- PDF Header -->
                    <div class="media-head">

                        <div>

                            <span class="eyebrow">
                                COURSE PDF
                            </span>

                            <b>
                                📄 Lesson PDF
                            </b>

                        </div>

                        <a
                            class="btn small"
                            href="<?= htmlspecialchars($pdf_src) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >

                            Open PDF ↗

                        </a>

                    </div>


                    <!-- PDF Viewer -->
                    <iframe
                        src="<?= htmlspecialchars($pdf_src) ?>"
                        title="Lesson PDF"
                        loading="lazy"
                    ></iframe>

                </section>

            <?php endif; ?>


            <!-- =====================================================
                 EMPTY CONTENT
                 ===================================================== -->
            <?php if (
                !$file_video
                && !$external_video_embed
                && !$direct_video
                && !$file_pdf
                && !$pdf_url
            ): ?>

                <div class="empty">

                    This lesson does not have video or PDF content yet.

                </div>

            <?php endif; ?>


            <!-- =====================================================
                 LESSON ACTIONS
                 ===================================================== -->
            <div class="lesson-actions">

                <a
                    class="btn outline"
                    href="course.php?id=<?= (int) $lesson['course_id'] ?>"
                >

                    ← Course Lessons

                </a>

            </div>

        <?php endif; ?>

    </div>

</main>


<?php

/*
|--------------------------------------------------------------------------
| Footer
|--------------------------------------------------------------------------
*/
require __DIR__ . '/includes/footer.php';

?>