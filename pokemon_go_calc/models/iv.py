"""個体値モデル."""

from pydantic import BaseModel, Field


class IV(BaseModel):
    """個体値（Individual Values）.

    Attributes:
        attack: 攻撃個体値（0-15）
        defense: 防御個体値（0-15）
        stamina: HP個体値（0-15）
    """

    attack: int = Field(ge=0, le=15, description="攻撃個体値")
    defense: int = Field(ge=0, le=15, description="防御個体値")
    stamina: int = Field(ge=0, le=15, description="HP個体値")

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
        return (self.total / 45) * 100
