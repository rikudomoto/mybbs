<?php
session_start();
require('dbconnect.php');

if(isset($_SESSION['id'])){ 
  $id = $_REQUEST['id'];    //urlパラメーター取得

  $messages = $db->prepare('SELECT * FROM posts WHERE id=?');
  $messages->execute(array($id)); //urlパラメータで取得したidを格納
  $message = $messages->fetch();

  //dbから取得したメッセージのmember_idとSESSION_idが一緒であれば削除できる
  if($message['member_id']== $_SESSION['id']){
    $del = $db->prepare('DELETE FROM posts WHERE id=?');
    $del->execute(array($id));
  }
}

header('Location: index.php');
exit();
?>