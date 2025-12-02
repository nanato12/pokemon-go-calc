"""ポケモン検索スクリプト.

ポケモン名で部分一致検索し、種族値を表示する。
"""

import logging

from pokemon_go_calc import get_pokemon, search_pokemon

# =============================================================================
# 設定（ここを変更）
# =============================================================================
SEARCH_QUERY = "ピカ"
# =============================================================================

logging.basicConfig(level=logging.INFO, format="%(message)s")
logger = logging.getLogger(__name__)


def main() -> None:
    """メイン処理."""
    logger.info("検索: '%s'", SEARCH_QUERY)
    logger.info("=" * 50)

    matches = search_pokemon(SEARCH_QUERY)

    if not matches:
        logger.info("該当するポケモンが見つかりません")
        return

    logger.info("%-25s%-6s%-6s%-6s", "ポケモン", "攻撃", "防御", "HP")
    logger.info("-" * 50)

    for name in matches:
        pokemon = get_pokemon(name)
        logger.info(
            "%-25s%-6d%-6d%-6d",
            pokemon.name,
            pokemon.base_attack,
            pokemon.base_defense,
            pokemon.base_stamina,
        )


if __name__ == "__main__":
    main()
