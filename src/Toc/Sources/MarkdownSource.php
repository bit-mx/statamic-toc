<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Toc\Sources;

final class MarkdownSource implements SourceExtractor
{
    /**
     * @return array<int, array{text: string, level: int}>
     */
    public function extract(mixed $content, int $minLevel, int $maxLevel): array
    {
        if (! is_string($content) || trim($content) === '') {
            return [];
        }

        preg_match_all('/^(#{1,6})\s+(.+)$/m', $content, $matches, PREG_SET_ORDER);

        $headings = [];
        foreach ($matches as $match) {
            $level = strlen($match[1]);
            if ($level < $minLevel || $level > $maxLevel) {
                continue;
            }

            $text = trim($match[2]);
            if ($text === '') {
                continue;
            }

            $headings[] = ['text' => $text, 'level' => $level];
        }

        return $headings;
    }
}
