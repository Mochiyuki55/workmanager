<?php
// ローカル環境
  define('HOST_NAME','localhost');
  define('DATABASE_USER_NAME','root');
  define('DATABASE_PASSWORD','root');
  define('DATABASE_NAME','work_manager');
  define('SITE_URL', 'http://localhost:8888/work_manager/web/');

// サーバーが変わったときは以下の設定を変更するだけで良い
  // define('HOST_NAME','mysql57.limesnake4.sakura.ne.jp');
  // define('DATABASE_USER_NAME','limesnake4');
  // define('DATABASE_PASSWORD','Yaguchi88');
  // define('DATABASE_NAME','limesnake4_workmanager');
  // define('SITE_URL', 'https://limesnake4.sakura.ne.jp/workmanager/web/');

// メールフォーム
define('ADMIN_EMAIL', 'yaguchi1061@gmail.com');

// アプリタイトル
define('TITLE', 'Work Manager');

// Cookieネーム
define('COOKIE_NAME','WORK_MANAGER');

// コピーライト
define('COPY_RIGHT', '&copy; Mochiyuki55');

// 設定初期値
define('START_AT','09:00');
define('END_AT','17:30');
define('REST','01:00');
define('HOLYDAY_REMAIN',10);

// カレンダーの対応月(2022~2049年まで)
define('CALENDAR_ARRAY',array(
  date('Y/m',strtotime("-1 month")) => date('Y年m月',strtotime("-1 month")),
  date('Y/m') => date('Y年m月'),
  date('Y/m',strtotime("+1 month")) => date('Y年m月',strtotime("+1 month")),

));

define('WEEK',array(
  "0" => "日",
  "1" => "月",
  "2" => "火",
  "3" => "水",
  "4" => "木",
  "5" => "金",
  "6" => "土"
));
?>
