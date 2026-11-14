<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/auth.php';
require_admin();

$admin_title = 'Users';
$admin_heading = 'User Management';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }

    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id === (int)$_SESSION['user_id'] && in_array($action, ['delete'], true)) {
        header('Location: users.php');
        exit;
    }

    if ($action === 'role') {
        $role = $_POST['role'] ?? 'student';
        if (!in_array($role, ['admin','teacher','student'], true)) $role = 'student';
        if ($id !== (int)$_SESSION['user_id']) {
            $s = $pdo->prepare('UPDATE users SET role=? WHERE id=?');
            $s->execute([$role, $id]);
        }
    } elseif ($action === 'status') {
        $status = $_POST['status'] ?? 'active';
        if (!in_array($status, ['active','inactive','pending','blocked'], true)) {
            $status = 'active';
        }
        if ($id !== (int)$_SESSION['user_id']) {
            $s = $pdo->prepare('UPDATE users SET status=?, locked_at=CASE WHEN ?="blocked" THEN locked_at ELSE NULL END, failed_login_attempts=CASE WHEN ?="blocked" THEN failed_login_attempts ELSE 0 END WHERE id=?');
            $s->execute([$status, $status, $status, $id]);
        }
    } elseif ($action === 'delete') {
        $s = $pdo->prepare('DELETE FROM users WHERE id=? AND id<>?');
        $s->execute([$id, $_SESSION['user_id']]);
    }

    header('Location: users.php');
    exit;
}

$q = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$roleFilter = $_GET['role'] ?? '';

$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if (in_array($statusFilter, ['active','inactive','pending','blocked'], true)) {
    $where[] = 'status = ?';
    $params[] = $statusFilter;
}
if (in_array($roleFilter, ['admin','teacher','student'], true)) {
    $where[] = 'role = ?';
    $params[] = $roleFilter;
}

$sql = 'SELECT id,name,email,phone,gender,education_level,role,status,last_login_at,created_at
        FROM users';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

require __DIR__.'/layout.php';
?>

<div class="admincard users-page">
    <div class="head users-head">
        <div>
            <span class="eyebrow">USER MANAGEMENT</span>
            <h2>All Users</h2>
            <p class="muted">View user profiles, update account information and manage access.</p>
        </div>
        <div class="user-count"><strong><?= count($rows) ?></strong><span>Users found</span></div>
    </div>

    <form class="users-filter" method="get">
        <div class="user-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search name, email or phone...">
        </div>

        <select name="role">
            <option value="">All Roles</option>
            <option value="student" <?= $roleFilter === 'student' ? 'selected' : '' ?>>Student</option>
            <option value="teacher" <?= $roleFilter === 'teacher' ? 'selected' : '' ?>>Teacher</option>
            <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>

        <select name="status">
            <option value="">All Status</option>
            <?php foreach (['active','inactive','pending','blocked'] as $st): ?>
                <option value="<?= $st ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
        </select>

        <button class="btn small" type="submit">Filter</button>
        <?php if ($q !== '' || $roleFilter !== '' || $statusFilter !== ''): ?>
            <a class="btn small outline" href="users.php">Reset</a>
        <?php endif; ?>
    </form>

    <div class="tablewrap">
        <table class="users-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Contact</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Joined</th>
                    <th class="actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        <div>👥</div>
                        <strong>No users found</strong>
                        <span>Try another search or filter.</span>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($rows as $u): ?>
                <tr>
                    <td>
                        <div class="table-user">
                            <span class="table-avatar"><?= strtoupper(mb_substr($u['name'] ?: 'U', 0, 1)) ?></span>
                            <span>
                                <b><?= htmlspecialchars($u['name']) ?></b>
                                <small>#<?= (int)$u['id'] ?> · <?= htmlspecialchars($u['education_level'] ?? 'Student') ?></small>
                            </span>
                        </div>
                    </td>

                    <td>
                        <div class="contact-cell">
                            <span><?= htmlspecialchars($u['email']) ?></span>
                            <small><?= htmlspecialchars($u['phone'] ?: 'No phone') ?></small>
                        </div>
                    </td>

                    <td>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <input type="hidden" name="action" value="role">
                            <select name="role" onchange="this.form.submit()" <?= $u['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                <option value="student" <?= $u['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                <option value="teacher" <?= $u['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </form>
                    </td>

                    <td>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <input type="hidden" name="action" value="status">
                            <select name="status" onchange="this.form.submit()" <?= $u['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                <?php foreach (['active','inactive','pending','blocked'] as $st): ?>
                                    <option value="<?= $st ?>" <?= $u['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>

                    <td class="date-cell">
                        <?= $u['last_login_at'] ? htmlspecialchars(date('M d, Y H:i', strtotime($u['last_login_at']))) : 'Never' ?>
                    </td>

                    <td class="date-cell">
                        <?= htmlspecialchars(date('M d, Y', strtotime($u['created_at']))) ?>
                    </td>

                    <td>
                        <div class="user-actions">
                            <a class="icon-action view" href="user_edit.php?id=<?= (int)$u['id'] ?>" title="View / Edit">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a class="icon-action edit" href="user_edit.php?id=<?= (int)$u['id'] ?>" title="Edit user">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <form method="post" onsubmit="return confirm('Delete this user permanently?')">
                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button class="icon-action delete" type="submit" title="Delete user">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__.'/end.php'; ?>
