<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Toc\Sources;

interface SourceExtractor
{
    /**
     * @return array<int, array{text: string, level: int}>
     */
    public function extract(mixed $content, int $minLevel, int $maxLevel): array;
}
