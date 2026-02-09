# 004: IVランキングAPI拡張

## 概要

画像からIV抽出後のAPIレスポンスに以下を追加:
- ポケモン英語名
- 各リーグ（リトル/スーパー/ハイパー/マスター）のIV順位
- 最大CP

## 実装内容

### 新規作成ファイル (`pokemon_go_calc/`)

- `pokemon_go_calc/__init__.py` - 公開API exports
- `pokemon_go_calc/constants/__init__.py` - 定数パッケージ
- `pokemon_go_calc/constants/cpm.py` - CPMテーブル (Lv1-51)
- `pokemon_go_calc/constants/league.py` - League Enum定義
- `pokemon_go_calc/models/__init__.py` - モデルパッケージ
- `pokemon_go_calc/models/iv.py` - 個体値モデル
- `pokemon_go_calc/models/pokemon.py` - ポケモン種族値モデル
- `pokemon_go_calc/models/pokemon_stats.py` - 計算済みステータスモデル
- `pokemon_go_calc/models/ranked_iv.py` - ランク付きIVモデル
- `pokemon_go_calc/services/__init__.py` - サービスパッケージ
- `pokemon_go_calc/services/calculator.py` - CP・ステータス計算
- `pokemon_go_calc/services/pokemon_db.py` - ポケモンDB（日英名+種族値）
- `pokemon_go_calc/services/ranking_service.py` - IV順位計算

### 変更ファイル

- `docs/openapi.yaml` - LeagueRank, LeagueRanks スキーマ追加、ExtractResponse更新
- `api/src/application/dto/extract_iv_result.py` - LeagueRankResult追加、ExtractIvResult拡張
- `api/src/application/dto/__init__.py` - export追加
- `api/src/application/usecases/extract_iv_usecase.py` - ランキング計算ロジック追加
- `api/src/presentation/controllers/default_controller.py` - レスポンス構築更新

## ステータス

- [x] pokemon_go_calc ライブラリ構築
- [x] API層拡張
- [x] OpenAPI spec更新
- [x] make lint パス確認
