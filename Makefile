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
	@echo "=== アプリケーション: http://localhost ==="
	@echo "=== phpMyAdmin: http://localhost:8080 ==="

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

# テスト実行
test:
	docker-compose exec php php artisan test

# ログ確認
logs:
	docker-compose logs -f

# PHPコンテナにシェル接続
shell:
	docker-compose exec php bash

# MySQLコンテナにシェル接続
mysql:
	docker-compose exec mysql mysql -u laravel_user -p laravel_db

# phpMyAdminを開く
phpmyadmin:
	@echo "phpMyAdminを開いています..."
	xdg-open http://localhost:8080 2>/dev/null || open http://localhost:8080 2>/dev/null || echo "ブラウザで http://localhost:8080 を開いてください"

# アプリケーションを開く
open:
	@echo "アプリケーションを開いています..."
	xdg-open http://localhost 2>/dev/null || open http://localhost 2>/dev/null || echo "ブラウザで http://localhost を開いてください"

# 開発環境の状態確認
status:
	@echo "=== Dockerコンテナの状態 ==="
	docker-compose ps
	@echo ""
	@echo "=== ディスク使用量 ==="
	du -sh src/storage src/node_modules 2>/dev/null || echo "一部のディレクトリが見つかりません"

# 完全クリーンアップ（注意: データが削除されます）
clean:
	@echo "⚠️  注意: すべてのデータが削除されます"
	@read -p "続行しますか? (y/N): " confirm && [ "$$confirm" = "y" ] || exit 1
	docker-compose down -v --remove-orphans
	rm -rf src/storage/logs/* src/storage/framework/cache/* src/storage/framework/sessions/* src/storage/framework/views/*
	rm -rf src/node_modules src/vendor
	@echo "クリーンアップ完了"

# ヘルプ表示
help:
	@echo "利用可能なコマンド:"
	@echo "  init        - 開発環境の初期化"
	@echo "  up          - Dockerコンテナを起動"
	@echo "  down        - Dockerコンテナを停止・削除"
	@echo "  restart     - コンテナを再起動"
	@echo "  build       - フロントエンドをビルド"
	@echo "  dev         - フロントエンド開発サーバー起動"
	@echo "  cache       - Laravelキャッシュクリア"
	@echo "  fresh       - データベースをリフレッシュ"
	@echo "  test        - テスト実行"
	@echo "  logs        - ログ確認"
	@echo "  shell       - PHPコンテナにシェル接続"
	@echo "  mysql       - MySQLに接続"
	@echo "  phpmyadmin  - phpMyAdminを開く"
	@echo "  open        - アプリケーションを開く"
	@echo "  status      - 開発環境の状態確認"
	@echo "  clean       - 完全クリーンアップ"
	@echo "  help        - このヘルプを表示" 