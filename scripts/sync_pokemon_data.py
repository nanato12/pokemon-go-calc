"""pogoapi.netからポケモンデータを取得し、PHPの進化DBコードを生成する."""

import json
import re
import sys
import urllib.request
from pathlib import Path

# プロジェクトルート
PROJECT_ROOT = Path(__file__).resolve().parent.parent
DATA_DIR = PROJECT_ROOT / "docs" / "data"
API_DIR = PROJECT_ROOT / "api" / "src"
WEB_DIR = PROJECT_ROOT / "web" / "app" / "Constants"

# pogoapi.net エンドポイント
STATS_URL = "https://pogoapi.net/api/v1/pokemon_stats.json"
EVOLUTIONS_URL = "https://pogoapi.net/api/v1/pokemon_evolutions.json"

# フォーム名マッピング（API英語 → DB日本語サフィックス）
FORM_SUFFIX_MAP: dict[str, str] = {
    "Alola": "アローラ",
    "Galarian": "ガラル",
    "Hisuian": "ヒスイ",
    "Paldea": "パルデア",
}

# API名の特殊マッピング（API名 → name_map名）
SPECIAL_NAME_MAP: dict[str, str] = {
    "Nidoran\u2640": "Nidoran Female",
    "Nidoran\u2642": "Nidoran Male",
}

# リージョナル進化先の手動追加
# APIでは Normal form のみだが、Pokemon GOでは
# リージョナルフォームにも進化可能なケース
REGIONAL_EVOLUTION_OVERRIDES: dict[str, list[str]] = {
    "タマタマ": ["ナッシー(アローラ)"],
    "カラカラ": ["ガラガラ(アローラ)"],
    "ドガース": ["マタドガス(ガラル)"],
    "ピカチュウ": ["ライチュウ(アローラ)"],
}


def fetch_json(url: str) -> list[dict]:
    """URLからJSONデータを取得."""
    req = urllib.request.Request(
        url,
        headers={"User-Agent": "pokemon-go-calc/1.0"},
    )
    with urllib.request.urlopen(req) as response:
        data: list[dict] = json.loads(response.read().decode())
    return data


def load_or_fetch_json(url: str, path: Path) -> list[dict]:
    """ローカルファイルがあれば読み込み、なければAPIから取得."""
    if path.exists():
        print(f"  Using cached: {path}")
        with open(path, encoding="utf-8") as f:
            data: list[dict] = json.load(f)
        return data
    data = fetch_json(url)
    save_json(data, path)
    return data


def save_json(data: list[dict], path: Path) -> None:
    """JSONデータをファイルに保存."""
    path.parent.mkdir(parents=True, exist_ok=True)
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=4)
    print(f"Saved: {path}")


def load_name_map() -> dict[str, str]:
    """英語名 → 日本語名のマッピングを構築."""
    name_map_path = API_DIR / "infrastructure" / "pokemon_data" / "pokemon_name_map.py"
    with open(name_map_path, encoding="utf-8") as f:
        content = f.read()

    entries = re.findall(r'"(.+?)":\s*\("(.+?)",\s*\d+\)', content)
    en_to_ja: dict[str, str] = {}
    for ja, en in entries:
        en_to_ja[en] = ja
    return en_to_ja


def load_db_names() -> set[str]:
    """PokemonDatabaseから登録済みの日本語名一覧を取得."""
    db_path = WEB_DIR / "PokemonDatabase.php"
    with open(db_path, encoding="utf-8") as f:
        content = f.read()

    names = re.findall(r"\['([^']+)',\s*\d+", content)
    return set(names)


def en_to_ja_name(
    pokemon_name: str,
    form: str,
    en_to_ja: dict[str, str],
) -> str | None:
    """英語名+フォーム → 日本語名に変換."""
    # 特殊名マッピング
    lookup_name = SPECIAL_NAME_MAP.get(pokemon_name, pokemon_name)
    ja_name = en_to_ja.get(lookup_name)
    if ja_name is None:
        return None
    if form == "Normal":
        return ja_name
    suffix = FORM_SUFFIX_MAP.get(form)
    if suffix:
        return f"{ja_name}({suffix})"
    return None


def build_evolution_map(
    evo_data: list[dict],
    en_to_ja: dict[str, str],
    db_names: set[str],
) -> dict[str, list[str]]:
    """進化マップを構築（日本語名ベース）."""
    evolution_map: dict[str, list[str]] = {}

    for entry in evo_data:
        src_name = en_to_ja_name(entry["pokemon_name"], entry["form"], en_to_ja)
        if src_name is None or src_name not in db_names:
            continue

        evos: list[str] = []
        for evo in entry["evolutions"]:
            evo_name = en_to_ja_name(evo["pokemon_name"], evo["form"], en_to_ja)
            if evo_name and evo_name in db_names:
                evos.append(evo_name)

        if evos:
            evolution_map[src_name] = evos

    # リージョナル進化先の手動追加
    for src, overrides in REGIONAL_EVOLUTION_OVERRIDES.items():
        if src not in db_names:
            continue
        existing = evolution_map.get(src, [])
        for evo_name in overrides:
            if evo_name in db_names and evo_name not in existing:
                existing.append(evo_name)
        if existing:
            evolution_map[src] = existing

    return evolution_map


def generate_php_constant(
    evolution_map: dict[str, list[str]],
) -> str:
    """PHPの定数配列コードを生成."""
    lines: list[str] = []
    for src in sorted(evolution_map.keys()):
        targets = evolution_map[src]
        targets_str = ", ".join(f"'{t}'" for t in targets)
        lines.append(f"        '{src}' => [{targets_str}],")
    return "\n".join(lines)


def generate_evolution_database_php(
    evolution_map: dict[str, list[str]],
) -> str:
    """EvolutionDatabase.phpのコード全体を生成."""
    constant_code = generate_php_constant(evolution_map)
    return f"""<?php

declare(strict_types=1);

namespace App\\Constants;

use App\\Domain\\Pokemon;

/**
 * 進化チェーンデータベース.
 *
 * pogoapi.netのデータを元に生成.
 * 再生成: python scripts/sync_pokemon_data.py
 */
final class EvolutionDatabase
{{
    /**
     * 前方進化マップ（直接の進化先のみ）.
     *
     * @var array<string, list<string>>
     */
    private const EVOLUTIONS = [
{constant_code}
    ];

    /**
     * 指定ポケモンの全前方進化先を再帰的に取得.
     *
     * @return Pokemon[]
     */
    public static function getForwardEvolutions(
        string $pokemonName,
    ): array {{
        /** @var Pokemon[] $result */
        $result = [];
        self::collectEvolutions($pokemonName, $result);

        return $result;
    }}

    /**
     * @param Pokemon[] $result
     */
    private static function collectEvolutions(
        string $name,
        array &$result,
    ): void {{
        $directEvolutions = self::EVOLUTIONS[$name] ?? [];

        foreach ($directEvolutions as $evoName) {{
            $pokemon = PokemonDatabase::findByName($evoName);

            if ($pokemon !== null) {{
                $result[] = $pokemon;
                self::collectEvolutions($evoName, $result);
            }}
        }}
    }}
}}
"""


def main() -> None:
    """メイン処理."""
    print("=== Pokemon GO Data Sync ===\n")

    # APIデータ取得（キャッシュがあればそちらを使用）
    use_cache = "--use-cache" in sys.argv

    if use_cache:
        print("Using cached data if available...\n")

    print("Loading evolution data...")
    evo_path = DATA_DIR / "pokemon_evolutions.json"
    if use_cache and evo_path.exists():
        evo_data = load_or_fetch_json(EVOLUTIONS_URL, evo_path)
    else:
        evo_data = fetch_json(EVOLUTIONS_URL)
        save_json(evo_data, evo_path)

    print("Loading stats data...")
    stats_path = DATA_DIR / "pokemon_stats.json"
    if use_cache and stats_path.exists():
        stats_data = load_or_fetch_json(STATS_URL, stats_path)
    else:
        stats_data = fetch_json(STATS_URL)
        save_json(stats_data, stats_path)

    # 名前マッピング読み込み
    en_to_ja = load_name_map()
    db_names = load_db_names()
    print(f"\nName map: {len(en_to_ja)} entries")
    print(f"PokemonDatabase: {len(db_names)} entries")

    # 進化マップ構築
    evolution_map = build_evolution_map(evo_data, en_to_ja, db_names)
    print(f"Evolution map: {len(evolution_map)} entries")

    # PHP コード生成
    php_code = generate_evolution_database_php(evolution_map)
    output_path = WEB_DIR / "EvolutionDatabase.php"
    with open(output_path, "w", encoding="utf-8") as f:
        f.write(php_code)
    print(f"\nGenerated: {output_path}")

    # サマリ表示
    multi_evo = {k: v for k, v in evolution_map.items() if len(v) > 1}
    if multi_evo:
        print("\n--- 分岐進化 ---")
        for src, targets in sorted(multi_evo.items()):
            print(f"  {src} => {targets}")

    return None


if __name__ == "__main__":
    sys.exit(main() or 0)
