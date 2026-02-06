"""個体値抽出ポート."""

from abc import ABC, abstractmethod
from typing import Any

from src.domain.value_objects.iv import IV


class IvExtractor(ABC):
    """個体値抽出インターフェース."""

    @abstractmethod
    def extract(self, image: Any) -> IV:
        """画像から個体値を抽出する.

        Args:
            image: 画像データ

        Returns:
            抽出された個体値
        """
        ...
