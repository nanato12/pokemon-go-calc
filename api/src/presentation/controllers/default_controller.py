"""APIエンドポイントの実装."""

import tempfile
from pathlib import Path

from werkzeug.datastructures import FileStorage

from src.application.usecases.extract_iv_usecase import ExtractIvUseCase
from src.infrastructure.image.opencv_image_reader import OpenCvImageReader
from src.infrastructure.ocr.bar_iv_extractor import BarIvExtractor
from src.infrastructure.ocr.tesseract_name_extractor import (
    TesseractNameExtractor,
)


def _create_extract_iv_usecase() -> ExtractIvUseCase:
    """ユースケースのファクトリ."""
    return ExtractIvUseCase(
        image_reader=OpenCvImageReader(),
        name_extractor=TesseractNameExtractor(),
        iv_extractor=BarIvExtractor(),
    )


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
        usecase = _create_extract_iv_usecase()
        result = usecase.execute(tmp.name)

    return {
        "pokemon": result.pokemon_name,
        "pokemon_en": result.pokemon_name_en,
        "dex": result.dex,
        "iv": {
            "attack": result.attack,
            "defense": result.defense,
            "stamina": result.stamina,
        },
    }, 200
