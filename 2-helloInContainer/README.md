
# 概要

Ubuntuのイメージからコンテナをつくり、コンテナに出たり入ったりしてみます。

## ゴール

コンテナに入るとはどういうことか・どうすればコンテナに出入りできるのか理解することを目指します。

※ また、標準入出力や端末の話が少し出てくるので、前提知識を固めておきたい方は、[参考書籍](https://www.sbcr.jp/product/4797386479/)を読んでみてください。

## 用語整理

## Ubuntuイメージの取得

まずは復習がてら、Ubuntuイメージを取りに行ってみましょう。
[DockerHub](https://hub.docker.com/_/ubuntu)でUbuntuのイメージを見つけたら、指定されたコマンドを実行してダウンロードします。

### タグ

以降の実践ではプログラムを動かすことが増えてくるので、イメージのバージョンが違うせいでなぜか動かない...といったことは避けたいです。
そこで、前回少し触れたタグをこれから取りに行くイメージ情報に加えたいと思います。
具体的には、`docker image pull ubuntu:タグ名`コマンドでタグ、すなわちイメージのバージョンを明示します。

検証時点では、下図の通り、20.04タグがlatestとなっています。何も指定しなければ20.04タグと対応するイメージがダウンロードされます。これだけでは、将来Ubuntuのバージョンが上がると、latestタグの指すものも変わってしまいます。
`ubuntu:20.04`のように、タグも加えたイメージ情報を記述することで、いつでも欲しいバージョンのイメージを手に入れることができます。

![image](https://user-images.githubusercontent.com/43694794/115165497-1714df00-a0e9-11eb-9f14-fb149428b416.png)

※ 厳密にはバージョン20.04のイメージも何度か差し替わっているので、本当に環境を一致させるには、ダイジェストを指定するべきです。今回はタグにフォーカスするため、タグに絞った説明をしています。

---

補足が長くなってしまいましたが、今度こそイメージを取得します。

```bash
$ docker image pull ubuntu:20.04
# 出力例
20.04: Pulling from library/ubuntu
a70d879fa598: Pull complete 
c4394a92d1f8: Pull complete 
10e6159c56c0: Pull complete 
Digest: sha256:3c9c713e0979e9bd6061ed52ac1e9e1f246c9495aa063619d9d695fb8039aa1f
Status: Downloaded newer image for ubuntu:20.04
docker.io/library/ubuntu:20.04

# イメージを取得できたか確認
$ docker image ls
REPOSITORY   TAG       IMAGE ID       CREATED       SIZE
ubuntu       20.04     26b77e58432b   2 weeks ago   72.9MB
```

タグを指定することで、バージョン20.04のUbuntuイメージを手に入れることができました。


## コンテナをつくって動かす

手に入れたイメージをもとにコンテナをつくってみましょう。
コンテナは、`docker container create`コマンドでつくることができます。

```bash
# Ubuntuの20.04タグが付与されたイメージをもとに、名前が「ubuntu_container」のコンテナを作成。
$ docker container create --name ubuntu_container ubuntu:20.04
# 出力例
5bc8d8515a10b6f4795acb52a60b8ffd5a5d14b72f8639fb5e3db462b938e4ac

# 作成できたか確認
$ docker container ls -a
CONTAINER ID   IMAGE          COMMAND       CREATED         STATUS    PORTS     NAMES
5bc8d8515a10   ubuntu:20.04   "/bin/bash"   5 seconds ago   Created             ubuntu_container
```

hello-worldコンテナのときから、いくつか変わったことがあります。
下記の書式と照らし合わせて見ていきましょう。

> 書式: `docker container create [OPTIONS] IMAGE [COMMAND] [ARG...]`

まずは、コンテナの名前を覚えやすいものにするため、`--name`オプションで明記しました。
そして、イメージ名の後ろに`:タグ名`の形式でタグを指定しています。タグを省略すると、latestタグが付与されたイメージを探しに行ってしまうので注意が必要です。

[参考](https://docs.docker.com/engine/reference/commandline/container_create/)

---

つくったコンテナを少しだけ動かしてみます。コンテナを起動するコマンドは、`docker container start <コンテナ名>`で記述します。
コンテナからの出力が見られるように、`-a`オプション(attach)もあわせて指定しておきます。

```bash
$ docker container start -a ubuntu_container
# 出力
ubuntu_container

# コンテナの状態を確認
$ docker container ls -a
# 状態が終了(Exited)となっている
CONTAINER ID   IMAGE          COMMAND       CREATED        STATUS                     PORTS     NAMES
5bc8d8515a10   ubuntu:20.04   "/bin/bash"   12 hours ago   Exited (0) 4 seconds ago             ubuntu_container
```

特に何も表示されないままコンテナが終了したようです。なぜこのような結果になったのか、原因を探ってみましょう。

### COMMANDフィールド

コンテナが起動したときに実行されるコマンドは、`docker container ls`で表示される一覧の「COMMANDフィールド」に書かれています。
Ubuntuイメージからつくられたコンテナで書かれているのは、おなじみのbashシェルを表す`/bin/bash`です。

### 対話的なシェル

先ほど実行されたbashシェルは対話(interactive)モードで起動しなかったことからルートプロセスであるbashが即座に終了し、コンテナもExitedとなりました。
対話的でない動きは、シェルスクリプトを起動したときの様子をイメージすると、見えてくるかもしれません。

コンテナが起動した状態を保ち、シェルを動かし続けるには、対話モードでシェルを起動する必要がありそうです。
対話モードであることは、シェル自身のプロセスの標準入出力の向き先が端末を指していることを表します。
[参考](https://www.gnu.org/software/bash/manual/html_node/What-is-an-Interactive-Shell_003f.html)


## コンテナの中に入る

コンテナでシェルを対話モードで起動し、ローカルから操作することは、しばしば「コンテナの中に入る」と表現されます。
コンテナの中に入ることができれば、より柔軟に、より楽しくコンテナを操作できるようになるので、中に入るための準備を整えていきましょう。

### tオプション

先ほども見た通り、対話モードで動くシェルは、標準入出力が端末と結びついています。となると、シェルと端末を結びつける設定を加えなければなりません。
Dockerは、そんなときのために便利なオプションとして、`-t`オプション(tty)を用意してくれています。

`-t`オプションは、公式では`Allocate a pseudo-TTY`と記述されており、擬似端末を割り当てることを意味します。擬似端末が具体的に何を指しているのかは、後ほどコンテナに入ったときに見ていきます。

#### 端末をつなげてみる

端末をつなげてもう一度コンテナを動かしてみましょう。このとき、一つ注意するべきことがあります。

コンテナの端末や入出力を制御するオプションは、コンテナをつくるとき(createコマンドを実行するとき)だけ設定できます。よって、端末をつなげてコンテナを動かすには一度コンテナを破棄し、つくり直す必要があります。
これを踏まえて、一連のコマンドを見てみます。

```bash
# 最初に、Exitedとなったコンテナを削除
$ docker container rm ubuntu_container
ubuntu_container
# 「-t」オプションで端末を割り当て、再度コンテナを作成
$ docker container create -t --name ubuntu_container ubuntu:20.04
# 出力例
f5d1c83e7d71bbc0bd5d3678cc970596aca5d5f841e98dcbf5a8815d3134b760

# attachオプションを加え、コンテナの標準出力をホストと接続してコンテナを起動
$ docker container start -a ubuntu_container
# 出力例 プロンプトが表示される
root@f5d1c83e7d71:/# 
# 標準入力が繋がっておらず、ローカルからは操作できないので、Ctrl+Cで停止
^C

# コンテナの状態を確認すると、状態が「Up(起動中)」となっている
$ docker container ls -a
CONTAINER ID   IMAGE          COMMAND       CREATED         STATUS         PORTS     NAMES
f5d1c83e7d71   ubuntu:20.04   "/bin/bash"   2 minutes ago   Up 2 minutes             ubuntu_container
```

重要なのは、コンテナ起動後にプロンプトが表示されたことです。
`-t`オプションにより、コンテナで動作しているbashプロセスへ端末が割り当てられ対話モードで起動したからこそ、プロンプトを見ることができるのです。

また、対話モードで起動したシェルは、`exit`コマンドで停止するまで動き続けるので、コンテナもあわせて起動したままになっています。

---

しかし、まだ問題は残されています。プロンプトが表示されたとき、何かを入力しても画面には何も表示されませんでした。
なぜこのような動きとなったか、再び読み解いていきましょう。

#### attachは何を接続しているのか

ポイントとなるのは、`docker container start`コマンドの`-a`オプション(attach)により接続されているものです。

> Attach STDOUT/STDERR and forward signals

と公式で書かれている通り、接続するのは、「標準出力・標準エラー出力」だけです。
どうやら、標準入力はattachオプションだけではコンテナとは結びつかないようです。

これを踏まえて先ほどの動きを整理すると、以下のような流れとなります。

* `-t`オプションでコンテナの標準入出力と端末が繋がったことにより、シェルが対話モードで起動
* 対話モードで起動したシェルは、標準出力へプロンプト(root@f5d1c83e7d71:/# のような文字列)を出力
* `-a`オプションでコンテナの標準出力がホストと接続されたので、ホスト側の端末にプロンプトが表示された
* ただし、ホストの端末の標準入力はコンテナと繋がっていないので、コンテナの中で動いているシェルは操作できない


### itオプションでコンテナの中へ

残りの問題は、ホストの端末の標準入力がコンテナに繋がっていないことです。
この問題も`-t`オプションと同じようにDockerがオプションで用意しており、`-i`オプション(interactive)で解決できます。

`-t`オプションと同じように、コンテナをつくり直してから試してみます。

```bash
# 既存コンテナを停止→削除
$ docker container stop ubuntu_container
ubuntu_container
$ docker container rm ubuntu_container
ubuntu_container

# iオプション(interactive)を追加し、ホストの標準入力もコンテナと接続
$ docker container create -it --name ubuntu_container ubuntu:20.04
62b39f09670076c0f97c70477a49dd510a1de44cd26a86553641386d1ee85eaf
# attachオプションでホストの標準出力を繋いでコンテナを起動
$ docker container start -a ubuntu_container

# プロンプトは表示されたが、tオプションのみの場合と同様、標準入力は送信されない
root@62b39f096700:/# echo hello
^C
```

ホストの標準入力がコンテナへ送信されることを期待していましたが、実際には異なる結果が得られました。
これは公式ドキュメントでもあまり解説されておらず、ハマったところなので、補足しておきます。
`docker container create`コマンドの`-i`オプションは、公式で

> Keep STDIN open even if not attached

と書かれています。
公式でも深くは言及されていませんでしたが、常に標準入力を繋げる(attach)というよりは、いつでも標準入力に繋げられる(open)ような挙動をとります。
※ 厳密には、`docker container inspect <コンテナ名>`で確認できる設定値の`Config.AttachStdin`がtrueとなります。

`docker container start`コマンドの`-a`オプションの説明とあわせて読むと、動きが見えやすくなると思います。公式では、

> Attach STDOUT/STDERR and forward signals

と書かれています。つまり、コンテナの標準入力は、`docker container create`コマンドで開かれている(open)だけで、
繋がって(attach)はいないようです。

---

やや直感には反しますが、実は`docker container start`コマンドにも`-i`オプションが存在し、
説明にも、`Attach container's STDIN`とあります。
ということで、一度コンテナを停止させ、起動コマンドに`-i`オプションを付与して再度動かしてみましょう。
今度こそ期待通りの結果が得られるはずです。

```bash
# 一度コンテナを停止
$ docker container stop ubuntu_container
ubuntu_container
# -aオプション(attach)で標準出力・標準エラー出力を・-iオプション(interactive)で標準入力を接続
$ docker container start -ai ubuntu_container

# コンテナと標準入力も繋がったことで、コンテナ上でシェルを操作できるようになった
root@62b39f096700:/# ls
bin  boot  dev  etc  home  lib  lib32  lib64  libx32  media  mnt  opt  proc  root  run  sbin  srv  sys  tmp  usr  var
```

ついにコンテナの中に入ることができました。
※ この辺りの話は公式ドキュメントにはほとんど書かれておらず、手探りで書いたものなので、間違いや、よい参考文献などございましたら、教えて頂けるとうれしいです。

---

少し長くなってしまったので、一連の流れを復習しておきます。各コマンドのオプションが何をしているのか、大まかにでも概要を掴めるようにしておきましょう。

```bash
# -i(interactive)オプションでコンテナの標準入力を開いておく(open)
# -t(tty)オプションでコンテナへ擬似端末を割り当て、シェルをインタラクティブモードで起動できるようにしておく
docker container create -it --name ubuntu_container ubuntu:20.04

# -a(attach)オプションでコンテナの標準出力・エラー出力をホストの端末と接続
# -i(interactive)オプションでホストの端末の標準入力とコンテナの標準入力を接続
docker container start -ai ubuntu_container 

# -tオプションによりシェルが対話モードで動作していることにより、プロセスが動き続けている
# -aオプションにより標準出力がホストの端末とコンテナ間でつながったことから、プロンプトがホストの端末で表示される
root@62b39f096700:/#

# create/startコマンドそれぞれのiオプションでホストの端末とコンテナの標準入力が接続された
# これによりホストの端末で入力したコマンドがコンテナへ送信され、コンテナで実行されたコマンドの結果がホストの端末の標準出力へ出力された
root@62b39f096700:/# ls
bin  boot  dev  etc  home  lib  lib32  lib64  libx32  media  mnt  opt  proc  root  run  sbin  srv  sys  tmp  usr  var
```

#### 補足 本当にコンテナの標準入出力と端末は繋がっているのか

`-t`オプションを指定することで端末が割り当てられる。公式に書かれているからそうなんだろう、という理解でも問題はないですが、一応確かめておきたいところです。

せっかくコンテナの中に入れたので、少し探ってみましょう。

特定のプロセスの標準入出力の向き先を調べるには、対象のプロセスのIDが必要です。bashシェルはルートプロセス(PID=1)として起動しているので、PIDは明白ですが、念のため確認しておきます。
そして、`/proc/[pid]/fd`ディレクトリ配下には、プロセスと繋がるファイルディスクリプタがシンボリックリンクとして存在しています。
中でも、「0・1・2」はそれぞれ、標準入力・標準出力・標準エラー出力と対応しています。

[参考](https://man7.org/linux/man-pages/man5/proc.5.html)

ということは、標準入出力と対応するファイルディスクリプタが指すシンボリックリンクの向き先が、端末を表すファイルであれば、本当に端末に繋がっていると言えそうです。確かめてみましょう。

```bash
# psコマンドでbashプロセスのPIDを確認
root@62b39f096700:/# ps
  PID TTY          TIME CMD
    1 pts/0    00:00:00 bash
   13 pts/0    00:00:00 ps

# /proc/[pid]/fdには、プロセスと繋がるファイルディスクリプタが存在
root@62b39f096700:/# ls -al /proc/1/fd 
total 0
dr-x------ 2 root root  0 May  3 12:25 .
dr-xr-xr-x 9 root root  0 May  3 12:25 ..
# 標準入出力は端末に向けられている
lrwx------ 1 root root 64 May  3 12:25 0 -> /dev/pts/0
lrwx------ 1 root root 64 May  3 12:25 1 -> /dev/pts/0
lrwx------ 1 root root 64 May  3 12:25 2 -> /dev/pts/0
lrwx------ 1 root root 64 May  3 12:33 255 -> /dev/pts/0
```

`/dev/pts/0`のptsは、`pseudoterminal slave`の略で、擬似端末を表しています。
ということで、公式ドキュメントにあった通り、擬似端末が割り当てられていたことが確認できました。

[参考](https://linux.die.net/man/4/pts)

#### 補足: docker container runコマンド

書籍などでは、`docker run`あるいは、`docker container run`コマンドをよく見かけるかと思います。

このコマンドは、イメージが無ければpullし、コンテナの作成・起動まで一気に進めてくれるので、実際に使うときは、非常に便利です。
今回の例も、`docker container run -it --name ubuntu_container ubuntu:20.04`と書くだけで、イメージの取得からコンテナの起動までが一度で完了します。

しかし、一度にたくさんの処理が裏で動いており、その仕組みを一度にまとめて理解するのは困難です。
便利なコマンドを使わない手はないですが、何を効率化しているのか・裏で何をしているのか理解せずに使うのと、理解して使うのとでは、雲泥の差があります。

最初はそうなんだ〜ぐらいの理解で流しておき、徐々に知識・経験を積み上げながら立ち向かっていくのがよいかと思います。

[参考](https://docs.docker.com/engine/reference/commandline/container_run/)

---

## コンテナの中に入る-実践

さて、

```bash
root@2d152f73460c:/# apt-get update
root@2d152f73460c:/# apt-get install cowsay

# 中略...
# インストールを続けるか訊かれたらyを入力
Do you want to continue? [Y/n] y

# コマンドを実行
root@2d152f73460c:/# /usr/games/cowsay 'Hello, Ubuntu container!!'
 ___________________________
< Hello, Ubuntu container!! >
 ---------------------------
        \   ^__^
         \  (oo)\_______
            (__)\       )\/\
                ||----w |
                ||     ||

```


### 後片付け

コンテナ・イメージは使い終わったらきれいにしておきましょう。
Hello Worldのときと同じ流れでコンテナ・イメージを削除していきます。

```bash
# exitコマンドで、コンテナ内で起動しているシェルのプロセスを終了
root@62b39f096700:/# exit
exit

# コンテナの状態が「Exited」となっていることを確認
$ docker container ls -a
CONTAINER ID   IMAGE          COMMAND       CREATED      STATUS                      PORTS     NAMES
62b39f096700   ubuntu:20.04   "/bin/bash"   2 days ago   Exited (0) 2 seconds ago              ubuntu_container

# コンテナを削除
$ docker container rm ubuntu_container
ubuntu_container

# コンテナが削除されたことを確認
$ docker container ls -a
CONTAINER ID   IMAGE     COMMAND   CREATED   STATUS    PORTS     NAMES

# イメージを削除
$ docker image rm ubuntu:20.04
Untagged: ubuntu:20.04
# 省略...

# イメージが削除されたことを確認
$ docker image ls
REPOSITORY   TAG       IMAGE ID   CREATED   SIZE
```


## まとめ

本章では、Dockerコンテナの中に入ることを目指し、必要なコマンド・オプションを掘り下げていきました。
コンテナの中に入ることは、コンテナ操作の基本として、何度も実践することになるので、実践/知識の確認を繰り返しながら、
理解を深めてみてください。