# UIを指定 デフォルトのnewtでインストール時にダイアログを開く挙動を抑止するために設定
ARG DEBIAN_FRONTEND=noninteractive
FROM ubuntu:20.04

# パッケージをインストール
RUN apt update && \
    apt install -y vim && \
    rm -rf /var/lib/apt/lists/*
