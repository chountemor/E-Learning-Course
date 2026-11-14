<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/auth.php';
require_admin();

$type = (($_GET['type'] ?? 'teachers') === 'staff') ? 'staff' : 'teachers';
$isTeacher = $type === 'teachers';
$table = $type;
$label = $isTeacher ? 'Teacher' : 'Staff';
$error = '';
$edit = null;

function people_upload_photo(string $field, ?string $old = null): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return $old;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Photo upload failed. Please choose the image again.');
    }

    if ((int)$_FILES[$field]['size'] > 5 * 1024 * 1024) {
        throw new Exception('Photo must be 5MB or smaller.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES[$field]['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new Exception('Only JPG, PNG, WEBP or GIF photos are allowed.');
    }

    $dir = __DIR__.'/../uploads';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new Exception('Unable to create the uploads folder.');
    }

    $name = 'teacher_'.bin2hex(random_bytes(10)).'.'.$allowed[$mime];
    $target = $dir.'/'.$name;

    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
        throw new Exception('Unable to save the photo. Check the uploads folder permission.');
    }

    /*
       Keep teacher profile images compact. If PHP GD is enabled, resize
       large images to a maximum of 800x800 while preserving aspect ratio.
       This reduces huge uploads and keeps the admin UI fast. GIF is left
       untouched to avoid breaking animated images.
    */
    $info = @getimagesize($target);
    if ($info && !empty($info[0]) && !empty($info[1]) &&
        ($info[0] > 800 || $info[1] > 800)) {
        if (function_exists('imagecreatetruecolor') && $mime !== 'image/gif') {
            $src = false;
            if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
                $src = @imagecreatefromjpeg($target);
            } elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) {
                $src = @imagecreatefrompng($target);
            } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
                $src = @imagecreatefromwebp($target);
            }

            if ($src) {
                $width = imagesx($src);
                $height = imagesy($src);
                $scale = min(800 / $width, 800 / $height);
                $newWidth = max(1, (int)round($width * $scale));
                $newHeight = max(1, (int)round($height * $scale));

                $dst = imagecreatetruecolor($newWidth, $newHeight);

                if ($mime === 'image/png' || $mime === 'image/webp') {
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                    imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
                }

                imagecopyresampled(
                    $dst, $src,
                    0, 0, 0, 0,
                    $newWidth, $newHeight,
                    $width, $height
                );

                if ($mime === 'image/jpeg' && function_exists('imagejpeg')) {
                    @imagejpeg($dst, $target, 84);
                } elseif ($mime === 'image/png' && function_exists('imagepng')) {
                    @imagepng($dst, $target, 6);
                } elseif ($mime === 'image/webp' && function_exists('imagewebp')) {
                    @imagewebp($dst, $target, 84);
                }

                imagedestroy($dst);
                imagedestroy($src);
            }
        }
    }

    /* Do not delete the old image here. The database update must succeed first. */
    return $name;
}

function people_delete_photo(?string $photo): void {
    if ($photo) {
        $file = __DIR__.'/../uploads/'.$photo;
        if (is_file($file)) @unlink($file);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }

    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    try {
        /* ---------------------------------------------------------
           DELETE
           --------------------------------------------------------- */
        if ($action === 'delete') {
            if ($id < 1) {
                throw new Exception('Invalid record ID.');
            }

            if ($isTeacher) {
                $q = $pdo->prepare('SELECT email, photo FROM teachers WHERE id=? LIMIT 1');
                $q->execute([$id]);
                $teacher = $q->fetch();

                if (!$teacher) {
                    throw new Exception('Teacher record was not found.');
                }

                $pdo->beginTransaction();

                /* lessons.created_by uses ON DELETE SET NULL, so teacher lessons remain safe. */
                $pdo->prepare('DELETE FROM teachers WHERE id=?')->execute([$id]);

                /* Remove the teacher login account created by Admin. */
                if (!empty($teacher['email'])) {
                    $pdo->prepare('DELETE FROM users WHERE email=? AND role=?')
                        ->execute([$teacher['email'], 'teacher']);
                }

                $pdo->commit();
                people_delete_photo($teacher['photo'] ?? null);

                header('Location: people.php?type=teachers&deleted=1');
                exit;
            }

            $pdo->prepare('DELETE FROM staff WHERE id=?')->execute([$id]);
            header('Location: people.php?type=staff&deleted=1');
            exit;
        }

        /* ---------------------------------------------------------
           COMMON FORM VALUES
           --------------------------------------------------------- */
        $name = trim($_POST['name'] ?? '');
        $name_kh = trim($_POST['name_kh'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $statusInput = $_POST['status'] ?? 'active';
        $status = in_array($statusInput, ['active', 'inactive'], true) ? $statusInput : 'active';
        $description = trim($_POST['description'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if ($name === '') {
            throw new Exception('Name is required.');
        }

        /* ---------------------------------------------------------
           TEACHERS
           --------------------------------------------------------- */
        if ($isTeacher) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('A valid email is required for teacher login.');
            }

            $categoryId = (int)($_POST['category_id'] ?? 0);
            $special = trim($_POST['specialization'] ?? '');

            if ($categoryId > 0) {
                $cs = $pdo->prepare("SELECT name FROM categories WHERE id=? AND status='active'");
                $cs->execute([$categoryId]);
                $categoryName = (string)$cs->fetchColumn();
                if ($categoryName === '') {
                    throw new Exception('Selected teaching skill is not available.');
                }
                $special = $categoryName;
            }

            $newPhoto = null;
            $oldPhoto = null;

            if ($action === 'edit') {
                if ($id < 1) throw new Exception('Invalid teacher ID.');
                $pq = $pdo->prepare('SELECT * FROM teachers WHERE id=? LIMIT 1');
                $pq->execute([$id]);
                $existingTeacher = $pq->fetch();
                if (!$existingTeacher) throw new Exception('Teacher record was not found.');
                $oldPhoto = $existingTeacher['photo'] ?? null;
            }

            if ($action === 'add') {
                if (strlen($password) < 8) {
                    throw new Exception('Teacher password must be at least 8 characters.');
                }

                /* Email is unique in users. Reuse an existing teacher account,
                   but never silently convert a student/admin account. */
                $uq = $pdo->prepare('SELECT id, role FROM users WHERE email=? LIMIT 1');
                $uq->execute([$email]);
                $existingUser = $uq->fetch();

                $newPhoto = people_upload_photo('photo');

                $pdo->beginTransaction();

                if ($existingUser) {
                    if ($existingUser['role'] !== 'teacher') {
                        throw new Exception('This email is already used by another account. Please use a different email.');
                    }

                    $u = $pdo->prepare('UPDATE users SET name=?, password=?, status=?, phone=?, failed_login_attempts=0, locked_at=NULL WHERE id=?');
                    $u->execute([
                        $name,
                        password_hash($password, PASSWORD_DEFAULT),
                        $status,
                        $phone,
                        $existingUser['id']
                    ]);
                } else {
                    $u = $pdo->prepare('INSERT INTO users(name,email,password,role,status,phone) VALUES(?,?,?,?,?,?)');
                    $u->execute([
                        $name,
                        $email,
                        password_hash($password, PASSWORD_DEFAULT),
                        'teacher',
                        $status,
                        $phone
                    ]);
                }

                $s = $pdo->prepare('INSERT INTO teachers(name,name_kh,email,phone,specialization,category_id,biography,photo,status) VALUES(?,?,?,?,?,?,?,?,?)');
                $s->execute([
                    $name,
                    $name_kh,
                    $email,
                    $phone,
                    $special,
                    $categoryId ?: null,
                    $description,
                    $newPhoto,
                    $status
                ]);

                $pdo->commit();
                header('Location: people.php?type=teachers&saved=1');
                exit;
            }

            if ($action === 'edit') {
                if ($password !== '' && strlen($password) < 8) {
                    throw new Exception('New teacher password must be at least 8 characters.');
                }

                $newPhoto = people_upload_photo('photo', $oldPhoto);

                $oldEmail = (string)($existingTeacher['email'] ?? '');

                $pdo->beginTransaction();

                /* Prevent email collision with another users account. */
                $uq = $pdo->prepare('SELECT id, role FROM users WHERE email=? LIMIT 1');
                $uq->execute([$email]);
                $userWithEmail = $uq->fetch();
                if ($userWithEmail && $userWithEmail['role'] !== 'teacher') {
                    throw new Exception('This email is already used by another account. Please use a different email.');
                }

                $s = $pdo->prepare('UPDATE teachers SET name=?,name_kh=?,email=?,phone=?,specialization=?,category_id=?,biography=?,photo=?,status=? WHERE id=?');
                $s->execute([
                    $name,
                    $name_kh,
                    $email,
                    $phone,
                    $special,
                    $categoryId ?: null,
                    $description,
                    $newPhoto,
                    $status,
                    $id
                ]);

                /* Find the teacher login by old email OR the new email. */
                $uq = $pdo->prepare('SELECT id FROM users WHERE (email=? OR email=?) AND role="teacher" ORDER BY id LIMIT 1');
                $uq->execute([$oldEmail, $email]);
                $teacherUserId = $uq->fetchColumn();

                if ($teacherUserId) {
                    $sql = 'UPDATE users SET name=?, email=?, status=?, phone=?';
                    $args = [$name, $email, $status, $phone];

                    if ($password !== '') {
                        $sql .= ', password=?, failed_login_attempts=0, locked_at=NULL';
                        $args[] = password_hash($password, PASSWORD_DEFAULT);
                    }

                    $sql .= ' WHERE id=?';
                    $args[] = $teacherUserId;
                    $pdo->prepare($sql)->execute($args);
                } else {
                    if ($password === '') {
                        throw new Exception('Teacher login account was not found. Please enter a new password to create it.');
                    }

                    $u = $pdo->prepare('INSERT INTO users(name,email,password,role,status,phone) VALUES(?,?,?,?,?,?)');
                    $u->execute([
                        $name,
                        $email,
                        password_hash($password, PASSWORD_DEFAULT),
                        'teacher',
                        $status,
                        $phone
                    ]);
                }

                $pdo->commit();

                if ($oldPhoto && $newPhoto && $newPhoto !== $oldPhoto) {
                    people_delete_photo($oldPhoto);
                }

                header('Location: people.php?type=teachers&saved=1');
                exit;
            }

            throw new Exception('Unsupported teacher action.');
        }

        /* ---------------------------------------------------------
           STAFF
           --------------------------------------------------------- */
        $position = trim($_POST['position'] ?? '');

        if ($action === 'add') {
            $s = $pdo->prepare('INSERT INTO staff(name,name_kh,position,email,phone,description,status) VALUES(?,?,?,?,?,?,?)');
            $s->execute([$name,$name_kh,$position,$email,$phone,$description,$status]);
        } elseif ($action === 'edit') {
            $s = $pdo->prepare('UPDATE staff SET name=?,name_kh=?,position=?,email=?,phone=?,description=?,status=? WHERE id=?');
            $s->execute([$name,$name_kh,$position,$email,$phone,$description,$status,$id]);
        } else {
            throw new Exception('Unsupported staff action.');
        }

        header('Location: people.php?type=staff&saved=1');
        exit;

    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        /* If a newly uploaded image was created and DB save failed,
           remove it so uploads does not fill with unused files. */
        if (!empty($newPhoto) && $newPhoto !== ($oldPhoto ?? null)) {
            people_delete_photo($newPhoto);
        }

        $error = $ex->getMessage();
    }
}

if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM $table WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch();
}

$rows = $pdo->query("SELECT * FROM $table ORDER BY created_at DESC")->fetchAll();
$categories = $isTeacher
    ? $pdo->query("SELECT id,name FROM categories WHERE status='active' ORDER BY name")->fetchAll()
    : [];

require __DIR__.'/layout.php';
?>

<div class="admincard form people-manager-card">
    <div class="head">
        <div>
            <span class="eyebrow"><?= $edit ? 'EDIT' : 'NEW' ?> <?=e($label)?></span>
            <h2><?= $edit ? 'Edit '.$label : 'Add '.$label ?></h2>
            <p class="page-description">
                <?= $isTeacher
                    ? 'Create a teacher account, assign one learning skill, upload a profile photo and allow the teacher to login.'
                    : 'Manage staff records.' ?>
            </p>
        </div>
    </div>

    <?php if (!empty($_GET['saved'])): ?>
        <div class="alert ok">Saved successfully.</div>
    <?php endif; ?>

    <?php if (!empty($_GET['deleted'])): ?>
        <div class="alert ok">Deleted successfully.</div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="alert err"><?=e($error)?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
        <input type="hidden" name="action" value="<?=$edit?'edit':'add'?>">
        <?php if($edit): ?><input type="hidden" name="id" value="<?=$edit['id']?>"><?php endif; ?>

        <?php if($isTeacher): ?>
            <div class="teacher-photo-upload">
                <div class="teacher-photo-current">
                    <?php if(!empty($edit['photo'])): ?>
                        <img src="../uploads/<?=e($edit['photo'])?>" alt="Teacher photo">
                    <?php else: ?>
                        <i class="fa-solid fa-user-tie"></i>
                    <?php endif; ?>
                </div>
                <label>Teacher Photo
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
                    <small class="muted">JPG, PNG, WEBP or GIF • max 5MB</small>
                </label>
            </div>
        <?php endif; ?>

        <div class="two">
            <label>Name<input name="name" value="<?=e($edit['name']??'')?>" required></label>
            <label>Name (Khmer)<input name="name_kh" value="<?=e($edit['name_kh']??'')?>"></label>
        </div>

        <div class="two">
            <label>Email<?php if($isTeacher && !$edit): ?><span class="muted"> (Login ID)</span><?php endif; ?>
                <input type="email" name="email" value="<?=e($edit['email']??'')?>" <?=$isTeacher?'required':''?>>
            </label>
            <label>Phone<input name="phone" value="<?=e($edit['phone']??'')?>"></label>
        </div>

        <?php if($isTeacher): ?>
            <div class="two">
                <label>Teaching Skill / Category
                    <select name="category_id" required>
                        <option value="">Select teacher skill</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?=$cat['id']?>" <?=((int)($edit['category_id']??0)===(int)$cat['id'])?'selected':''?>>
                                <?=e($cat['name'])?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>Login Password
                    <span class="muted">(<?=$edit?'leave blank to keep current':'min. 8 characters'?>)</span>
                    <input type="password" name="password" minlength="8" <?=$edit?'':'required'?> autocomplete="new-password">
                </label>
            </div>
            <input type="hidden" name="specialization" value="<?=e($edit['specialization']??'')?>">
        <?php else: ?>
            <label>Position<input name="position" value="<?=e($edit['position']??'')?>"></label>
        <?php endif; ?>

        <label>Description / Biography
            <textarea name="description" rows="4"><?=e($edit[$isTeacher?'biography':'description']??'')?></textarea>
        </label>

        <?php $formStatus = in_array(($edit['status'] ?? 'active'), ['active','inactive'], true) ? ($edit['status'] ?? 'active') : 'active'; ?>
        <label>Status
            <select name="status">
                <option value="active" <?=$formStatus==='active'?'selected':''?>>Active</option>
                <option value="inactive" <?=$formStatus==='inactive'?'selected':''?>>Inactive</option>
            </select>
        </label>

        <button class="btn" type="submit"><?=$edit?'Save Changes':'Add '.$label?></button>
        <?php if($edit): ?><a class="btn outline" href="people.php?type=<?=$type?>">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="admincard">
    <div class="head">
        <div><span class="eyebrow">MANAGEMENT</span><h2><?=$label?>s</h2></div>
        <a class="btn outline" href="people.php?type=<?=$isTeacher?'staff':'teachers'?>">View <?=$isTeacher?'Staff':'Teachers'?></a>
    </div>

    <div class="tablewrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th><?= $isTeacher?'Teaching Skill':'Role / Position' ?></th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($rows as $r): ?>
                <?php
                    $rowStatus = in_array(($r['status'] ?? 'active'), ['active','inactive'], true)
                        ? ($r['status'] ?? 'active')
                        : 'active';
                ?>
                <tr>
                    <td>
                        <div class="person-cell">
                            <span class="person-avatar">
                                <?php if($isTeacher && !empty($r['photo'])): ?>
                                    <img src="../uploads/<?=e($r['photo'])?>" alt="">
                                <?php else: ?>
                                    <i class="fa-solid <?= $isTeacher ? 'fa-chalkboard-user' : 'fa-user-tie' ?>"></i>
                                <?php endif; ?>
                            </span>
                            <span>
                                <b><?=e($r['name'])?></b>
                                <small><?=e($r['name_kh']??'')?></small>
                            </span>
                        </div>
                    </td>
                    <td><?=e($isTeacher?($r['specialization']??''):($r['position']??''))?></td>
                    <td><?=e($r['email']??'')?><br><?=e($r['phone']??'')?></td>
                    <td>
                        <span class="status-pill <?=e($rowStatus)?>">
                            <?=e(ucfirst($rowStatus))?>
                        </span>
                    </td>
                    <td>
                        <a class="action-link" href="people.php?type=<?=$type?>&edit=<?=$r['id']?>">Edit</a>
                        <form style="display:inline" method="post" onsubmit="return confirm('Delete this record and its teacher login account?')">
                            <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?=$r['id']?>">
                            <button class="action-link danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if(!$rows): ?>
                <tr><td colspan="5"><div class="empty-state">No <?=$label?> records yet.</div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function(){
    const input = document.querySelector('input[name="photo"]');
    const preview = document.querySelector('.teacher-photo-current img');
    if (!input) return;
    input.addEventListener('change', function(){
        const file = this.files && this.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) return;
        if (file.size > 5 * 1024 * 1024) {
            alert('Photo must be 5MB or smaller.');
            this.value = '';
            return;
        }
        const url = URL.createObjectURL(file);
        if (preview) {
            preview.src = url;
        } else {
            const box = document.querySelector('.teacher-photo-current');
            if (box) box.innerHTML = '<img src="'+url+'" alt="Teacher photo preview">';
        }
    });
})();
</script>
<?php require __DIR__.'/end.php'; ?>
