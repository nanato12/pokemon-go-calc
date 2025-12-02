"""Pokemon GO スクリーンショットから情報を抽出するサービス."""

import re

import cv2
import numpy as np
import pytesseract
from PIL import Image

from pokemon_go_calc.constants.league import IV_MAX
from pokemon_go_calc.models.iv import IV


def extract_pokemon_name(image: np.ndarray) -> str | None:
    """テキストボックスからポケモン名を抽出する.

    「この〇〇を捕まえた」または「この〇〇は」のパターンから名前を取得。

    Args:
        image: OpenCV形式の画像（BGR）

    Returns:
        ポケモン名（見つからない場合はNone）
    """
    h, w = image.shape[:2]

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


def extract_pokemon_iv(image: np.ndarray) -> IV:
    """個体値バーから攻撃/防御/HPの値を抽出する.

    バーの色付き部分と灰色部分の比率から個体値を推定。
    ピンク色のバーは常にIV=15（MAX）として扱う。

    Args:
        image: OpenCV形式の画像（BGR）

    Returns:
        IV オブジェクト
    """
    h, w = image.shape[:2]

    # 個体値バー領域を切り出し
    y1, y2 = int(h * 0.72), int(h * 0.87)
    x1, x2 = int(w * 0.12), int(w * 0.48)
    bar_region = image[y1:y2, x1:x2]
    bar_hsv = cv2.cvtColor(bar_region, cv2.COLOR_BGR2HSV)
    b, g, r = cv2.split(bar_region)

    # オレンジマスク（Hue 10-30, 高彩度）
    orange_mask = cv2.inRange(
        bar_hsv, np.array([10, 80, 150]), np.array([30, 255, 255])
    )

    # ピンクマスク（Hue 0-10 または 160-180, 中彩度）
    pink_mask1 = cv2.inRange(
        bar_hsv, np.array([0, 40, 150]), np.array([10, 200, 255])
    )
    pink_mask2 = cv2.inRange(
        bar_hsv, np.array([160, 40, 150]), np.array([180, 200, 255])
    )
    pink_mask = cv2.bitwise_or(pink_mask1, pink_mask2)

    # 灰色マスク
    gray_diff = np.maximum(
        np.abs(r.astype(int) - g.astype(int)),
        np.abs(g.astype(int) - b.astype(int)),
    )
    is_gray = (gray_diff < 20) & (r > 200) & (r < 245)
    gray_mask = is_gray.astype(np.uint8) * 255

    # バー位置を検出（彩度プロファイルで）
    sat_profile = np.sum(bar_hsv[:, :, 1], axis=1)
    threshold = np.max(sat_profile) * 0.5
    bar_rows = np.where(sat_profile > threshold)[0]

    if len(bar_rows) == 0:
        return IV(attack=0, defense=0, stamina=0)

    # 連続する行をグループ化
    bar_groups: list[tuple[int, int]] = []
    start = int(bar_rows[0])
    for i in range(1, len(bar_rows)):
        if bar_rows[i] - bar_rows[i - 1] > 20:
            bar_groups.append((start, int(bar_rows[i - 1])))
            start = int(bar_rows[i])
    bar_groups.append((start, int(bar_rows[-1])))

    results = {"attack": 0, "defense": 0, "stamina": 0}
    stat_names = ["attack", "defense", "stamina"]

    for i, stat in enumerate(stat_names):
        if i >= len(bar_groups):
            continue

        y_start, y_end = bar_groups[i]
        bar_y = (y_start + y_end) // 2

        # ピンクかどうかチェック（ピンクなら15）
        pink_count: int = int(np.sum(pink_mask[bar_y, :] > 0))
        if pink_count > 50:
            results[stat] = IV_MAX
            continue

        # オレンジバーの場合は比率で計算
        orange_cols = np.where(orange_mask[bar_y, :] > 0)[0]
        gray_cols = np.where(gray_mask[bar_y, :] > 0)[0]

        # オレンジがない、または連続したバー形状でない場合は0
        if len(orange_cols) == 0:
            results[stat] = 0
            continue

        # バー形状の判定：オレンジピクセルが50以上連続している
        orange_width = orange_cols[-1] - orange_cols[0]
        if orange_width < 50 or len(orange_cols) < 30:
            results[stat] = 0
            continue

        bar_left = orange_cols[0]
        colored_right = orange_cols[-1]

        if len(gray_cols) > 0:
            gray_after = gray_cols[gray_cols > colored_right]
            if len(gray_after) > 0:
                bar_right = gray_after[-1]
            else:
                bar_right = colored_right
        else:
            bar_right = colored_right

        total_length = bar_right - bar_left
        colored_length = colored_right - bar_left

        if total_length <= 0:
            results[stat] = IV_MAX
            continue

        ratio = colored_length / total_length
        iv_value = min(IV_MAX, max(0, round(ratio * IV_MAX)))
        results[stat] = iv_value

    return IV(
        attack=results["attack"],
        defense=results["defense"],
        stamina=results["stamina"],
    )


def extract_from_screenshot(image_path: str) -> tuple[str | None, IV]:
    """スクリーンショットからポケモン名と個体値を抽出する.

    Args:
        image_path: 画像ファイルのパス

    Returns:
        (ポケモン名, IV) のタプル

    Raises:
        ValueError: 画像を読み込めない場合
    """
    image = cv2.imread(image_path)
    if image is None:
        raise ValueError(f"画像を読み込めません: {image_path}")

    name = extract_pokemon_name(image)
    iv = extract_pokemon_iv(image)

    return name, iv
