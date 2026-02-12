"""Tesseractを使用したCP抽出アダプター."""

import re

import cv2
import numpy as np
import pytesseract
from PIL import Image

from src.application.ports.cp_extractor import CpExtractor


class TesseractCpExtractor(CpExtractor):
    """Tesseract OCRを使用したCP抽出."""

    def extract(self, image: np.ndarray) -> int | None:
        """スクリーンショットからCPを抽出する.

        画像上部15%を切り出し、OCRでCP値を読み取る。

        Args:
            image: OpenCV形式の画像（BGR）

        Returns:
            CP値（見つからない場合はNone）
        """
        h, _w = image.shape[:2]

        # 上部のCP表示領域を切り出し（画像上部15%）
        cp_region = image[: int(h * 0.15), :]

        # グレースケール変換
        gray = cv2.cvtColor(cp_region, cv2.COLOR_BGR2GRAY)

        # 二値化
        _, binary = cv2.threshold(gray, 200, 255, cv2.THRESH_BINARY)

        # OCRでテキスト抽出
        pil_image = Image.fromarray(binary)
        text = pytesseract.image_to_string(pil_image, lang="eng")

        # "CP 1500" や "CP1500" のパターンでCP値を抽出
        match = re.search(r"CP\s*(\d+)", text)
        if match:
            return int(match.group(1))

        return None
