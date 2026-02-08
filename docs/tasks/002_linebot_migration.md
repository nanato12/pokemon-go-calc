# LINE Bot 移植

## 概要
uparupaのLINE Bot機能をserverに移植し、`/webhook` で受け取れるようにする。

## タスク

- [x] LINE Bot SDK パッケージ導入 (nanato12/phine)
- [x] 環境変数設定 (.env)
- [x] Webhook コントローラ作成
- [x] ルーティング設定 (/api/webhook)
- [x] 署名検証 (PhineWrapper)
- [x] イベントハンドラ基盤 (EventDispatcherWrapper)
- [ ] 動作確認

## 参考
- /Users/mac/github.com/line-bot-uparupa
