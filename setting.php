<?php
require_once('config.php');
require_once('functions.php');
$title = '設定画面';
$toppage_url = 'calendars_set.php';
$toppage = '';
$setting = '';
session_start();
if(!isset($_SESSION['USER'])){redirect();} // ログインチェック


// セッションからユーザ情報を取得
$user = $_SESSION['USER'];
$pdo = connectDb();

// settingテーブルからユーザーの設定データを読み込む（初回以外では）
$data = getSetting($user['id'], $pdo);


if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  setToken(); // CSRF 対策

} else {
  checkToken(); // CSRF 対策

  // 入力データを変数に格納する
  $start_at = $_POST['start_at'];
  $end_at = $_POST['end_at'];
  $rest = $_POST['rest'];
  $style = $_POST['style'][0];

  // DBに接続する
  $pdo = connectDb();

  // エラーチェック
  $err = array();

    // エラー：出勤時刻が退勤時刻より遅い。出勤時刻と退勤時刻の差が、休憩時間より短い。
    if($end_at - $start_at <= $rest){
      $err['setting'] = '勤務時間の設定を見直してください。';
    }
    if(!$style){
      $err['setting'] = '勤務状態を設定してください。';
    }

  // エラーがなければ
  if(empty($err)){
    // ユーザーの設定情報を確認する
    $data = getSetting($user['id'], $pdo);

    // 設定情報がなければ、初期登録処理を行う
    if(!$data){
      registerSetting($user['id'],$start_at,$end_at,$rest,$style,HOLYDAY_REMAIN,$pdo);

    // ユーザー情報があれば、更新処理を行う
    }else{
      // ユーザーの設定情報を更新する
      $sql = "UPDATE setting
                      SET
                      start_at = :start_at,
                      end_at = :end_at,
                      rest = :rest,
                      style = :style
                      WHERE
                      user_id = :user_id";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(':user_id',$user['id']);
      $stmt->bindValue(':start_at', $start_at);
      $stmt->bindValue(':end_at', $end_at);
      $stmt->bindValue(':rest', $rest);
      $stmt->bindValue(':style', $style);
      $stmt->execute();
    }

    unset($pdo);
    redirect($toppage_url);

  }

  unset($pdo);
}

include 'templetes/head.php'; // head.phpの読み込み
?>

  </head>
  <body  class="text-center bg-info" >

    <!-- navbar -->
    <?php include 'templetes/navbar.php'; ?>

    <!-- container -->
    <div class="container pt-5">
      <h1 class="mt-5 font-weight-bold text-white"><?php echo $title; ?></h1>
      <div class="row mt-2">
          <div class="col-md-4 pt-3 offset-md-4 bg-light rounded">

            <form class="form" action="" method="post">
              <div class="modal-body">
                <h5><?php echo date("Y年m月d日"); ?>時点の年休残日数</h5>
                <h5><?php if($data['holyday_remain']){echo $data['holyday_remain'];}else{echo HOLYDAY_REMAIN;} ?>日</h5>

                <h5 class="mt-3">勤務情報の初期値設定</h5>
                <p>
                  出勤時刻：<input type="time" name="start_at" value="<?php if($data['start_at']){echo $data['start_at'];}else{echo START_AT;} ?>"> <br>
                  退勤時刻：<input type="time" name="end_at" value="<?php if($data['end_at']){echo $data['end_at'];}else{echo END_AT;} ?>"> <br>
                  休憩時間：<input type="time" name="rest" value="<?php if($data['rest']){echo $data['rest'];}else{echo REST;} ?>">
                </p>
                <div class="my-2">
                  勤務状態：
                  <label><input type="radio" name="style[]" value="出社" <?php if($data['style'] == "出社"){echo 'checked';} ?>> 出社　</label>
                  <label><input type="radio" name="style[]" value="在宅" <?php if($data['style'] == "在宅"){echo 'checked';} ?>> 在宅</label>
                </div>

              </div>

              <p>
                <span class="text-danger"> <?php echo h($err['setting']); ?></span>
                <span class="text-success"> <?php echo h($msg_setting); ?></span>
              </p>

              <div class="my-3">
                <input type="submit" class="btn btn-primary btn-block" value="登録">
              </div>

              <!-- CSRF対策：index.phpがPOSTされて遷移してきた場合、次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
              <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

            </form>

          </div>
      </div>
    </div><!-- container -->

    <!-- footer -->
    <?php include 'templetes/footer.php'; ?>
  </body>
</html>
