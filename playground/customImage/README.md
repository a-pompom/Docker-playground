# 概要

いくつかのパターンでDockerfileを書いてみる。

## ゴール

Dockerfileの基本的な命令の書き方を理解することを目指す。

## 用語整理

* context: Dockerfileからイメージをビルドするときに参照される環境 コンテキストはDockerfileのADD/COPY命令などで参照することができる

## チートシート

[参考](https://docs.docker.com/engine/reference/builder/)

## vimの入ったUbuntuイメージをつくりたい

最初にDockerfileの記法を見ていく。

```Dockerfile
# UIを指定 デフォルトのnewtでインストール時にダイアログを開く挙動を抑止するために設定
ARG DEBIAN_FRONTEND=noninteractive
FROM ubuntu:20.04

# パッケージをインストール
RUN apt update && \
    apt install -y vim && \
    rm -rf /var/lib/apt/lists/*
```

### フォーマット

Dockerfileの記法の基本は、`INSTRUCTION arguments`である。
命令に対して引数を指定することで、イメージをつくるときの挙動を定義している。また、コメントは`#`で記述。

```Dockerfile
# Comment
INSTRUCTION arguments
```

[参考](https://docs.docker.com/engine/reference/builder/#format)

### FROM

各命令のベースイメージを定義。

[参考](https://docs.docker.com/engine/reference/builder/#from)

> 記法: `FROM [--platform=<platform>] <image>[:<tag>] [AS <name>]`

### RUN

指定されたコマンドを実行。

[参考](https://docs.docker.com/engine/reference/builder/#run)

> 記法(shell form): `RUN <command>`
> 記法(exec form): `RUN ["executable", "param1", "param2"]`

コマンドはシェルを介して実行する方法・execコマンドを介して実行する方法がある。
シェルを介する場合はシェル変数の展開などに注意が必要。


### イメージをビルド

指定されたコンテキストをもとにDockerfileからイメージを構築。
Dockerfileはデフォルトでは、`PATH/Dockerfile`が参照される。

[参考](https://docs.docker.com/engine/reference/commandline/image_build/)

> 記法: `docker image build [OPTIONS] PATH | URL | -`

多くの場合、PATH引数へ`.`を指定し、カレントディレクトリ配下をコンテキストとする。
コンテキストとなったディレクトリは、DockerfileのADDやCOPY命令などで参照することができる。

また、`-t`オプションはイメージ名・タグ名を記述する。

```bash
$ docker image build -t vim_ubuntu:latest .
[+] Building 17.0s (6/6) FINISHED                                                                                                                                          

 => [internal] load build definition from Dockerfile                                                                                                                  0.0s
 => => transferring dockerfile: 175B                                                                                                                                  0.0s
 => [internal] load .dockerignore                                                                                                                                     0.0s
 => => transferring context: 2B                                                                                                                                       0.0s
 => [internal] load metadata for docker.io/library/ubuntu:20.04                                                                                                       0.0s
 => [1/2] FROM docker.io/library/ubuntu:20.04                                                                                                                         0.0s
 => [2/2] RUN apt update &&     apt install -y vim &&     rm -rf /var/lib/apt/lists/*                                                                                16.2s
 => exporting to image                                                                                                                                                0.7s 
 => => exporting layers                                                                                                                                               0.7s 
 => => writing image sha256:a015682600c3f84bdfcf1885e7145d80e592ad82edc1f24f89cd567f77e8a6d6                                                                          0.0s 
 => => naming to docker.io/library/vim_ubuntu:latest
```

#### コンテナを起動してvimを動かす

ビルドしたイメージからつくられたコンテナでは、vimを起動することができるか確かめてみる。

```bash
$ docker container create -it --name vim vim_ubuntu 
5c61bf5b7495a47fdde53e2c6c5da29ecb62b521255a6c56416dbc91bcd089de

$ docker container start vim
vim

$ docker container exec -it vim bash
root@5c61bf5b7495:/# vim
root@5c61bf5b7495:/# 
```

## 設定値を変更したPHP + Apacheイメージをつくりたい

## MariaDBの環境変数を設定したイメージをつくりたい