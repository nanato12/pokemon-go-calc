"""Pokemon GO スクリーンショットから個体値を抽出するスクリプト.

画像からポケモン名と個体値（攻撃/防御/HP）を抽出する。
"""

import argparse
import logging

from pokemon_go_calc import extract_from_screenshot

logging.basicConfig(level=logging.INFO, format="%(message)s")
logger = logging.getLogger(__name__)


def parse_args() -> argparse.Namespace:
    """コマンドライン引数をパースする."""
    parser = argparse.ArgumentParser(
        description="Pokemon GOスクリーンショットから個体値を抽出する"
    )
    parser.add_argument("image", help="画像ファイルのパス")
    return parser.parse_args()


def main() -> None:
    """メイン処理."""
    args = parse_args()

    name, iv = extract_from_screenshot(args.image)

    logger.info("=" * 50)
    logger.info("抽出結果")
    logger.info("=" * 50)
    logger.info("ポケモン名: %s", name or "（抽出失敗）")
    logger.info("こうげき: %d", iv.attack)
    logger.info("ぼうぎょ: %d", iv.defense)
    logger.info("HP: %d", iv.stamina)


if __name__ == "__main__":
    main()
