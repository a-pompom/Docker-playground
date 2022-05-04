# ベースとなるビルドステージ
FROM python:3.9-slim as base
ENV PYTHONDONTWRITEBYTECODE=1

# Pipfile.lockをもとに必要なパッケージをインストールするためのビルドステージ
FROM base as dependencies
ENV PIPENV_VENV_IN_PROJECT=1

# パッケージをインストール
COPY "./env/Pipfile" .
COPY "./env/Pipfile.lock" .
RUN pip install pipenv
RUN pipenv install --deploy

# 実行に必要なファイルのみで構成されるビルドステージ
FROM base as runtime
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

ENTRYPOINT ["python", "manage.py", "runserver", "0.0.0.0:8000"]