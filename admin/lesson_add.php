<?php
require_once __DIR__ . '/../config/database.php';
require __DIR__ . '/layout.php';

$course_id = (int)($_GET['course_id'] ?? 0);
$s = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
$s->execute([$course_id]);
$course = $s->fetch();
if (!$course) exit('Course not found');

$error = '';
$dir = __DIR__ . '/../uploads/';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

function upload_lesson_file(array $file, string $dir, string $prefix, array $allowed, int $maxBytes, string $message): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException($message);
    }

    if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxBytes) {
        throw new RuntimeException($message);
    }

    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException($message);
    }

    $name = $prefix . bin2hex(random_bytes(12)) . '.' . $ext;
    $target = $dir . $name;

    if (!is_uploaded_file($file['tmp_name'] ?? '') || !move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('The uploaded file could not be saved. Check the uploads folder permissions and PHP upload limits.');
    }

    return $name;
}

function valid_optional_url(string $url): bool
{
    if ($url === '') return true;
    if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
    $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? null)) {
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $dur = trim($_POST['duration'] ?? '');
    $order = max(1, (int)($_POST['lesson_order'] ?? 1));
    $video_url = trim($_POST['video_url'] ?? '');
    $pdf_url = trim($_POST['document_url'] ?? '');
    $vf = '';
    $df = '';

    if ($title === '') {
        $error = 'Lesson title is required.';
    } elseif (!valid_optional_url($video_url)) {
        $error = 'Video URL is not valid. Use a complete http:// or https:// link.';
    } elseif (!valid_optional_url($pdf_url)) {
        $error = 'PDF URL is not valid. Use a complete http:// or https:// link.';
    }

    try {
        if (!$error && !empty($_FILES['video']['name'])) {
            $vf = upload_lesson_file(
                $_FILES['video'],
                $dir,
                'video_',
                ['mp4', 'webm', 'ogg', 'mov'],
                500 * 1024 * 1024,
                'Video must be MP4, WebM, OGG or MOV and max 500MB.'
            );
        }

        if (!$error && !empty($_FILES['document']['name'])) {
            $df = upload_lesson_file(
                $_FILES['document'],
                $dir,
                'pdf_',
                ['pdf'],
                20 * 1024 * 1024,
                'PDF must be a .pdf file and max 20MB.'
            );
        }
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }

    if (!$error) {
        // IMPORTANT: use a parameter for status. Do not write \"active\" inside a single-quoted PHP string.
        $sql = 'INSERT INTO lessons
                (course_id, title, description, video_url, video_file, document_url, document_file, duration, lesson_order, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $s = $pdo->prepare($sql);
        $s->execute([
            $course_id,
            $title,
            $desc,
            $video_url !== '' ? $video_url : null,
            $vf !== '' ? $vf : null,
            $pdf_url !== '' ? $pdf_url : null,
            $df !== '' ? $df : null,
            $dur !== '' ? $dur : null,
            $order,
            'active'
        ]);

        header('Location: course_edit.php?id=' . $course_id);
        exit;
    }
}
?>

<div class="admincard form">
    <div class="head">
        <div>
            <span class="eyebrow">ADD LESSON</span>
            <h2><?= htmlspecialchars($course['title']) ?></h2>
        </div>
        <a class="btn outline" href="course_edit.php?id=<?= $course_id ?>">← Back</a>
    </div>

    <?php if ($error): ?>
        <div class="alert err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

        <label>
            Lesson Title
            <input name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" placeholder="e.g. Lesson 01 — Introduction" required>
        </label>

        <div class="two">
            <label>
                Lesson Order
                <input name="lesson_order" type="number" min="1" value="<?= (int)($_POST['lesson_order'] ?? 1) ?>">
            </label>
            <label>
                Duration
                <input name="duration" value="<?= htmlspecialchars($_POST['duration'] ?? '') ?>" placeholder="10:30">
            </label>
        </div>

        <label>
            Description
            <textarea name="description" rows="4" placeholder="What will students learn in this lesson?"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </label>

        <div class="upload-grid">
            <div class="upload-box">
                <span>🎬</span>
                <b>Video File</b>
                <input name="video" type="file" accept="video/mp4,video/webm,video/ogg,video/quicktime,.mp4,.webm,.ogg,.mov">
                <small>MP4 / WebM / OGG / MOV — Max 500MB</small>
            </div>

            <div class="upload-box">
                <span>📄</span>
                <b>PDF File</b>
                <input name="document" type="file" accept="application/pdf,.pdf">
                <small>PDF — Max 20MB</small>
            </div>
        </div>

        <div class="two">
            <label>
                Video URL <small>(optional)</small>
                <input name="video_url" type="url" value="<?= htmlspecialchars($_POST['video_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=...">
                <small class="field-help">YouTube, YouTube Shorts, Vimeo, or a direct MP4/WebM URL.</small>
            </label>

            <label>
                PDF URL <small>(optional)</small>
                <input name="document_url" type="url" value="<?= htmlspecialchars($_POST['document_url'] ?? '') ?>" placeholder="https://example.com/lesson.pdf">
                <small class="field-help">Use a direct public PDF link.</small>
            </label>
        </div>

        <button class="btn" type="submit">Publish Lesson →</button>
    </form>
</div>

<?php require __DIR__ . '/end.php'; ?>
