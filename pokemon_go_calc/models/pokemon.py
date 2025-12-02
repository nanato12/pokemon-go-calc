"""ポケモンモデル."""

from pydantic import BaseModel, Field


class Pokemon(BaseModel):
    """ポケモン種族値.

    Attributes:
        name: ポケモン名
        dex: 図鑑番号
        base_attack: 攻撃種族値
        base_defense: 防御種族値
        base_stamina: HP種族値
    """

    name: str = Field(description="ポケモン名")
    dex: int = Field(ge=1, description="図鑑番号")
    base_attack: int = Field(ge=0, description="攻撃種族値")
    base_defense: int = Field(ge=0, description="防御種族値")
    base_stamina: int = Field(ge=0, description="HP種族値")

    model_config = {"frozen": True}
