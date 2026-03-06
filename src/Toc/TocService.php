<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Toc;

use BitMx\StatamicToc\Toc\DTO\Heading;
use BitMx\StatamicToc\Toc\Sources\BardSource;
use BitMx\StatamicToc\Toc\Sources\HtmlSource;
use BitMx\StatamicToc\Toc\Sources\MarkdownSource;
use BitMx\StatamicToc\Toc\Sources\SourceExtractor;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use InvalidArgumentException;

final class TocService
{
    /**
     * @param array<string, SourceExtractor> $extractors
     */
    public function __construct(
        private readonly AnchorGenerator $anchorGenerator,
        private readonly array $extractors = [],
    ) {
    }

    /**
     * @return array<int, Heading>
     */
    public function extract(string $source, mixed $content, int $minLevel = 1, int $maxLevel = 6): array
    {
        [$minLevel, $maxLevel] = $this->normalizeLevels($minLevel, $maxLevel);
        $this->anchorGenerator->reset();

        $rows = $this->extractRows($source, $content, $minLevel, $maxLevel);

        return array_map(
            fn (array $item): Heading => new Heading(
                text: $item['text'],
                level: $item['level'],
                id: $this->anchorGenerator->generate($item['text']),
            ),
            $rows,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function extractAsArray(string $source, mixed $content, int $minLevel = 1, int $maxLevel = 6, bool $tree = true): array
    {
        [$minLevel, $maxLevel] = $this->normalizeLevels($minLevel, $maxLevel);
        $headings = $this->extract($source, $content, $minLevel, $maxLevel);

        if (! $tree) {
            return array_map(static fn (Heading $heading): array => $heading->toArray(false), $headings);
        }

        return array_map(static fn (Heading $heading): array => $heading->toArray(true), $this->toTree($headings));
    }

    /**
     * @param array<int, Heading> $headings
     * @return array<int, Heading>
     */
    public function toTree(array $headings): array
    {
        $result = [];
        $stack = [];

        foreach ($headings as $heading) {
            $node = [
                'text' => $heading->text,
                'level' => $heading->level,
                'id' => $heading->id,
                'children' => [],
            ];

            while (! empty($stack) && end($stack)['level'] >= $node['level']) {
                array_pop($stack);
            }

            if (empty($stack)) {
                $result[] = $node;
                $stack[] = &$result[array_key_last($result)];
                unset($node);
                continue;
            }

            $parent = &$stack[array_key_last($stack)];
            $parent['children'][] = $node;
            $stack[] = &$parent['children'][array_key_last($parent['children'])];
            unset($parent, $node);
        }

        return array_map(fn (array $row): Heading => $this->mapRowToHeading($row), $result);
    }

    /**
     * @param array<string, string> $attributes
     */
    public function injectIdsIntoHtml(string $html, int $minLevel = 1, int $maxLevel = 6, bool $preserveExisting = true, array $attributes = []): string
    {
        [$minLevel, $maxLevel] = $this->normalizeLevels($minLevel, $maxLevel);

        if (trim($html) === '') {
            return $html;
        }

        $this->anchorGenerator->reset();

        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($document);
        $query = sprintf('//h%d | //h%d | //h%d | //h%d | //h%d | //h%d', 1, 2, 3, 4, 5, 6);

        foreach ($xpath->query($query) ?: [] as $node) {
            if (! $node instanceof DOMNode || ! $node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->nodeName);
            $level = (int) substr($tag, 1);
            if ($level < $minLevel || $level > $maxLevel) {
                continue;
            }

            $existingId = $node->attributes?->getNamedItem('id')?->nodeValue;
            if ($preserveExisting && is_string($existingId) && $existingId !== '') {
                continue;
            }

            $node->setAttribute('id', $this->anchorGenerator->generate($node->textContent ?? ''));

            foreach ($attributes as $attribute => $value) {
                if ($attribute === '' || $value === '') {
                    continue;
                }

                $node->setAttribute($attribute, $value);
            }
        }

        return (string) $document->saveHTML();
    }

    /**
     * @return array<int, array{text: string, level: int}>
     */
    private function extractRows(string $source, mixed $content, int $minLevel, int $maxLevel): array
    {
        $extractor = $this->extractors[$source] ?? $this->defaultExtractor($source);

        return $extractor->extract($content, $minLevel, $maxLevel);
    }

    private function defaultExtractor(string $source): SourceExtractor
    {
        return match ($source) {
            'html' => new HtmlSource(),
            'markdown' => new MarkdownSource(),
            'bard' => new BardSource(),
            default => throw new InvalidArgumentException("Unsupported TOC source [{$source}]."),
        };
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function normalizeLevels(int $minLevel, int $maxLevel): array
    {
        $minLevel = max(1, min(6, $minLevel));
        $maxLevel = max(1, min(6, $maxLevel));

        if ($minLevel > $maxLevel) {
            [$minLevel, $maxLevel] = [$maxLevel, $minLevel];
        }

        return [$minLevel, $maxLevel];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRowToHeading(array $row): Heading
    {
        return new Heading(
            text: (string) $row['text'],
            level: (int) $row['level'],
            id: (string) $row['id'],
            children: array_map(fn (array $child): Heading => $this->mapRowToHeading($child), $row['children'] ?? []),
        );
    }
}
