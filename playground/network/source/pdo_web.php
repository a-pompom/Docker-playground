<?php
// PDO設定
$pdo_dsn = 'mysql:host=mariadb;dbname=network_sample;charset=utf8;';
$pdo_user = 'root';
$pdo_pass = 'mariadb';
$pdo_option = array(
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES => false,
	PDO::ATTR_STRINGIFY_FETCHES => false
);
// データベース接続
try {
	$pdo = new PDO($pdo_dsn, $pdo_user, $pdo_pass, $pdo_option);
} catch (Exception $e) {
	header('Content-Type: text/plain; charset=UTF-8', true, 500);
	exit($e->getMessage());
}

// SQL実行
$sql = 'SELECT * FROM sample';
$statement = $pdo->prepare($sql);
$statement->execute();
$result = $statement->fetch();
// 画面表示
print_r($result);
echo PHP_EOL;