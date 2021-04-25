<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

# Dockerを用いた環境構築方法

## プロジェクトのローカルへのコピー
```
$ git clone https://github.com/program-kitchen/crm-backend
```


## dockerでの利用方法(手順)

すでにLaravelプロジェクトで必要なファイル等は構築しておりますので、ご自身のDockerでビルド後、コンテナを生成し、アプリケーションコンテナ(PHP)にログインしてください。

### イメージのビルドと各種dockerコンテナ起動
```
$ docker-compose build
```
以下のようなターミナルで出力されれば成功
```
Successfully built 27d58ffbe8e1
Successfully tagged my-application_app:latest
```

コンテナを起動する
```
$ docker-compose up -d
```


### Laravelプロジェクトが入っているphpコンテナへログイン
```
$ docker-compose exec php bash

```

※下記よりコンテナ内での作業となります。

### composerインストール

git cloneしただけではcomposerが入っていないため、下記コマンドにてインストール

```
/var/www/html# composer install
```

※composer updateは最新に更新してしまうため、やらないこと！

(composer installはcomposer.lockを見てインストールする)

### .envとキーを作成

同じくgit cloneしただけでは不足しているので、.envを.env.exampleをコピーして作成してください。

```
// .envファイルを作成
/var/www/html# cp .env.example .env
// アプリケーションキー作成
/var/www/html# php artisan key:generate
```

### Laravelバージョン確認

Laravelの8であること確認してください。

```
/var/www/html# php artisan --version
Laravel Framework 8.36.2
```

## MySQLバージョン8での注意

MySQL8から認証が変更になっており、既存ではLaravelで使用できません。

プラグインを変更してください。


### LaravelとMySQLの接続
Laravelでの開発が行えるようにMySQLとの接続を設定します。

エディターで.envを開きます。

まずは`docker-compose.yml`を確認する。
```
  mysql:
    image: mysql:8.0
    container_name: mysql
    ports:
      - '4306:3306'
    environment:
      MYSQL_DATABASE: coachtech-crm
      MYSQL_USER: coachtech
      MYSQL_PASSWORD: coachtech
      MYSQL_ROOT_PASSWORD: root
      TZ: 'Asia/Tokyo'
    volumes:
      - db-volume:/var/lib/mysql
```

Laravelの`.env`を下記で入力

```
DB_CONNECTION=mysql
DB_HOST=mysql（←コンテナ名）
DB_PORT=3306（←コンテナ側のポート番号）
DB_DATABASE=coachtech-crm
DB_USERNAME=coachtech
DB_PASSWORD=coachtech
```
<br>

### MySQLの認証方法変更

MySQL8.0で使う場合は、MySQL 8〜ではデフォルトの認証方法が変更になっているようです。
ここからはターミナルの別タブを開いて、MySQLコンテナに入ってください。

まずは、MySQLコンテナに入り、MySQLにログイン
```
// コンテナに入る
$ docker-compose exec mysql bash
// mysqlログイン(root権限で)
/# mysql -u root -p
```

データベースの切り替え、ユーザーごとの認証方式を確認する。
```
mysql> show databases;
⇨おそらくmysqlというデータベースがあるはずです。
mysql> use mysql;
mysql> select user, host, plugin from user;
+------------------+-----------+-----------------------+
| user             | host      | plugin                |
+------------------+-----------+-----------------------+
| admin            | %         | caching_sha2_password |
| root             | %         | caching_sha2_password |
| mysql.infoschema | localhost | caching_sha2_password |
| mysql.session    | localhost | caching_sha2_password |
| mysql.sys        | localhost | caching_sha2_password |
| root             | localhost | caching_sha2_password |
+------------------+-----------+-----------------------+
```

この場合、coachtechとrootで認証がうまくいっていない.

coachtechとrootの認証方法を変更します。
```
mysql> alter user 'root'@'%' identified with mysql_native_password by 'root';

mysql> alter user 'admin'@'%' identified with mysql_native_password by 'CrmTest2021';
```

プラグインが「mysql_native_password」になれば成功
```
mysql> select user, host, plugin from user;
+------------------+-----------+-----------------------+
| user             | host      | plugin                |
+------------------+-----------+-----------------------+
| admin            | %         | mysql_native_password |
| root             | %         | mysql_native_password |
| mysql.infoschema | localhost | caching_sha2_password |
| mysql.session    | localhost | caching_sha2_password |
| mysql.sys        | localhost | caching_sha2_password |
| root             | localhost | caching_sha2_password |
+------------------+-----------+-----------------------+
6 rows in set (0.00 sec)
```



## 接続確認
***
phpコンテナのLaravelプロジェクトがnginxサーバーで動いているかを確認するため、ブラウザで下記にアクセス

```
http://localhost:8000/
```


phpコンテナにてマイグレーションが問題ないかも確認する。

```
php artisan migrate
```
<br>

## コンテナ停止
***

```
docker-compose down
```

補足：コンテナ停止をすることでコンテナが破棄される。
その場合、MySQLのコンテナが破棄されてしまうと、次回起動時にコンテナ内のデータが保持できない。

そのため、`docker-compose.yml`にvolumeを指定している。
volumeを指定していることで、コンテナ破棄されてもデータを保持できる。

```
volumes:
  db-volume:

  (途中略)


  mysql:
    image: mysql:8.0
    container_name: mysql
    ports:
      - '4306:3306'
    environment:
      MYSQL_DATABASE: coachtech-crm
      MYSQL_USER: coachtech
      MYSQL_PASSWORD: coachtech
      MYSQL_ROOT_PASSWORD: root
      TZ: 'Asia/Tokyo'
    volumes:
      - db-volume:/var/lib/mysql  ⇦ここ(db-volume)
```