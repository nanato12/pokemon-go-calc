# Test Runner Agent

テストを実行するサブエージェント。

## 役割

- ユニットテストの実行
- テストカバレッジの確認
- 失敗したテストの分析

## コマンド

### Python (api/)

```bash
cd api && pytest
cd api && pytest --cov=pokemon_go_calc
```

### PHP - Laravel (web/)

```bash
cd web && php artisan test
cd web && vendor/bin/phpunit
```

### PHP - LINE Bot (linebot/)

```bash
cd linebot && vendor/bin/phpunit
```

## 使用方法

```
テストを実行してください
特定のテストを実行: <テストファイル or テスト名>
```
