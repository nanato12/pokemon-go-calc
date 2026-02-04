# pokemon-go-calc

Pokemon GO スクリーンショットから個体値 (IV) を抽出するAPI

## API

### `POST /extract`

スクリーンショット画像からポケモン名と個体値を抽出する。

**Request:** `multipart/form-data`
- `image`: スクリーンショット画像ファイル

**Response:**
```json
{
  "pokemon": "デデンネ",
  "iv": {
    "attack": 11,
    "defense": 15,
    "stamina": 11
  }
}
```

### `GET /`

ヘルスチェック

## 開発

```bash
make init    # 依存関係インストール
make lint    # ruff check + ruff format --check + mypy
make fmt     # ruff format + ruff check --fix
```

## テスト

```bash
python -m pytest tests/
```

## デプロイ

Cloud Run にデプロイ。タグ (`v*`) をpushすると Cloud Build が自動ビルド・デプロイを実行。

```bash
git tag v0.2
git push origin v0.2
```

## CI

GitHub Actions で ruff / mypy による lint チェックを実行。
