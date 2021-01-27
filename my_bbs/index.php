<?php
session_start();
require('dbconnect.php');

  //↓ログイン者以外が投稿画面に遷移しないよう定義する↓
  if(isset($_SESSION['id'] ) && $_SESSION['time'] + 3600 > time()){ //sessionidがない場合&1時間何もしなければログインアウトするのでfalseで12行目へ
    $_SESSION['time']= time();  //ログイン状態で何かアクションすればtimeが3600秒上書きされる

    $members = $db->prepare('SELECT * FROM members WHERE id=?');  //membersテーブルの情報を参照
    $members -> execute(array($_SESSION['id']));  //idを情報を参照
    $member = $members->fetch();  //membersのidを取得
  } else {
    header('Location: login.php');
    exit();
  }
  if(!empty($_POST)){   //POSTがあれば投稿できる
    if  ($_POST['message'] !== ''){   //メッセージが空でなければ
      $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_message_id=?, created=NOW()');
      $message->execute(array(
        $member['id'],
        $_POST['message'],
        $_POST['reply_post_id']
      ));

      header('Location: index.php');
      exit();
    }
  }
  //ページネーション定義
  $page = $_REQUEST['page'];
  if ($page == ''){
    $page =1;   //URLパラメーターが空なら1にする
  }
  $page = max($page,1); //1以下にならない(max1だから)
  $counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
  $cnt = $counts->fetch();
  $maxPage = ceil($cnt['cnt'] / 5); //メッセージの件数を取得し5で割り少数を切り上げるpage数がわかる
  $page = min($page,$maxPage);  //maxpageより数字が大きいものは表示しない
  $start = ($page - 1) * 5;

  //membersとpostsのリレーショナル取得(m.id=p.member_id) ページネーション定義
  $posts = $db->prepare('SELECT m.name, p.*FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?,5');  
  $posts->bindParam(1,$start,PDO::PARAM_INT);
  $posts->execute();

  //返信の処理
  if (isset($_REQUEST['res'])){ //(RE)がクリックされた場合
    $response = $db->prepare ('SELECT m.name, p.*FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');  //postのidを取得したい
    $response->execute(array($_REQUEST['res']));   //postのidのurlパラメーターの取得

    $table = $response->fetch(); // responseを読み込み
    $message = '@' . $table['name'] . '' . $table ['message'];    // @name メッセージを読み込む
  }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>MYBBS</title>

	<link rel="stylesheet" href="style.css?v=2" />
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>MYBBS</h1>
  </div>
  <div id="content">
<?php foreach ($posts as $post): ?>
    <div class="msg">
    <p>
    <span class="name">[<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>] / [<?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?>]
    </span>[<a href="index.php?res=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>">返信</a>]</p>
    <p class="day"><a href="view.php?id=<?php print(htmlspecialchars($post['id']));?>">
    <?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?></a>
    <?php if($post['reply_message_id'] >0): ?>
<a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id']));?>">
返信元のメッセージ</a>
<?php endif;?>

<?php if($_SESSION['id'] == $post['member_id']): ?>
[<a href="delete.php?id=<?php print(htmlspecialchars($post['id']));?>"
style="color: #F33;">削除</a>]
<?php endif; ?>
    </p>
    </div>
<?php endforeach;?>
<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
    <form action="" method="post">
      <dl>
        <dt><?php print(htmlspecialchars($member['name'], ENT_QUOTES));?>さん</dt>
        <dd>
          <textarea name="message" cols="50" rows="5"><?php print(htmlspecialchars($message, ENT_QUOTES)); ?></textarea>
          <input type="hidden" name="reply_post_id" value="<?php print(htmlspecialchars($_REQUEST['res'], ENT_QUOTES)); ?>" />
        </dd>
      </dl>
      <div>
        <p>
          <input type="submit" class="sub" value="投稿" />
        </p>
      </div>
    </form>
<ul class="paging">
<?php if($page > 1): ?>
<li><a href="index.php?page=<?php print($page-1); ?>">
前のページへ</a></li>
<?php else: ?>
<li>前のページへ</li>
<?php endif; ?>

<?php if($page < $maxPage): ?>
<li><a href="index.php?page=<?php print($page+1); ?>">
次のページへ</a></li>
<?php else: ?>
<li>次のページへ</li>
<?php endif; ?>
</ul>
  </div>
</div>
</body>
</html>
