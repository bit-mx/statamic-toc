<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Toc;

use Illuminate\Support\Str;

final class AnchorGenerator
{
    /**
     * @var array<string, int>
     */
    private array $seen = [];

    public function reset(): void
    {
        $this->seen = [];
    }

    public function generate(string $text): string
    {
        $base = Str::slug(strip_tags($text));

        if ($base === '') {
            $base = 'section';
        }

        $count = ($this->seen[$base] ?? 0) + 1;
        $this->seen[$base] = $count;

        if ($count === 1) {
            return $base;
        }

        return $base.'-'.$count;
    }
}
