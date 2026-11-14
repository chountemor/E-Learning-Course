<?php
require_once __DIR__ . '/../config/database.php';
require __DIR__ . '/layout.php';

$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare('SELECT * FROM lessons WHERE id = ?');
$s->execute([$id]);
$l = $s->fetch();
if (!$l) exit('Lesson not found');

$course_id = (int)$l['course_id'];
$error = '';
$dir = __DIR__ . '/../uploads/';
if (!is_dir($dir)) mkdir($dir, 0755, true);

function replace_lesson_file(array $file, string $dir, string $prefix, array $allowed, int $maxBytes, string $message, ?string $old): string
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

    if (!is_uploaded_file($file['tmp_name'] ?? '')) {
        throw new RuntimeException('Invalid uploaded file.');
    }

    $new = $prefix . bin2hex(random_bytes(12)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . $new)) {
        throw new RuntimeException('The uploaded file could not be saved. Check the uploads folder permissions and PHP upload limits.');
    }

    if ($old && is_file($dir . basename($old))) {
        @unlink($dir . basename($old));
    }

    return $new;
}

function valid_optional_url_edit(string $url): bool
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
    $vf = $l['video_file'] ?: null;
    $df = $l['document_file'] ?: null;

    if ($title === '') {
        $error = 'Lesson title is required.';
    } elseif (!valid_optional_url_edit($video_url)) {
        $error = 'Video URL is not valid. Use a complete http:// or https:// link.';
    } elseif (!valid_optional_url_edit($pdf_url)) {
        $error = 'PDF URL is not valid. Use a complete http:// or https:// link.';
    }

    try {
        if (!$error && !empty($_FILES['video']['name'])) {
            $vf = replace_lesson_file(
                $_FILES['video'], $dir, 'video_',
                ['mp4', 'webm', 'ogg', 'mov'],
                500 * 1024 * 1024,
                'Video must be MP4, WebM, OGG or MOV and max 500MB.',
                $vf
            );
        }

        if (!$error && !empty($_FILES['document']['name'])) {
            $df = replace_lesson_file(
                $_FILES['document'], $dir, 'pdf_',
                ['pdf'],
                20 * 1024 * 1024,
                'PDF must be a .pdf file and max 20MB.',
                $df
            );
        }
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }

    if (!$error) {
        $sql = 'UPDATE lessons
                SET title = ?, description = ?, video_url = ?, video_file = ?,
                    document_url = ?, document_file = ?, duration = ?, lesson_order = ?
                WHERE id = ?';
        $s = $pdo->prepare($sql);
        $s->execute([
            $title,
            $desc,
            $video_url !== '' ? $video_url : null,
            $vf,
            $pdf_url !== '' ? $pdf_url : null,
            $df,
            $dur !== '' ? $dur : null,
            $order,
            $id
        ]);

        header('Location: course_edit.php?id=' . $course_id);
        exit;
    }

    // Keep the submitted values visible if validation fails.
    $l['title'] = $title;
    $l['description'] = $desc;
    $l['duration'] = $dur;
    $l['lesson_order'] = $order;
    $l['video_url'] = $video_url;
    $l['document_url'] = $pdf_url;
}
?>

<div class="admincard form">
    <div class="head">
        <div>
            <span class="eyebrow">EDIT LESSON</span>
            <h2><?= htmlspecialchars($l['title']) ?></h2>
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
            <input name="title" value="<?= htmlspecialchars($l['title']) ?>" required>
        </label>

        <div class="two">
            <label>
                Lesson Order
                <input name="lesson_order" type="number" min="1" value="<?= (int)$l['lesson_order'] ?>">
            </label>
            <label>
                Duration
                <input name="duration" value="<?= htmlspecialchars($l['duration'] ?? '') ?>">
            </label>
        </div>

        <label>
            Description
            <textarea name="description" rows="5"><?= htmlspecialchars($l['description'] ?? '') ?></textarea>
        </label>

        <div class="upload-grid">
            <div class="upload-box">
                <span>🎬</span>
                <b>Replace Video</b>
                <input name="video" type="file" accept="video/mp4,video/webm,video/ogg,video/quicktime,.mp4,.webm,.ogg,.mov">
                <small>Current: <?= htmlspecialchars($l['video_file'] ?? 'None') ?></small>
            </div>

            <div class="upload-box">
                <span>📄</span>
                <b>Replace PDF</b>
                <input name="document" type="file" accept="application/pdf,.pdf">
                <small>Current: <?= htmlspecialchars($l['document_file'] ?? 'None') ?></small>
            </div>
        </div>

        <div class="two">
            <label>
                Video URL <small>(optional)</small>
                <input name="video_url" type="url" value="<?= htmlspecialchars($l['video_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=...">
                <small class="field-help">YouTube, YouTube Shorts, Vimeo, or a direct MP4/WebM URL.</small>
            </label>

            <label>
                PDF URL <small>(optional)</small>
                <input name="document_url" type="url" value="<?= htmlspecialchars($l['document_url'] ?? '') ?>" placeholder="https://example.com/lesson.pdf">
                <small class="field-help">Use a direct public PDF link.</small>
            </label>
        </div>

        <button class="btn" type="submit">Save Lesson</button>
    </form>
</div>

<?php require __DIR__ . '/end.php'; ?>
