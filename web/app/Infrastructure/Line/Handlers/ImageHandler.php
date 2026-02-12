<?php

declare(strict_types=1);

namespace App\Infrastructure\Line\Handlers;

use App\Constants\EvolutionDatabase;
use App\Constants\PokemonDatabase;
use App\Domain\IV;
use App\Domain\League;
use App\Infrastructure\IvExtractor\IvExtractorClient;
use App\Infrastructure\Line\Flex\Ranking\RankingFlex;
use App\Services\RankingService;
use Exception;
use LINE\Clients\MessagingApi\Api\MessagingApiBlobApi;
use LINE\Webhook\Model\Event;
use LINE\Webhook\Model\ImageMessageContent;
use LINE\Webhook\Model\MessageEvent;
use Phine\Client;
use Phine\Handlers\BaseEventHandler;
use Phine\MessageBuilders\RawFlexMessageBuilder;
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
            $start = microtime(true);

            /** @var MessagingApiBlobApi $blobApi */
            $blobApi = app(MessagingApiBlobApi::class);

            /** @var IvExtractorClient $ivExtractor */
            $ivExtractor = app(IvExtractorClient::class);

            // 画像をダウンロード
            /** @var SplFileObject $imageFile */
            $imageFile = $blobApi->getMessageContent($message->getId());
            $imageData = $imageFile->fread($imageFile->getSize() ?: 0) ?: '';
            error_log(sprintf('[ImageHandler] 画像DL: %.2fs', microtime(true) - $start));

            // IV抽出APIを呼び出し
            $t = microtime(true);
            $result = $ivExtractor->extract($imageData);
            error_log(sprintf('[ImageHandler] IV抽出API: %.2fs', microtime(true) - $t));

            $ivData = $result->getIv();
            $pokemonName = $result->getPokemon() ?? '不明';
            $cp = $result->getCp();

            $t = microtime(true);

            // メインポケモンのランキング計算
            $iv = new IV(
                attack: $ivData->getAttack(),
                defense: $ivData->getDefense(),
                stamina: $ivData->getStamina(),
            );

            $pokemon = PokemonDatabase::findByName($pokemonName);
            $dex = $pokemon !== null ? $pokemon->dex : 0;

            $leagueRankings = [];
            $leagues = [League::GREAT, League::ULTRA, League::MASTER];

            if ($pokemon !== null) {
                foreach ($leagues as $league) {
                    $leagueRankings[$league->value] = RankingService::getIvRank($pokemon, $iv, $league);
                }
            }

            // メインポケモンのbubble
            $mainBubble = RankingFlex::buildBubble($pokemonName, $dex, $iv, $leagueRankings, $cp);

            // 進化先のBubble（最終進化から表示するため逆順）
            $evoBubbles = [];

            if ($pokemon !== null) {
                $evolutions = EvolutionDatabase::getForwardEvolutions($pokemonName);

                foreach ($evolutions as $evolution) {
                    $evoRankings = [];

                    foreach ($leagues as $league) {
                        $evoRankings[$league->value] = RankingService::getIvRank($evolution, $iv, $league);
                    }
                    $evoBubbles[] = RankingFlex::buildBubble($evolution->name, $evolution->dex, $iv, $evoRankings);
                }
            }

            // 最終進化先を先頭に（逆順）、スキャン元を最後に
            $bubbles = [...array_reverse($evoBubbles), $mainBubble];

            error_log(sprintf('[ImageHandler] ランキング計算 + Flex構築: %.2fs', microtime(true) - $t));

            $t = microtime(true);

            // Flex Message送信
            $flexContents = count($bubbles) === 1 ? $bubbles[0] : RankingFlex::buildCarousel($bubbles);
            $client->reply([
                new RawFlexMessageBuilder($flexContents, RankingFlex::ALT_MESSAGE),
            ]);

            error_log(sprintf('[ImageHandler] LINE返信: %.2fs', microtime(true) - $t));
            error_log(sprintf('[ImageHandler] 合計: %.2fs', microtime(true) - $start));
        } catch (Exception $e) {
            error_log(sprintf('[ImageHandler] エラー: %s', $e->getMessage()));
            $client->reply([
                new RawFlexMessageBuilder(
                    [
                        'type' => 'bubble',
                        'body' => [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => "画像の解析に失敗しました\n\nPokemon GOのスクリーンショットを送信してください",
                                    'wrap' => true,
                                ],
                            ],
                        ],
                    ],
                    'エラー'
                ),
            ]);
        }
    }
}
