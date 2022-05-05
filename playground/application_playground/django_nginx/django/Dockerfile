# ベースとなるビルドステージ
FROM python:3.9-slim as base
ENV PYTHONDONTWRITEBYTECODE=1

# Pipfile.lockをもとに必要なパッケージをインストールするためのビルドステージ
FROM base as dependencies
ENV PIPENV_VENV_IN_PROJECT=1

RUN apt-get update && apt-get install -y python3-dev default-libmysqlclient-dev gcc
# パッケージをインストール
COPY "./Pipfile" .
COPY "./Pipfile.lock" .
RUN pip install pipenv
RUN pipenv install --deploy

# 実行に必要なファイルのみで構成されるビルドステージ
FROM base as runtime
# mysqlclientは実行にもパッケージが必要なので、インストール
RUN apt-get update && apt-get install -y default-libmysqlclient-dev && rm -rf /var/lib/apt/lists/*
# Djangoなどの必要なパッケージは/.venvに固まっているので、左記のディレクトリの中身のみコピーすればよい
COPY --from=dependencies "/.venv" "/.venv"
# 環境変数PATHの先頭に追加することにより、インストールしたパッケージを実行できるようになる
ENV PATH="/.venv/bin:$PATH"

# app配下のソースを操作するためのユーザ
RUN mkdir /var/log/app_log
RUN useradd --create-home app && chown app /var/log/app_log
WORKDIR /home/app
USER app

COPY "./source/" "."

# migrateコマンドは起動後に実行するのが良さそう
ENTRYPOINT ["gunicorn", "--bind", ":8000", "config.wsgi:application"]