"""Pokemon GO IV抽出 APIサーバー."""

import logging
import tempfile
from pathlib import Path

from flask import Flask, jsonify, request

from pokemon_go_calc import extract_from_screenshot

logging.basicConfig(level=logging.INFO, format="%(message)s")
logger = logging.getLogger(__name__)

app = Flask(__name__)


@app.route("/", methods=["GET"])
def health() -> tuple[dict[str, str], int]:
    """ヘルスチェック."""
    return {"status": "ok"}, 200


@app.route("/extract", methods=["POST"])
def extract() -> tuple[dict[str, object], int]:
    """スクリーンショットからポケモン名と個体値を抽出する."""
    if "image" not in request.files:
        return {"error": "imageフィールドが必要です"}, 400

    file = request.files["image"]
    if file.filename == "":
        return {"error": "ファイルが選択されていません"}, 400

    suffix = Path(file.filename or "image.png").suffix
    with tempfile.NamedTemporaryFile(suffix=suffix, delete=True) as tmp:
        file.save(tmp.name)
        name, iv = extract_from_screenshot(tmp.name)

    return jsonify(
        {
            "pokemon": name,
            "iv": {
                "attack": iv.attack,
                "defense": iv.defense,
                "stamina": iv.stamina,
            },
        }
    ), 200
