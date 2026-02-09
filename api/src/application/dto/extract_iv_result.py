"""IV抽出結果DTO."""

from dataclasses import dataclass


@dataclass(frozen=True)
class ExtractIvResult:
    """IV抽出ユースケースの結果.

    Attributes:
        pokemon_name: ポケモン名（日本語）
        pokemon_name_en: ポケモン名（英語）
        dex: 図鑑番号
        attack: 攻撃個体値
        defense: 防御個体値
        stamina: HP個体値
    """

    pokemon_name: str | None
    pokemon_name_en: str | None
    dex: int | None
    attack: int
    defense: int
    stamina: int
