"""ポケモンリポジトリサービス.

Data source: pokemongo-get.com/cpcal/
"""

from pokemon_go_calc.constants.evolution import get_evolution_chain
from pokemon_go_calc.models.pokemon import Pokemon

# ポケモンデータベース（別ファイルからインポート）
from pokemon_go_calc.services.pokemon_db import POKEMON_DB

# 図鑑番号 -> ポケモン名リスト のキャッシュ
_DEX_TO_NAMES: dict[int, list[str]] | None = None


def _build_dex_index() -> dict[int, list[str]]:
    """図鑑番号からポケモン名へのインデックスを構築."""
    global _DEX_TO_NAMES
    if _DEX_TO_NAMES is None:
        _DEX_TO_NAMES = {}
        for name, pokemon in POKEMON_DB.items():
            if pokemon.dex not in _DEX_TO_NAMES:
                _DEX_TO_NAMES[pokemon.dex] = []
            _DEX_TO_NAMES[pokemon.dex].append(name)
    return _DEX_TO_NAMES


def get_pokemon(name: str) -> Pokemon:
    """名前でポケモンを取得する.

    Args:
        name: ポケモン名（日本語）

    Returns:
        ポケモンインスタンス

    Raises:
        KeyError: ポケモンが見つからない場合
    """
    if name not in POKEMON_DB:
        raise KeyError(f"ポケモンが見つかりません: {name}")
    return POKEMON_DB[name]


def get_pokemon_by_dex(dex: int) -> list[Pokemon]:
    """図鑑番号でポケモンを取得する.

    Args:
        dex: 図鑑番号

    Returns:
        ポケモンインスタンスのリスト（フォーム違い含む）
    """
    dex_index = _build_dex_index()
    names = dex_index.get(dex, [])
    return [POKEMON_DB[name] for name in names]


def get_evolution_chain_pokemon(name: str) -> list[Pokemon]:
    """進化チェーンのポケモンを取得する.

    Args:
        name: ポケモン名

    Returns:
        進化チェーンのポケモンリスト（進化順）
    """
    pokemon = get_pokemon(name)
    chain_dex_list = get_evolution_chain(pokemon.dex)

    result: list[Pokemon] = []
    for dex in chain_dex_list:
        pokemon_list = get_pokemon_by_dex(dex)
        # 基本フォームを優先（カッコなし）
        pokemon_list.sort(key=lambda p: ("(" in p.name, p.name))
        if pokemon_list:
            result.append(pokemon_list[0])

    return result


def search_pokemon(query: str) -> list[str]:
    """部分一致でポケモンを検索する.

    Args:
        query: 検索クエリ

    Returns:
        マッチしたポケモン名のリスト
    """
    return [name for name in POKEMON_DB if query in name]


def get_all_pokemon_names() -> list[str]:
    """全ポケモン名を取得する.

    Returns:
        ポケモン名のリスト
    """
    return list(POKEMON_DB.keys())
