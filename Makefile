.PHONY: init fresh restart up down cache stop build dev test logs shell mysql phpmyadmin

# 開発環境の初期化
init:
	@echo "=== 開発用Dockerコンテナ起動 ==="
	docker-compose up -d --build

	@echo "=== MySQLの起動待ち ==="
	@until docker-compose exec mysql mysqladmin ping -hmysql -uroot -proot --silent; do \
		echo "Waiting for MySQL..."; \
		sleep 2; \
	done
	@sleep 3

	@echo "=== PHP依存パッケージインストール ==="
	docker-compose exec php composer install

	@echo "=== Node.js依存パッケージインストール ==="
	cd src && npm install

	@echo "=== .envファイル作成 ==="
	@if [ ! -f src/.env ]; then cp src/.env.example src/.env; fi

	@echo "=== .envファイル自動修正 ==="
	sed -i 's/^DB_DATABASE=.*/DB_DATABASE=laravel_db/' src/.env
	sed -i 's/^DB_USERNAME=.*/DB_USERNAME=laravel_user/' src/.env
	sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=laravel_pass/' src/.env
	sed -i 's/^DB_HOST=.*/DB_HOST=mysql/' src/.env

	@echo "=== アプリケーションキー生成 ==="
	docker-compose exec php php artisan key:generate

	@echo "=== 権限設定 ==="
	docker-compose exec php chmod -R 777 storage bootstrap/cache

	@echo "=== マイグレーション実行 ==="
	docker-compose exec php php artisan migrate

	@echo "=== フロントエンドビルド ==="
	cd src && npm run build

	@echo "=== 開発環境初期化完了 ==="

# データベースをリフレッシュ
fresh:
	docker-compose exec php php artisan migrate:fresh --seed

# コンテナを再起動
restart:
	@make down
	@make up

# コンテナを起動
up:
	docker-compose up -d

# コンテナを停止・削除
down:
	docker-compose down --remove-orphans

# キャッシュクリア
cache:
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php artisan config:clear
	docker-compose exec php php artisan route:clear
	docker-compose exec php php artisan view:clear

# コンテナを停止
stop:
	docker-compose stop

# フロントエンドビルド
build:
	cd src && npm run build

# フロントエンド開発サーバー起動
dev:
	cd src && npm run dev