<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Tests\Unit;

use BitMx\StatamicToc\Tags\Toc;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class TagContractTest extends TestCase
{
    public function test_index_uses_mixed_return_type(): void
    {
        $method = new ReflectionMethod(Toc::class, 'index');

        self::assertTrue($method->hasReturnType());
        self::assertSame('mixed', (string) $method->getReturnType());
    }

    public function test_items_uses_mixed_return_type(): void
    {
        $method = new ReflectionMethod(Toc::class, 'items');

        self::assertTrue($method->hasReturnType());
        self::assertSame('mixed', (string) $method->getReturnType());
    }
}
