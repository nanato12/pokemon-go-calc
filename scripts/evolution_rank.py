"""進化系個体値順位計算スクリプト.

ポケモン名と個体値を指定して、進化系全体の各リーグでの順位を出力する。
現在CPが指定された場合は、進化前リーグ上限CPも表示する。
"""

import argparse
import logging

from pokemon_go_calc import (
    IV,
    League,
    calculate_pre_evolution_cap_cp,
    get_iv_rank,
)
from pokemon_go_calc.constants import TOTAL_IV_COMBINATIONS
from pokemon_go_calc.services import get_evolution_chain_pokemon

logging.basicConfig(level=logging.INFO, format="%(message)s")
logger = logging.getLogger(__name__)


def parse_args() -> argparse.Namespace:
    """コマンドライン引数をパースする."""
    parser = argparse.ArgumentParser(
        description="進化系ポケモンの各リーグ順位を計算する"
    )
    parser.add_argument("pokemon", help="ポケモン名")
    parser.add_argument("attack", type=int, help="攻撃個体値 (0-15)")
    parser.add_argument("defense", type=int, help="防御個体値 (0-15)")
    parser.add_argument("stamina", type=int, help="HP個体値 (0-15)")
    parser.add_argument(
        "--cp", type=int, default=None, help="現在のCP（オプション）"
    )
    return parser.parse_args()


def main() -> None:
    """メイン処理."""
    args = parse_args()

    iv = IV(attack=args.attack, defense=args.defense, stamina=args.stamina)
    current_cp = args.cp

    # 進化チェーンを取得
    chain = get_evolution_chain_pokemon(args.pokemon)

    if not chain:
        logger.error("ポケモンが見つかりません: %s", args.pokemon)
        return

    # 入力されたポケモンをチェーン内で特定
    input_pokemon = None
    input_index = -1
    for i, p in enumerate(chain):
        if p.name == args.pokemon:
            input_pokemon = p
            input_index = i
            break

    if input_pokemon is None:
        logger.error("ポケモンが見つかりません: %s", args.pokemon)
        return

    logger.info("=" * 70)
    logger.info("進化系個体値ランキング")
    logger.info("個体値: %s", iv)
    if current_cp is not None:
        logger.info("現在CP: %d (%s)", current_cp, input_pokemon.name)
    logger.info("=" * 70)
    logger.info("")

    for i, pokemon in enumerate(chain):
        logger.info(
            "【%s】(#%d) 種族値: %d/%d/%d",
            pokemon.name,
            pokemon.dex,
            pokemon.base_attack,
            pokemon.base_defense,
            pokemon.base_stamina,
        )

        for league in League:
            ranked = get_iv_rank(pokemon, iv, league)

            # 入力ポケモンより後の進化先のみ上限CP表示
            cap_info = ""
            is_evolution_of_input = i > input_index
            if is_evolution_of_input and league.cp_cap is not None:
                cap_cp, _, _ = calculate_pre_evolution_cap_cp(
                    input_pokemon, pokemon, iv, league
                )
                cap_info = f" {input_pokemon.name}上限CP:{cap_cp}"

                # 現在CPが指定されている場合、超過判定を追加
                if current_cp is not None and current_cp > cap_cp:
                    cap_info += " [超過]"

            logger.info(
                "  %-12s 順位:%4d/%d  Lv%-5.1f CP%-5d ステ積%%:%.2f%%%s",
                league.display_name,
                ranked.rank,
                TOTAL_IV_COMBINATIONS,
                ranked.level,
                ranked.cp,
                ranked.stat_product_percent,
                cap_info,
            )
        logger.info("")


if __name__ == "__main__":
    main()
