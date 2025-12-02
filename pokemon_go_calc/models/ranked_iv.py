"""ランク付き個体値モデル."""

from pydantic import BaseModel, Field

from pokemon_go_calc.models.iv import IV
from pokemon_go_calc.models.pokemon_stats import PokemonStats


class RankedIV(BaseModel):
    """リーグ用にランク付けされた個体値.

    Attributes:
        rank: 順位（1が最高）
        iv: 個体値
        level: 最適レベル
        cp: 最適レベルでのCP
        stats: 最適レベルでのステータス
        stat_product: ステータス積
        stat_product_percent: ステータス積パーセンテージ（1位比）
    """

    rank: int = Field(ge=1, description="順位")
    iv: IV = Field(description="個体値")
    level: float = Field(description="レベル")
    cp: int = Field(description="CP")
    stats: PokemonStats = Field(description="ステータス")
    stat_product: float = Field(description="ステータス積")
    stat_product_percent: float = Field(description="ステータス積%")

    model_config = {"frozen": True}
