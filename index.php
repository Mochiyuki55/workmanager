<?php
require_once('config.php');
require_once('functions.php');
session_start();
session_regenerate_id(true);


if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // ログイン画面を表示する前にまずCookieがあるかをチェックする。
  if(isset($_COOKIE[COOKIE_NAME])){ // Cookieがある場合
    $auto_login_key = $_COOKIE[COOKIE_NAME];
    // DBに照合する
    $pdo = connectDb();
    $sql = "SELECT * FROM auto_login WHERE c_key = :c_key AND expire >= :expire LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":c_key" => $auto_login_key, ":expire" => date('Y-m-d H:i:s')));
    $row = $stmt->fetch();

  // DBにも存在しており、かつ有効期間内であれば認証OKとみなして自動ログインさせる。
    if ($row){
      // 照合成功、セッションにユーザー情報を入れる(自動ログイン)

      $user = getUserbyUserId($row['user_id'], $pdo);
      // セッションハイジャック対策
      session_regenerate_id(true);

      // 登録したユーザー情報をセッションに保存
      $_SESSION['USER'] = $user;
      $_SESSION['CURRENT_CALENDAR'] = date('Y/m');


      unset($pdo);

      // デフォルト画面に遷移する
      // 社員の場合
      if($user['user_status'] == "player"){
        redirect('calendars_set.php');
      // 管理者の場合
      }elseif($user['user_status'] == "manager"){
        redirect('players.php');
      }
    }
  }

  // CSRF 対策
  setToken();

} else {

  // CSRF 対策
  checkToken();

  // 入力データを変数に格納する
  $user_email = $_POST['user_email'];         // メールアドレス
  $user_password = $_POST['user_password'];   // パスワード
  $auto_login = $_POST['auto_login'];         // 自動ログイン

  // DBに接続する
  $pdo = connectDb();

  // エラーチェック
  $err = array();

    // エラー：入力したメールアドレスが登録されていない
    if (!checkEmail($user_email, $pdo)) {
       $err['user_email'] = 'このメールアドレスが登録されていません。';
    }

    // エラー：メールアドレスとパスワードが正しくない
    $user = getUser($user_email, $user_password, $pdo);
    if (!$user) {
      $err['user_password'] = 'パスワードが正しくありません。';
    }

    // もし$err配列に何もエラーメッセージが保存されていなかったら
    if (empty($err)) {
      // セッションハイジャック対策
      session_regenerate_id(true);

      // ログインに成功したのでセッションにユーザデータを保存する
      $_SESSION['USER'] = $user;

      // Cookieが残っていれば、を一度クリアする。
      if (isset($_COOKIE[COOKIE_NAME])) {
        $auto_login_key = $_COOKIE[COOKIE_NAME];

        // Cookie情報をクリア
        setcookie(COOKIE_NAME, '', time()-86400, '/work_manager/web/');

        // DB情報をクリア
        $sql = "DELETE FROM auto_login WHERE c_key = :c_key";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(":c_key" => $auto_login_key));
      }

      // 「次回から・・・」にチェックが入っていた場合、ランダムキーを発行してCookieに保存する
      if (isset($_POST['auto_login'])){
        $auto_login_key= sha1(uniqid(mt_rand(), true));
        setcookie(COOKIE_NAME, $auto_login_key, time()+3600*24*365);

      // 発行したCookieをDBにも保存しておく
        $spl ='INSERT INTO auto_login (user_id, c_key, expire, created_at, updated_at) VALUES(:user_id, :c_key, :expire, now(), now())';
        $stmt = $pdo->prepare($spl);
        $stmt->execute(array(':user_id' => $user['id'], ':c_key' => $auto_login_key, ':expire' => date('Y-m-d H:i:s', time()+3600*24*365)));
      }


      unset($pdo);
      // デフォルト画面に遷移する
        // 社員の場合
        if($user['user_status'] == "player"){
          redirect('calendars_set.php');
        // 管理者の場合
        }elseif($user['user_status'] == "manager"){
          redirect('players.php');
        }

    }
  unset($pdo);
}

// レンダリング
$title = 'ログイン画面';
include 'templetes/head.php'; // head.phpの読み込み
?>

  </head>
  <body  class="text-center d-flex align-items-center justify-content-center bg-info" >

    <div class="container pt-5">
      <h1 class="mt-5 font-weight-bold text-white"><?php echo h($title); ?></h1>
      <div class="row mt-2">
          <div class="col-md-4 pt-3 offset-md-4 bg-light rounded">
            <form class="form" method="POST">

              <div class="form-group">
                <input type="email" class="form-control" name="user_email" value="<?php echo h($user_email); ?>" placeholder="メールアドレス" required>
                <span class="text-danger"><?php echo h($err['user_email']); ?></span>
              </div>

              <div class="form-group">
                <input type="password" class="form-control" name="user_password" value="<?php echo h($user_password); ?>" placeholder="パスワード" required>
                <span class="text-danger"><?php echo h($err['user_password']); ?></span>
              </div>

              <div class="form-group">
                <input type="submit" value="ログイン" class="btn btn-primary btn-block">
              </div>

              <div class="form-group">
                <label for="auto_login">
                  <input id="auto_login" type="checkbox" name="auto_login"> 次回から自動でログイン
                </label>
              </div>

              <!-- CSRF対策：index.phpがPOSTされて遷移してきた場合、次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
              <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

            </form>

            <p><?php echo COPY_RIGHT; ?></p>
            <a href="signup.php">登録画面へ</a>

          </div>
      </div>

    </div><!-- container -->

  </body>
</html>
