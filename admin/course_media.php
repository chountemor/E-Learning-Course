<?php
require_once __DIR__.'/../config/database.php';
require __DIR__.'/layout.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) exit('Invalid course ID');

$stmt = $pdo->prepare('SELECT * FROM courses WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$course = $stmt->fetch();
if (!$course) exit('Course not found');

$error = '';
$success = '';
$uploadDir = __DIR__.'/../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

function media_delete(string $filename, string $dir): void {
    if ($filename !== '' && is_file($dir.$filename)) @unlink($dir.$filename);
}

function media_upload(array $file, string $type, string $dir, string &$error): string {
    if (empty($file['name'])) return '';

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Check PHP upload limits and try again.';
        return '';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($type === 'image') {
        $max = 5 * 1024 * 1024;
        $allowed = ['jpg','jpeg','png','webp'];
        $label = 'JPG, JPEG, PNG or WebP (max 5MB)';
        $prefix = 'course';
    } elseif ($type === 'video') {
        $max = 500 * 1024 * 1024;
        $allowed = ['mp4','webm','ogg','mov'];
        $label = 'MP4, WebM, OGG or MOV (max 500MB)';
        $prefix = 'course_video';
    } else {
        $max = 30 * 1024 * 1024;
        $allowed = ['pdf'];
        $label = 'PDF (max 30MB)';
        $prefix = 'course_pdf';
    }

    if (($file['size'] ?? 0) > $max || !in_array($ext, $allowed, true)) {
        $error = ucfirst($type).' must be '.$label.'.';
        return '';
    }

    $name = $prefix.'_'.bin2hex(random_bytes(10)).'.'.$ext;
    if (!move_uploaded_file($file['tmp_name'], $dir.$name)) {
        $error = 'Could not save the uploaded '.$type.'.';
        return '';
    }

    return $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? null)) {
    $action = $_POST['action'] ?? 'save';

    $image = (string)($course['image'] ?? '');
    $video_file = (string)($course['video_file'] ?? '');
    $pdf_file = (string)($course['pdf_file'] ?? '');
    $video_url = trim($_POST['video_url'] ?? '');
    $pdf_url = trim($_POST['pdf_url'] ?? '');

    if ($action === 'remove_image') {
        media_delete($image, $uploadDir);
        $image = '';
    } elseif ($action === 'remove_video') {
        media_delete($video_file, $uploadDir);
        $video_file = '';
    } elseif ($action === 'remove_pdf') {
        media_delete($pdf_file, $uploadDir);
        $pdf_file = '';
    } else {
        if (!empty($_FILES['image']['name'])) {
            $new = media_upload($_FILES['image'], 'image', $uploadDir, $error);
            if (!$error && $new) {
                media_delete($image, $uploadDir);
                $image = $new;
            }
        }

        if (!$error && !empty($_FILES['video_file']['name'])) {
            $new = media_upload($_FILES['video_file'], 'video', $uploadDir, $error);
            if (!$error && $new) {
                media_delete($video_file, $uploadDir);
                $video_file = $new;
            }
        }

        if (!$error && !empty($_FILES['pdf_file']['name'])) {
            $new = media_upload($_FILES['pdf_file'], 'pdf', $uploadDir, $error);
            if (!$error && $new) {
                media_delete($pdf_file, $uploadDir);
                $pdf_file = $new;
            }
        }
    }

    if (!$error) {
        $update = $pdo->prepare('UPDATE courses SET image=?,video_url=?,video_file=?,pdf_url=?,pdf_file=? WHERE id=?');
        $update->execute([$image, $video_url, $video_file, $pdf_url, $pdf_file, $id]);
        header('Location: course_media.php?id='.$id.'&saved=1');
        exit;
    }
}

// Refresh after POST/redirect or initial load.
$stmt = $pdo->prepare('SELECT * FROM courses WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$course = $stmt->fetch();

$hasImage = !empty($course['image']);
$hasVideo = !empty($course['video_file']) || !empty($course['video_url']);
$hasPdf = !empty($course['pdf_file']) || !empty($course['pdf_url']);
?>

<div class="admincard course-media-manager">
    <div class="head media-manager-head">
        <div>
            <span class="eyebrow">COURSE MEDIA MANAGER</span>
            <h2><?=e($course['title'])?></h2>
            <p class="page-description">
                Upload the image, learning video and PDF for this skill/course. Each course keeps its own learning content.
            </p>
        </div>
        <div class="media-manager-head-actions">
            <a class="btn outline" href="courses.php">← Back to Courses</a>
            <a class="btn" href="../course.php?id=<?=$id?>" target="_blank"><i class="fa-solid fa-eye"></i> View Course</a>
        </div>
    </div>

    <?php if(!empty($_GET['saved'])): ?>
        <div class="alert ok">✓ Course media saved successfully. Students can now open this course and learn from the uploaded content.</div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert err"><?=e($error)?></div>
    <?php endif; ?>

    <div class="media-course-summary">
        <div class="media-summary-image">
            <?php if($hasImage): ?>
                <img src="../uploads/<?=e($course['image'])?>" alt="<?=e($course['title'])?>">
            <?php else: ?>
                <div class="media-summary-placeholder"><i class="fa-solid fa-image"></i></div>
            <?php endif; ?>
        </div>
        <div class="media-summary-info">
            <span class="table-chip"><?=e($course['category'] ?: 'General')?></span>
            <h3><?=e($course['title'])?></h3>
            <?php if(!empty($course['title_kh'])): ?><p><?=e($course['title_kh'])?></p><?php endif; ?>
            <div class="media-status-row">
                <span class="media-status <?= $hasImage ? 'yes' : 'no' ?>"><i class="fa-solid fa-image"></i> Image <?= $hasImage ? 'Ready' : 'Missing' ?></span>
                <span class="media-status <?= $hasVideo ? 'yes' : 'no' ?>"><i class="fa-solid fa-video"></i> Video <?= $hasVideo ? 'Ready' : 'Missing' ?></span>
                <span class="media-status <?= $hasPdf ? 'yes' : 'no' ?>"><i class="fa-solid fa-file-pdf"></i> PDF <?= $hasPdf ? 'Ready' : 'Missing' ?></span>
            </div>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" class="media-manager-form">
        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
        <input type="hidden" name="id" value="<?=$id?>">
        <input type="hidden" name="action" value="save">

        <div class="media-manager-grid">
            <section class="media-manager-card image-card">
                <div class="manager-card-icon image"><i class="fa-solid fa-image"></i></div>
                <span class="eyebrow">01 • COURSE IMAGE</span>
                <h3>Upload Course Image</h3>
                <p>This image appears on Popular/Featured Courses and at the top of the learning page.</p>

                <?php if($hasImage): ?>
                    <div class="current-media-preview image-preview">
                        <img src="../uploads/<?=e($course['image'])?>" alt="Current course image">
                    </div>
                    <div class="current-file"><i class="fa-solid fa-image"></i> <?=e($course['image'])?></div>
                <?php endif; ?>

                <label class="upload-box">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <strong><?= $hasImage ? 'Replace Course Image' : 'Choose Course Image' ?></strong>
                    <small>JPG / JPEG / PNG / WebP • Max 5MB</small>
                    <input name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/*">
                </label>

                <?php if($hasImage): ?>
                    <button class="remove-media-btn" type="submit" name="action" value="remove_image" onclick="return confirm('Remove this course image?');">
                        <i class="fa-solid fa-trash"></i> Remove Image
                    </button>
                <?php endif; ?>
            </section>

            <section class="media-manager-card video-card">
                <div class="manager-card-icon video"><i class="fa-solid fa-video"></i></div>
                <span class="eyebrow">02 • COURSE VIDEO</span>
                <h3>Upload Learning Video</h3>
                <p>Students will watch this video directly on the course page. You can upload a video or use YouTube/Vimeo.</p>

                <label>Video URL
                    <input name="video_url" type="url" value="<?=e($course['video_url'] ?? '')?>" placeholder="https://www.youtube.com/watch?v=...">
                </label>

                <?php if(!empty($course['video_file'])): ?>
                    <div class="current-file"><i class="fa-solid fa-film"></i> <?=e($course['video_file'])?></div>
                <?php endif; ?>

                <label class="upload-box">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <strong><?=!empty($course['video_file']) ? 'Replace Uploaded Video' : 'Choose Video File'?></strong>
                    <small>MP4 / WebM / OGG / MOV • Max 500MB</small>
                    <input name="video_file" type="file" accept=".mp4,.webm,.ogg,.mov,video/*">
                </label>

                <div class="manager-tip"><i class="fa-solid fa-lightbulb"></i> For long videos, YouTube/Vimeo is faster than uploading a large MP4 to localhost.</div>

                <?php if(!empty($course['video_file'])): ?>
                    <button class="remove-media-btn" type="submit" name="action" value="remove_video" onclick="return confirm('Remove the uploaded video?');">
                        <i class="fa-solid fa-trash"></i> Remove Uploaded Video
                    </button>
                <?php endif; ?>
            </section>

            <section class="media-manager-card pdf-card">
                <div class="manager-card-icon pdf"><i class="fa-solid fa-file-pdf"></i></div>
                <span class="eyebrow">03 • COURSE PDF</span>
                <h3>Upload Learning PDF</h3>
                <p>Students can read the PDF inside the course page or open it in a new tab.</p>

                <label>PDF URL
                    <input name="pdf_url" type="url" value="<?=e($course['pdf_url'] ?? '')?>" placeholder="https://example.com/course.pdf">
                </label>

                <?php if(!empty($course['pdf_file'])): ?>
                    <div class="current-file"><i class="fa-solid fa-file-pdf"></i> <?=e($course['pdf_file'])?></div>
                <?php endif; ?>

                <label class="upload-box">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <strong><?=!empty($course['pdf_file']) ? 'Replace Uploaded PDF' : 'Choose PDF File'?></strong>
                    <small>PDF • Max 30MB</small>
                    <input name="pdf_file" type="file" accept=".pdf,application/pdf">
                </label>

                <?php if(!empty($course['pdf_file'])): ?>
                    <button class="remove-media-btn" type="submit" name="action" value="remove_pdf" onclick="return confirm('Remove the uploaded PDF?');">
                        <i class="fa-solid fa-trash"></i> Remove Uploaded PDF
                    </button>
                <?php endif; ?>
            </section>
        </div>

        <div class="media-save-bar">
            <div>
                <strong>Ready for students?</strong>
                <span>Save the media, then open View Course to test Video and PDF.</span>
            </div>
            <div class="media-save-actions">
                <a class="btn outline" href="course_edit.php?id=<?=$id?>">Edit Course Details</a>
                <button class="btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save All Media</button>
            </div>
        </div>
    </form>
</div>

<?php require __DIR__.'/end.php'; ?>
