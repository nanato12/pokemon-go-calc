"""Pokemon GO IV Calculator.

使い方:
    scripts/ディレクトリ内のスクリプトを実行してください。

    # 個体値順位を計算
    python scripts/iv_rank.py

    # トップIV一覧
    python scripts/top_ivs.py

    # ポケモン検索
    python scripts/search_pokemon.py

    # CP計算
    python scripts/calc_cp.py

各スクリプト内の設定（POKEMON_NAME, IV等）を変更して使用してください。
"""

import logging

logging.basicConfig(level=logging.INFO, format="%(message)s")
logger = logging.getLogger(__name__)


def main() -> None:
    """メイン処理."""
    logger.info(__doc__)


if __name__ == "__main__":
    main()
