"""ポケモンステータス Entity."""

from dataclasses import dataclass

from src.domain.value_objects.iv import IV


@dataclass(frozen=True)
class PokemonStats:
    """スクリーンショットから抽出されたポケモン情報.

    Attributes:
        name: ポケモン名（OCR抽出失敗時はNone）
        iv: 個体値
    """

    name: str | None
    iv: IV
