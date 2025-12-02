"""Pokemon GO IV Calculator Library.

Data source: pokemongo-get.com/cpcal/
"""

from pokemon_go_calc.constants import League, get_all_levels, get_cpm
from pokemon_go_calc.models import IV, Pokemon, PokemonStats, RankedIV
from pokemon_go_calc.services import (
    calculate_cp,
    calculate_pre_evolution_cap_cp,
    calculate_stat_product,
    calculate_stats,
    check_evolution_exceeds_cap,
    find_best_iv_for_league,
    get_all_pokemon_names,
    get_iv_rank,
    get_pokemon,
    rank_all_ivs_for_league,
    search_pokemon,
)

__all__ = [
    # Calculator functions
    "calculate_cp",
    "calculate_pre_evolution_cap_cp",
    "calculate_stats",
    "calculate_stat_product",
    "check_evolution_exceeds_cap",
    "find_best_iv_for_league",
    "get_iv_rank",
    "rank_all_ivs_for_league",
    # CPM functions
    "get_cpm",
    "get_all_levels",
    # Pokemon database
    "get_pokemon",
    "search_pokemon",
    "get_all_pokemon_names",
    # Models
    "IV",
    "Pokemon",
    "PokemonStats",
    "RankedIV",
    # Constants
    "League",
]
