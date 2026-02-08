# CLAUDE.md

このファイルはClaude Code (claude.ai/code) がこのリポジトリで作業する際のガイダンスを提供します。

## ワークフロールール

### プランニング

計画を立てる際は、必ず以下の形式でファイルを作成すること:

```
docs/plan/yyyymmdd_description.md
```

例: `docs/plan/20260209_vue_setup.md`

### タスク管理

ユーザーからの指示で修正を行う場合は、必ず `docs/tasks/` ディレクトリにタスクファイルを作成すること。

## プロジェクト概要

Pokemon GO個体値計算ライブラリ。pokemongo-get.com/cpcal/のロジックをPythonで実装。

## 仮想環境

特に指定がない限りvenvを使用:

```bash
python -m venv venv
source venv/bin/activate
```

## コマンド

```bash
make init    # 依存関係インストール
make lint    # ruff check + ruff format --check + mypy
make fmt     # ruff format + ruff check --fix (自動修正)
make run     # main.py実行
```

## スクリプト

```bash
python scripts/iv_rank.py        # 個体値順位計算
python scripts/evolution_rank.py # 進化系個体値順位計算
python scripts/top_ivs.py        # トップIV一覧
python scripts/search_pokemon.py # ポケモン検索
python scripts/calc_cp.py        # CP計算
```

## Lint設定

- **ruff**: `pyproject.toml`で設定。行長79文字、E/F/Iルール有効
- **mypy**: 厳格な型チェック (`disallow_untyped_defs`, `disallow_untyped_calls`等)

## 開発ルール

### 型安全性

- pydanticを使用してデータ型の安全性を確保
- すべての関数/メソッドに型アノテーション必須 (mypyの`disallow_untyped_defs`で強制)

### インポート

- PEP 8に従う

### ファイル構成

- 1ファイル1クラス
- クラス名とファイル名を一致させる (例: `MyClass` → `my_class.py`)

### オブジェクト指向設計

- 単一責任の原則に従う
- 継承より合成を優先

### 定数

- マジックナンバー/文字列禁止
- `constants/`に定数を定義して参照

### Enum定義

- `auto()`は使用しない
- タプル`(code, display_name)`形式でプロパティアクセサを定義

```python
from enum import Enum

class Status(Enum):
    ACTIVE = (1, "有効")
    INACTIVE = (0, "無効")

    def __init__(self, code: int, display_name: str) -> None:
        self._code = code
        self._display_name = display_name

    @property
    def code(self) -> int:
        return self._code

    @property
    def display_name(self) -> str:
        return self._display_name
```

### ディレクトリ構成

```text
pokemon_go_calc/
├── __init__.py       # 公開API
├── logger.py         # ロギング設定
├── constants/        # Enum・定数
│   ├── cpm.py        # CPMテーブル (Lv1-51)
│   ├── evolution.py  # 進化チェーンDB
│   └── league.py     # League Enum、IV範囲
├── models/           # pydanticモデル
│   ├── iv.py         # 個体値
│   ├── pokemon.py    # ポケモン種族値
│   ├── pokemon_stats.py  # 計算済みステータス
│   └── ranked_iv.py  # ランク付き個体値
└── services/         # ビジネスロジック
    ├── calculator.py # CP・ステータス計算
    ├── pokemon_db.py # ポケモンデータベース
    └── pokemon_repository.py # ポケモン取得
scripts/              # 実行スクリプト
tests/                # テストコード
```

### Docstring

- すべての公開API (公開関数/クラス) にdocstringを記述
- Google styleを使用

### テスト

- pytestを使用
- `tests/`に`test_*.py`の命名規則でテストファイルを配置

### ロギング

- `print()`は使用しない
- `logging`モジュールを使用

### コード変更後の必須事項

- コード変更後は必ず`make lint`を実行してlintエラーがないことを確認
- コミット前にlintエラーを修正

## 計算式 (pokemongo-get.comより)

### CP
```
CP = floor((Attack × √Defense × √HP × CPM²) / 10)
最小値: 10
```

### ステータス
```
Attack = (種族値 + 個体値) × CPM
Defense = (種族値 + 個体値) × CPM
HP = floor((種族値 + 個体値) × CPM)
```

### ステータス積 (PvPランキング)
```
Stat Product = Attack × Defense × HP / 1000
```

### リーグCP上限
- リトルカップ: 500
- スーパーリーグ: 1500
- ハイパーリーグ: 2500
- マスターリーグ: なし
