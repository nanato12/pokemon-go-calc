"""個体値 (Individual Values) Value Object."""

from pydantic import BaseModel, Field

IV_MIN = 0
IV_MAX = 15


class IV(BaseModel):
    """個体値（Individual Values）.

    不変のValue Object。0-15の範囲で攻撃/防御/HPを表す。

    Attributes:
        attack: 攻撃個体値（0-15）
        defense: 防御個体値（0-15）
        stamina: HP個体値（0-15）
    """

    attack: int = Field(ge=IV_MIN, le=IV_MAX, description="攻撃個体値")
    defense: int = Field(ge=IV_MIN, le=IV_MAX, description="防御個体値")
    stamina: int = Field(ge=IV_MIN, le=IV_MAX, description="HP個体値")

    model_config = {"frozen": True}

    def __str__(self) -> str:
        """文字列表現を返す."""
        return f"{self.attack}/{self.defense}/{self.stamina}"

    @property
    def total(self) -> int:
        """合計値を返す."""
        return self.attack + self.defense + self.stamina

    @property
    def percentage(self) -> float:
        """パーセンテージを返す（0-100%）."""
        max_total = IV_MAX * 3
        return (self.total / max_total) * 100

    @property
    def is_perfect(self) -> bool:
        """100%個体かどうか."""
        return (
            self.attack == IV_MAX
            and self.defense == IV_MAX
            and self.stamina == IV_MAX
        )
