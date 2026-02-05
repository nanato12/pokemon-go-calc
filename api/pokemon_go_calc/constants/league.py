"""リーグ定数."""

from enum import Enum
from typing import Final


class League(Enum):
    """Pokemon GOバトルリーグ."""

    LITTLE = (500, "リトルカップ")
    SUPER = (1500, "スーパーリーグ")
    HYPER = (2500, "ハイパーリーグ")
    MASTER = (None, "マスターリーグ")

    def __init__(self, cp_cap: int | None, display_name: str) -> None:
        """初期化.

        Args:
            cp_cap: CP上限（Noneは無制限）
            display_name: 表示名
        """
        self._cp_cap = cp_cap
        self._display_name = display_name

    @property
    def cp_cap(self) -> int | None:
        """CP上限を取得."""
        return self._cp_cap

    @property
    def display_name(self) -> str:
        """表示名を取得."""
        return self._display_name


# IV範囲
IV_MIN: Final[int] = 0
IV_MAX: Final[int] = 15

# CP計算定数
MIN_CP: Final[int] = 10
CP_DIVISOR: Final[int] = 10  # CP計算式の除数

# ステータス積定数
STAT_PRODUCT_DIVISOR: Final[int] = 1000  # ステータス積の除数

# パーセンテージ定数
PERCENTAGE_MULTIPLIER: Final[int] = 100

# IV組み合わせ総数
TOTAL_IV_COMBINATIONS: Final[int] = 4096  # 16^3
