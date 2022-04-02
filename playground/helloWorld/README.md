# 概要

Dockerの入門としてHello Worldの一連の流れを試してみる。

## ゴール

`hello-world`イメージを題材に、イメージをもとにコンテナを作成→起動→削除するまでの流れを理解することを目指す。

## 用語整理

* イメージ: コンテナの元となるもの
* コンテナ: イメージからつくりだされるもの 1つのプロセスで1つの環境を表現する

## チートシート

```bash
# イメージを手に入れたい
docker image pull [OPTIONS] NAME[:TAG|@DIGEST]
# 例:
$ docker image pull hello-world

# イメージを一覧表示したい
docker image ls [OPTIONS] [REPOSITORY[:TAG]]

# イメージを削除したい
docker image rm [OPTIONS] IMAGE [IMAGE...]

# コンテナをつくりたい
docker container create [OPTIONS] IMAGE [COMMAND] [ARG...]
# 例:
$ docker container create -it --name hello-world-container hello-world

# コンテナを起動したい
docker container start [OPTIONS] CONTAINER [CONTAINER...]

# コンテナを削除したい
docker container rm [OPTIONS] CONTAINER [CONTAINER...]

```

---

### イメージを手に入れたい

[参考](https://docs.docker.com/engine/reference/commandline/image_pull/)

> 書式: `docker image pull [OPTIONS] NAME[:TAG|@DIGEST]`

```bash
$ docker image pull hello-world
```

NAME引数へhello-worldを指定することで、hello-worldイメージをレジストリから取得。

### イメージを一覧表示したい

[参考](https://docs.docker.com/engine/reference/commandline/image_ls/)

> 書式: `docker image ls [OPTIONS] [REPOSITORY[:TAG]]`

```bash
$ docker image ls

REPOSITORY     TAG       IMAGE ID       CREATED        SIZE
hello-world    latest    feb5d9fea6a5   6 months ago   13.3kB
```

ダウンロードしたイメージが表示された。

### イメージからコンテナをつくりたい

[参考](https://docs.docker.com/engine/reference/commandline/container_create/)
[COMMAND参考](https://docs.docker.com/engine/reference/run/#cmd-default-command-or-options)

> 書式: `docker container create [OPTIONS] IMAGE [COMMAND] [ARG...]`

```bash
$ docker container create -it --name hello-world-container hello-world

a8a80b9f1dcde9cd59e2a5d60260121c77a2911269a233adc7b2cf329d064962
```

コンテナがつくられた。
例では、`-it --name hello-world`が`[OPTIONS]`・`hello-world`が`IMAGE`と対応。
`[COMMAND]`引数はイメージで明示されることから、省略されることが多い。

### コンテナを起動したい

[参考](https://docs.docker.com/engine/reference/commandline/container_start/)

> 書式: `docker container start [OPTIONS] CONTAINER [CONTAINER...]`

```bash
$ docker container start hello-world-container

hello-world-container
```

`docker container create`コマンドで指定したコンテナ名を`CONTAINER`引数とし、コンテナを起動。


### コンテナを一覧表示したい

[参考](https://docs.docker.com/engine/reference/commandline/container_ls/)

> 書式: `docker container ls [OPTIONS]`

```bash
$ docker container ls -a

CONTAINER ID   IMAGE         COMMAND    CREATED         STATUS                      PORTS     NAMES
a8a80b9f1dcd   hello-world   "/hello"   6 minutes ago   Exited (0) 53 seconds ago             hello-world-container
```

`-a`オプションは停止中のコンテナも含めて表示。
コンテナ内部に存在する`/hello`コマンドが実行され、終了したことからコンテナは停止している。

### 不要になったコンテナを削除したい

[参考](https://docs.docker.com/engine/reference/commandline/container_rm/)

> 書式: `docker container rm [OPTIONS] CONTAINER [CONTAINER...]`

```bash
$ docker container rm hello-world-container
hello-world-container

# コンテナ一覧からも消えたことを確認
$ docker container ls -a
CONTAINER ID   IMAGE     COMMAND   CREATED   STATUS    PORTS     NAMES
```

#### 補足: コンテナが停止したら削除したい

`docker container create`コマンドへ`--rm`オプションを付与することで、コンテナを使い捨てとすることができる。
より具体的には、コンテナが割り当てられたコマンドを実行し、停止した後に削除されるようになる。

```bash
# 実践例
$ docker container create -it --name hello-world-rm --rm hello-world
f97a94c83a8d06b64decd5f0328b693ae70f3447a0e18b803e3cc3a95599682f

$ docker container ls -a
CONTAINER ID   IMAGE         COMMAND    CREATED         STATUS    PORTS     NAMES
f97a94c83a8d   hello-world   "/hello"   3 seconds ago   Created             hello-world-rm

$ docker container start hello-world-rm
hello-world-rm

# コンテナが停止したあと、自動的に削除されたことを確認
$ docker container ls -a
CONTAINER ID   IMAGE     COMMAND   CREATED   STATUS    PORTS     NAMES
```

### イメージを削除したい

[参考](https://docs.docker.com/engine/reference/commandline/image_rm/)

> 書式: `docker image rm [OPTIONS] IMAGE [IMAGE...]`

```bash
$ docker image rm hello-world
```

イメージ名やイメージIDを指定することで、削除される。