<?php

declare(strict_types=1);

namespace App\Infrastructure\Line\Flex;

use ReflectionClass;
use RuntimeException;

abstract class BaseFlex
{
    private const COPYRIGHT_NAME = 'GO Pilot';

    /**
     * 継承先のディレクトリ内のflex.jsonを読み込み、フッター付きで返す.
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    public static function get(): array
    {
        $r = new ReflectionClass(static::class);
        $filePath = $r->getFileName();

        if ($filePath === false) {
            throw new RuntimeException(sprintf('Not found %s class file.', $r->getName()));
        }

        $flexContent = static::getFlexContent(dirname($filePath));
        $flexContent['footer'] = self::getFooterContent();

        return $flexContent;
    }

    /**
     * JSONファイルからFlexコンテンツを取得.
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    protected static function getFlexContent(string $dir, string $fileName = 'flex.json'): array
    {
        $fileContent = file_get_contents("{$dir}/{$fileName}");

        if ($fileContent === false) {
            throw new RuntimeException("{$dir}/{$fileName} is not found.");
        }

        $flexContent = json_decode($fileContent, true);

        if (!is_array($flexContent)) {
            throw new RuntimeException("Invalid JSON content in {$dir}/{$fileName}.");
        }

        /** @var array<string, mixed> $flexContent */
        return $flexContent;
    }

    /**
     * フッターコンテンツを取得.
     *
     * @return array<string, mixed>
     */
    private static function getFooterContent(): array
    {
        $footer = self::getFlexContent(__DIR__, 'footer.json');

        /** @var array<string, mixed> $footer */
        /** @var list<array<string, mixed>> $contents */
        $contents = $footer['contents'];
        $contents[0]['text'] = sprintf("\u{00A9} %d %s", (int) date('Y'), self::COPYRIGHT_NAME);
        $footer['contents'] = $contents;

        return $footer;
    }
}
