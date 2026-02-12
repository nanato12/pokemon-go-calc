"""画像抽出機能のテスト."""

import json
from pathlib import Path

import pytest

from src.application.usecases.extract_iv_usecase import ExtractIvUseCase
from src.domain.value_objects.iv import IV
from src.infrastructure.image.opencv_image_reader import OpenCvImageReader
from src.infrastructure.ocr.bar_iv_extractor import BarIvExtractor
from src.infrastructure.ocr.tesseract_cp_extractor import (
    TesseractCpExtractor,
)
from src.infrastructure.ocr.tesseract_name_extractor import (
    TesseractNameExtractor,
)

# テストケースディレクトリを動的に取得
FIXTURES_DIR = Path(__file__).parent / "fixtures"
CASE_DIRS = sorted(FIXTURES_DIR.glob("case*"))


def load_expected(case_dir: Path) -> tuple[str, IV]:
    """期待値をJSONから読み込む."""
    json_path = case_dir / "expected.json"
    with open(json_path, encoding="utf-8") as f:
        data = json.load(f)

    return data["pokemon"], IV(
        attack=data["iv"]["attack"],
        defense=data["iv"]["defense"],
        stamina=data["iv"]["stamina"],
    )


def create_usecase() -> ExtractIvUseCase:
    """テスト用ユースケースを生成."""
    return ExtractIvUseCase(
        image_reader=OpenCvImageReader(),
        name_extractor=TesseractNameExtractor(),
        iv_extractor=BarIvExtractor(),
        cp_extractor=TesseractCpExtractor(),
    )


@pytest.mark.parametrize(
    "case_dir",
    CASE_DIRS,
    ids=[d.name for d in CASE_DIRS],
)
def test_extract_from_screenshot(case_dir: Path) -> None:
    """スクリーンショットからの抽出をテスト."""
    image_path = case_dir / "image.png"
    expected_name, expected_iv = load_expected(case_dir)

    usecase = create_usecase()
    result = usecase.execute(str(image_path))

    assert result.pokemon_name == expected_name, (
        f"名前不一致: {result.pokemon_name} != {expected_name}"
    )
    actual_iv = IV(
        attack=result.attack,
        defense=result.defense,
        stamina=result.stamina,
    )
    assert actual_iv == expected_iv, f"IV不一致: {actual_iv} != {expected_iv}"
