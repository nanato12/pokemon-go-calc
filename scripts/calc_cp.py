"""CP計算スクリプト.

指定したポケモン・個体値・レベルでのCPとステータスを計算する。
"""

import logging

from pokemon_go_calc import IV, calculate_stats, get_pokemon

# =============================================================================
# 設定（ここを変更）
# =============================================================================
POKEMON_NAME = "マリルリ"
ATTACK_IV = 0
DEFENSE_IV = 14
STAMINA_IV = 15
LEVEL = 45.5
# =============================================================================

logging.basicConfig(level=logging.INFO, format="%(message)s")
logger = logging.getLogger(__name__)


def main() -> None:
    """メイン処理."""
    pokemon = get_pokemon(POKEMON_NAME)
    iv = IV(attack=ATTACK_IV, defense=DEFENSE_IV, stamina=STAMINA_IV)
    stats = calculate_stats(pokemon, iv, LEVEL)

    logger.info("=" * 50)
    logger.info("ポケモン: %s", pokemon.name)
    logger.info("個体値: %s", iv)
    logger.info("レベル: %.1f", LEVEL)
    logger.info("=" * 50)
    logger.info("")
    logger.info("CP: %d", stats.cp)
    logger.info("攻撃: %.2f", stats.attack)
    logger.info("防御: %.2f", stats.defense)
    logger.info("HP: %d", stats.stamina)
    logger.info("")
    stat_product = stats.attack * stats.defense * stats.stamina / 1000
    logger.info("ステ積: %.2f", stat_product)


if __name__ == "__main__":
    main()
