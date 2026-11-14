<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$admin_title = 'Edit User';
$admin_heading = 'Edit User';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: users.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: users.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $dob = trim($_POST['date_of_birth'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $education = trim($_POST['education_level'] ?? '');
    $role = $_POST['role'] ?? 'student';
    $status = $_POST['status'] ?? 'active';
    $password = $_POST['password'] ?? '';

    if ($name === '') $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (!in_array($gender, ['', 'male', 'female', 'other'], true)) $gender = '';
    if (!in_array($role, ['admin', 'teacher', 'student'], true)) $role = 'student';
    if (!in_array($status, ['active', 'inactive', 'pending', 'blocked'], true)) $status = 'active';

    $emailCheck = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $emailCheck->execute([$email, $id]);
    if ($emailCheck->fetch()) {
        $errors[] = 'This email address is already used by another user.';
    }

    if ($password !== '' && strlen($password) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }

    // Do not allow an admin to remove their own admin role or deactivate their own account.
    if ($id === (int)$_SESSION['user_id']) {
        $role = 'admin';
        $status = 'active';
    }

    if (!$errors) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = 'UPDATE users
                    SET name=?, email=?, phone=?, gender=?, date_of_birth=?, address=?,
                        education_level=?, role=?, status=?, password=?,
                        failed_login_attempts=CASE WHEN ?="blocked" THEN failed_login_attempts ELSE 0 END,
                        locked_at=CASE WHEN ?="blocked" THEN locked_at ELSE NULL END
                    WHERE id=?';
            $save = $pdo->prepare($sql);
            $save->execute([
                $name, $email, $phone ?: null, $gender ?: null, $dob ?: null,
                $address ?: null, $education ?: null, $role, $status, $hash,
                $status, $status, $id
            ]);
        } else {
            $sql = 'UPDATE users
                    SET name=?, email=?, phone=?, gender=?, date_of_birth=?, address=?,
                        education_level=?, role=?, status=?,
                        failed_login_attempts=CASE WHEN ?="blocked" THEN failed_login_attempts ELSE 0 END,
                        locked_at=CASE WHEN ?="blocked" THEN locked_at ELSE NULL END
                    WHERE id=?';
            $save = $pdo->prepare($sql);
            $save->execute([
                $name, $email, $phone ?: null, $gender ?: null, $dob ?: null,
                $address ?: null, $education ?: null, $role, $status,
                $status, $status, $id
            ]);
        }

        // Keep current session name/role synchronized if the admin edited their own profile.
        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['user_name'] = $name;
            $_SESSION['role'] = 'admin';
        }

        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $success = 'User information updated successfully.';
    }
}

require __DIR__ . '/layout.php';
?>

<div class="admin-user-edit">
    <div class="admincard">
        <div class="head">
            <div>
                <span class="eyebrow">USER PROFILE</span>
                <h2>Edit User Information</h2>
                <p class="muted">View and update the user's account, contact and learning information.</p>
            </div>
            <a class="btn small" href="users.php">← Back to Users</a>
        </div>

        <?php if ($errors): ?>
            <div class="admin-alert danger">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="admin-alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" class="user-edit-form" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <div class="user-profile-preview">
                <div class="large-user-avatar">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="">
                    <?php else: ?>
                        <span>👤</span>
                    <?php endif; ?>
                </div>
                <div>
                    <strong><?= htmlspecialchars($user['name']) ?></strong>
                    <span><?= htmlspecialchars($user['email']) ?></span>
                    <small>User ID #<?= (int)$user['id'] ?> · Joined <?= htmlspecialchars(date('M d, Y', strtotime($user['created_at']))) ?></small>
                </div>
            </div>

            <div class="form-section-title">Basic Information</div>
            <div class="form-grid two">
                <label>
                    <span>Full Name *</span>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </label>

                <label>
                    <span>Email Address *</span>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </label>

                <label>
                    <span>Phone</span>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </label>

                <label>
                    <span>Gender</span>
                    <select name="gender">
                        <option value="">Not specified</option>
                        <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= ($user['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </label>

                <label>
                    <span>Date of Birth</span>
                    <input type="date" name="date_of_birth" value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>">
                </label>

                <label>
                    <span>Education Level</span>
                    <input type="text" name="education_level" value="<?= htmlspecialchars($user['education_level'] ?? '') ?>" placeholder="e.g. High School, Bachelor">
                </label>
            </div>

            <label class="full-field">
                <span>Address</span>
                <textarea name="address" rows="3" placeholder="User address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            </label>

            <div class="form-section-title">Account Access</div>
            <div class="form-grid two">
                <label>
                    <span>Role</span>
                    <select name="role" <?= $id === (int)$_SESSION['user_id'] ? 'disabled' : '' ?>>
                        <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                        <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <?php if ($id === (int)$_SESSION['user_id']): ?>
                        <input type="hidden" name="role" value="admin">
                    <?php endif; ?>
                </label>

                <label>
                    <span>Status</span>
                    <select name="status" <?= $id === (int)$_SESSION['user_id'] ? 'disabled' : '' ?>>
                        <?php foreach (['active','inactive','pending','blocked'] as $st): ?>
                            <option value="<?= $st ?>" <?= $user['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($id === (int)$_SESSION['user_id']): ?>
                        <input type="hidden" name="status" value="active">
                    <?php endif; ?>
                </label>

                <label class="full-field">
                    <span>New Password <small>(leave blank to keep current password)</small></span>
                    <input type="password" name="password" minlength="8" placeholder="Minimum 8 characters">
                </label>
            </div>

            <div class="edit-actions">
                <a class="btn outline" href="users.php">Cancel</a>
                <button class="btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/end.php'; ?>
