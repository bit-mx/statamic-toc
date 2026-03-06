<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Tests\Unit;

use BitMx\StatamicToc\Toc\AnchorGenerator;
use PHPUnit\Framework\TestCase;

final class AnchorGeneratorTest extends TestCase
{
    public function test_generates_unique_anchors_for_duplicates(): void
    {
        $generator = new AnchorGenerator;

        self::assertSame('intro', $generator->generate('Intro'));
        self::assertSame('intro-2', $generator->generate('Intro'));
        self::assertSame('intro-3', $generator->generate('Intro'));
    }
}
