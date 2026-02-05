"""APIエンドポイントの実装."""

import logging
import tempfile
from pathlib import Path

from werkzeug.datastructures import FileStorage

from pokemon_go_calc import extract_from_screenshot

logger = logging.getLogger(__name__)


def healthCheck() -> tuple[dict[str, str], int]:  # noqa: N802
    """ヘルスチェック."""
    return {"status": "ok"}, 200


def extractIv(  # noqa: N802
    image: FileStorage,
) -> tuple[dict[str, object], int]:
    """スクリーンショットからポケモン名と個体値を抽出する."""
    if image.filename == "":
        return {"error": "ファイルが選択されていません"}, 400

    suffix = Path(image.filename or "image.png").suffix
    with tempfile.NamedTemporaryFile(suffix=suffix, delete=True) as tmp:
        image.save(tmp.name)
        name, iv = extract_from_screenshot(tmp.name)

    return {
        "pokemon": name,
        "iv": {
            "attack": iv.attack,
            "defense": iv.defense,
            "stamina": iv.stamina,
        },
    }, 200
