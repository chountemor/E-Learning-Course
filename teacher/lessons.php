<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/teacher_auth.php';
require_teacher();
$title='My Lessons'; $heading='My Lessons'; $uid=(int)$_SESSION['user_id'];
$st=$pdo->prepare("SELECT l.*,c.title AS course_title,cat.name AS skill_name FROM lessons l INNER JOIN courses c ON c.id=l.course_id LEFT JOIN categories cat ON cat.id=c.category_id WHERE l.created_by=? ORDER BY l.created_at DESC,l.id DESC");
$st->execute([$uid]); $rows=$st->fetchAll(PDO::FETCH_ASSOC);
require 'layout.php';
?>
<section class="teacher-card">
<div class="teacher-card-head"><div><h2>My Uploaded Lessons</h2><p>Only lessons uploaded by your teacher account are listed here. Teachers cannot delete content.</p></div><a class="btn" href="lesson_add.php"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Lesson</a></div>
<?php if(isset($_GET['saved'])): ?><div class="notice success"><i class="fa-solid fa-circle-check"></i> Lesson uploaded successfully.</div><?php endif; ?>
<?php if(!$rows): ?><div class="teacher-empty"><div><i class="fa-regular fa-folder-open"></i></div><strong>No lessons uploaded yet</strong><div class="muted">Upload your first Video or PDF for your assigned skill.</div></div>
<?php else: ?><div class="teacher-table-wrap"><table class="teacher-table"><thead><tr><th>Lesson</th><th>Course / Skill</th><th>Materials</th><th>Status</th><th>Created</th><th>Action</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><div class="lesson-title"><span class="lesson-icon"><i class="fa-solid fa-file-lines"></i></span><span><strong><?=e($r['title'])?></strong><small><?=e($r['duration']?:'Self-paced')?></small></span></div></td><td><strong><?=e($r['course_title'])?></strong><small><?=e($r['skill_name']??'')?></small></td><td><div class="material-pills"><?php if(!empty($r['video_file'])||!empty($r['video_url'])):?><span class="video-pill"><i class="fa-solid fa-video"></i> Video</span><?php endif;?><?php if(!empty($r['document_file'])||!empty($r['document_url'])):?><span class="pdf-pill"><i class="fa-solid fa-file-pdf"></i> PDF</span><?php endif;?></div></td><td><span class="status <?=e($r['status'])?>"><?=e(ucfirst($r['status']))?></span></td><td><?=e(date('d M Y',strtotime($r['created_at'])))?></td><td><a class="btn secondary small" href="../lesson.php?id=<?=$r['id']?>" target="_blank"><i class="fa-solid fa-eye"></i> View</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
</main></div></body></html>
