<?php 
session_start();
require('../dbconnect.php');

if(!empty($_POST)){					//←入力が空ではない時、エラーチェックを走らせる
	//空のエラーチェック
	if ($_POST['name'] === ''){
		$error['name'] = 'blank';
	}
	if ($_POST['email'] === ''){
		$error['email'] = 'blank';
	}
	if (strlen($_POST['password']) < 4) {
		$error['password'] = 'length';
	}
	if ($_POST['password'] === ''){
		$error['password'] = 'blank';
	}
	//空のエラーチェック

	//アカウントの重複チェック
	if(empty($error)){							//前の記述で$errorがないかチェックしている
		$member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE email=?');	//memberテーブルの全emailを参照
		$member->execute(array($_POST['email']));	//emailの発行
		$record = $member->fetch();		//emailが存在しないなら0,存在したら1
		if($record['cnt'] > 0) {		//cntに数字が格納される1ならtrueでエラー
			$error['email'] = 'duplicate';	//エラーを発行＆変数定義
		}
	}
	//アカウントの重複チェック
	
	//入力確認画面に移行する
	if (empty($error)){            //前の記述で$errorがないかチェックしている
		$_SESSION['join'] = $_POST;  //sessionに値を保存する
	header('Location: check.php');
	exit();
	}
	//入力確認画面に移行する
	}

	//書き直した時に入力した内容を保存したまま遷移する
	if($_REQUEST['action'] == 'rewrite' && isset($_SESSION['join'])){		//URLパラメーターで書き直したかどうか判断する
		$_POST = $_SESSION['join'];
	}
	//書き直した時に入力した内容を保存したまま遷移する
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>会員登録</title>

	<link rel="stylesheet" href="../style.css" />
</head>
<body>
<div id="wrap">
<div id="head">
<h1>会員登録</h1>
</div>

<div id="content">
<p>次のフォームに必要事項をご記入ください。</p>
<form action="" method="post" enctype="multipart/form-data">
	<dl>
		<dt>ニックネーム<span class="required">必須</span></dt>
		<dd>
					<input type="text" name="name" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['name'],ENT_QUOTES));?>" />
					<?php if ($error['name'] === 'blank'):?>
						<p class ="error">*ニックネームを入力してください</p>
					<?php endif;?>
		</dd>
		<dt>メールアドレス<span class="required">必須</span></dt>
					<input type="text" name="email" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['email'],ENT_QUOTES));?>" />
					<?php if ($error['email'] === 'blank'):?>
						<p class ="error">*メールアドレスを入力してください</p>
					<?php endif;?>
					<?php if ($error['email'] === 'duplicate'):?>
						<p class ="error">*指定されたメールアドレスは、既に登録されています</p>
					<?php endif;?>
		<dd>
		<dt>パスワード<span class="required">必須</span></dt>
		<dd>
					<input type="password" name="password" size="10" maxlength="20" value="<?php print(htmlspecialchars($_POST['password'],ENT_QUOTES));?>" />
					<?php if ($error['password'] === 'length'):?>
						<p class ="error">*パスワードは4文字以上で入力してください</p>
					<?php endif;?>
					<?php if ($error['password'] === 'blank'):?>
						<p class ="error">*パスワードを入力してください</p>
					<?php endif;?>
        </dd>
	</dl>
	<div><input type="submit" value="入力内容を確認する" /></div>
</form>
</div>
</body>
</html>
