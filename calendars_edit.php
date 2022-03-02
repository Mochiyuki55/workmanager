<?php
require_once('config.php');
require_once('functions.php');
$title = '勤務編集画面';
$toppage_url = 'calendars_set.php';
$toppage = 'トップページへ';
$setting = '設定';
session_start();
if(!isset($_SESSION['USER'])){redirect();} // ログインチェック


// セッションからユーザ情報を取得
$user = $_SESSION['USER'];
$pdo = connectDb();

// GETアクセス時の処理
// 編集ボタンが押された日のidを取得する
$edit_id = $_GET['id'];
// 取得したidのレコードを取得する
$sql = "SELECT * FROM calendars WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":id" => $edit_id));
$edited_date = $stmt->fetch(PDO::FETCH_ASSOC);

$this_day = $edited_date['set_date1']."/".$edited_date['set_date2'];


if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  setToken(); // CSRF 対策

} else {
  checkToken(); // CSRF 対策

    // 入力データを変数に格納する
    $start_at = $_POST['start_at'];
    $end_at = $_POST['end_at'];
    $rest = $_POST['rest'];
    $style = $_POST['style'][0];
    if($_POST['holyday']){
      $holyday = 'あり';
    }else{
      $holyday = 'なし';
    }

    $set_message = $_POST['set_message'];

    // DBに接続する
    $pdo = connectDb();

    // エラーチェック
    $err = array();

      // エラー：出勤時刻が退勤時刻より遅い。出勤時刻と退勤時刻の差が、休憩時間より短い。
      if($end_at - $start_at <= $rest){
        $err['start_at'] = '勤務時間の設定を見直してください。';
      }


    // エラーがなければ
    if(empty($err)){
      // ユーザーの設定情報を更新する
      $sql = "UPDATE calendars
              SET
              start_at = :start_at,
              end_at = :end_at,
              rest = :rest,
              style = :style,
              holyday = :holyday,
              set_message = :set_message
              WHERE
              id = :id";
      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(':id',$edit_id);
      $stmt->bindValue(':start_at', $start_at);
      $stmt->bindValue(':end_at', $end_at);
      $stmt->bindValue(':rest', $rest);
      $stmt->bindValue(':style', $style);
      $stmt->bindValue(':holyday', $holyday);
      $stmt->bindValue(':set_message', $set_message);

      $stmt->execute();


      // トップページへリダイレクト
      unset($pdo);
      redirect($toppage_url);

    } // --- if(empty($err))

  unset($pdo);
}

include 'templetes/head.php'; // head.phpの読み込み
?>
    <script type="text/javascript">

      function LoadProc_start() {
        var now = new Date();
        var target = document.getElementById("time_start");
        var Hour = now.getHours();
        var Min = now.getMinutes();
        target.value = Hour + ":" + Min;
      }
      function LoadProc_end() {
        var now = new Date();
        var target = document.getElementById("time_end");
        var Hour = now.getHours();
        var Min = now.getMinutes();
        target.value = Hour + ":" + Min;
      }

    </script>
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

                <h5><?php echo $this_day; ?></h5>
                <p class=" <?php if($this_day < date('Y/m/d')){echo 'd-none';} ?>">
                  出勤時刻：<input id="time_start" type="time" name="start_at" value="<?php echo h($edited_date['start_at']); ?>"> <input type="button" value="打刻" onclick="LoadProc_start();"/> <br>
                  退勤時刻：<input id="time_end" type="time" name="end_at" value="<?php echo h($edited_date['end_at']); ?>"> <input type="button" value="打刻" onclick="LoadProc_end();"/><br>
                  休憩時間：<input type="time" name="rest" value="<?php echo h($edited_date['rest']); ?>">
                </p>

                <div class="my-2">
                  勤務状態：
                  <label><input type="radio" name="style[]" value="出社" <?php if($edited_date['style'] == "出社"){echo 'checked';} ?>> 出社　</label>
                  <label><input type="radio" name="style[]" value="在宅" <?php if($edited_date['style'] == "在宅"){echo 'checked';} ?>> 在宅</label>
                </div>

                <div class="checkbox my-3">
                  <label>
                    <input type="checkbox" name="holyday" value="1"> 年休を使用する
                  </label>
                </div>

                <div>
                  <textarea type="textarea" name="set_message" rows="5" cols="33" placeholder="備考記入欄"></textarea>
                </div>

              <p><span class="text-danger"> <?php echo h($err['start_at']); ?></span></p>


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
