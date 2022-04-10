# 概要

コンテナ間通信・ホストからコンテナへの通信を試したい。

## ゴール

コンテナ間通信・ホスト-コンテナ通信のための設定を理解することを目指す。

## 用語整理

* bridge: Dockerのネットワークのドライバのデフォルト設定 表面上はIPアドレスで通信しているが、内部的にはMACアドレスで通信

## チートシート

```bash
# ネットワークをつくりたい
docker network create [OPTIONS] NETWORK

# ネットワークを一覧表示したい
docker network ls [OPTIONS]

# ネットワークを削除したい
docker network rm NETWORK [NETWORK...]

# 接続するネットワークを指定してコンテナをつくりたい
docker container create -it [OPTIONS] --network=NETWORK_NAME IMAGE [COMMAND] [ARG...]
# ポートをホストへ公開してコンテナをつくりたい
docker container create -it [OPTIONS] -p HOST_PORT:CONTAINER_PORT IMAGE [COMMAND] [ARG...]
```

## PHP-CLIからMariaDBへ接続したい

### ネットワークをつくりたい

[参考](https://docs.docker.com/engine/reference/commandline/network_create/)

> 書式: `docker network create [OPTIONS] NETWORK`

```bash
$ docker network create php-mariadb
0bea12975e9244a4fef4ca5c1f01186e44ae80ad93b4dd0f126a6d4c74cc1454
```

### ネットワークを一覧表示したい

[参考](https://docs.docker.com/engine/reference/commandline/network_ls/)

> 書式: `docker network ls [OPTIONS]`

```bash
$ docker network ls
NETWORK ID     NAME          DRIVER    SCOPE
db0be0acb999   bridge        bridge    local
e4bcfab1120c   host          host      local
a4e9019b8275   none          null      local
0bea12975e92   php-mariadb   bridge    local
```

### コンテナをネットワークへ接続させたい-PHP

#### イメージをダウンロード

```bash
$ docker image pull php:8.1-cli-buster
```

#### コンテナを作成・起動

[参考](https://docs.docker.com/engine/reference/run/#network-settings)

```bash
# php-mariadbネットワークへ接続
# ホストのsourceディレクトリ配下をコンテナの/appディレクトリへbind mount
$ docker container create -it --name php --network=php-mariadb --mount type=bind,source="${PWD}/source",target=/app php:8.1-cli-buster
7e454f8eaa769b9e5189800edbfe21f15edd16334d89b08294b4b0ec26c2c03a

$ docker container start php
php
```

### コンテナをネットワークへ接続させたい-MariaDB

#### ボリュームを作成

```bash
$ docker volume create mariadb
mariadb

$ docker volume ls
DRIVER    VOLUME NAME
local     mariadb
```

#### イメージをダウンロード

```bash
$ docker image pull mariadb:latest
```

#### コンテナを作成・起動

```bash
$ docker container create -it --name mariadb --network=php-mariadb --mount type=volume,source=mariadb,target=/var/lib/mysql -e MARIADB_ROOT_PASSWORD=mariadb  mariadb:latest
e01e06aabb3d0d825ae9c5d4fd7784de3ba26ed52a1dbd05b75f691ce25e47ba

$ docker container start mariadb
mariadb
```

#### DB・サンプルテーブルを作成

```bash
root@e01e06aabb3d:/# mysql -u root -p
Enter password: 
# データベースを作成
MariaDB [(none)]> create database network_sample;
Query OK, 1 row affected (0.001 sec)

# テーブルを作成
MariaDB [(none)]> use network_sample;
Database changed
MariaDB [network_sample]> create table sample(text varchar(255));
Query OK, 0 rows affected (0.009 sec)
```

### PHPからデータベースを操作し、レコードを挿入したい

以下のソースを作成。PDOでデータベースへ接続し、レコードを挿入。
また、ユーザ定義のネットワークへbridgeドライバで接続することで、コンテナ名でホスト名を名前解決できるようになる。

```PHP
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
$bindText = 'Hello from PDO';
$sql = 'INSERT INTO sample(text) values(:text)';
$statement = $pdo->prepare($sql);
$statement->bindParam('text', $bindText, PDO::PARAM_STR);

$statement->execute();
```

#### 動作確認

```bash
$ docker container exec -it php bash
# MariaDB(MySQL)のPDOドライバをインストール
root@7e454f8eaa76:/app# docker-php-ext-install pdo_mysql

# インストールされたことを確認
root@7e454f8eaa76:/app# php -m | grep -P 'pdo.*'
pdo_mysql
pdo_sqlite

# /appディレクトリへ移動し、スクリプトを実行
root@7e454f8eaa76:/app# ls
pdo_cli.php

root@7e454f8eaa76:/app# php ./pdo_cli.php
```

[参考-How to install more PHP extensions](https://hub.docker.com/_/php)

#### レコード確認

```bash
# MariaDBコンテナへ
$ docker container exec -it mariadb bash
root@e01e06aabb3d:/# mysql -u root -p

# データベースのテーブルを参照
MariaDB [(none)]> use network_sample;
Reading table information for completion of table and column names
You can turn off this feature to get a quicker startup with -A

Database changed
# レコードがつくられたことを確認
MariaDB [network_sample]> select * from sample;
+----------------+
| text           |
+----------------+
| Hello from PDO |
+----------------+
1 row in set (0.000 sec)

```

#### 後始末

```bash
# コンテナを削除
$ docker container stop mariadb
mariadb
$ docker container stop php    
php

$ docker container rm mariadb
mariadb
$ docker container rm php    
php

# ボリュームを削除
$ docker volume rm mariadb
mariadb
```

### ネットワークを削除したい

[参考](https://docs.docker.com/engine/reference/commandline/network_rm/)

> 書式: `docker network rm NETWORK [NETWORK...]`

```bash
$ docker network rm php-mariadb
php-mariadb
```

---

## データベースへ接続するPHPアプリケーションをブラウザで参照したい

#### ネットワークをつくる

```bash
$ docker network create php-mariadb   
6058f792ce8cf5c55e7d90d91882104c135a773dd0ca95dec70690debc1f5831

$ docker network ls
NETWORK ID     NAME          DRIVER    SCOPE
0fad83f83893   bridge        bridge    local
e4bcfab1120c   host          host      local
a4e9019b8275   none          null      local
6058f792ce8c   php-mariadb   bridge    local
```

#### ボリュームをつくる

```bash
$ docker volume create mariadb
mariadb

$ docker volume ls
DRIVER    VOLUME NAME
local     mariadb
```

#### DBコンテナをつくる

```bash
$ docker container create -it --name mariadb --mount type=volume,source=mariadb,target=/var/lib/mysql --network=php-mariadb -e MARIADB_ROOT_PASSWORD=mariadb mariadb:latest
5bf4553142aa7b103b61b68a944dd0ded99151c7140f19c17aba25823d2e6545

$ docker container start mariadb
mariadb

$ docker container exec -it mariadb bash
root@5bf4553142aa:/# mysql -u root -p
Enter password: 
Welcome to the MariaDB monitor.  Commands end with ; or \g.
Your MariaDB connection id is 3
Server version: 10.7.3-MariaDB-1:10.7.3+maria~focal mariadb.org binary distribution

Copyright (c) 2000, 2018, Oracle, MariaDB Corporation Ab and others.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

MariaDB [(none)]> create database network_sample;
Query OK, 1 row affected (0.001 sec)

MariaDB [(none)]> use network_sample;
Database changed
MariaDB [network_sample]> create table sample(text varchar(255));
Query OK, 0 rows affected (0.007 sec)

MariaDB [network_sample]> INSERT INTO sample(text) values('Hello PHP');
Query OK, 1 row affected (0.002 sec)

MariaDB [network_sample]> exit
Bye
root@5bf4553142aa:/# exit
exit
```


### Webサーバコンテナをつくりたい

PHPのコンテナをWebサーバとしたい場合、PHP-FPMやApacheを利用するとシンプル。今回はよくサンプルとして紹介されているApacheで構築してみる。

[参考-php:<version>-apache](https://hub.docker.com/_/php)

#### イメージの取得

```bash
$ docker image pull php:8.1-apache-buster
```

#### コンテナの作成

Webサーバにホストからアクセスできるよう、新たに`-p/--publish`オプションを追加する。
これは、Dockerコンテナのポートをホストへ公開するためのものである。

[参考](https://docs.docker.com/config/containers/container-networking/#published-ports)

また、以下のPHPファイルをsourceディレクトリ以下へ配置。

```PHP
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
```

```bash
$ docker container create -it --name php --mount type=bind,source="${PWD}/source",target=/var/www/html --network=php-mariadb -p 8080:80 php:8.1-apache-buster
d295d3e5c30f658e6bb8b142e1510871a5502820bab600f9e422da3be52fbcd7

$ docker container start php
php

$ docker container exec -it php bash
root@d295d3e5c30f:/var/www/html# docker-php-ext-install pdo_mysql
```

#### 動作確認

```bash
$ curl localhost:8080/pdo_web.php
Array
(
    [text] => Hello PHP
)
```

Webサーバと通信し、PHPを通じてMariaDBに保存されたレコードを読み出せたことを確認。