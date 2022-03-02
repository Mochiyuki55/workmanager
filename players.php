<?php
require_once('config.php');
require_once('functions.php');
$toppage_url = 'players.php';
$toppage = 'トップページへ';
$title = '社員名簿';
$setting = '';
session_start();
if(!isset($_SESSION['USER'])){redirect();} // ログインチェック

// 読み込み時の処理
$user = $_SESSION['USER'];
$pdo = connectDb();

// ・社員名簿を表示する
// manager_playerテーブルから、manager_idがユーザーのidと一致するplayer_idを取得する
$players_id = array();
$sql = "SELECT * FROM manager_player WHERE manager_id = :manager_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":manager_id" => $user['id']));
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
  array_push($players_id, $row);
}


// 取得したplayer_idごとに、usersテーブルからuser_nameと、settingテーブルからholyday_remainを取得する
$players_name = array();
$holyday_remain = array();
$calendar_status = array();
foreach ($players_id as $player_id) {
  // usersテーブルからuser_name
  $player = getUserbyUserId($player_id['player_id'], $pdo);
  $players_name[$player_id['player_id']] = $player['user_name'];

  // settingテーブルからholyday_remain
  $sql = "SELECT * FROM setting WHERE user_id = :user_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":user_id" => $player_id['player_id']));
  $holyday_data = $stmt->fetch();
  $holyday_remain[$player_id['player_id']] = $holyday_data['holyday_remain'];

  // 今月の勤務表提出状況、承認状況を取得する。（未提出、提出済、承認済の３パターン）
  $sql = "SELECT * FROM calendars WHERE user_id = :user_id AND set_date1 = :set_date1 LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":user_id" => $player_id['player_id'], "set_date1" => date('Y/m',strtotime("-1 month"))));
  $calendar_flag = $stmt->fetch(PDO::FETCH_ASSOC);
  // 状況判定
  if($calendar_flag['submit_flag'] == 'not submited' && $calendar_flag['checked_flag'] == 'unchecked'){
    $calendar_status[$player_id['player_id']] = '未提出';

  }elseif($calendar_flag['submit_flag'] == 'submited' && $calendar_flag['checked_flag'] == 'unchecked'){
    $calendar_status[$player_id['player_id']] = '提出済';

  }elseif($calendar_flag['submit_flag'] == 'submited' && $calendar_flag['checked_flag'] == 'checked'){
    $calendar_status[$player_id['player_id']] = '承認済';
  }else{
    $calendar_status[$player_id['player_id']] = '勤務表未作成';
  }
} // --foreach($players_id as $player_id)


// ・社員登録リストを表示する
// usersテーブルからuser_statusがplayerであるレコードを取得する
$users_data = array();
$sql = "SELECT * FROM users WHERE user_status = :user_status";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":user_status" => 'player'));
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
  array_push($users_data, $row);
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  setToken(); // CSRF 対策
} else {
  checkToken(); // CSRF 対策

// ----------------------------
  // 社員登録ボタンが押された時
// ----------------------------
  // チェックされた社員のidを取得する
  $checked_players_id = $_POST['checked_players_id'];
  // チェックされた社員のidごとに、manager_playerテーブルに登録されているか確認する
  foreach ($checked_players_id as $checked_player_id => $value) {
    $sql = "SELECT * FROM manager_player WHERE manager_id = :manager_id AND player_id = :player_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":manager_id" => $user['id'], ":player_id" => $checked_player_id));
    $flag = $stmt->fetch();
    // 登録されていなければ新規登録する
    if(!$flag){
      $sql = "INSERT INTO manager_player
              (manager_id, player_id, created_at, updated_at)
              VALUES
              (:manager_id, :player_id, now(), now())";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array(":manager_id" => $user['id'], ":player_id" => $checked_player_id));
    }
  }

  // このページを再読み込みする
  unset($pdo);
  redirect($toppage_url);

}

unset($pdo);
include 'templetes/head.php'; // head.phpの読み込み
?>

  </head>
  <body  class="text-center bg-info" >
    <!-- navbar -->
    <?php include 'templetes/navbar.php'; ?>


    <!-- container -->
    <div class="container pt-5">
      <h1 class="mt-5 font-weight-bold text-white"> <?php echo h($user['user_name'])."さんの".h($title); ?></h1>

      <!-- 操作ボタン -->
      <div class="row">

        <div class="col-md-10 offset-md-1 d-flex flex-row justify-content-center">
          <div class="btn-group ml-auto">
            <!-- モーダルを開くボタン・リンク -->
            <button type="button" class="btn btn-primary btn-lg border" data-toggle="modal" data-target="#modal1">社員登録</button>
          </div>

        </div>
      </div>


      <!-- 社員リスト -->
      <div class="row mt-2">
          <div class="col py-3 bg-light">

            <table class="table table-striped">
              <thead>
                <tr>
                  <th></th>
                  <th>名前</th>
                  <th><?php echo date('Y年m月',strtotime("-1 month"));?>の勤務表状況</th>
                  <th>年休残日数</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>

                <?php foreach ($players_id as $player_id): ?>
                  <tr>

                    <!-- リンクじゃなくフォームの方がいいかも -->
                    <td>
                      <form class="form" action="calendars_check.php" method="post">
                        <!-- 社員id引き継ぎ用input -->
                        <input type="hidden" name="submited_player_id" value="<?php echo h($player_id['player_id']); ?>">
                        <!-- CSRF対策：次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
                        <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
                        <input class="btn btn-primary <?php if($calendar_status[$player_id['player_id']] == '未提出' || $calendar_status[$player_id['player_id']] == '勤務表未作成'){echo 'd-none';} ?>"
                               type="submit" name="" value="確認">
                      </form>
                    </td>
                    <td><?php echo h($players_name[$player_id['player_id']]); ?></td>
                    <td><?php echo h($calendar_status[$player_id['player_id']]); ?></td>
                    <td><?php echo h($holyday_remain[$player_id['player_id']]); ?></td>
                    <td><a class="btn btn-secondary" href="player_delete.php?id=<?php echo h($player_id['player_id']); ?>">削除</a></td>
                  </tr>
                <?php endforeach; ?>

              </tbody>
            </table>

            <h4> <?php if(empty($players_id)){echo "社員登録ボタンから社員を追加してください。";} ?></h4>

          </div>
      </div>
    </div><!-- container -->

    <!-- modal -->
    <div class="modal fade" id="modal1" tabindex="-1" role="dialog" aria-labelledby="label1" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title" id="label1">追加する社員を選択してください</h5>
            <!-- 閉じるボタン -->
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <form class="form" action="" method="post">
            <div class="modal-body">
              <ul class="list-group">

                <?php foreach ($users_data as $user_data): ?>
                  <li class="list-group-item">
                    <input type="checkbox" name="checked_players_id[<?php echo h($user_data['id']); ?>]" id="name<?php echo h($user_data['id']); ?>">
                    <label for="name<?php echo h($user_data['id']); ?>">　<?php echo h($user_data['user_name']); ?></label>
                  </li>

                <?php endforeach; ?>

              </ul>
            </div>

            <div class="modal-footer">
              <!-- CSRF対策：次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
              <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
              <!-- フォーム識別タグ -->
              <input type="hidden" name="player_insert" value="1">

              <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
              <input type="submit" class="btn btn-primary" value="社員登録">
            </div>
          </form>

        </div>
      </div>
    </div>


    <!-- footer -->
    <?php include 'templetes/footer.php'; ?>
  </body>
</html>
