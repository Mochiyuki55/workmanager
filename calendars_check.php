<?php
require_once('config.php');
require_once('functions.php');
$title = '勤務確認画面';
$toppage_url = 'players.php';
$toppage = 'トップページへ';
$setting = '';
session_start();
if(!isset($_SESSION['USER'])){redirect();} // ログインチェック

// GETアクセス時の処理
$user = $_SESSION['USER'];
$pdo = connectDb();

// 要求された社員のidと名前を取得
$submited_player_id = $_POST['submited_player_id'];
$submited_player_data = getUserbyUserId($submited_player_id, $pdo);

// 表示する月（先月）を取得
$last_month = date('Y/m',strtotime("-1 month"));


// 選択された月の勤務表を取得する
$rows = getCalendar($submited_player_id, $last_month, $pdo);

// 選択された月の集計を計算
$calc_rows = calcMonthly($rows,$pdo);


if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  setToken(); // CSRF 対策

} else {
  checkToken(); // CSRF 対策

// -----------------------------------------------------
// 勤務表の差戻処理
// -----------------------------------------------------
  if(isset($_POST['calendar_back'])){
    // 表示している社員の先月の勤務表をnot submitedにする
    // if関数内ではスコープの範囲外になるので、改めて定義する必要がある
    $submited_player_id = $_POST['submited_player_id'];
    $last_month = date('Y/m',strtotime("-1 month"));
    updateSubmitFlag($submited_player_id,$last_month,'not submited',$pdo);
    updateCheckedFlag($submited_player_id,$last_month,'unchecked',$pdo);

    // 今の画面を再表示する
    unset($pdo);
    redirect($toppage_url);

// -----------------------------------------------------
// 勤務表の承認処理
// -----------------------------------------------------
  }elseif (isset($_POST['calendar_check'])) {

    $submited_player_id = $_POST['submited_player_id'];
    $last_month = date('Y/m',strtotime("-1 month"));

    // 選択された月の勤務表を取得する
    $rows = getCalendar($submited_player_id, $last_month, $pdo);
    // 選択された月の集計を計算
    $calc_rows = calcMonthly($rows,$pdo);

    // 表示している社員の年休残日数を取得
    $sql = "SELECT * FROM setting WHERE user_id = :user_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_id" => $submited_player_id));
    $user_setting = $stmt->fetch(PDO::FETCH_ASSOC);


    // 年休残日数の計算
    $user_setting['holyday_remain'] = $user_setting['holyday_remain'] - $calc_rows['holyday_count'];
    // 年休残日数の更新
    $sql = "UPDATE setting SET holyday_remain = :holyday_remain WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id',$submited_player_id);
    $stmt->bindValue(':holyday_remain',$user_setting['holyday_remain']);
    $stmt->execute();

    // 表示している社員の先月の勤務表をcheckedにする
    updateCheckedFlag($submited_player_id,$last_month,'checked',$pdo);

    // 今の画面を再表示する
    unset($pdo);
    redirect($toppage_url);

  }

}

unset($pdo);
include 'templetes/head.php'; // head.phpの読み込み
?>
    <script>
      function confirm_back() {
          var select = confirm("この勤務表を差戻しますか？");
          return select;
      }
      function confirm_check() {
          var select = confirm("この勤務表を承認しますか？");
          return select;
      }
    </script>
  </head>
  <body  class="text-center bg-info" >
    <!-- navbar -->
    <?php include 'templetes/navbar.php'; ?>

    <!-- container -->
    <div class="container pt-5">
      <h1 class="mt-5 font-weight-bold text-white"><?php echo h($submited_player_data['user_name'])."さんの".h($last_month)."の".h($title); ?></h1>

      <!-- 操作ボタン -->
      <div class="row">
        <div class="col-md-10 offset-md-1 d-flex flex-row justify-content-center">

          <div class="btn-group mr-auto">
            <a class="btn btn-primary btn-lg border" href="players.php">戻る</a>
          </div>

          <div class="btn-group ml-auto">
            <form class="form" action="" method="post" onsubmit="return confirm_back()">
              <!-- CSRF対策：次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
              <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
              <!-- フォーム識別タグ -->
              <input type="hidden" name="calendar_back" value="1">
              <input type="hidden" name="submited_player_id" value="<?php echo h($submited_player_id); ?>">
              <input type="submit" class="btn btn-secondary btn-lg border <?php if($rows[0]['checked_flag'] == 'checked'){echo 'd-none';} ?>" value="差戻">

            </form>

            <form class="form" action="" method="post" onsubmit="return confirm_check()">
              <input type="hidden" name="calendar_check" value="1">
              <input type="hidden" name="submited_player_id" value="<?php echo h($submited_player_id); ?>">
              <!-- CSRF対策：次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
              <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
              <input type="submit" class="btn btn-success btn-lg border <?php if($rows[0]['checked_flag'] == 'checked'){echo 'd-none';} ?>" value="承認">
            </form>
          </div>

        </div>
      </div>

      <!-- 勤務表 -->
      <div class="row mt-2">
          <div class="col py-3 bg-light">
            <p class="text-success"><?php if($rows[0]['submit_flag'] == 'submited'){echo 'この勤務表は提出されています。';} ?></p>
            <p class="text-danger"><?php if($rows[0]['checked_flag'] == 'checked'){echo 'この勤務表は承認されています。';} ?></p>

            <table class="table table-striped">
              <thead>
                <tr>
                  <th>日付</th>
                  <th>曜日</th>
                  <th>出勤時刻</th>
                  <th>退勤時刻</th>
                  <th>休憩時間</th>
                  <th>勤務時間</th>
                  <th>出社/在宅</th>
                  <th>年休有無</th>
                  <th>備考</th>

                </tr>
              </thead>
              <tbody>

                <?php foreach ($rows as $row): ?>
                    <?php $what_week = WEEK[date("w", strtotime($row['set_date1']."/".$row['set_date2']))];?>
                  <tr class="<?php if($what_week == '土'){echo "text-primary";}elseif($what_week == '日'){echo "text-danger";} ?>
                             <?php if($row['submit_flag'] == 'submited'){echo ' font-weight-bold';} ?>">
                    <td><?php echo h($row['set_date2']); ?></td>
                    <td><?php echo WEEK[date("w", strtotime($row['set_date1']."/".$row['set_date2']))];?></td>
                    <td><?php echo h(substr($row['start_at'], 0, 5)); ?></td>
                    <td><?php echo h(substr($row['end_at'], 0, 5)); ?></td>
                    <td><?php echo h(substr($row['rest'], 0, 5)); ?></td>
                    <td><?php echo h($calc_rows['worktime'][$row['id']].":00");?></td>
                    <td><?php echo h($row['style']); ?></td>
                    <td><?php echo h($row['holyday']); ?></td>
                    <td class=""><?php echo h($row['set_message']); ?></td>

                  </tr>
                <?php endforeach; ?>

                <!-- 合計 -->
                <tr>
                  <td>計</td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td><?php echo h($calc_rows['rest_all']); ?></td>
                  <td><?php echo h($calc_rows['worktime_all']); ?></td>
                  <td>出社:<?php echo h($calc_rows['style_go_count']); ?>回<br>在宅:<?php echo h($calc_rows['style_stay_count']); ?>回</td>
                  <td>年休:<?php echo h($calc_rows['holyday_count']); ?>回</td>
                  <td></td>
                  <td></td>
                </tr>

              </tbody>
            </table>

          </div>
      </div>
    </div><!-- container -->


    <!-- footer -->
    <?php include 'templetes/footer.php'; ?>
  </body>
</html>
