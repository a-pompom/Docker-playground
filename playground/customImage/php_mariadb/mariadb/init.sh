#!/bin/bash
# アプリケーションで操作するためのデータベース・ユーザを作成
mysql -uroot -p${MARIADB_ROOT_PASSWORD} -e "
CREATE DATABASE ${DATABASE_NAME};
CREATE USER '${DATABASE_USER}'@'%' IDENTIFIED BY '${DATABASE_PASSWORD}';
GRANT ALL ON ${DATABASE_NAME}.* TO '${DATABASE_USER}'@'%';
USE ${DATABASE_NAME};
CREATE TABLE sample(text varchar(255));
INSERT INTO sample(text) VALUES('Hello From MariaDB');"
