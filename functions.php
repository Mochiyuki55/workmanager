<?php
require_once('config.php');

// データベースに接続する
function connectDb() {
  $host = HOST_NAME; //データベースサーバ名
  $user = DATABASE_USER_NAME; //データベースユーザー名
  $pass = DATABASE_PASSWORD; //パスワード
  $db = DATABASE_NAME; //データベース名
  $param= 'mysql:dbname='.$db.';host='.$host;

  // 例外処理は”起きることが期待されない問題”で、多くの場合、プログラムの実行を停止しても構わない場合に使う
  try{
    // 例外処理：以下の処理でエラーが発生したら
    $pdo = new PDO('mysql:host='.$host.';dbname='.$db, $user, $pass);
    $pdo->query('SET NAMES utf8;');
    return $pdo;

  } catch (PDOException $e){
    // 例外処理：エラー内容をエコーして処理を終了
    echo $e->getMessage();
    exit;
  }

}

// 配列からプルダウンメニューを生成する
// 引数として、selectタグのname値($inputName),メニュー項目用の配列($srcArray),選択値($selectedIndex),の3つを受け取るようにしています。
function arrayToSelect($inputName, $srcArray, $selectedIndex = "") {
    $temphtml = '<select class="form-select form-select-lg" name="'. $inputName. '">';

    foreach ($srcArray as $key => $val) {
        if ($selectedIndex == $key) {
            $selectedText = ' selected="selected"';
        } else {
            $selectedText = '';
        }
        // 「.=」というのは「$temphtml = $temphtml.'xxx'」と同じ意味で、文字列を変数に連結しているという意味です。
        $temphtml .= '<option value="'. $key. '"'. $selectedText. '>'. $val. '</option>';
    }

    // もとの$temphtmlに次の文字列を付け加える。
    $temphtml .= '</select>';

    return $temphtml;
}

// HTMLエスケープ用関数（XSSのサイバー攻撃の対策）
// HTML上のecho関数のところに設置する"<?php echo h($変数名);
function h($original_str){
  return htmlspecialchars($original_str, ENT_QUOTES, "UTF-8");
}

// CSRF対策用関数
// CSRF対策は、呼び出し元の画面が固定であり、呼び出しによりDB登録などの重要処理を行う画面には必ず施します。
// 逆に、登録処理などを行わないページや複数箇所から呼び出されるページ（例えばTOPページや一覧ページなど）についてはCSRF対策は施しません。
function setToken() { // トークンを発行する処理（このトークンはCookieとは関係ない）
    // 暗号化されたランダムな文字列を作成
    $token = sha1(uniqid(mt_rand(), true));
    // 作成したトークンをセッションに登録
    $_SESSION['sstoken'] = $token;
}
function checkToken() { // トークンをチェックする処理
    // 発行したトークンをセッション内に持っていない、もしくはPOSTされたトークンがセッション内のトークンと異なる場合
    if (empty($_SESSION['sstoken']) || ($_SESSION['sstoken'] != $_POST['token'])) {
        echo '<html><head><meta charset="utf-8"></head><body>不正なアクセスです。</body></html>';
        // 処理を強制終了
        exit;
    }
}


// メールアドレス確認用関数
function checkEmail($user_email, $pdo){
  // Player
  $sql = "SELECT * FROM users WHERE user_email = :user_email LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":user_email" => $user_email));
  $check_user = $stmt->fetch(PDO::FETCH_ASSOC);
  return $check_user ? true : false;

}

// ユーザー情報登録用関数
function registerUser($user_status,$user_name,$user_email,$user_password,$pdo){
  // 判定：社員としての登録か、管理者としての登録か
  // 社員としての場合
    $sql ="INSERT INTO users (user_name, user_password, user_email, user_status, created_at, updated_at) VALUES (:user_name, :user_password, :user_email, :user_status, now(), now())";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_name',$user_name);
    $stmt->bindValue(':user_email',$user_email);
    $stmt->bindValue(':user_password',$user_password);
    $stmt->bindValue(':user_status',$user_status);
    $stmt->execute();
}

// ユーザー情報取得用関数(Player)
function getUser($user_email, $user_password, $pdo) {
  $sql = "SELECT * FROM users WHERE user_email = :user_email AND user_password = :user_password LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":user_email" => $user_email, ":user_password" => $user_password));
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  // $userが存在すれば$user, なければfalseを返す
  return $user ? $user : false;
}

// リダイレクト用関数
function redirect($url){
  header('Location: '.SITE_URL.$url);
  exit;
}

// ユーザIDからuserを検索する
function getUserbyUserId($user_id, $pdo) {
    $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":id" => $user_id));
    $user = $stmt->fetch();
    return $user ? $user : false;
}

// ユーザーの設定を読み込む
function getSetting($user_id, $pdo){
  $sql = "SELECT * FROM setting WHERE user_id = :user_id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":user_id" => $user_id));
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  return $data ? $data : false;
}

// ユーザーの設定を追加する
function registerSetting($user_id,$start_at,$end_at,$rest,$style,$holyday_remain,$pdo){
  $sql = "INSERT INTO setting
          (user_id, start_at, end_at, rest, style, holyday_remain, created_at, updated_at)
          VALUES
          (:user_id, :start_at, :end_at, :rest, :style, :holyday_remain, now(), now())";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':user_id',$user_id);
  $stmt->bindValue(':start_at', $start_at);
  $stmt->bindValue(':end_at', $end_at);
  $stmt->bindValue(':rest', $rest);
  $stmt->bindValue(':style', $style);
  $stmt->bindValue(':holyday_remain', $holyday_remain);
  $stmt->execute();
}


// calendarsテーブルに勤務表を登録する
function registerCalendar($selected_month, $user_id,$set_date1,$start_at,$end_at,$rest,$style,$pdo){
  for($day = 1; $day < date( 't' , strtotime($selected_month . "/01"))+1; $day++){
    $sql = "INSERT INTO calendars
            (user_id, set_date1, set_date2, start_at, end_at, rest, style, holyday, set_message, submit_flag, checked_flag, created_at, updated_at)
            VALUES
            (:user_id, :set_date1, :set_date2, :start_at, :end_at, :rest, :style, :holyday, :set_message, :submit_flag, :checked_flag, now(), now())";
    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':user_id',$user_id,PDO::PARAM_INT);
    $stmt->bindValue(':set_date1', $set_date1);
    $stmt->bindValue(':set_date2', $day);

        // 土曜日か日曜日の場合、入力内容をーーとする
    $what_week = WEEK[date("w", strtotime($selected_month."/".$day))];
    if($what_week == '土' || $what_week == '日'){
      $stmt->bindValue(':start_at', '00:00');
      $stmt->bindValue(':end_at', '00:00');
      $stmt->bindValue(':rest', '00:00');
      $stmt->bindValue(':style', '');
      $stmt->bindValue(':holyday', '');
    }else{

      $stmt->bindValue(':start_at', $start_at);
      $stmt->bindValue(':end_at', $end_at);
      $stmt->bindValue(':rest', $rest);
      $stmt->bindValue(':style', $style);
      $stmt->bindValue(':holyday', 'なし');

    }
    $stmt->bindValue(':set_message', '');
    $stmt->bindValue(':submit_flag', 'not submited');
    $stmt->bindValue(':checked_flag', 'unchecked');

    $stmt->execute();

  }
}

// ユーザーの現在の月の勤務表を表示する
function getCalendar($user_id, $set_date1, $pdo){
  $rows = array();
  $sql = "SELECT * FROM calendars WHERE user_id = :user_id AND set_date1 = :set_date1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(":user_id" => $user_id, ":set_date1" => $set_date1));
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    array_push($rows, $row);
  };
  return $rows ? $rows : false;

}

// 選択された月の集計を計算
function calcMonthly($rows,$pdo){
  // 初期値設定
  $rest_all = 0;
  $style_go_count = 0;   // 出社の回数
  $style_stay_count = 0; // 在宅の回数
  $holyday_count = 0;    // 有給休暇の回数
  $worktime = array();   // 勤務時間の連想配列
  $worktime_all = 0;     // 勤務総時間

  // 積算処理
  foreach ($rows as $row) {
    // 休憩時間の積算
    $rest_all += $row['rest'];
    // 各日の残業時間を格納
    $worktime[$row['id']] = $row['end_at'] - $row['start_at'] - $row['rest'];
    $worktime_all += $worktime[$row['id']];
    if($row['style'] == '出社'){$style_go_count++;
    }elseif($row['style'] == '在宅'){$style_stay_count++;}
    if($row['holyday'] == 'あり'){$holyday_count++;}
  }

  // 各値を配列に格納
  $calc_array = array();
  $calc_array['rest_all'] = $rest_all;
  $calc_array['worktime'] = $worktime;
  $calc_array['worktime_all'] = $worktime_all;
  $calc_array['style_go_count'] = $style_go_count;
  $calc_array['style_stay_count'] = $style_stay_count;
  $calc_array['holyday_count'] = $holyday_count;

  return $calc_array;

}

// 提出した月のsubmit_flagをsubmitedに更新する
function updateSubmitFlag($user_id,$set_date1,$flag,$pdo){
  $sql = "UPDATE calendars
                  SET
                  submit_flag = :submit_flag
                  WHERE
                  user_id = :user_id AND set_date1 = :set_date1";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':user_id',$user_id);
  $stmt->bindValue(':set_date1',$set_date1);
  $stmt->bindValue(':submit_flag',$flag);
  $stmt->execute();
}
// 提出した月のchecked_flagをcheckedに更新する
function updateCheckedFlag($user_id,$set_date1,$flag,$pdo){
  $sql = "UPDATE calendars
                  SET
                  checked_flag = :checked_flag
                  WHERE
                  user_id = :user_id AND set_date1 = :set_date1";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':user_id',$user_id);
  $stmt->bindValue(':set_date1',$set_date1);
  $stmt->bindValue(':checked_flag',$flag);
  $stmt->execute();
}

?>
