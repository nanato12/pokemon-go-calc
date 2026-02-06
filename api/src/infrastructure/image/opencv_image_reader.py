"""OpenCV画像読み込みアダプター."""

import cv2
import numpy as np

from src.application.ports.image_reader import ImageReader


class OpenCvImageReader(ImageReader):
    """OpenCVを使用した画像読み込み."""

    def read(self, image_path: str) -> np.ndarray:
        """画像ファイルを読み込む.

        Args:
            image_path: 画像ファイルパス

        Returns:
            OpenCV形式の画像（BGR）

        Raises:
            ValueError: 画像を読み込めない場合
        """
        image: np.ndarray | None = cv2.imread(image_path)
        if image is None:
            raise ValueError(f"画像を読み込めません: {image_path}")
        return image
