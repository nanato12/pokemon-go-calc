<?php

declare(strict_types=1);

namespace PokemonGoCalc\LineBot\Application\Ports;

use PokemonGoCalc\LineBot\Domain\Entities\ExtractResult;

/**
 * IV抽出サービスのインターフェース.
 */
interface IvExtractorInterface
{
    /**
     * 画像からIVを抽出する.
     *
     * @param string $imageData Base64エンコードされた画像データ
     * @return ExtractResult 抽出結果
     * @throws \RuntimeException 抽出に失敗した場合
     */
    public function extract(string $imageData): ExtractResult;
}
