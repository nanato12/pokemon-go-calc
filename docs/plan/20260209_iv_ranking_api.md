# IVランキング機能 実装計画（改訂版 v3）

## 概要

画像からIV抽出後、ポケモンの英語名・各リーグIV順位・最大CPを返す機能を追加する。

### アーキテクチャ方針

**責務分離**:
- **API (Python)**: OCR特化。画像 → ポケモン基本情報 + 個体値を返す
- **Web (Laravel/PHP)**: 計算ロジック。CP計算・IVランキング・レスポンス整形

```
[LINE Bot ユーザー]
     │ 画像送信
     ▼
[Web (Laravel/PHP)]
     │ 1. 画像をIV抽出APIへ転送
     ▼
[API (Python)] ── OCR ── → { pokemon, pokemon_en, dex, iv }
     │
     ▼ レスポンス受信
[Web (Laravel/PHP)]
     │ 2. dex + iv → CP計算・ランキング計算
     │ 3. LINE Bot返信メッセージ構築
     ▼
[LINE Bot ユーザー] ← ランキング付きメッセージ
```

---

## Part A: API側の変更（最小限）

### A-1. ExtractResponse に `pokemon_en`, `dex` 追加

現在:
```json
{
  "pokemon": "デデンネ",
  "iv": { "attack": 11, "defense": 15, "stamina": 11 }
}
```

変更後:
```json
{
  "pokemon": "デデンネ",
  "pokemon_en": "Dedenne",
  "dex": 702,
  "iv": { "attack": 11, "defense": 15, "stamina": 11 }
}
```

- `pokemon_en`: nullable（DBに存在しない場合 `null`）
- `dex`: nullable（DBに存在しない場合 `null`）
- IV抽出（OCR）自体が失敗 → 従来通り 400 エラー

### A-2. OpenAPI スキーマ更新 (`docs/openapi.yaml`)

```yaml
ExtractResponse:
  type: object
  required:
    - pokemon
    - iv
  properties:
    pokemon:
      type: string
      description: ポケモン名（日本語）
      example: "デデンネ"
    pokemon_en:
      type: string
      nullable: true
      description: ポケモン名（英語）。DBに存在しない場合null
      example: "Dedenne"
    dex:
      type: integer
      nullable: true
      minimum: 1
      description: 図鑑番号。DBに存在しない場合null
      example: 702
    iv:
      $ref: "#/components/schemas/IV"
```

### A-3. ポケモン名マッピングデータ (API側)

**新規ファイル**: `api/src/infrastructure/pokemon/pokemon_name_db.py`

OCR結果の日本語名 → 英語名・図鑑番号の軽量マッピング辞書。
種族値は不要（計算はPHP側で行う）。

```python
"""ポケモン名マッピングDB."""

from dataclasses import dataclass


@dataclass(frozen=True)
class PokemonNameEntry:
    """ポケモン名エントリ."""
    name_en: str
    dex: int


# 日本語名 → (英語名, 図鑑番号) マッピング
POKEMON_NAME_DB: dict[str, PokemonNameEntry] = {
    "フシギダネ": PokemonNameEntry(name_en="Bulbasaur", dex=1),
    "フシギソウ": PokemonNameEntry(name_en="Ivysaur", dex=2),
    ...
}
```

- git履歴 `533bbf9:pokemon_go_calc/services/pokemon_db.py` の1143エントリから `name` + `dex` を抽出
- 英語名は新規追加が必要
- **種族値は含めない**（API側では計算しないため）

### A-4. API側の変更ファイル一覧

| ファイル | 変更内容 |
|---|---|
| `docs/openapi.yaml` | ExtractResponse に `pokemon_en`, `dex` 追加 |
| `api/src/infrastructure/pokemon/__init__.py` | パッケージ初期化（新規） |
| `api/src/infrastructure/pokemon/pokemon_name_db.py` | 日本語名→英語名+dexマッピング（新規） |
| `api/src/application/dto/extract_iv_result.py` | `pokemon_name_en`, `dex` フィールド追加 |
| `api/src/application/usecases/extract_iv_usecase.py` | マッピング検索ロジック追加 |
| `api/src/presentation/controllers/default_controller.py` | レスポンスに `pokemon_en`, `dex` 追加 |

### A-5. API側の実装詳細

**`extract_iv_result.py` 変更**:
```python
@dataclass(frozen=True)
class ExtractIvResult:
    pokemon_name: str | None
    pokemon_name_en: str | None  # 追加
    dex: int | None              # 追加
    attack: int
    defense: int
    stamina: int
```

**`extract_iv_usecase.py` 変更**:
```python
from src.infrastructure.pokemon.pokemon_name_db import (
    POKEMON_NAME_DB,
)

# execute() 内:
name = self._name_extractor.extract(image)
iv = self._iv_extractor.extract(image)

# 名前マッピング検索
entry = POKEMON_NAME_DB.get(name) if name else None

return ExtractIvResult(
    pokemon_name=name,
    pokemon_name_en=entry.name_en if entry else None,
    dex=entry.dex if entry else None,
    attack=iv.attack,
    defense=iv.defense,
    stamina=iv.stamina,
)
```

**`default_controller.py` 変更**:
```python
return {
    "pokemon": result.pokemon_name,
    "pokemon_en": result.pokemon_name_en,
    "dex": result.dex,
    "iv": {
        "attack": result.attack,
        "defense": result.defense,
        "stamina": result.stamina,
    },
}, 200
```

---

## Part B: Web (Laravel/PHP) 側の実装

### B-1. ディレクトリ構成

```
web/app/
├── Domain/
│   ├── Pokemon/
│   │   ├── Pokemon.php              # ポケモン種族値モデル
│   │   ├── IV.php                   # 個体値 Value Object
│   │   ├── PokemonStats.php         # 計算済みステータス
│   │   └── RankedIV.php             # ランク付きIV
│   └── League/
│       └── League.php               # League Enum
├── Constants/
│   └── Pokemon/
│       ├── CpmTable.php             # CPMテーブル (Lv1-51)
│       └── PokemonDatabase.php      # ポケモンDB（種族値+英語名+dex）
└── Services/
    └── Pokemon/
        ├── CpCalculator.php          # CP・ステータス計算
        └── RankingService.php        # IVランキング計算
```

### B-2. Domain モデル

**`app/Domain/League/League.php`** — League Enum:
```php
enum League: string
{
    case LITTLE = 'little';
    case GREAT = 'great';
    case ULTRA = 'ultra';
    case MASTER = 'master';

    public function cpCap(): ?int
    {
        return match($this) {
            self::LITTLE => 500,
            self::GREAT  => 1500,
            self::ULTRA  => 2500,
            self::MASTER => null,
        };
    }

    public function displayName(): string
    {
        return match($this) {
            self::LITTLE => 'リトルカップ',
            self::GREAT  => 'スーパーリーグ',
            self::ULTRA  => 'ハイパーリーグ',
            self::MASTER => 'マスターリーグ',
        };
    }
}
```

**`app/Domain/Pokemon/Pokemon.php`**:
```php
final readonly class Pokemon
{
    public function __construct(
        public string $name,
        public string $nameEn,
        public int $dex,
        public int $baseAttack,
        public int $baseDefense,
        public int $baseStamina,
    ) {}
}
```

**`app/Domain/Pokemon/IV.php`**:
```php
final readonly class IV
{
    public function __construct(
        public int $attack,   // 0-15
        public int $defense,  // 0-15
        public int $stamina,  // 0-15
    ) {}
}
```

**`app/Domain/Pokemon/PokemonStats.php`**:
```php
final readonly class PokemonStats
{
    public function __construct(
        public float $attack,
        public float $defense,
        public int $stamina,
        public int $cp,
        public float $level,
    ) {}
}
```

**`app/Domain/Pokemon/RankedIV.php`**:
```php
final readonly class RankedIV
{
    public function __construct(
        public int $rank,
        public IV $iv,
        public float $level,
        public int $cp,
        public PokemonStats $stats,
        public float $statProduct,
        public float $statProductPercent,
    ) {}
}
```

### B-3. 定数

**`app/Constants/Pokemon/CpmTable.php`** — CPMテーブル:
```php
final class CpmTable
{
    public const MIN_LEVEL = 1.0;
    public const MAX_LEVEL = 51.0;

    /** @var array<float, float> */
    private const TABLE = [
        1.0  => 0.094,
        1.5  => 0.1351374322,
        2.0  => 0.16639787,
        // ... Lv51.0 まで 101エントリ
        51.0 => 0.8453000188,
    ];

    public static function get(float $level): float { ... }

    /** @return list<float> */
    public static function getAllLevels(): array { ... }
}
```

- Python版 `533bbf9:pokemon_go_calc/constants/cpm.py` の `CPM_TABLE` をそのまま移植

**`app/Constants/Pokemon/PokemonDatabase.php`** — ポケモンDB:
```php
final class PokemonDatabase
{
    /** @var array<string, Pokemon> 日本語名 → Pokemon */
    private static ?array $byName = null;

    /** @var array<int, list<Pokemon>> dex → Pokemon[] */
    private static ?array $byDex = null;

    public static function findByName(string $name): ?Pokemon { ... }
    public static function findByDex(int $dex): ?Pokemon { ... }
    public static function findByNameEn(string $nameEn): ?Pokemon { ... }

    /** @return array<string, Pokemon> */
    private static function buildDatabase(): array
    {
        return [
            'フシギダネ' => new Pokemon(
                name: 'フシギダネ',
                nameEn: 'Bulbasaur',
                dex: 1,
                baseAttack: 118,
                baseDefense: 111,
                baseStamina: 128,
            ),
            // ... 1143エントリ
        ];
    }
}
```

- Python版 `533bbf9:pokemon_go_calc/services/pokemon_db.py` のデータを移植
- **英語名を追加**（Python側 `pokemon_name_db.py` と同一のマッピングを使用）
- PHP側は種族値も含む（計算に必要なため）

### B-4. サービス

**`app/Services/Pokemon/CpCalculator.php`** — CP・ステータス計算:
```php
final class CpCalculator
{
    /**
     * CP = floor((Atk × √Def × √HP × CPM²) / 10), 最小10
     */
    public static function calculateCp(
        Pokemon $pokemon, IV $iv, float $level
    ): int { ... }

    public static function calculateStats(
        Pokemon $pokemon, IV $iv, float $level
    ): PokemonStats { ... }

    public static function calculateStatProduct(
        PokemonStats $stats
    ): float { ... }

    /**
     * CP上限以下での最大レベルを探す
     */
    public static function findMaxLevelForCp(
        Pokemon $pokemon, IV $iv, int $maxCp,
        float $maxLevel = CpmTable::MAX_LEVEL
    ): float { ... }

    /**
     * 最大CP = IV 15/15/15, Lv51
     */
    public static function calculateMaxCp(Pokemon $pokemon): int { ... }
}
```

- Python版 `calculator.py` のロジックをPHPに移植

**`app/Services/Pokemon/RankingService.php`** — IVランキング計算:
```php
final class RankingService
{
    /** @var array<string, list<RankedIV>> キャッシュ (pokemon_name:league) */
    private static array $cache = [];

    /**
     * 全4096通りのIVをリーグ用にランク付け
     * @return list<RankedIV>
     */
    public static function rankAllIvs(
        Pokemon $pokemon, League $league
    ): array { ... }

    /**
     * 指定IVの順位を取得
     */
    public static function getIvRank(
        Pokemon $pokemon, IV $iv, League $league
    ): RankedIV { ... }
}
```

- Python版 `rank_all_ivs_for_league`, `get_iv_rank` を移植
- `$cache` でメモリ内キャッシュ（同一リクエスト内で同じポケモン×リーグの再計算を回避）

### B-5. ImageHandler の更新

**`web/app/Infrastructure/Line/Handlers/ImageHandler.php`**:
```php
// IV抽出APIを呼び出し
$result = $ivExtractor->extract($imageData);

$iv = $result->getIv();
$pokemonEn = $result->getPokemonEn();
$dex = $result->getDex();

// ランキング計算（dexが取得できた場合のみ）
$rankingText = '';
if ($dex !== null) {
    $pokemon = PokemonDatabase::findByDex($dex);
    if ($pokemon !== null) {
        $calcIv = new IV(
            $iv->getAttack(), $iv->getDefense(), $iv->getStamina()
        );
        $maxCp = CpCalculator::calculateMaxCp($pokemon);

        $rankingText = "\n\n📊 リーグ順位";
        foreach (League::cases() as $league) {
            $ranked = RankingService::getIvRank(
                $pokemon, $calcIv, $league
            );
            $rankingText .= sprintf(
                "\n%s: %d位 (CP%d, Lv%.1f)",
                $league->displayName(),
                $ranked->rank,
                $ranked->cp,
                $ranked->level,
            );
        }
        $rankingText .= sprintf("\n\n💪 最大CP: %d", $maxCp);
    }
}

$text = sprintf(
    "🎮 %s%s\n\n攻撃: %d\n防御: %d\nHP: %d%s",
    $result->getPokemon(),
    $pokemonEn ? " ({$pokemonEn})" : '',
    $iv->getAttack(),
    $iv->getDefense(),
    $iv->getStamina(),
    $rankingText,
);
```

### B-6. OpenAPI クライアント再生成

`docs/openapi.yaml` 更新後、PHPクライアントを再生成:
```bash
cd web && openapi-generator-cli generate \
  -i ../docs/openapi.yaml \
  -g php \
  -o generated/iv-extractor-client \
  --additional-properties=invokerPackage=IvExtractorClient
```

新しい `ExtractResponse` に `getPokemonEn()`, `getDex()` メソッドが追加される。

### B-7. Web側の変更ファイル一覧

| ファイル | 内容 | 新規/変更 |
|---|---|---|
| `web/app/Domain/League/League.php` | League Enum | 新規 |
| `web/app/Domain/Pokemon/Pokemon.php` | ポケモン種族値モデル | 新規 |
| `web/app/Domain/Pokemon/IV.php` | 個体値 Value Object | 新規 |
| `web/app/Domain/Pokemon/PokemonStats.php` | 計算済みステータス | 新規 |
| `web/app/Domain/Pokemon/RankedIV.php` | ランク付きIV | 新規 |
| `web/app/Constants/Pokemon/CpmTable.php` | CPMテーブル | 新規 |
| `web/app/Constants/Pokemon/PokemonDatabase.php` | ポケモンDB（種族値+英語名） | 新規 |
| `web/app/Services/Pokemon/CpCalculator.php` | CP・ステータス計算 | 新規 |
| `web/app/Services/Pokemon/RankingService.php` | IVランキング計算 | 新規 |
| `web/app/Infrastructure/Line/Handlers/ImageHandler.php` | ランキング表示追加 | 変更 |
| `web/generated/iv-extractor-client/` | OpenAPIクライアント再生成 | 再生成 |

---

## 実装順序

### Step 1: API側の最小限変更
1. `api/src/infrastructure/pokemon/pokemon_name_db.py` — 名前マッピングDB新規作成
2. `api/src/application/dto/extract_iv_result.py` — `pokemon_name_en`, `dex` 追加
3. `api/src/application/usecases/extract_iv_usecase.py` — マッピング検索追加
4. `api/src/presentation/controllers/default_controller.py` — レスポンス更新
5. `docs/openapi.yaml` — スキーマ更新

### Step 2: OpenAPI クライアント再生成
1. `web/generated/iv-extractor-client/` を再生成

### Step 3: Web側 Domain/Constants 構築
1. `League.php` — League Enum
2. `Pokemon.php`, `IV.php`, `PokemonStats.php`, `RankedIV.php` — ドメインモデル
3. `CpmTable.php` — CPMテーブル
4. `PokemonDatabase.php` — ポケモンDB

### Step 4: Web側 Services 構築
1. `CpCalculator.php` — CP・ステータス計算（Python版calculator.pyの移植）
2. `RankingService.php` — IVランキング計算（Python版rank_all_ivs_for_leagueの移植）

### Step 5: ImageHandler 更新
1. `ImageHandler.php` — ランキング表示ロジック追加

### Step 6: テスト
1. `tests/Unit/Services/Pokemon/CpCalculatorTest.php` — CP計算テスト
2. `tests/Unit/Services/Pokemon/RankingServiceTest.php` — ランキングテスト
3. `tests/Unit/Constants/Pokemon/PokemonDatabaseTest.php` — DB検索テスト

---

## データの二重管理について

### 英語名 + 図鑑番号
- **API側 (Python)**: `pokemon_name_db.py` — 日本語名 → (英語名, dex) のみ。種族値なし
- **Web側 (PHP)**: `PokemonDatabase.php` — 日本語名 → (英語名, dex, 種族値) 全て含む

### 同期方針
- 両方のデータは同一ソース（git履歴 `533bbf9` の pokemon_db.py + 追加する英語名）から生成
- API側は軽量版（名前+dexのみ）、Web側はフル版（種族値含む）
- 将来的にはJSON等の共有データファイルから両方を生成するスクリプトも検討可能

---

## パフォーマンス考慮

### PHP側のランキング計算
- 1ポケモン × 1リーグ = 4096通り × レベル探索
- PHP 8.3 では Python より高速に動作する可能性あり
- `static $cache` でリクエスト内キャッシュ（同じポケモン×リーグの再計算回避）
- 4リーグ分: 初回計算 1-2秒程度を想定

### Laravel キャッシュ（将来最適化）
- 必要に応じて `Cache::remember()` でファイル/Redisキャッシュ化
- 初回リクエスト時に計算し、一定期間キャッシュ
- 初期実装では `static` キャッシュで十分

---

## LINE Bot レスポンス例

### 正常ケース
```
🎮 デデンネ (Dedenne)

攻撃: 11 / 防御: 15 / HP: 11

📊 リーグ順位
リトルカップ: 245位 (CP489, Lv25.0)
スーパーリーグ: 123位 (CP1498, Lv40.0)
ハイパーリーグ: 456位 (CP2100, Lv51.0)
マスターリーグ: 789位 (CP2100, Lv51.0)

💪 最大CP: 2100
```

### ポケモンがDBに未登録の場合
```
🎮 不明なポケモン

攻撃: 11 / 防御: 15 / HP: 11
```
（ランキング情報なし — IV抽出結果のみ表示）
