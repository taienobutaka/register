# 会員登録システム (Register System)

Laravel + React + Docker を使用した会員登録システムです。

![alt text](<スクリーンショット 2025-07-14 150548.png>)

## 🚀 技術スタック

### バックエンド

- **PHP 8.4** - メイン言語
- **Laravel 12.0** - PHP フレームワーク
- **Laravel Fortify 1.26** - 認証機能
- **MySQL 8.0** - データベース

### フロントエンド

- **React 19.1.0** - JavaScript ライブラリ
- **Tailwind CSS 4.0** - CSS フレームワーク
- **Vite 6.2.4** - ビルドツール

### インフラ・開発環境

- **Docker & Docker Compose** - コンテナ化
- **Nginx 1.24** - Web サーバー
- **PHP-FPM** - PHP 実行環境
- **phpMyAdmin** - データベース管理

## 📋 必要な環境

- Docker & Docker Compose
- Make (Makefile を使用する場合)
- Node.js 18+ (ローカル開発の場合)

## 🔧 環境構築

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd register
```

### 2. 初回セットアップ（Makefile を使用）

```bash
make init
```

このコマンドで以下が自動実行されます：

- Docker コンテナのビルド・起動
- PHP 依存パッケージのインストール
- Node.js 依存パッケージのインストール
- .env ファイルの作成・設定
- アプリケーションキーの生成
- データベースマイグレーション
- フロントエンドのビルド

### 3. 手動セットアップ（Makefile を使わない場合）

```bash
# Dockerコンテナ起動
docker-compose up -d --build

# PHP依存パッケージインストール
docker-compose exec php composer install

# Node.js依存パッケージインストール
cd src && npm install

# 環境設定ファイルの作成
cp src/.env.example src/.env

# アプリケーションキー生成
docker-compose exec php php artisan key:generate

# データベースマイグレーション
docker-compose exec php php artisan migrate

# フロントエンドビルド
cd src && npm run build
```

## 🎯 開発コマンド

### 基本操作

```bash
# 開発環境の初期化
make init

# コンテナ起動
make up

# コンテナ停止・削除
make down

# コンテナ再起動
make restart

# 開発環境の状態確認
make status
```

### フロントエンド開発

```bash
# フロントエンド開発サーバー起動（ホットリロード有効）
make dev

# フロントエンドビルド（本番用）
make build
```

### データベース操作

```bash
# データベースリフレッシュ（マイグレーション + シーダー）
make fresh

# MySQLに直接接続
make mysql

# phpMyAdminを開く
make phpmyadmin
```

### その他

```bash
# Laravelキャッシュクリア
make cache

# ログ確認
make logs

# PHPコンテナにシェル接続
make shell

# アプリケーションをブラウザで開く
make open

# ヘルプ表示
make help
```

## 🧪 テスト実行

### 全テスト実行

```bash
# Makefileを使用
make test

# 直接実行
docker-compose exec php php artisan test
```

### 詳細なテスト実行

```bash
# 詳細表示でテスト実行
docker-compose exec php ./vendor/bin/phpunit --testdox

# 特定のテストファイルを実行
docker-compose exec php ./vendor/bin/phpunit tests/Feature/Auth/RegisterTest.php

# デバッグモードでテスト実行
docker-compose exec php ./vendor/bin/phpunit --debug
```

### テストの種類

#### Feature テスト（統合テスト）

- 会員登録フォーム表示テスト
- 正常な会員登録フローテスト
- バリデーションエラーテスト
- レスポンス形式テスト

#### Unit テスト（単体テスト）

- バリデーションルールテスト
- リクエストクラステスト

## 📱 アクセス方法

環境構築完了後、以下の URL でアクセス可能です：

- **アプリケーション**: http://localhost
- **会員登録画面**: http://localhost/register
- **phpMyAdmin**: http://localhost:8080

### phpMyAdmin 接続情報

- **サーバー**: mysql
- **ユーザー名**: laravel_user
- **パスワード**: laravel_pass
- **データベース**: laravel_db

## 📂 プロジェクト構造

```
register/
├── docker/                    # Docker設定
│   ├── nginx/                 # Nginx設定
│   ├── php/                   # PHP設定
│   └── mysql/                 # MySQL設定・データ
├── src/                       # Laravelアプリケーション
│   ├── app/                   # アプリケーションコード
│   │   ├── Http/Controllers/  # コントローラー
│   │   ├── Http/Requests/     # フォームリクエスト
│   │   └── Models/            # Eloquentモデル
│   ├── resources/             # フロントエンドリソース
│   ├── tests/                 # テストファイル
│   │   ├── Feature/           # 統合テスト
│   │   └── Unit/              # 単体テスト
│   └── routes/                # ルート定義
├── docker-compose.yml         # Docker Compose設定
├── Makefile                   # 開発用コマンド
└── README.md                  # このファイル
```

## 🔍 トラブルシューティング

### よくある問題と解決方法

#### 1. Docker コンテナが起動しない

```bash
# コンテナとボリュームを完全削除して再構築
docker-compose down -v
make init
```

#### 2. パーミッションエラー

```bash
# ストレージディレクトリの権限修正
docker-compose exec php chmod -R 777 storage bootstrap/cache
```

#### 3. データベース接続エラー

```bash
# MySQLコンテナの状態確認
docker-compose ps
docker-compose logs mysql

# データベース設定確認
cat src/.env | grep DB_
```

#### 4. フロントエンドビルドエラー

```bash
# node_modulesを再インストール
rm -rf src/node_modules
cd src && npm install
npm run build
```

#### 5. テスト実行時のエラー

```bash
# テスト用データベースのリフレッシュ
docker-compose exec php php artisan migrate:fresh --env=testing
```

### ログの確認方法

```bash
# 全サービスのログ
make logs

# 特定のサービスのログ
docker-compose logs nginx
docker-compose logs php
docker-compose logs mysql
```

## 🧹 環境のクリーンアップ

```bash
# 完全クリーンアップ（データも削除）
make clean

# コンテナのみ削除（データは保持）
make down
```

## 📝 開発メモ

### 新機能追加時の流れ

1. 機能の実装
2. テストの作成
3. テストの実行・確認
4. ドキュメントの更新

### テスト作成のガイドライン

- Feature テスト: エンドツーエンドの動作確認
- Unit テスト: 個別の機能・クラスの動作確認
- 境界値テスト: バリデーションの確認
- エラーハンドリングテスト: 異常系の確認

## 🤝 貢献方法

1. このリポジトリをフォーク
2. 機能ブランチを作成 (`git checkout -b feature/new-feature`)
3. 変更をコミット (`git commit -am 'Add new feature'`)
4. ブランチにプッシュ (`git push origin feature/new-feature`)
5. プルリクエストを作成

## 📄 ライセンス

このプロジェクトは MIT ライセンスの下で公開されています。
