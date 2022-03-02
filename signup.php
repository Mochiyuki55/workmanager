<?php
require_once('config.php');
require_once('functions.php');
session_start();
session_regenerate_id(true);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  setToken(); // CSRF 対策

} else {
  checkToken(); // CSRF 対策

  // 入力データを変数に格納する
  $user_name = $_POST['user_name'];           // ユーザーネーム
  $user_email = $_POST['user_email'];         // メールアドレス
  $user_password = $_POST['user_password'];   // パスワード
  $user_status = $_POST['user_status'][0];    // 社員か管理者か


  // DBに接続する
  $pdo = connectDb();

  // エラーチェック
  $err = array();

    // エラー：ユーザーネームが長すぎる
    if(strlen(mb_convert_encoding($user_name,'SJIS','UTF-8')) > 30){
      $err['user_name'] = 'ユーザーネームは30バイト以内で入力してください。';
    }

    // エラー：入力されたメールアドレスがすでにplayerテーブルまたはmanagerテーブルに登録されている
    if(checkEmail($user_email, $pdo)){
      $err['user_email'] = '入力されたメールアドレスはすでに使用されています。';
    }

    // エラー：社員か管理者かを選択できていない
    if(!isset($user_status)){
      $err['user_status'] = '社員か管理者かを選択してください。';
    }


  // エラーがない場合、登録処理を行い、セッションに保存する
  if(empty($err)){
    // 登録処理(ユーザー情報)
    registerUser($user_status,$user_name,$user_email,$user_password,$pdo);

    // 社員の設定初期値の登録処理
    // 読込処理(ユーザー情報)
    $user = getUser($user_email, $user_password, $pdo);

    // 登録したユーザー情報をセッションに保存
    $_SESSION['USER'] = $user;

    // 画面遷移前にDB解放する
    unset($pdo);

    // デフォルト画面に遷移する
      // 社員の場合
      if($user_status == "player"){
        redirect('setting.php');
      // 管理者の場合
      }elseif($user_status == "manager"){
        redirect('players.php');
      }

  }
  // DBを解放する
  unset($pdo);
}

// レンダリング
$title = '登録画面';
include 'templetes/head.php'; // head.phpの読み込み
?>

<!-- HTML -->
  </head>
  <body  class="text-center d-flex align-items-center justify-content-center bg-info" >

    <div class="container pt-5">
      <h1 class="mt-5 font-weight-bold text-white"><?php echo h($title); ?></h1>
      <div class="row mt-2">
          <div class="col-md-4 pt-3 offset-md-4 bg-light rounded">
            <form class="form-signin" action="" method="post">

              <div class="form-group">
                <p>
                  <input type="text" class="form-control" name="user_name" value="<?php echo h($user_name); ?>" placeholder="ユーザーネーム" required autofocus>
                  <span class="text-danger"><?php echo h($err['user_name']); ?></span>
                </p>
              </div>

              <div class="form-group">
                <p>
                  <input type="email" class="form-control" name="user_email" value="<?php echo h($user_email); ?>" placeholder="メールアドレス" required>
                  <span class="text-danger"><?php echo h($err['user_email']); ?></span>
                </p>
              </div>

              <div class="form-group">
                <p>
                  <input type="password" class="form-control" name="user_password" value="<?php echo h($user_password); ?>" placeholder="パスワード" required>
                  <span class="text-danger"><?php echo h($err['user_password']); ?></span>
                </p>

              </div>

              <div class="my-3 form-group">
                <p>
                  <label for="player">
                    <input id="player" type="radio" name="user_status[]" value="player">社員　
                  </label>
                  <label for="manager">
                    <input id="manager" type="radio" name="user_status[]" value="manager">管理者
                  </label>
                </p>
                <span class="text-danger"><?php echo h($err['user_status']); ?></span>
              </div>

              <input class="btn btn-lg btn-primary btn-block" type="submit" value="登録">

              <!-- CSRF対策：index.phpがPOSTされて遷移してきた場合、次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
              <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

            </form>

            <p><?php echo COPY_RIGHT; ?></p>
            <a href="index.php">ログイン画面へ</a>
          </div>
      </div>

    </div><!-- container -->

  </body>
</html>
