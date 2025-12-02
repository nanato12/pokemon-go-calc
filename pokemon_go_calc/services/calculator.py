"""CP・ステータス計算サービス.

計算ロジック: pokemongo-get.com/cpcal/
"""

import math

from pokemon_go_calc.constants.cpm import MAX_LEVEL, get_all_levels, get_cpm
from pokemon_go_calc.constants.league import (
    CP_DIVISOR,
    IV_MAX,
    IV_MIN,
    MIN_CP,
    PERCENTAGE_MULTIPLIER,
    STAT_PRODUCT_DIVISOR,
    League,
)
from pokemon_go_calc.models.iv import IV
from pokemon_go_calc.models.pokemon import Pokemon
from pokemon_go_calc.models.pokemon_stats import PokemonStats
from pokemon_go_calc.models.ranked_iv import RankedIV


def calculate_cp(pokemon: Pokemon, iv: IV, level: float) -> int:
    """CPを計算する.

    計算式: CP = floor((Atk × √Def × √HP × CPM²) / 10)

    Args:
        pokemon: ポケモン種族値
        iv: 個体値
        level: レベル（1.0〜51.0）

    Returns:
        CP（最小10）
    """
    cpm = get_cpm(level)

    attack = pokemon.base_attack + iv.attack
    defense = pokemon.base_defense + iv.defense
    stamina = pokemon.base_stamina + iv.stamina

    cp = int(
        (attack * math.sqrt(defense) * math.sqrt(stamina) * (cpm * cpm))
        / CP_DIVISOR
    )

    if cp < MIN_CP:
        cp = MIN_CP
    return cp


def calculate_stats(pokemon: Pokemon, iv: IV, level: float) -> PokemonStats:
    """ステータスを計算する.

    計算式:
        Attack = (種族値 + 個体値) × CPM
        Defense = (種族値 + 個体値) × CPM
        HP = floor((種族値 + 個体値) × CPM)

    Args:
        pokemon: ポケモン種族値
        iv: 個体値
        level: レベル（1.0〜51.0）

    Returns:
        計算されたステータス
    """
    cpm = get_cpm(level)

    attack = (pokemon.base_attack + iv.attack) * cpm
    defense = (pokemon.base_defense + iv.defense) * cpm
    stamina = int((pokemon.base_stamina + iv.stamina) * cpm)

    cp = calculate_cp(pokemon, iv, level)

    return PokemonStats(
        attack=attack,
        defense=defense,
        stamina=stamina,
        cp=cp,
        level=level,
    )


def calculate_stat_product(stats: PokemonStats) -> float:
    """ステータス積を計算する.

    計算式: Stat Product = Attack × Defense × HP / 1000

    Args:
        stats: ポケモンステータス

    Returns:
        ステータス積
    """
    return stats.attack * stats.defense * stats.stamina / STAT_PRODUCT_DIVISOR


def _find_max_level_for_cp(
    pokemon: Pokemon,
    iv: IV,
    max_cp: int,
    max_level: float = MAX_LEVEL,
) -> float:
    """CP上限以下で最大のレベルを探す.

    Args:
        pokemon: ポケモン種族値
        iv: 個体値
        max_cp: CP上限
        max_level: 最大レベル

    Returns:
        CP上限以下で最大のレベル
    """
    levels = [lvl for lvl in get_all_levels() if lvl <= max_level]

    best_level = levels[0]
    for level in levels:
        cp = calculate_cp(pokemon, iv, level)
        if cp <= max_cp:
            best_level = level
        else:
            break

    return best_level


def rank_all_ivs_for_league(
    pokemon: Pokemon,
    league: League,
    max_level: float = MAX_LEVEL,
) -> list[RankedIV]:
    """全4096通りの個体値をリーグ用にランク付けする.

    Args:
        pokemon: ポケモン種族値
        league: リーグ
        max_level: 最大レベル

    Returns:
        ステータス積順にソートされたランキング
    """
    cp_cap = league.cp_cap

    results: list[tuple[IV, float, PokemonStats, float]] = []

    for atk_iv in range(IV_MIN, IV_MAX + 1):
        for def_iv in range(IV_MIN, IV_MAX + 1):
            for sta_iv in range(IV_MIN, IV_MAX + 1):
                iv = IV(attack=atk_iv, defense=def_iv, stamina=sta_iv)

                if cp_cap is not None:
                    level = _find_max_level_for_cp(
                        pokemon, iv, cp_cap, max_level
                    )
                else:
                    level = max_level

                stats = calculate_stats(pokemon, iv, level)
                stat_product = calculate_stat_product(stats)

                results.append((iv, level, stats, stat_product))

    results.sort(key=lambda x: x[3], reverse=True)

    best_stat_product = results[0][3] if results else 1.0

    rankings: list[RankedIV] = []
    for rank, (iv, level, stats, stat_product) in enumerate(results, 1):
        percent = (stat_product / best_stat_product) * PERCENTAGE_MULTIPLIER
        rankings.append(
            RankedIV(
                rank=rank,
                iv=iv,
                level=level,
                cp=stats.cp,
                stats=stats,
                stat_product=stat_product,
                stat_product_percent=percent,
            )
        )

    return rankings


def get_iv_rank(
    pokemon: Pokemon,
    iv: IV,
    league: League,
    max_level: float = MAX_LEVEL,
) -> RankedIV:
    """指定した個体値の順位を取得する.

    Args:
        pokemon: ポケモン種族値
        iv: 個体値
        league: リーグ
        max_level: 最大レベル

    Returns:
        ランク付きの個体値情報
    """
    rankings = rank_all_ivs_for_league(pokemon, league, max_level)

    for ranked in rankings:
        if ranked.iv == iv:
            return ranked

    raise ValueError(f"IV {iv} not found in rankings")


def find_best_iv_for_league(
    pokemon: Pokemon,
    league: League,
    max_level: float = MAX_LEVEL,
) -> RankedIV:
    """リーグ最適の個体値を探す.

    Args:
        pokemon: ポケモン種族値
        league: リーグ
        max_level: 最大レベル

    Returns:
        最適な個体値情報
    """
    rankings = rank_all_ivs_for_league(pokemon, league, max_level)
    return rankings[0]


def calculate_pre_evolution_cap_cp(
    pre_evolution: Pokemon,
    evolved: Pokemon,
    iv: IV,
    league: League,
    max_level: float = MAX_LEVEL,
) -> tuple[int, float, int]:
    """進化前ポケモンのリーグ上限CPを計算する.

    進化後ポケモンがリーグCP上限ギリギリになるレベルでの
    進化前ポケモンのCPを計算する。
    これにより、捕獲時のCPから進化後にCP上限を超えるかどうかがわかる。

    Args:
        pre_evolution: 進化前ポケモン種族値
        evolved: 進化後ポケモン種族値
        iv: 個体値
        league: リーグ
        max_level: 最大レベル

    Returns:
        (進化前上限CP, レベル, 進化後CP)のタプル
    """
    cp_cap = league.cp_cap

    if cp_cap is not None:
        level = _find_max_level_for_cp(evolved, iv, cp_cap, max_level)
    else:
        level = max_level

    pre_evolution_cp = calculate_cp(pre_evolution, iv, level)
    evolved_cp = calculate_cp(evolved, iv, level)

    return pre_evolution_cp, level, evolved_cp


def check_evolution_exceeds_cap(
    pre_evolution: Pokemon,
    evolved: Pokemon,
    iv: IV,
    current_cp: int,
    league: League,
    max_level: float = MAX_LEVEL,
) -> tuple[bool, float | None, int | None]:
    """現在のCPから進化後にリーグCP上限を超えるか判定する.

    Args:
        pre_evolution: 進化前ポケモン種族値
        evolved: 進化後ポケモン種族値
        iv: 個体値
        current_cp: 現在のCP
        league: リーグ
        max_level: 最大レベル

    Returns:
        (超えるかどうか, 現在のレベル, 進化後CP)のタプル
        現在のCPに該当するレベルが見つからない場合は (False, None, None)
    """
    current_level = _find_level_for_cp(
        pre_evolution, iv, current_cp, max_level
    )
    if current_level is None:
        return False, None, None

    evolved_cp = calculate_cp(evolved, iv, current_level)

    cp_cap = league.cp_cap
    if cp_cap is None:
        return False, current_level, evolved_cp

    exceeds = evolved_cp > cp_cap
    return exceeds, current_level, evolved_cp


def _find_level_for_cp(
    pokemon: Pokemon,
    iv: IV,
    target_cp: int,
    max_level: float = MAX_LEVEL,
) -> float | None:
    """指定CPになるレベルを探す.

    Args:
        pokemon: ポケモン種族値
        iv: 個体値
        target_cp: 目標CP
        max_level: 最大レベル

    Returns:
        該当レベル（見つからない場合はNone）
    """
    levels = [lvl for lvl in get_all_levels() if lvl <= max_level]

    for level in levels:
        cp = calculate_cp(pokemon, iv, level)
        if cp == target_cp:
            return level

    return None
