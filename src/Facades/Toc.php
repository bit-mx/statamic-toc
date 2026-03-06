<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Facades;

use Illuminate\Support\Facades\Facade;

final class Toc extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'statamic-toc';
    }
}
