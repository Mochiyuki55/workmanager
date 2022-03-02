<?php
require_once('config.php');
require_once('functions.php');
$title = '勤務管理画面';
$toppage_url = 'calendars_set.php';
$toppage = '';
$setting = '設定';
session_start();
if(!isset($_SESSION['USER'])){redirect();} // ログインチェック

// GETアクセス時の処理
$user = $_SESSION['USER'];
$pdo = connectDb();

// もしセッションがなければ、現在の月とする
if(!isset($_SESSION['CURRENT_CALENDAR'])){
  $_SESSION['CURRENT_CALENDAR'] = date('Y/m');
}

// 選択された月の勤務表を取得する
$rows = getCalendar($user['id'], $_SESSION['CURRENT_CALENDAR'], $pdo);
// もし選択された月の勤務表データがなければ作成する
if(empty($rows)){
  $data = getSetting($user['id'], $pdo);
  registerCalendar($_SESSION['CURRENT_CALENDAR'],$user['id'],$_SESSION['CURRENT_CALENDAR'],$data['start_at'],$data['end_at'],$data['rest'],$data['style'],$pdo);

  // その後、画面更新
  $rows = getCalendar($user['id'], $_SESSION['CURRENT_CALENDAR'], $pdo);
}

// 選択された月の集計を計算
$calc_rows = calcMonthly($rows,$pdo);


if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  setToken(); // CSRF 対策

} else {
  checkToken(); // CSRF 対策

  // -----------------------------------------------------
  // カレンダー切り替え処理
  // -----------------------------------------------------
  if(isset($_POST['calendar_update'])){
    // 選択された月を取得
    $_SESSION['CURRENT_CALENDAR'] = $_POST['selected_calendar'];

    // 選択された月の勤務表データをcalendarsテーブルから取得
    $rows = getCalendar($user['id'], $_SESSION['CURRENT_CALENDAR'], $pdo);

    // もし選択された月の勤務表データがなければ作成する
    if(empty($rows)){
      $data = getSetting($user['id'], $pdo);
      registerCalendar($_SESSION['CURRENT_CALENDAR'],$user['id'],$_SESSION['CURRENT_CALENDAR'],$data['start_at'],$data['end_at'],$data['rest'],$data['style'],$pdo);

      // その後、画面更新
      $rows = getCalendar($user['id'], $_SESSION['CURRENT_CALENDAR'], $pdo);

    }

    // 選択された月の集計を計算
    $calc_rows = calcMonthly($rows,$pdo);


    // -----------------------------------------------------
    // 勤務表提出処理
    // -----------------------------------------------------
  }elseif(isset($_POST['calendar_submit'])){
    // 提出した月のsubmit_flagをsubmitedに更新する
    // ユーザーの設定情報を更新する
    updateSubmitFlag($user['id'],$_SESSION['CURRENT_CALENDAR'],'submited',$pdo);


    $msg_submit = 'この勤務表は提出されています。';

    unset($pdo);
    redirect($toppage_url);
  }

}

unset($pdo);

include 'templetes/head.php'; // head.phpの読み込み
?>
    <script>
      function confirm_test() {
          var select = confirm("この月の勤務表を提出しますか？");
          return select;
      }
    </script>
  </head>
  <body  class="text-center bg-info" >
    <!-- navbar -->
    <?php include 'templetes/navbar.php'; ?>

    <!-- container -->
    <div class="container pt-5">
      <h1 class="mt-5 font-weight-bold text-white"><?php echo h($user['user_name'])."さんの".CALENDAR_ARRAY[$_SESSION['CURRENT_CALENDAR']]."の".h($title); ?></h1>

      <!-- 操作ボタン -->
      <div class="row">
        <div class="col-md-10 offset-md-1 d-flex flex-row justify-content-center">

          <div class="btn-group mr-auto">
            <form class="form" action="" method="post">
              <span class="lead"><?php echo arrayToSelect("selected_calendar", CALENDAR_ARRAY); ?></span>
              <!-- CSRF対策：次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
              <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
              <!-- フォーム識別タグ -->
              <input type="hidden" name="calendar_update" value="1">
              <input type="submit" class="btn btn-secondary border btn-lg" value="表示">

            </form>
          </div>

          <div class="btn-group ml-auto">
            <form class="form" action="" method="post" onsubmit="return confirm_test()">
              <input type="hidden" name="calendar_submit" value="1">
              <!-- CSRF対策：次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
              <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
              <input class="btn btn-primary btn-lg border <?php if($rows[0]['submit_flag'] == 'submited'){echo 'd-none';} ?>"
                     type="submit" name="" value="勤務表提出">
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
                  <th></th>
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
                    <td>
                        <a class="btn btn-secondary border <?php if($row['submit_flag'] == 'submited'){echo ' d-none';} ?>"
                           href="calendars_edit.php?id=<?php echo h($row['id']); ?>">編集</a>
                    </td>
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
    <?php include 'templetes/modal_edit.php'; ?>

    <!-- footer -->
    <?php include 'templetes/footer.php'; ?>

  </body>
</html>
