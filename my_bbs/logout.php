<?php
session_start();

$_SESSION = array();
if (ini_set('session.use_cookies')){
  $params = session_get_cookie_params();
  setcookie(session_name() . '', time() - 42000,  //クッキーの有効期限を切る
    $params['path'], $params['domain'],$params
    ['secure'], $params['httponly']);
}
session_destroy();

setcookie('email', '', time() - 3600); //クッキーに保存されているemailも切る

header('Location: login.php');
exit();
?>