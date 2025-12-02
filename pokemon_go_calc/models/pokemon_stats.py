"""ポケモンステータスモデル."""

from pydantic import BaseModel, Field


class PokemonStats(BaseModel):
    """計算されたポケモンステータス.

    Attributes:
        attack: 計算された攻撃力
        defense: 計算された防御力
        stamina: 計算されたHP（整数）
        cp: CP
        level: レベル
    """

    attack: float = Field(description="攻撃力")
    defense: float = Field(description="防御力")
    stamina: int = Field(description="HP")
    cp: int = Field(description="CP")
    level: float = Field(description="レベル")

    model_config = {"frozen": True}
