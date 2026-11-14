<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/auth.php';
require_admin();
$msg=''; $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!verify_csrf($_POST['csrf']??null)){ exit('Invalid CSRF token'); }
  $id=(int)($_POST['teacher_id']??0); $password=$_POST['password']??'';
  try{
    if($id<1 || strlen($password)<8) throw new Exception('Select a teacher and use a password of at least 8 characters.');
    $q=$pdo->prepare('SELECT * FROM teachers WHERE id=?'); $q->execute([$id]); $t=$q->fetch();
    if(!$t || !$t['email']) throw new Exception('Teacher email not found.');
    $q=$pdo->prepare('SELECT id FROM users WHERE email=? AND role="teacher"'); $q->execute([$t['email']]); $u=$q->fetchColumn();
    if($u){ $st=$pdo->prepare('UPDATE users SET name=?,password=?,status="active",phone=? WHERE id=?'); $st->execute([$t['name'],password_hash($password,PASSWORD_DEFAULT),$t['phone'],$u]); }
    else { $st=$pdo->prepare('INSERT INTO users(name,email,password,role,status,phone) VALUES(?,?,?,"teacher","active",?)'); $st->execute([$t['name'],$t['email'],password_hash($password,PASSWORD_DEFAULT),$t['phone']]); }
    $msg='Teacher login account repaired successfully.';
  }catch(Throwable $e){$error=$e->getMessage();}
}
$teachers=$pdo->query('SELECT id,name,email FROM teachers ORDER BY name')->fetchAll();
?><!doctype html><html><head><meta charset="utf-8"><title>Repair Teacher Login</title></head><body><h2>Repair Teacher Login</h2><?php if($msg)echo '<p style="color:green">'.e($msg).'</p>';if($error)echo '<p style="color:red">'.e($error).'</p>';?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Teacher <select name="teacher_id" required><?php foreach($teachers as $t):?><option value="<?=$t['id']?>"><?=e($t['name'].' — '.$t['email'])?></option><?php endforeach;?></select></label><br><label>New Password <input type="password" name="password" minlength="8" required></label><br><button>Repair Account</button></form><p>Delete this file after use.</p></body></html>