# 概要

Docker Composeで色々試したい。

## ゴール

Docker Composeの基本コマンド・`compose.yaml`の基本的な書き方を身につけたい。

## チートシート

* compose.yamlの基本形

```yaml
services:
  service_name:
    image: "イメージ名:tag"
    volumes:
      - type: "bindまたはvolume"
        source: "volume名またはマウント元パス"
        target: "マウント先パス"
    networks:
      - "所属するネットワーク名"
    environment:
      ENVIRONMENT_VARIABLE_NAME: "value"
    ports:
      - "host:container"
    depends_on:
      - "依存対象サービス名を記述"

# network top level element
networks:
  network_name:
    driver: bridge

# volume top level element
volumes:
  volume_name:
```

```bash
# サービスを作成したい
docker compose create [SERVICE...]

# サービスを起動したい
docker compose start [SERVICE...]

# サービスを一覧表示したい
docker compose ls

# サービスのコンテナでコマンドを実行したい
docker compose exec [options] [-e KEY=VAL...] [--] SERVICE COMMAND [ARGS...]

# サービスを停止したい
docker compose stop [SERVICE...]

# サービスを削除したい
docker compose rm [SERVICE...]

# サービスを作成・起動・attachしたい
docker compose up [SERVICE...]

# サービスを停止・破棄したい
docker compose down
```

[Compose概略](https://docs.docker.com/compose/)

[Compose Specification](https://docs.docker.com/compose/compose-file/)

## 単一のコンテナでvolumeを扱いたい

ここでは、`compose.yaml`の書き方・`docker compose`の基本コマンドを見る。
まずはコンテナの設定を記述した`compose.yaml`から。最初に全体像を見た上で、基本的な記法をドキュメントを参照しながら読み解いていく。

```yaml
# PHPを動かしたい
services:
  php:
    image: php:8.1-cli-buster
    # 複数のvolumeを定義できるよう配列記法となっている
    # よって、type要素とは無関係
    volumes:
      - type: bind
        source: "${PWD}/source"
        target: /app
    # stdin_open要素は標準入力を開くだけなので、PID=1のプロセスで必要にならない限り指定する必要はない
    # 今回の場合、stdin_openをtrueとしておくと、PID=1のプロセスからPHPのREPLを操作できるようになる
    tty: true
```

### version

Compose Specificationへ移行したことに伴い、`version`要素は不要になった。
[参考](https://docs.docker.com/compose/compose-file/#version-top-level-element)

### services

トップレベルに定義する要素で、値にはサービス名が指定される。サービス名をキーとするハッシュの値には、利用するイメージやボリュームなど、コンテナの動作に必要な情報を定義する。

[参考](https://docs.docker.com/compose/compose-file/#services-top-level-element)

```yaml
# 例: phpがサービス名となる
services:
  php:
```

#### serviceとは

Webサーバ・データベースサーバのような、アプリケーションにおいて独立して存在できるコンピューティングリソースを抽象化して表現したもの。
ここでのコンピューティングリソースは、アプリケーションの動作に必要なモジュールを表すイメージ。

### image

値にイメージを指定。記法は`addressable image format`と呼ばれる。

[参考](https://docs.docker.com/compose/compose-file/#image)

> 記法: `[<registry>/][<project>/]<image>[:<tag>|@<digest>]`

今回はイメージ名:タグのみ指定する。

```yaml
services:
  php:
    image: php:8.1-cli-buster
```

### volumes

コンテナが利用するvolumeを指定。bind mountはサービス内で・volume mountはトップレベルで記述するのが推奨されているようだ。

[参考](https://docs.docker.com/compose/compose-file/#volumes)

`--mount`フラグと同様、type, source, targetを指定することができる。
また、配列記法はtypeではなく、volumesキーの値に対して書かれていることに注意が必要。

[参考](https://docs.docker.com/compose/compose-file/#long-syntax-4)

```yaml
services:
  php:
    volumes:
      # 複数のvolumeを定義できるよう配列記法となっている
      # よって、type要素とは無関係
      - type: bind
        source: "${PWD}/source"
        target: /app
```

### tty

`docker container create`コマンドの`-t`オプションに相当。コンテナへ仮想端末を接続する。

[参考](https://docs.docker.com/compose/compose-file/#tty)

```yaml
services:
  php:
    # stdin_open要素は標準入力を開くだけなので、PID=1のプロセスで必要にならない限り指定する必要はない
    # 今回の場合、stdin_openをtrueとしておくと、PID=1のプロセスからPHPのREPLを操作できるようになる
    tty: true
```

---

`compose.yaml`が記述できたので、次は`docker compose`コマンドでサービスを操作してみる。

[参考](https://docs.docker.com/engine/reference/commandline/compose/)

`docker compose`コマンドの基本的な書式は以下の通り。

> 書式: `docker compose [-f <arg>...] [options] [COMMAND] [ARGS...]`

また、以降のコマンドにて、`SERVICE`を指定しない場合は、`compose.yaml`をもとに動作するようだ。

### build

Dockerfileからimageをビルド。`compose.yaml`にてimage要素で直接imageを指定していた場合も何もしない。

[参考](https://docs.docker.com/engine/reference/commandline/compose_build/)

> 書式: `docker compose build [SERVICE...]`

### create

service要素をもとにコンテナを作成。

[参考](https://docs.docker.com/engine/reference/commandline/compose_create/)

> 書式: `docker compose create [SERVICE...]`

```bash
$ docker compose create

[+] Running 1/1
 ⠿ Container single_php_1  Created 
```

ここで、コンテナ名はデフォルトで`compose.yamlのあるディレクトリ_サービス名_サービス内のコンテナ番号`の規則に従う。

### ls

Compose Projectと呼ばれるものを一覧表示。これは、`compose.yaml`が配置されたディレクトリを単位としているようだ。
より具体的には、`docker compose`コマンドの操作単位と捉えることができる。

[参考](https://docs.docker.com/engine/reference/commandline/compose_ls/)

> 記法: `docker compose ls`

※ 記法には書かれていないが、オプションを指定することも可能。

```bash
$ docker compose ls -a
NAME                STATUS
single              created(1)
```

### start

サービスを起動。より具体的には、サービスに属するコンテナを起動。

[参考](https://docs.docker.com/engine/reference/commandline/compose_start/)

> 記法: `docker compose start [SERVICE...]`

```bash
$ docker compose start 
[+] Running 1/1
 ⠿ Container single_php_1  Started
 
# Compose Projectの状態もrunningへ移行した
$ docker compose ls
NAME                STATUS
single              running(1)
```

### exec

コンテナに対してコマンドを実行。

[参考](https://docs.docker.com/engine/reference/commandline/compose_exec/)

> 記法: `docker compose exec [options] [-e KEY=VAL...] [--] SERVICE COMMAND [ARGS...]`

基本の記法は、`docker compose exec SERVICE COMMAND`。
また、サービスに複数のコンテナがある場合は、`--index`オプションを付与する。

```bash
$ docker compose exec php bash
root@310085d56253:/# 
```

ここで、ドキュメントにて、`docker compose exec`コマンドについて以下のように補足されている。

> Commands are by default allocating a TTY, so you can use a command such as docker compose exec web sh to get an interactive prompt.

要約すると、`docker compose exec`は`docker container exec -it`と等価であるといったことが書かれている。

#### PHPを実行

`docker compose exec`コマンドを利用し、コンテナの中でPHPファイルを動かしてみる。

```bash
$ docker compose exec php bash

root@310085d56253:/# cd /app
root@310085d56253:/app# ls
hello.php

root@310085d56253:/app# php ./hello.php 
Hello From Compose

root@310085d56253:/app# exit
exit
```

### stop

サービスを停止する。

[参考](https://docs.docker.com/engine/reference/commandline/compose_stop/)

> 記法: `docker compose stop [SERVICE...]`

```bash
$ docker compose stop
[+] Running 1/1
 ⠿ Container single_php_1  Stopped 
 
# Compose Projectが停止していることを確認
$ docker compose ls -a
NAME                STATUS
single              exited(1)
```

### rm

停止中のサービスを削除

[参考](https://docs.docker.com/engine/reference/commandline/compose_rm/)

> 記法: `docker compose rm [SERVICE...]`

```bash
$ docker compose rm
? Going to remove single_php_1 Yes
[+] Running 1/0
 ⠿ Container single_php_1  Removed 
 
# 一覧へ表示されなくなったことを確認
$ docker compose ls -a
NAME                STATUS
```

### up

`build・create・start・attach`を一括実行するコマンド。

[参考](https://docs.docker.com/engine/reference/commandline/compose_up/)

> 記法: `docker compose up [SERVICE...]`

※ 記法にはオプションの記述が無いが、オプションも指定できる。

また、デフォルトではattach状態でコンテナを起動するので、通常は`-d`オプションを付与する。
そして、注意点として、`docker compose up`コマンドは`compose.yaml`ファイルに変更があり、かつサービスが既に存在する場合、
サービスをつくり直すよう動作する。よって、`compose.yaml`を書き換えるときは注意が必要。

```bash
$ docker compose up -d
[+] Running 1/1
 ⠿ Container single_php_1  Started 

$ docker compose ls
NAME                STATUS
single              running(1)
```

### down

サービス・ネットワークを停止し、削除する。

[参考](https://docs.docker.com/engine/reference/commandline/compose_down/)

> 記法: `docker compose down`

```bash
$ docker compose down
[+] Running 2/2
 ⠿ Container single_php_1  Removed                                                                                                                                    0.1s
 ⠿ Network single_default  Removed                                                                                                                                    2.6s

# Compose Projectが削除されたことを確認
$ docker compose ls -a
NAME                STATUS
```

---

## 単一のコンテナでネットワークを扱いたい

ここではPHP + Apacheのコンテナを起動し、ポートを公開することでホストから通信できるようにしたい。

```yaml
# PHP + Apacheを動かしたい
services:
  php:
    image: php:8.1-apache-buster
    volumes:
      - type: bind
        source: "${PWD}/source"
        target: "/var/www/html"
    ports:
      - "8080:80"
```

### ports

コンテナのポートを公開。

[参考](https://docs.docker.com/compose/compose-file/#ports)

> 記法: `[HOST:]CONTAINER[/PROTOCOL]`

HOSTはIPアドレス(省略可能) + ポート番号を記述。CONTAINERにはコンテナのポート番号を記述。
PROTOCOLにはtcp/udpを記述。

#### 実際に通信してみる

```bash
$ docker compose create
[+] Running 1/0
 ⠿ Container single_network_php_1  Created                                                                                                                            0.0s
$ docker compose start
[+] Running 1/1
 ⠿ Container single_network_php_1  Started                                                                                                                            2.9s
$ docker compose exec php bash
# コンテナの/var/www/htmlへマウントされたことを確認
root@9994419143f5:/var/www/html# cat hello.php 
<?php
namespace source;

echo 'Hello From Compose'.PHP_EOL;
root@9994419143f5:/var/www/html# exit
exit

# ホストの8080ポートへHTTPで通信すると、レスポンスとしてhello.phpの出力が得られたことを確認
$ curl http://localhost:8080/hello.php
Hello From Compose
```

## 複数のコンテナを一度に扱いたい

PHP + MariaDBの通信をDocker Composeで表現してみる。
まずはcompose.yamlを見てみる。

```yaml
# PHP-MariaDBコンテナを扱いたい
services:
  mariadb:
    image: mariadb:latest
    # volume mountでvolumeを設定
    volumes:
      - type: volume
        source: mariadb
        target: /var/lib/mysql
    networks:
      - php-mariadb
    environment:
      MARIADB_ROOT_PASSWORD: "mariadb"

  php:
    image: php:8.1-apache-buster
    volumes:
      - type: bind
        source: "${PWD}/source"
        target: "/var/www/html"
    # コンテナ間通信ができるようにbridge ネットワークへ接続
    networks:
      - php-mariadb
    ports:
      - "8080:80"
      # データベースが起動してから接続可能とする
    depends_on:
      - mariadb


networks:
  php-mariadb:
    driver: bridge

volumes:
  mariadb:
```

以降では追加された要素の概要を見ていく。

### networks

サービスに属するコンテナが接続するネットワークを定義。属するネットワーク名を配列記法で記述する。

[参考](https://docs.docker.com/compose/compose-file/#networks)

```yaml
services:
  mariadb:
    networks:
      - php-mariadb
```

### networks(top level element)

実際につくられるネットワーク情報を定義。services属性のように値には複数のネットワーク名が記述される。
また、driver属性の値bridgeは実装が必須ではないようなので、注意が必要。

[参考](https://docs.docker.com/compose/compose-file/#networks-top-level-element)

```yaml
networks:
  php-mariadb:
    driver: bridge
```

### environment

コンテナの環境変数を定義。
yamlパーサに変換されないよう、環境変数の値はクォートで括るのが推奨されている。

[参考](https://docs.docker.com/compose/compose-file/#environment)

```yaml
services:
  mariadb:
    environment:
      MARIADB_ROOT_PASSWORD: "mariadb"
```

### volumes(top level element)

volume情報を定義。`docker volume`コマンドで操作されるvolumeが対象。
networksと同様、値にはvolume名を記述する。

[参考](https://docs.docker.com/compose/compose-file/#volumes-top-level-element)

```yaml
volumes:
  mariadb:
```

### depends_on

サービス間の起動・終了における依存関係を定義。
依存対象は配列記法で記述。短縮記法では、対象のサービスは、依存対象より後に起動し、前に終了する。

[参考](https://docs.docker.com/compose/compose-file/#depends_on)

```yaml
services:
  php:
    depends_on:
      - mariadb
```

#### サービスを起動

```bash
$ docker compose create
[+] Running 4/2
 ⠿ Network multiple_php-mariadb  Created                                                                                                                              3.8s
 ⠿ Network multiple_default      Created                                                                                                                              3.7s
 ⠿ Container multiple_mariadb_1  Created                                                                                                                              0.0s
 ⠿ Container multiple_php_1      Created
 
 $ docker compose start
[+] Running 2/2
 ⠿ Container multiple_mariadb_1  Started                                                                                                                              2.4s
 ⠿ Container multiple_php_1      Started
 
 $ docker compose ls
NAME                STATUS
multiple            running(2)
```

#### volume, network

volume, networkがどのような状態か確認しておく。

```bash
$ docker volume ls
DRIVER    VOLUME NAME
local     multiple_mariadb

$ docker network ls
NETWORK ID     NAME                   DRIVER    SCOPE
913c1f0d76c5   multiple_default       bridge    local
684f238b74c2   multiple_php-mariadb   bridge    local
```

#### DBのテーブル作成

MariaDBへテーブルをつくっておく。

```bash
$ docker compose exec mariadb bash
root@e430304bf72d:/# mysql -u root -p
Enter password: 
Welcome to the MariaDB monitor.  Commands end with ; or \g.
Your MariaDB connection id is 3
Server version: 10.7.3-MariaDB-1:10.7.3+maria~focal mariadb.org binary distribution

Copyright (c) 2000, 2018, Oracle, MariaDB Corporation Ab and others.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

MariaDB [(none)]> create database sample;
Query OK, 1 row affected (0.000 sec)

MariaDB [(none)]> use sample;
Database changed
MariaDB [sample]> create table sample(text varchar(255));
Query OK, 0 rows affected (0.008 sec)

MariaDB [sample]> insert into sample(text) values('Hello from Compose DB');
Query OK, 1 row affected (0.002 sec)

MariaDB [sample]> exit
Bye
root@e430304bf72d:/# exit
exit
```

#### PHP

PDOのMySQLドライバをインストールし、接続してみる。

```bash
$ docker compose exec php bash 
root@c8b206a52662:/var/www/html# docker-php-ext-install pdo_mysql

$ curl localhost:8080/pdo_web.php
Array
(
    [text] => Hello from Compose DB
)
```

データベースのテーブルを取得できたことを確認。

---

以上のように、Docker Composeコマンドを利用すると、複数のコンテナをシンプルに扱えるようになる。