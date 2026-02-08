# LINE Bot Webhook 移植計画

## 概要
uparupaのLINE Bot機能をGO Pilotに移植

## 参考実装
- `/Users/mac/github.com/line-bot-uparupa`

## 実装ステップ

### 1. パッケージ導入
```bash
composer require nanato12/phine:dev-develop
```

### 2. 設定ファイル
- `config/line-bot.php` 作成
- `.env` に環境変数追加

### 3. コントローラ
- `app/Http/Controllers/Api/LineBotController.php`
- `/webhook` エンドポイント

### 4. ラッパークラス
- `app/LineBot/Wrappers/PhineWrapper.php`
- `app/LineBot/Wrappers/EventDispatcherWrapper.php`

### 5. イベントハンドラ（最小構成）
- FollowHandler
- TextMessageHandler（エコー）

### 6. ルーティング
- `routes/api.php` に `/webhook` 追加

## ディレクトリ構成
```
app/
├── Http/Controllers/Api/
│   └── LineBotController.php
└── LineBot/
    ├── Wrappers/
    │   ├── PhineWrapper.php
    │   └── EventDispatcherWrapper.php
    └── Handlers/
        ├── FollowHandler.php
        └── TextMessageHandlers/
            └── EchoHandler.php
```
