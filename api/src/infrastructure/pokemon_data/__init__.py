"""ポケモン名マッピングデータ."""

from src.infrastructure.pokemon_data.pokemon_name_map import (
    PokemonNameEntry,
    find_by_japanese_name,
)

__all__ = ["PokemonNameEntry", "find_by_japanese_name"]
