"""IV抽出ユースケースのユニットテスト（英語名・dex統合）."""

from typing import Any

from src.application.dto.extract_iv_result import ExtractIvResult
from src.application.ports.image_reader import ImageReader
from src.application.ports.iv_extractor import IvExtractor
from src.application.ports.pokemon_name_extractor import (
    PokemonNameExtractor,
)
from src.application.usecases.extract_iv_usecase import (
    ExtractIvUseCase,
)
from src.domain.value_objects.iv import IV


class StubImageReader(ImageReader):
    """テスト用画像リーダー."""

    def read(self, image_path: str) -> Any:
        """ダミー画像を返す."""
        return "dummy_image"


class StubNameExtractor(PokemonNameExtractor):
    """テスト用ポケモン名抽出."""

    def __init__(self, name: str | None) -> None:
        """Initialize."""
        self._name = name

    def extract(self, image: Any) -> str | None:
        """固定名を返す."""
        return self._name


class StubIvExtractor(IvExtractor):
    """テスト用IV抽出."""

    def __init__(self, iv: IV) -> None:
        """Initialize."""
        self._iv = iv

    def extract(self, image: Any) -> IV:
        """固定IVを返す."""
        return self._iv


def _create_usecase(
    name: str | None,
    iv: IV,
) -> ExtractIvUseCase:
    """テスト用ユースケースを生成."""
    return ExtractIvUseCase(
        image_reader=StubImageReader(),
        name_extractor=StubNameExtractor(name),
        iv_extractor=StubIvExtractor(iv),
    )


class TestExtractIvUseCaseWithNameMapping:
    """英語名・dexマッピング統合テスト."""

    def test_pikachu_has_english_name_and_dex(
        self,
    ) -> None:
        """ピカチュウで英語名とdexが返る."""
        iv = IV(attack=15, defense=15, stamina=15)
        usecase = _create_usecase("ピカチュウ", iv)
        result = usecase.execute("dummy.png")

        assert result.pokemon_name == "ピカチュウ"
        assert result.pokemon_name_en == "Pikachu"
        assert result.dex == 25
        assert result.attack == 15
        assert result.defense == 15
        assert result.stamina == 15

    def test_snorlax_has_english_name_and_dex(
        self,
    ) -> None:
        """カビゴンで英語名とdexが返る."""
        iv = IV(attack=10, defense=12, stamina=14)
        usecase = _create_usecase("カビゴン", iv)
        result = usecase.execute("dummy.png")

        assert result.pokemon_name == "カビゴン"
        assert result.pokemon_name_en == "Snorlax"
        assert result.dex == 143

    def test_unknown_pokemon_has_none_fields(
        self,
    ) -> None:
        """マッピングにないポケモンはNone."""
        iv = IV(attack=5, defense=5, stamina=5)
        usecase = _create_usecase("未知のポケモン", iv)
        result = usecase.execute("dummy.png")

        assert result.pokemon_name == "未知のポケモン"
        assert result.pokemon_name_en is None
        assert result.dex is None

    def test_name_none_has_none_fields(self) -> None:
        """名前がNoneの場合もNone."""
        iv = IV(attack=0, defense=0, stamina=0)
        usecase = _create_usecase(None, iv)
        result = usecase.execute("dummy.png")

        assert result.pokemon_name is None
        assert result.pokemon_name_en is None
        assert result.dex is None

    def test_regional_form_mapping(self) -> None:
        """リージョンフォームのマッピング."""
        iv = IV(attack=8, defense=12, stamina=10)
        usecase = _create_usecase("ライチュウ(アローラ)", iv)
        result = usecase.execute("dummy.png")

        assert result.pokemon_name == "ライチュウ(アローラ)"
        assert result.pokemon_name_en == "Raichu (Alolan)"
        assert result.dex == 26

    def test_iv_values_always_passed_through(
        self,
    ) -> None:
        """IVは名前に関係なく常に返る."""
        iv = IV(attack=1, defense=2, stamina=3)
        usecase = _create_usecase(None, iv)
        result = usecase.execute("dummy.png")

        assert result.attack == 1
        assert result.defense == 2
        assert result.stamina == 3


class TestExtractIvResult:
    """ExtractIvResult DTOのテスト."""

    def test_all_fields_present(self) -> None:
        """全フィールドが存在する."""
        result = ExtractIvResult(
            pokemon_name="ピカチュウ",
            pokemon_name_en="Pikachu",
            dex=25,
            attack=15,
            defense=15,
            stamina=15,
        )
        assert result.pokemon_name == "ピカチュウ"
        assert result.pokemon_name_en == "Pikachu"
        assert result.dex == 25
        assert result.attack == 15
        assert result.defense == 15
        assert result.stamina == 15

    def test_nullable_fields(self) -> None:
        """Nullable フィールド."""
        result = ExtractIvResult(
            pokemon_name=None,
            pokemon_name_en=None,
            dex=None,
            attack=0,
            defense=0,
            stamina=0,
        )
        assert result.pokemon_name is None
        assert result.pokemon_name_en is None
        assert result.dex is None

    def test_frozen(self) -> None:
        """不変であることの確認."""
        result = ExtractIvResult(
            pokemon_name="test",
            pokemon_name_en="test",
            dex=1,
            attack=0,
            defense=0,
            stamina=0,
        )
        try:
            result.attack = 10  # type: ignore[misc]
            raise AssertionError("Should be frozen")
        except AttributeError:
            pass
