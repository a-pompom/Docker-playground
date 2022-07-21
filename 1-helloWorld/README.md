# 概要

まずは基本の、hello-worldイメージによるHelloWorldから始めます。
イメージを取得してからコンテナを動かし、そして破棄するまでの一連の流れを見ていきます。あわせて、各コマンドの基本オプションも都度確認します。

## ゴール

イメージ・コンテナを操作する基本コマンドの使い方を学習します。
具体的には、以下のコマンドの基本操作が理解できることを目指します。

* docker image pull
* docker image ls
* docker image rm

* docker container ls
* docker container create
* docker container start
* docker container rm

## 目次

[toc]

## 用語整理

* ホスト: Dockerをインストールした自身のコンピュータ
* コンテナ: アプリケーション・ミドルウェアなどの実行環境 環境をコンテナごとに独立させることで、ほかに影響しないシンプルな環境を構築できる
* イメージ: コンテナの元になるもの
* レジストリ: Dockerイメージを保管しているサービス 基本的な使い方であれば、デフォルトのレジストリであるDockerHubからイメージを取得し、コンテナをつくっていく

## イメージの取得

いわゆるHelloWorldを通してDockerへ入門していきます。DockerのHelloWorldは、`hello-world`という名前のイメージからコンテナをつくることがゴールです。
まずはコンテナの元になるイメージを用意することから始めていきましょう。

Dockerイメージを取得するための`docker image pull`コマンドは、次の書式に従います。

[参考](https://docs.docker.com/engine/reference/commandline/image_pull/)

>記法: `docker image pull [OPTIONS] NAME[:TAG|@DIGEST]`

最小の書き方は、`docker image pull <イメージ名>`で、最新版(latest)のイメージを取得します。

オプションは、普段使いではあまり意識することはないかと思います。興味がありましたら、参考リンクを探ってみてください。

### タグ

タグは、Dockerイメージのエイリアスの役割を持ち、イメージ名の後ろに`:タグ名`で指定します。

例えばUbuntuではDockerHubにて、`20.04`や`18.04`のようなタグが登録されています。
これは、ローカル・検証環境・本番環境など、どのような環境でも同じように動作するようバージョンを固定させたいときに有用です。

[参考](https://hub.docker.com/_/ubuntu?tab=tags&page=1&ordering=last_updated)

### ダイジェスト

イメージ名の後ろには、`@DIGEST`でダイジェストを指定することもできます。
公式で`immutable identifier`と書かれている通り、タグよりも更に厳密に、特定のイメージに対するエイリアスとして振る舞います。
まずはタグの使い方に慣れることを目指したいので、ダイジェストに踏み込むのはこのぐらいにしておきます。

[参考](https://docs.docker.com/engine/reference/commandline/pull/#pull-an-image-by-digest-immutable-identifier)

### hello-worldイメージの取得

`docker image pull`コマンドの基本文法が見えてきたところで、hello-worldイメージを取りにいきます。

まずは、[https://hub.docker.com/_/hello-world](docker hello-world)で検索し、DockerHubのWebサイトにアクセスします。
ここで、イメージ名が`hello-world`であることが分かったので、`docker image pull`コマンドでイメージを取得します。取得する様子は、以下のようになります。

```bash
$ docker image pull hello-world
# 出力
# tagを指定しなかったので、latestタグのイメージがダウンロードされる
Using default tag: latest
latest: Pulling from library/hello-world
# ...
```

### イメージの一覧表示

出力を見る限りうまくいったように見えますが、本当にイメージが手に入ったのか、確認したいところです。
ローカルで作成、または取得したイメージは、`docker image ls`コマンドで見ることができます。`ls`は「LiSt」を表しています。

[参考](https://docs.docker.com/engine/reference/commandline/image_ls/)

> 記法: `docker image ls [OPTIONS] [REPOSITORY[:TAG]]`

細かなオプションはひとまず置いておいて、まずは取得結果を実際に見てみましょう。

```bash
$ docker image ls

REPOSITORY    TAG       IMAGE ID       CREATED       SIZE
hello-world   latest    d1165f221234   5 weeks ago   13.3kB
```

hello-worldイメージが一覧に表示されました。イメージ一覧は何度も見ることになるので、ここで表示項目をざっくりと押さえておきます。

* REPOSITORY: イメージ取得元
* TAG: イメージに付与されたタグ
* IMAGE ID: イメージの識別子
* CREATED: イメージが作成された日時と現在日時との差異 ※ここでの作成日時は、pullコマンドで取得した時点ではなく、取得元が作成された時点を指します
* SIZE: イメージの容量

[参考](https://docs.docker.com/engine/reference/commandline/images/)

イメージが取得できたので、イメージをもとにコンテナをつくってみます。

#### 補足: REPOSITORYとは

hello-worldイメージのREPOSITORYフィールドには、hello-worldと書かれていました。これだけでは、リポジトリなるものが何を指しているのか見えづらいので、少し補足しておきます。

リポジトリという単語は「貯蔵庫・何かを貯めておく場所」のような意味があります。
Dockerではイメージを貯めておくと色々良いことがあるので、リポジトリは、「イメージを保管する場所」の役割を持ちます。
つまり、REPOSITORYの記述は、より正確には、イメージを保管しているDockerHubのリポジトリ名を表しています。

[参考](https://docs.docker.com/docker-hub/repos/)

#### 補足: docker pull vs docker image pull

書籍などでは、`docker pull`コマンドが使われているのを見ることがあるかもしれません。`docker image pull`コマンドと違いはあるのでしょうか。

Dockerは増えてきたコマンドを管理しやすく・ヘルプテキストをより分かりやすく記述するために、操作対象を明記する`docker image ...`のような記法を追加しました。
互換性のために短い記法も残されていますが、公式に

> The old command syntax is still supported, but we encourage everybody to adopt the new syntax.

新しい記法(操作対象を明記するもの)を推奨する旨が記載されています。
これからDockerを学ぶのであれば、`docker image pull`のように書くのがよいでしょう。

※ 公式ドキュメントは旧コマンドに詳細な説明が書かれていることもあるので、読むときには注意が必要です。

[参考](https://www.docker.com/blog/whats-new-in-docker-1-13/)


## コンテナの作成

イメージをもとに、コンテナをつくっていきます。
Dockerコンテナは、`docker container create`コマンドから生成することができます。。

[参考](https://docs.docker.com/engine/reference/commandline/container_create/)

> 書式: `docker container create [OPTIONS] IMAGE [COMMAND] [ARG...]`

最小の表現は、`docker container create <イメージ名>`です。
まさにイメージからコンテナをつくることを表していますね。これからたくさん使うことになるので、必要なオプションはその都度見ていくことにします。

概要だけ拾っておくと、OPTIONSでは、作成するコンテナの振る舞いを・COMMAND/ARGではコンテナが実行するコマンドとその引数を指定します。

---

hello-worldでは、イメージ名だけを指定してコンテナをつくります。実行イメージは下の通りです。

```bash
$ docker container create hello-world
# 出力例 コンテナを一意に識別するための識別子が表示される
956c6c34cfe85c96bb2004a58bd28b54d517e4d2c59ec1dd72bf575326bca9b2
```

### コンテナの一覧表示

イメージと同じように、コンテナが本当にできあがったのか確認しておきましょう。
コンテナも`ls`コマンドから一覧で見ることができます。

[参考](https://docs.docker.com/engine/reference/commandline/container_ls/)

> 記法: `docker container ls [OPTIONS]`

デフォルトでは「動いているコンテナ」のみが表示対象となり、できたてのコンテナは表示されないので、`-a`オプション(All)を加えて実行してみます。

```bash
$ docker container ls -a
# 出力例
CONTAINER ID   IMAGE         COMMAND    CREATED         STATUS    PORTS     NAMES
956c6c34cfe8   hello-world   "/hello"   4 seconds ago   Created             angry_wiles
```

表示項目をさらっと見ておきます。

* CONTAINER ID: コンテナの識別子
* IMAGE: コンテナ生成元イメージ名
* COMMAND: コンテナが実行するコマンド
* CREATED: コンテナが作成された日時と現在日時の差異
* STATUS: コンテナの状態
* PORTS: ホストとコンテナのポートのマッピング情報
* NAMES: コンテナ名

NAMESは少し補足が必要です。`angry_wiles`という見慣れない文字列がコンテナ名に設定されています。
これは特別な意味を持つものではなく、Dockerがよしなに命名してくれたランダムなものです。

コンテナを管理するときには、覚えやすい名前があると便利なので、hello-world以降につくるコンテナは、明示的に名前をつけることにします。

[参考](https://docs.docker.com/engine/reference/commandline/container_create/)


## コンテナの起動

コンテナをつくることができたので、動かしてみましょう。
コンテナは、`docker container start`コマンドで起動することができます。

[参考](https://docs.docker.com/engine/reference/commandline/container_start/)

> 記法: `docker container start [OPTIONS] CONTAINER [CONTAINER...]`

最低限の書き方は、`docker container start <コンテナ名またはコンテナID>`です。
オプションなしでも実行できるので、早速動かしてみます。

```bash
# ここではコンテナIDを指定
$ docker container start 956c6c34cfe8
# 出力
956c6c34cfe8
```

イメージをつくったときと同じように、コンテナIDが表示されました。
ここで、`docker container ls`コマンドでもう一度ステータスを見てみると、`Exited`となっています。

※ なぜ既にExitedとなっているかは、後述します。

```bash
$ docker container ls -a
# 出力
CONTAINER ID   IMAGE         COMMAND    CREATED              STATUS                      PORTS     NAMES
956c6c34cfe8   hello-world   "/hello"   About a minute ago   Exited (0) 29 seconds ago             angry_wiles
```

どうやら、もう既にDockerへのあいさつは完了していたようです。一体何が起こっていたのでしょうか。

### attach/detach

Dockerコンテナの入出力について理解を深めるには、「attach/detach」を知ることが重要です。
単語自体は、「接続する/切り離す」といったことを意味しており、ホストの端末とDockerコンテナの標準入出力の繋がりを表しています。

`docker container start`コマンドは、デフォルトでは「detachedモード」でコンテナを起動します。detachedモードでは、ホストの端末とコンテナの標準入出力が繋がっていません。
よって、そのままでは何も表示されませんでした。(※)

※ `docker container logs`コマンドで出力を見ることもできますが、今回は割愛します。

---

ということは、attach、つまりホストの端末とコンテナの標準入出力を繋げてコンテナを動かせば、コンテナの出力が見えるようになるはずです。
`docker container start`コマンドは、`-a`オプションを付与することで、「attachedモード/foreground」でコンテナを起動させることができます。

もう一度動かしてみましょう。

```bash
# attachedモードでコンテナを起動
$ docker container start -a 956c6c34cfe8
# 出力

Hello from Docker!
This message shows that your installation appears to be working correctly.

To generate this message, Docker took the following steps:
 1. The Docker client contacted the Docker daemon.
 2. The Docker daemon pulled the "hello-world" image from the Docker Hub.
    (amd64)
 3. The Docker daemon created a new container from that image which runs the
    executable that produces the output you are currently reading.
 4. The Docker daemon streamed that output to the Docker client, which sent it
    to your terminal.

To try something more ambitious, you can run an Ubuntu container with:
 $ docker run -it ubuntu bash

Share images, automate workflows, and more with a free Docker ID:
 https://hub.docker.com/

For more examples and ideas, visit:
 https://docs.docker.com/get-started/
```

いい感じですね、上手くいきました。
これで今度こそ、Dockerコンテナへあいさつを済ませることができました。

#### 補足: 「/hello」とは

`docker container ls`コマンドでコンテナの情報を確認したとき、「COMMANDフィールド」に`/hello`と書かれていました。
実行することで上記の通り文字が表示されましたが、これだけでは、何が起こっているのかいまいち見えづらいですね。 

DockerHubに記載された[参考リンク](https://github.com/docker-library/hello-world/blob/master/hello.c)をたどってみると、実体が見えてくるかと思います。
どうやら`/hello`は、C言語で書かれた、文字列を出力する処理の実行バイナリだったようです。


## コンテナの停止

ここからは後片付けをしていきます。まずは動いているコンテナを止めてあげます。
コンテナは、`docker container stop`コマンドで止めることができます。

[参考](https://docs.docker.com/engine/reference/commandline/container_stop/)

> 記法: `docker container stop [OPTINS] CONTAINER [CONTAINER...]`

の書式で記述できます。`docker container start`コマンドと対になっていることが分かります。
さて、コマンドを実行する前にコンテナの状態を確認してみると、

```bash
$ docker container ls -a
# 出力
CONTAINER ID   IMAGE         COMMAND    CREATED          STATUS                      PORTS     NAMES
956c6c34cfe8   hello-world   "/hello"   21 minutes ago   Exited (0) 20 minutes ago             angry_wiles
```

既に「Exited(終了)」となっていました。コンテナを停止するコマンドを実行していないのに、いつ止まったのでしょうか。公式では、コンテナが止まる条件を次のように述べています。

[参考](https://docs.docker.com/engine/reference/run/#detached--d)

> By design, containers started in detached mode exit when the root process used to run the container exits

大まかには、「detachedモードでは、コンテナを動かしているルートプロセスが終了すると、コンテナも終了する」といったことが書かれています。
attachedモードであっても、「ルートプロセスが終了した時点」でコンテナは停止するようです。

ここでのルートプロセスは、前節で見たCOMMANDフィールドを表しており、hello-worldイメージでは、`/hello`コマンドと対応しています。`/hello`コマンドは、文字列を出力したら処理を完了させるため、端末に文字が表示された時点で、プロセスも終了していました。
また、`STATUS`のExited (0)は、プロセスの終了ステータスを表しています。

---

以上より、コンテナはルートプロセスが終わらない限りは動き続け、ルートプロセスの終了と共に止まることが分かりました。
もう停止していることは明らかですが、せっかくなので、`docker container stop`コマンドを実行しておきましょう。

```bash
$ docker container stop 956c6c34cfe8
# 出力 STATUSはExitedのまま
956c6c34cfe8
```

`docker container start`と同じ出力となりました。


## コンテナの削除

使い終わったコンテナは消しておかないと、後々管理が大変になってしまいます。
そうならないよう、`docker container rm`コマンドでコンテナを削除します。

[参考](https://docs.docker.com/engine/reference/commandline/container_rm/)

> 書式: `docker container rm [OPTIONS] CONTAINER [CONTAINER...]`

hello-worldコンテナ1つを削除したいので、`docker container rm <コンテナ名またはコンテナID>`を記述します。

```bash
$ docker container rm 956c6c34cfe8
# 出力
956c6c34cfe8
# コンテナ一覧の確認
$ docker container ls -a
# コンテナは削除された
CONTAINER ID   IMAGE     COMMAND   CREATED   STATUS    PORTS     NAMES
```

コンテナを削すことができました。最後にイメージも消してしまえば、一連の処理は完了します。


## イメージの削除

イメージが溜まるとディスクを圧迫するので、忘れずに消しておきましょう。コンテナ同様`rm`コマンドで削除できます。

[参考](https://docs.docker.com/engine/reference/commandline/image_rm/)

> `docker image rm [OPTIONS] IMAGE [IMAGE...]`

hello-worldイメージに別れのあいさつを済ませておきます。

```bash
$ docker image rm hello-world
# 出力例
Untagged: hello-world:latest
Untagged: hello-world@sha256:308866a43596e83578c7dfa15e27a73011bdd402185a84c5cd7f32a88b501a24
Deleted: sha256:d1165f2212346b2bab48cb01c1e39ee8ad1be46b87873d9ca7a4e434980a7726
# 一覧を確認すると、確かにイメージが削除された
$ docker image ls -a
REPOSITORY   TAG       IMAGE ID   CREATED   SIZE
```

これでイメージを取得し、イメージからコンテナをつくり、コンテナを動かし、破棄するまでの一連の流れをたどれました。


## まとめ

Dockerの基本として、イメージ・コンテナの基本操作を学びました。
より実用的なイメージ・コンテナを動かすときにも、これらは大事になってくるので、しっかりと理解しておきましょう。
