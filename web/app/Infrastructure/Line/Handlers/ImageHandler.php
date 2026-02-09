<?php

declare(strict_types=1);

namespace App\Infrastructure\Line\Handlers;

use App\Constants\PokemonDatabase;
use App\Domain\IV;
use App\Domain\League;
use App\Domain\RankedIv;
use App\Infrastructure\IvExtractor\IvExtractorClient;
use App\Services\RankingService;
use Exception;
use LINE\Clients\MessagingApi\Api\MessagingApiBlobApi;
use LINE\Webhook\Model\Event;
use LINE\Webhook\Model\ImageMessageContent;
use LINE\Webhook\Model\MessageEvent;
use Phine\Client;
use Phine\Handlers\BaseEventHandler;
use Phine\Helpers\MessageBuilders\TextMessageBuilder;
use SplFileObject;

/**
 * 画像メッセージハンドラ.
 */
final class ImageHandler extends BaseEventHandler
{
    public const EVENT_CLASS = MessageEvent::class;
    public const MESSAGE_TYPE_CLASS = ImageMessageContent::class;

    public function handle(Client $client, Event $event): void
    {
        /** @var MessageEvent $event */
        /** @var ImageMessageContent $message */
        $message = $event->getMessage();

        try {
            /** @var MessagingApiBlobApi $blobApi */
            $blobApi = app(MessagingApiBlobApi::class);

            /** @var IvExtractorClient $ivExtractor */
            $ivExtractor = app(IvExtractorClient::class);

            // 画像をダウンロード
            /** @var SplFileObject $imageFile */
            $imageFile = $blobApi->getMessageContent($message->getId());
            $imageData = $imageFile->fread($imageFile->getSize() ?: 0) ?: '';

            // IV抽出APIを呼び出し
            $result = $ivExtractor->extract($imageData);

            $ivData = $result->getIv();
            $pokemonName = $result->getPokemon() ?? '不明';

            $text = $this->buildBasicInfo($pokemonName, $ivData);
            $text .= $this->buildRankingInfo($pokemonName, $ivData);

            $client->reply([
                new TextMessageBuilder($text),
            ]);
        } catch (Exception $e) {
            $client->reply([
                new TextMessageBuilder("画像の解析に失敗しました\n\nPokemon GOのスクリーンショットを送信してください"),
            ]);
        }
    }

    /**
     * 基本情報テキストを構築.
     *
     * @param object $ivData IV data from API response
     */
    private function buildBasicInfo(string $pokemonName, object $ivData): string
    {
        return sprintf(
            "%s\n\n攻撃: %d / 防御: %d / HP: %d",
            $pokemonName,
            $ivData->getAttack(),
            $ivData->getDefense(),
            $ivData->getStamina(),
        );
    }

    /**
     * ランキング情報テキストを構築.
     *
     * @param object $ivData IV data from API response
     */
    private function buildRankingInfo(string $pokemonName, object $ivData): string
    {
        $pokemon = PokemonDatabase::findByName($pokemonName);

        if ($pokemon === null) {
            return '';
        }

        $iv = new IV(
            attack: $ivData->getAttack(),
            defense: $ivData->getDefense(),
            stamina: $ivData->getStamina(),
        );

        $leagues = [
            League::GREAT,
            League::ULTRA,
        ];

        $text = "\n";

        foreach ($leagues as $league) {
            $ranked = RankingService::getIvRank($pokemon, $iv, $league);

            if ($ranked !== null) {
                $text .= $this->formatLeagueRank($league, $ranked);
            }
        }

        return $text;
    }

    /**
     * リーグランキングを整形.
     */
    private function formatLeagueRank(League $league, RankedIv $ranked): string
    {
        return sprintf(
            "\n【%s】\n順位: %d位 / CP: %d / Lv: %s\nSP: %.1f (%.1f%%)",
            $league->displayName(),
            $ranked->rank,
            $ranked->cp,
            $this->formatLevel($ranked->level),
            $ranked->statProduct,
            $ranked->statProductPercent,
        );
    }

    /**
     * レベルを整形（整数なら小数点なし）.
     */
    private function formatLevel(float $level): string
    {
        if ($level === floor($level)) {
            return (string) (int) $level;
        }

        return number_format($level, 1);
    }
}
