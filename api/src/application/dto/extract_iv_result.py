"""IV抽出結果DTO."""

from dataclasses import dataclass


@dataclass(frozen=True)
class ExtractIvResult:
    """IV抽出ユースケースの結果.

    Attributes:
        pokemon_name: ポケモン名
        attack: 攻撃個体値
        defense: 防御個体値
        stamina: HP個体値
    """

    pokemon_name: str | None
    attack: int
    defense: int
    stamina: int
