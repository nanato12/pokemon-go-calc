<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Pokemon;
use RuntimeException;

/**
 * ポケモンデータベース.
 *
 * Data source: pokemongo-get.com/cpcal/
 */
final class PokemonDatabase
{
    /** @var null|array<string, Pokemon> */
    private static ?array $cache = null;

    /**
     * 日本語名からポケモンを検索.
     */
    public static function findByName(string $name): ?Pokemon
    {
        $db = self::getDatabase();

        return $db[$name] ?? null;
    }

    /**
     * 図鑑番号からポケモンを検索（最初に見つかったもの）.
     */
    public static function findByDex(int $dex): ?Pokemon
    {
        foreach (self::getDatabase() as $pokemon) {
            if ($pokemon->dex === $dex) {
                return $pokemon;
            }
        }

        return null;
    }

    /**
     * 図鑑番号から全フォームを取得.
     *
     * @return list<Pokemon>
     */
    public static function findAllByDex(int $dex): array
    {
        $results = [];

        foreach (self::getDatabase() as $pokemon) {
            if ($pokemon->dex === $dex) {
                $results[] = $pokemon;
            }
        }

        return $results;
    }

    /**
     * @return array<string, Pokemon>
     */
    private static function getDatabase(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        self::$cache = self::buildDatabase();

        return self::$cache;
    }

    /**
     * @return array<string, Pokemon>
     */
    private static function buildDatabase(): array
    {
        $jsonPath = __DIR__ . '/pokemon_database.json';
        $json = file_get_contents($jsonPath);

        if ($json === false) {
            throw new RuntimeException('Failed to read pokemon_database.json');
        }

        /** @var list<array{name: string, dex: int, baseAttack: int, baseDefense: int, baseStamina: int}> $entries */
        $entries = json_decode($json, true);

        $db = [];

        foreach ($entries as $entry) {
            $db[$entry['name']] = new Pokemon(
                name: $entry['name'],
                dex: $entry['dex'],
                baseAttack: $entry['baseAttack'],
                baseDefense: $entry['baseDefense'],
                baseStamina: $entry['baseStamina'],
            );
        }

        return $db;
    }
}
