"""ポケモン名マッピングのユニットテスト."""

from src.infrastructure.pokemon_data.pokemon_name_map import (
    PokemonNameEntry,
    find_by_japanese_name,
)


class TestFindByJapaneseName:
    """find_by_japanese_nameのテスト."""

    def test_find_pikachu(self) -> None:
        """ピカチュウを検索."""
        result = find_by_japanese_name("ピカチュウ")
        assert result is not None
        assert result.name_ja == "ピカチュウ"
        assert result.name_en == "Pikachu"
        assert result.dex == 25

    def test_find_snorlax(self) -> None:
        """カビゴンを検索."""
        result = find_by_japanese_name("カビゴン")
        assert result is not None
        assert result.name_ja == "カビゴン"
        assert result.name_en == "Snorlax"
        assert result.dex == 143

    def test_find_mewtwo(self) -> None:
        """ミュウツーを検索."""
        result = find_by_japanese_name("ミュウツー")
        assert result is not None
        assert result.name_en == "Mewtwo"
        assert result.dex == 150

    def test_find_dedenne(self) -> None:
        """デデンネを検索."""
        result = find_by_japanese_name("デデンネ")
        assert result is not None
        assert result.name_en == "Dedenne"
        assert result.dex == 702

    def test_find_azumarill(self) -> None:
        """マリルリを検索."""
        result = find_by_japanese_name("マリルリ")
        assert result is not None
        assert result.name_en == "Azumarill"
        assert result.dex == 184

    def test_find_bulbasaur(self) -> None:
        """フシギダネ（図鑑番号1）を検索."""
        result = find_by_japanese_name("フシギダネ")
        assert result is not None
        assert result.name_en == "Bulbasaur"
        assert result.dex == 1

    def test_not_found_returns_none(self) -> None:
        """存在しない名前はNoneを返す."""
        result = find_by_japanese_name("存在しないポケモン")
        assert result is None

    def test_empty_string_returns_none(self) -> None:
        """空文字はNoneを返す."""
        result = find_by_japanese_name("")
        assert result is None

    def test_regional_form_alolan_raichu(self) -> None:
        """アローラライチュウ（リージョンフォーム）."""
        result = find_by_japanese_name("ライチュウ(アローラ)")
        assert result is not None
        assert result.name_en == "Raichu (Alolan)"
        assert result.dex == 26

    def test_regional_form_galarian_ponyta(self) -> None:
        """ガラルポニータ."""
        result = find_by_japanese_name("ポニータ(ガラル)")
        assert result is not None
        assert result.name_en == "Ponyta (Galarian)"
        assert result.dex == 77

    def test_hisuian_form(self) -> None:
        """ヒスイフォーム."""
        result = find_by_japanese_name("ガーディ(ヒスイ)")
        assert result is not None
        assert result.name_en == "Growlithe (Hisuian)"
        assert result.dex == 58

    def test_gigantamax_form(self) -> None:
        """キョダイマックス."""
        result = find_by_japanese_name("カビゴン(キョダイ)")
        assert result is not None
        assert result.name_en == "Snorlax (Gigantamax)"
        assert result.dex == 143

    def test_gen9_pokemon(self) -> None:
        """第9世代ポケモン."""
        result = find_by_japanese_name("ニャオハ")
        assert result is not None
        assert result.name_en == "Sprigatito"
        assert result.dex == 906

    def test_latest_gen9_pokemon(self) -> None:
        """第9世代の最新ポケモン."""
        result = find_by_japanese_name("モモワロウ")
        assert result is not None
        assert result.name_en == "Pecharunt"
        assert result.dex == 1025


class TestPokemonNameEntry:
    """PokemonNameEntryのテスト."""

    def test_frozen_dataclass(self) -> None:
        """frozen dataclassであることの確認."""
        entry = PokemonNameEntry(
            name_ja="ピカチュウ",
            name_en="Pikachu",
            dex=25,
        )
        assert entry.name_ja == "ピカチュウ"
        assert entry.name_en == "Pikachu"
        assert entry.dex == 25

    def test_immutable(self) -> None:
        """不変であることの確認."""
        entry = PokemonNameEntry(
            name_ja="ピカチュウ",
            name_en="Pikachu",
            dex=25,
        )
        try:
            entry.name_ja = "ライチュウ"  # type: ignore[misc]
            raise AssertionError("Should be frozen")
        except AttributeError:
            pass

    def test_equality(self) -> None:
        """等価性テスト."""
        e1 = PokemonNameEntry(
            name_ja="ピカチュウ",
            name_en="Pikachu",
            dex=25,
        )
        e2 = PokemonNameEntry(
            name_ja="ピカチュウ",
            name_en="Pikachu",
            dex=25,
        )
        assert e1 == e2
