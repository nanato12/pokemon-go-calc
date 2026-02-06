"""画像読み込みポート."""

from abc import ABC, abstractmethod
from typing import Any


class ImageReader(ABC):
    """画像読み込みインターフェース."""

    @abstractmethod
    def read(self, image_path: str) -> Any:
        """画像ファイルを読み込む.

        Args:
            image_path: 画像ファイルパス

        Returns:
            画像データ（実装依存）

        Raises:
            ValueError: 画像を読み込めない場合
        """
        ...
