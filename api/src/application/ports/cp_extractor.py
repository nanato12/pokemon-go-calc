"""CP抽出ポート."""

from abc import ABC, abstractmethod
from typing import Any


class CpExtractor(ABC):
    """CP抽出インターフェース."""

    @abstractmethod
    def extract(self, image: Any) -> int | None:
        """画像からCPを抽出する.

        Args:
            image: 画像データ

        Returns:
            CP値（抽出失敗時はNone）
        """
        ...
