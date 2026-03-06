<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Toc\Sources;

use Illuminate\Support\Arr;

final class BardSource implements SourceExtractor
{
    /**
     * @return array<int, array{text: string, level: int}>
     */
    public function extract(mixed $content, int $minLevel, int $maxLevel): array
    {
        if (! is_array($content)) {
            return [];
        }

        $headings = [];
        $this->walkNodes($content, $headings, $minLevel, $maxLevel);

        return $headings;
    }

    /**
     * @param array<int|string, mixed> $nodes
     * @param array<int, array{text: string, level: int}> $headings
     */
    private function walkNodes(array $nodes, array &$headings, int $minLevel, int $maxLevel): void
    {
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            if (! array_key_exists('type', $node)) {
                $this->walkNodes($node, $headings, $minLevel, $maxLevel);
                continue;
            }

            $type = Arr::get($node, 'type');
            if ($type === 'heading') {
                $level = (int) Arr::get($node, 'attrs.level', 2);
                if ($level >= $minLevel && $level <= $maxLevel) {
                    $text = trim($this->extractText($node));
                    if ($text !== '') {
                        $headings[] = [
                            'text' => $text,
                            'level' => $level,
                        ];
                    }
                }
            }

            $content = Arr::get($node, 'content');
            if (is_array($content)) {
                $this->walkNodes($content, $headings, $minLevel, $maxLevel);
            }

            $values = Arr::get($node, 'values');
            if (is_array($values)) {
                $this->walkNodes($values, $headings, $minLevel, $maxLevel);
            }
        }
    }

    /**
     * @param array<int|string, mixed> $node
     */
    private function extractText(array $node): string
    {
        $parts = [];

        $walker = static function (array $items) use (&$walker, &$parts): void {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                if (isset($item['text']) && is_string($item['text'])) {
                    $parts[] = $item['text'];
                }

                if (isset($item['content']) && is_array($item['content'])) {
                    $walker($item['content']);
                }
            }
        };

        if (isset($node['content']) && is_array($node['content'])) {
            $walker($node['content']);
        }

        return trim(implode(' ', $parts));
    }
}
