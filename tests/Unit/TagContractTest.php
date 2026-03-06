<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Tests\Unit;

use BitMx\StatamicToc\Tags\Toc;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class TagContractTest extends TestCase
{
    public function test_index_does_not_use_strict_array_return_type(): void
    {
        $method = new ReflectionMethod(Toc::class, 'index');

        self::assertFalse(
            $method->hasReturnType(),
            'Toc::index() must not declare a strict return type because Statamic may return Collection via OutputsItems.'
        );
    }

    public function test_items_does_not_use_strict_array_return_type(): void
    {
        $method = new ReflectionMethod(Toc::class, 'items');

        self::assertFalse(
            $method->hasReturnType(),
            'Toc::items() must not declare a strict return type because Statamic may return Collection via OutputsItems.'
        );
    }
}
