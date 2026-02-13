<?php

declare(strict_types=1);

namespace App\Infrastructure\Line\Flex\Ranking;

use App\Domain\IV;
use App\Domain\League;
use App\Domain\RankedIv;
use App\Infrastructure\Line\Flex\BaseFlex;

final class RankingFlex extends BaseFlex
{
    public const ALT_MESSAGE = 'IV ランキング';

    private const POKEMON_IMAGE_PATH = '/images/pokemon/%s';

    private const LEAGUE_ICON_PATH = '/images/league/%d.png';

    private const LEAGUE_ICON_INDEX = [
        'great' => 1,
        'ultra' => 2,
        'master' => 3,
    ];

    /**
     * Build a single bubble with ranking data.
     *
     * @param array<string, null|RankedIv> $leagueRankings
     *
     * @return array<string, mixed>
     */
    public static function buildBubble(string $pokemonName, int $dex, string $image, IV $iv, array $leagueRankings, ?int $cp = null): array
    {
        $bubble = self::get();

        // Inject pokemon image
        /** @var string $appUrl */
        $appUrl = config('app.url', '');
        // @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible
        $bubble['header']['contents'][0]['url'] = $appUrl . sprintf(self::POKEMON_IMAGE_PATH, $image);

        // Inject pokemon name
        // @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible
        $bubble['header']['contents'][1]['contents'][0]['text'] = $pokemonName;

        // Inject IV values
        $ivText = sprintf('攻撃:%d 防御:%d HP:%d', $iv->attack, $iv->defense, $iv->stamina);

        if ($cp !== null) {
            $ivText = sprintf('CP%d | %s', $cp, $ivText);
        }
        // @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible
        $bubble['header']['contents'][1]['contents'][1]['text'] = $ivText;

        // Build new body contents excluding null leagues
        $newContents = [];
        $leagueOrder = [
            'great' => 0,
            'ultra' => 2,
            'master' => 4,
        ];

        foreach ($leagueOrder as $value => $index) {
            $ranked = $leagueRankings[$value] ?? null;

            if ($ranked === null) {
                continue;
            }

            if ($newContents !== []) {
                // separator between leagues
                $newContents[] = ['type' => 'separator', 'margin' => 'md'];
            }
            // Clone and inject the league box from template
            // @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible
            $leagueBox = $bubble['body']['contents'][$index];
            // inject league icon
            $iconIndex = self::LEAGUE_ICON_INDEX[$value];
            // @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible
            $leagueBox['contents'][0]['url'] = $appUrl . sprintf(self::LEAGUE_ICON_PATH, $iconIndex);
            // inject rank text + color
            // @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible
            $leagueBox['contents'][1]['contents'][0]['contents'][0]['text'] = sprintf('%d位', $ranked->rank);
            // @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible
            $leagueBox['contents'][1]['contents'][0]['contents'][0]['color'] = self::rankColor($ranked->rank);
            // @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible
            $leagueBox['contents'][1]['contents'][0]['contents'][1]['text'] = sprintf('CP%d / Lv%s', $ranked->cp, self::formatLevel($ranked->level));
            // @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible
            $leagueBox['contents'][1]['contents'][1]['text'] = sprintf('SP: %.1f (%.1f%%)', $ranked->statProduct, $ranked->statProductPercent);
            $newContents[] = $leagueBox;
        }
        // @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible
        $bubble['body']['contents'] = $newContents;

        return $bubble;
    }

    /**
     * Build a carousel from multiple bubbles.
     *
     * @param array<array<string, mixed>> $bubbles
     *
     * @return array<string, mixed>
     */
    public static function buildCarousel(array $bubbles): array
    {
        return [
            'type' => 'carousel',
            'contents' => $bubbles,
        ];
    }

    /**
     * Get rank color based on rank value.
     */
    private static function rankColor(int $rank): string
    {
        return match (true) {
            $rank === 1 => '#FFD700',
            $rank <= 10 => '#2E7D32',
            $rank <= 100 => '#1565C0',
            default => '#666666',
        };
    }

    /**
     * レベルを整形（整数なら小数点なし）.
     */
    private static function formatLevel(float $level): string
    {
        if ($level === floor($level)) {
            return (string) (int) $level;
        }

        return number_format($level, 1);
    }
}
