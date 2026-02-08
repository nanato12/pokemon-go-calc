# LINE Bot クリーンアーキテクチャ リファクタ

## 概要

LINE SDK に密結合した構成から、クリーンアーキテクチャに移行

## 設計方針

- ドメイン層は外部ライブラリに依存しない
- UseCase は Interface 経由で Infrastructure を利用
- LINE SDK は Infrastructure 層に閉じ込める

## ディレクトリ構成

```
app/
├── Domain/                           # ドメイン層
│   ├── Bot/
│   │   ├── Entities/
│   │   │   ├── Message.php           # メッセージエンティティ
│   │   │   ├── Event.php             # イベントエンティティ
│   │   │   └── User.php              # ユーザーエンティティ
│   │   └── Contracts/
│   │       ├── BotClientInterface.php      # Bot操作インターフェース
│   │       └── EventParserInterface.php    # イベントパーサーインターフェース
│   └── Handlers/
│       └── MessageHandlerInterface.php     # メッセージハンドラインターフェース
│
├── UseCases/                         # アプリケーション層
│   └── Bot/
│       └── HandleWebhookUseCase.php  # Webhookハンドリング
│
├── Infrastructure/                   # インフラ層
│   └── Line/
│       ├── LineBotClient.php         # BotClientInterface 実装
│       ├── LineEventParser.php       # EventParserInterface 実装
│       └── Handlers/
│           └── EchoHandler.php       # メッセージハンドラ実装
│
└── Http/Controllers/                 # プレゼンテーション層
    └── Api/
        └── WebhookController.php     # Controller (薄く)
```

## 依存関係

```
Controller → UseCase → Domain ← Infrastructure
                ↓
           Interface経由で注入
```

## 実装順序

1. Domain層 (Entities, Contracts)
2. UseCase層
3. Infrastructure層 (LINE実装)
4. Controller リファクタ
5. ServiceProvider で DI 設定
