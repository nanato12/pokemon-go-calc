"""バー解析による個体値抽出アダプター."""

import cv2
import numpy as np

from src.application.ports.iv_extractor import IvExtractor
from src.domain.value_objects.iv import IV, IV_MAX


class BarIvExtractor(IvExtractor):
    """個体値バーの色解析による抽出."""

    def extract(self, image: np.ndarray) -> IV:
        """個体値バーから攻撃/防御/HPの値を抽出する.

        バーの色付き部分と灰色部分の比率から個体値を推定。
        ピンク色のバーは常にIV=15（MAX）として扱う。

        Args:
            image: OpenCV形式の画像（BGR）

        Returns:
            IV オブジェクト
        """
        h, w = image.shape[:2]

        # 個体値バー領域を切り出し（トリミング画像にも対応）
        y1, y2 = int(h * 0.65), int(h * 0.95)
        x1, x2 = int(w * 0.12), int(w * 0.48)
        bar_region = image[y1:y2, x1:x2]
        bar_hsv = cv2.cvtColor(bar_region, cv2.COLOR_BGR2HSV)
        b, g, r = cv2.split(bar_region)

        # マスク生成
        orange_mask = self._create_orange_mask(bar_hsv)
        pink_mask = self._create_pink_mask(bar_hsv)
        gray_mask = self._create_gray_mask(r, g, b)

        # バー位置検出
        bar_groups = self._detect_bar_groups(orange_mask, pink_mask, gray_mask)

        if not bar_groups:
            return IV(attack=0, defense=0, stamina=0)

        # 各ステータスの個体値を計算
        results = {"attack": 0, "defense": 0, "stamina": 0}
        stat_names = ["attack", "defense", "stamina"]

        for i, stat in enumerate(stat_names):
            if i >= len(bar_groups):
                continue
            results[stat] = self._calculate_stat_iv(
                bar_groups[i], orange_mask, pink_mask, gray_mask
            )

        return IV(
            attack=results["attack"],
            defense=results["defense"],
            stamina=results["stamina"],
        )

    def _create_orange_mask(self, hsv: np.ndarray) -> np.ndarray:
        """オレンジ色マスクを生成."""
        result: np.ndarray = cv2.inRange(
            hsv, np.array([10, 80, 150]), np.array([30, 255, 255])
        )
        return result

    def _create_pink_mask(self, hsv: np.ndarray) -> np.ndarray:
        """ピンク色マスクを生成."""
        pink_mask1 = cv2.inRange(
            hsv, np.array([0, 40, 150]), np.array([10, 200, 255])
        )
        pink_mask2 = cv2.inRange(
            hsv, np.array([160, 40, 150]), np.array([180, 200, 255])
        )
        result: np.ndarray = cv2.bitwise_or(pink_mask1, pink_mask2)
        return result

    def _create_gray_mask(
        self, r: np.ndarray, g: np.ndarray, b: np.ndarray
    ) -> np.ndarray:
        """灰色マスクを生成."""
        gray_diff = np.maximum(
            np.abs(r.astype(int) - g.astype(int)),
            np.abs(g.astype(int) - b.astype(int)),
        )
        is_gray = (gray_diff < 20) & (r > 200) & (r < 245)
        result: np.ndarray = is_gray.astype(np.uint8) * 255
        return result

    def _detect_bar_groups(
        self,
        orange_mask: np.ndarray,
        pink_mask: np.ndarray,
        gray_mask: np.ndarray,
    ) -> list[tuple[int, int]]:
        """バー位置をグループ化して検出."""
        combined_mask = cv2.bitwise_or(
            orange_mask, cv2.bitwise_or(pink_mask, gray_mask)
        )
        bar_profile = np.sum(combined_mask, axis=1)
        threshold = np.max(bar_profile) * 0.3
        bar_rows = np.where(bar_profile > threshold)[0]

        if len(bar_rows) == 0:
            return []

        # 連続する行をグループ化
        bar_groups: list[tuple[int, int]] = []
        start = int(bar_rows[0])
        for i in range(1, len(bar_rows)):
            if bar_rows[i] - bar_rows[i - 1] > 15:
                bar_groups.append((start, int(bar_rows[i - 1])))
                start = int(bar_rows[i])
        bar_groups.append((start, int(bar_rows[-1])))

        # バー高さでフィルタ（15-50ピクセル）
        return [(s, e) for s, e in bar_groups if 15 <= (e - s) <= 50]

    def _calculate_stat_iv(
        self,
        bar_group: tuple[int, int],
        orange_mask: np.ndarray,
        pink_mask: np.ndarray,
        gray_mask: np.ndarray,
    ) -> int:
        """単一ステータスの個体値を計算."""
        y_start, y_end = bar_group
        bar_y = (y_start + y_end) // 2

        # ピンクチェック（ピンクなら15）
        pink_count: int = int(np.sum(pink_mask[bar_y, :] > 0))
        if pink_count > 50:
            return IV_MAX

        # オレンジバーの場合は比率で計算
        orange_cols = np.where(orange_mask[bar_y, :] > 0)[0]
        gray_cols = np.where(gray_mask[bar_y, :] > 0)[0]

        if len(orange_cols) == 0:
            return 0

        bar_left = orange_cols[0]
        colored_right = orange_cols[-1]

        if len(gray_cols) > 0:
            gray_after = gray_cols[gray_cols > colored_right]
            if len(gray_after) > 0:
                bar_right = gray_after[-1]
            else:
                orange_width = colored_right - bar_left
                if orange_width < 50 or len(orange_cols) < 30:
                    return 0
                bar_right = colored_right
        else:
            orange_width = colored_right - bar_left
            if orange_width < 50 or len(orange_cols) < 30:
                return 0
            bar_right = colored_right

        total_length = bar_right - bar_left
        colored_length = colored_right - bar_left

        if total_length <= 0:
            return IV_MAX

        ratio = colored_length / total_length
        iv_value: int = min(IV_MAX, max(0, round(ratio * IV_MAX)))
        return iv_value
