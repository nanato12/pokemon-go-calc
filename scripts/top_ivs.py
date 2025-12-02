"""トップIV一覧スクリプト.

指定したポケモン・リーグのトップIV組み合わせを表示する。
"""

import logging

from pokemon_go_calc import League, get_pokemon, rank_all_ivs_for_league

# =============================================================================
# 設定（ここを変更）
# =============================================================================
POKEMON_NAME = "マリルリ"
LEAGUE = League.SUPER  # LITTLE, SUPER, HYPER, MASTER
TOP_N = 20
# =============================================================================

logging.basicConfig(level=logging.INFO, format="%(message)s")
logger = logging.getLogger(__name__)


def main() -> None:
    """メイン処理."""
    pokemon = get_pokemon(POKEMON_NAME)

    logger.info("=" * 60)
    logger.info("%s - %s TOP%d", pokemon.name, LEAGUE.display_name, TOP_N)
    logger.info("=" * 60)
    logger.info("")

    rankings = rank_all_ivs_for_league(pokemon, LEAGUE)

    logger.info(
        "%-6s%-12s%-8s%-8s%-10s", "順位", "個体値", "Lv", "CP", "ステ積%"
    )
    logger.info("-" * 50)

    for r in rankings[:TOP_N]:
        logger.info(
            "%-6d%-12s%-8.1f%-8d%.2f%%",
            r.rank,
            str(r.iv),
            r.level,
            r.cp,
            r.stat_product_percent,
        )


if __name__ == "__main__":
    main()
