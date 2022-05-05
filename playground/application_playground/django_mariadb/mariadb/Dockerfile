FROM mariadb:latest
ENV TZ=Asia/Tokyo

# 設定ファイル
COPY "./conf/my.cnf" "/etc/mysql/conf.d/custom_my.cnf"
# 初期化スクリプト
COPY "./init.sh" "/docker-entrypoint-initdb.d/init.sh"

# ユーザ設定
RUN adduser mariadb && chown -R mariadb /var/lib/mysql
USER mariadb

VOLUME /var/lib/mysql
EXPOSE 3306
