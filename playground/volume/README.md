# 概要

volumeを扱うあれこれを試したい。

## ゴール

bind mount, volume mountを利用し、ホスト/コンテナ間でデータを共有できるようになることを目指す

## 用語整理

* volume: `docker volume`コマンドで操作されるDockerエンジンが管理する領域
* volumes: `docker volume`コマンドを使ったデータ永続化手法
* bind mount: volumesとはマウント元が異なるデータ永続化手法

## チートシート

```bash
# volumeをつくりたい
docker volume create [OPTIONS] [VOLUME]

# volumeを一覧表示したい
docker volume ls [OPTIONS]

# volumeを削除したい
docker volume rm [OPTIONS] VOLUME [VOLUME...]

# コンテナの中に入りたい
docker container exec [OPTIONS] CONTAINER COMMAND [ARG...]
# 例:
docker container exec -it php bash

# bind mountでコンテナをつくりたい
docker container create -it [OPTIONS] --mount type=bind,source=SOURCE_DIR,target=TARGET_PATH IMAGE [COMMAND] [ARG...]
# volume mountでコンテナをつくりたい
docker container create -it [OPTIONS] --mount type=volume,source=VOLUME_NAME,target=TARGET_PATH IMAGE [COMMAND] [ARG...]
```

## PHPのソースコードをコンテナで実行したい(bind mount)

```PHP
// PHPソース
// source/hello.php
<?php
namespace source;

echo 'Hello From Container'.PHP_EOL;
```

#### イメージをダウンロード

```bash
$ docker image  pull php:8.1-cli-buster
```

### コンテナを作成・起動したい

```bash
$ docker container create -it --name php --mount type=bind,source="$(PWD)/source",target=/app php:8.1-cli-buster
52079bb776c353ee3d8ce45faa4e59f72f4da598014a387833b34a873e207340
```

[参考](https://docs.docker.com/storage/bind-mounts/#choose-the--v-or---mount-flag)

書式の説明が公式にはなかったので、ここでは概要のみ記す。

* type: bind, volume, tmpfsのいずれかを指定
* source: マウント元
* destination: コンテナのマウント先

```bash
# コンテナを起動
$ docker container start php
php
# コンテナがつくられたことを確認
$ docker container ls
CONTAINER ID   IMAGE                COMMAND                  CREATED          STATUS         PORTS     NAMES
52079bb776c3   php:8.1-cli-buster   "docker-php-entrypoi…"   24 seconds ago   Up 2 seconds             php
```

### コンテナの中に入りたい

[参考](https://docs.docker.com/engine/reference/commandline/container_exec/)

> 書式 `docker container exec [OPTIONS] CONTAINER COMMAND [ARG...]`

```bash
$ docker container exec -it php bash
```

`CONTAINER`が`php`・`COMMAND`が`bash`に相当。

```bash
# コンテナの中
root@52079bb776c3:/# ls
app  bin  boot  dev  etc  home  lib  lib64  media  mnt  opt  proc  root  run  sbin  srv  sys  tmp  usr  var
root@52079bb776c3:/# cd app
# bind mount先のappディレクトリへファイルがマウントされたことを確認
root@52079bb776c3:/app# ls
hello.php
```

### PHPファイルをCLIで実行したい

```bash
root@52079bb776c3:/app# php ./hello.php 
# 実行結果が出力されたことを確認
Hello From Container
```

#### 後始末

```bash
# コンテナを停止
$ docker container stop php
php

# コンテナを削除
$ docker container rm php
php

# イメージを削除
# イメージIDを指定
docker image rm d35d81cc7522
```

---

## MySQLのデータベースをvolume mountで永続化したい

### volumeをつくりたい

[参考](https://docs.docker.com/engine/reference/commandline/volume_create/)

> 書式: `docker volume create [OPTIONS] [VOLUME]`

```bash
$ docker volume create mariadb
mariadb
```

volumeがつくられたことを確認。

### 作成されたvolumeを一覧表示したい

[参考](https://docs.docker.com/engine/reference/commandline/volume_ls/)

> 書式: `docker volume ls [OPTIONS]`

```bash
$ docker volume ls

DRIVER    VOLUME NAME
local     mariadb
```

### MariaDBコンテナをvolume マウントでつくりたい

[参考 Where to Store Data, 環境変数](https://hub.docker.com/_/mariadb)
[eオプション参考](https://docs.docker.com/engine/reference/run/#env-environment-variables)

```bash
# コンテナ名はmariadb, マウント方法はvolume マウントで、mariadbボリュームをコンテナの/var/lib/mysqlディレクトリへマウント
# 環境変数としてMARIADB_ROOT_PASSWORDを設定 最後に、もととなるイメージとしてmariadbを指定
$ docker container create -it --name mariadb --mount type=volume,source=mariadb,target="/var/lib/mysql" -e MARIADB_ROOT_PASSWORD=mariadb mariadb:latest
```

#### コンテナを起動後、中に入る

```bash
$ docker container start mariadb
mariadb

$ docker container ls
CONTAINER ID   IMAGE            COMMAND                  CREATED              STATUS          PORTS      NAMES
c25650069bac   mariadb:latest   "docker-entrypoint.s…"   About a minute ago   Up 24 seconds   3306/tcp   mariadb

$ docker container exec -it mariadb bash
root@c25650069bac:/#
```

### データベースを操作してテーブルへレコードを挿入したい

```bash
# ログイン
root@c25650069bac:/# mysql -u root -p
Enter password: 
# データベースを左ｋ酢英
MariaDB [(none)]> create database sample;
Query OK, 1 row affected (0.000 sec)

# 作成したデータベースを利用
MariaDB [(none)]> use sample;
Database changed
# テーブルを作成
MariaDB [sample]> create table sample(text varchar(255));
Query OK, 0 rows affected (0.007 sec)
# テーブルへレコードを挿入
MariaDB [sample]> insert into sample(text) values('Hello MariaDB');
Query OK, 1 row affected (0.002 sec)

MariaDB [sample]> select * from sample;
+---------------+
| text          |
+---------------+
| Hello MariaDB |
+---------------+
1 row in set (0.000 sec)
```

#### 後始末

```bash
$ docker container stop mariadb
mariadb
aoi@aoinoMacBook-puro: ~/Desktop/playground/Docker/playground/volume (main *%=)
$ docker container rm mariadb
mariadb

$ docker container ls -a
CONTAINER ID   IMAGE     COMMAND   CREATED   STATUS    PORTS     NAMES
```

### volume マウントにより、別のコンテナでも上述のテーブルが復元されることを確認したい

```bash
# コンテナを作成
$ docker container create -it --name mariadb2nd --mount type=volume,source=mariadb,target="/var/lib/mysql" mariadb
c96d8fee6ce449d3a90a6fb2267f8d2bd8daddbbe8b9dfcf12a5360c480c370f

# コンテナを起動して中に入る
$ docker container start mariadb2nd
mariadb2nd
$ docker container exec -it mariadb2nd bash
root@c96d8fee6ce4:/# 

# ログインして利用するデータベースを指定
root@c96d8fee6ce4:/# mysql -u root -p
Enter password: 
MariaDB [(none)]> use sample;
Database changed

# テーブルが復元されたことを確認
MariaDB [sample]> select * from sample;
+---------------+
| text          |
+---------------+
| Hello MariaDB |
+---------------+
1 row in set (0.001 sec)
```

#### 後始末

[参考](https://docs.docker.com/engine/reference/commandline/volume_rm/)

> 書式: `docker volume rm [OPTIONS] VOLUME [VOLUME...]`

```bash
$ docker container stop mariadb2nd
mariadb2nd
aoi@aoinoMacBook-puro: ~/Desktop/playground/Docker/playground/volume (main *%=)
$ docker container rm mariadb2nd
mariadb2nd

# ボリュームを削除
$ docker volume rm mariadb
mariadb
aoi@aoinoMacBook-puro: ~/Desktop/playground/Docker/playground/volume (main *%=)
$ docker volume ls
DRIVER    VOLUME NAME
```
