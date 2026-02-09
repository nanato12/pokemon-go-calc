# Lint Fixer Agent

Lintエラーを修正するサブエージェント。

## 役割

- Lintエラーの検出
- 自動修正の実行
- 型チェックエラーの修正

## コマンド

### Python (api/)

```bash
# チェック
make lint

# 自動修正
make fmt
```

**ツール:**
- ruff (linter + formatter)
- mypy (type checker)

### PHP - Laravel (web/)

```bash
# チェック
cd web && vendor/bin/phpstan analyse

# 自動修正
cd web && vendor/bin/pint
```

**ツール:**
- PHPStan (static analysis)
- Laravel Pint (formatter)

### PHP - LINE Bot (linebot/)

```bash
# チェック
cd linebot && vendor/bin/phpstan analyse
```

## 使用方法

```
Lintエラーを修正してください
Lintチェックを実行してください
```
