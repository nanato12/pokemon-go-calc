<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Pokemon;
use RuntimeException;

/**
 * 進化チェーンデータベース.
 *
 * Data source: pokemongo-get.com/cpcal/
 */
final class EvolutionDatabase
{
    /** @var null|array<string, list<string>> */
    private static ?array $cache = null;

    /**
     * 指定ポケモンの全前方進化先を再帰的に取得.
     *
     * @return Pokemon[]
     */
    public static function getForwardEvolutions(
        string $pokemonName,
    ): array {
        /** @var Pokemon[] $result */
        $result = [];
        self::collectEvolutions($pokemonName, $result);

        return $result;
    }

    /**
     * @param Pokemon[] $result
     */
    private static function collectEvolutions(
        string $name,
        array &$result,
    ): void {
        $evolutions = self::getDatabase();
        $directEvolutions = $evolutions[$name] ?? [];

        foreach ($directEvolutions as $evoName) {
            $pokemon = PokemonDatabase::findByName($evoName);

            if ($pokemon !== null) {
                $result[] = $pokemon;
                self::collectEvolutions($evoName, $result);
            }
        }
    }

    /**
     * @return array<string, list<string>>
     */
    private static function getDatabase(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $jsonPath = __DIR__ . '/evolution_database.json';
        $json = file_get_contents($jsonPath);

        if ($json === false) {
            throw new RuntimeException('Failed to read evolution_database.json');
        }

        /** @var array<string, list<string>> $data */
        $data = json_decode($json, true);

        self::$cache = $data;

        return self::$cache;
    }
}
