<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Toc\Sources;

use DOMDocument;
use DOMNode;
use DOMXPath;

final class HtmlSource implements SourceExtractor
{
    /**
     * @return array<int, array{text: string, level: int}>
     */
    public function extract(mixed $content, int $minLevel, int $maxLevel): array
    {
        if (! is_string($content) || trim($content) === '') {
            return [];
        }

        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $headings = [];
        $xpath = new DOMXPath($document);
        $query = sprintf('//h%d | //h%d | //h%d | //h%d | //h%d | //h%d', 1, 2, 3, 4, 5, 6);

        foreach ($xpath->query($query) ?: [] as $node) {
            if (! $node instanceof DOMNode) {
                continue;
            }

            $tag = strtolower($node->nodeName);
            $level = (int) substr($tag, 1);

            if ($level < $minLevel || $level > $maxLevel) {
                continue;
            }

            $text = trim($node->textContent ?? '');
            if ($text === '') {
                continue;
            }

            $headings[] = ['text' => $text, 'level' => $level];
        }

        return $headings;
    }
}
