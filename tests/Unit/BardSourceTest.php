<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Tests\Unit;

use BitMx\StatamicToc\Toc\Sources\BardSource;
use PHPUnit\Framework\TestCase;

final class BardSourceTest extends TestCase
{
    public function test_extracts_headings_from_nested_bard_nodes(): void
    {
        $source = new BardSource;

        $bard = [
            [
                'type' => 'set',
                'values' => [
                    [
                        'type' => 'doc',
                        'content' => [
                            [
                                'type' => 'heading',
                                'attrs' => ['level' => 2],
                                'content' => [
                                    ['type' => 'text', 'text' => 'Nested Heading'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $headings = $source->extract($bard, 1, 6);

        self::assertCount(1, $headings);
        self::assertSame('Nested Heading', $headings[0]['text']);
        self::assertSame(2, $headings[0]['level']);
    }
}
