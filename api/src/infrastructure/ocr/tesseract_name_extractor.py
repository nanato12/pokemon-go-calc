"""Tesseractを使用したポケモン名抽出アダプター."""

import re

import cv2
import numpy as np
import pytesseract
from PIL import Image

from src.application.ports.pokemon_name_extractor import PokemonNameExtractor


class TesseractNameExtractor(PokemonNameExtractor):
    """Tesseract OCRを使用したポケモン名抽出."""

    def extract(self, image: np.ndarray) -> str | None:
        """テキストボックスからポケモン名を抽出する.

        「この〇〇を捕まえた」または「この〇〇は」のパターンから名前を取得。

        Args:
            image: OpenCV形式の画像（BGR）

        Returns:
            ポケモン名（見つからない場合はNone）
        """
        h, _w = image.shape[:2]

        # 下部のテキストボックス領域を切り出し（画像下部20%程度）
        text_region = image[int(h * 0.80) :, :]

        # グレースケール変換
        gray = cv2.cvtColor(text_region, cv2.COLOR_BGR2GRAY)

        # 二値化（白背景に黒文字を想定）
        _, binary = cv2.threshold(gray, 200, 255, cv2.THRESH_BINARY)

        # OCRでテキスト抽出
        pil_image = Image.fromarray(binary)
        text = pytesseract.image_to_string(pil_image, lang="jpn")

        # 「この〇〇を」または「この〇〇は」パターンで名前を抽出
        match = re.search(r"この(.+?)[をは]", text)
        if match:
            return match.group(1).strip()

        return None
