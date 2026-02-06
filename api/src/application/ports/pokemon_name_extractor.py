"""ポケモン名抽出ポート."""

from abc import ABC, abstractmethod
from typing import Any


class PokemonNameExtractor(ABC):
    """ポケモン名抽出インターフェース."""

    @abstractmethod
    def extract(self, image: Any) -> str | None:
        """画像からポケモン名を抽出する.

        Args:
            image: 画像データ

        Returns:
            ポケモン名（抽出失敗時はNone）
        """
        ...
