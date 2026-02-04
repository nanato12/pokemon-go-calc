"""画像抽出機能のテスト."""

import json
from pathlib import Path

import pytest

from pokemon_go_calc import IV, extract_from_screenshot

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


@pytest.mark.parametrize(
    "case_dir",
    CASE_DIRS,
    ids=[d.name for d in CASE_DIRS],
)
def test_extract_from_screenshot(case_dir: Path) -> None:
    """スクリーンショットからの抽出をテスト."""
    image_path = case_dir / "image.png"
    expected_name, expected_iv = load_expected(case_dir)

    name, iv = extract_from_screenshot(str(image_path))

    assert name == expected_name, f"名前不一致: {name} != {expected_name}"
    assert iv == expected_iv, f"IV不一致: {iv} != {expected_iv}"
