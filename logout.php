<?php
require_once('config.php');
require_once('functions.php');

session_start();
$pdo = connectDb();


// ログアウト処理で行うことは、以下の3つです。
//   ・セッション内のデータ削除
//   ・クッキーの無効化
//   ・セッションの破棄
// これらを行うことで、このアプリケーションに関するセッション情報が完全に削除され、クリーンな状態になります。


// CookieとDBのデータも削除する
if(isset($_COOKIE[COOKIE_NAME])){

    $auto_login_key = $_COOKIE[COOKIE_NAME];

    // Cookie情報をクリア
  	setcookie(COOKIE_NAME, '', time()-86400, '/work_manager/web/');

  	// DB情報をクリア
  	$sql = "DELETE FROM auto_login WHERE c_key = :c_key";
  	$stmt = $pdo->prepare($sql);
  	$stmt->execute(array(":c_key" => $auto_login_key));

}

// セッション内のデータ削除
$_SESSION = array();

// クッキーの無効化
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-86400, '/work_manager/web/');
}

// セッションの破棄
session_destroy();
unset($pdo);

// ログアウト後はログイン画面に遷移させる。
header('Location:'.SITE_URL.'index.php');

?>
