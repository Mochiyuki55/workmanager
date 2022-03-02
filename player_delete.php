<?php
require_once('config.php');
require_once('functions.php');
session_start();
if(!isset($_SESSION['USER'])){redirect();} // ログインチェック

// 読み込み時の処理
$user = $_SESSION['USER'];
$pdo = connectDb();

// 社員名簿で選択された社員のidをmanager_playerテーブルから削除する
// 選択された社員のidを受け取る
$delete_id = $_GET['id'];

$sql = "DELETE FROM manager_player
        WHERE manager_id = :manager_id
        AND player_id = :player_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":manager_id" => $user['id'], ":player_id" => $delete_id));

// 削除したらトップページに戻る
unset($pdo);
redirect('players.php');
?>
