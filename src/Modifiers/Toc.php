<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Modifiers;

use BitMx\StatamicToc\Toc\TocService;
use Illuminate\Contracts\Support\Htmlable;
use Statamic\Modifiers\Modifier;
use Stringable;

final class Toc extends Modifier
{
    /**
     * @param  array<int|string, mixed>  $params
     * @param  array<int|string, mixed>  $context
     */
    public function index(mixed $value, array $params, array $context): string
    {
        /** @var TocService $service */
        $service = app(TocService::class);

        $attributes = $this->extractAttributes($params);
        $minLevel = isset($params[0]) ? $this->toInt($params[0], 1) : $this->toInt(config('statamic-toc.min_level', 1), 1);
        $maxLevel = isset($params[1]) ? $this->toInt($params[1], 6) : $this->toInt(config('statamic-toc.max_level', 6), 6);
        $preserve = isset($params[2]) ? $this->toBool($params[2], true) : $this->toBool(config('statamic-toc.preserve_existing_ids', true), true);

        if ($attributes !== []) {
            $minLevel = $this->toInt(config('statamic-toc.min_level', 1), 1);
            $maxLevel = $this->toInt(config('statamic-toc.max_level', 6), 6);
            $preserve = $this->toBool(config('statamic-toc.preserve_existing_ids', true), true);
        }

        return $service->injectIdsIntoHtml($this->toString($value), $minLevel, $maxLevel, $preserve, $attributes);
    }

    /**
     * @param  array<int|string, mixed>  $params
     * @return array<string, string>
     */
    private function extractAttributes(array $params): array
    {
        $attributes = [];

        foreach ($params as $key => $value) {
            if (! is_string($key) || ! is_scalar($value)) {
                continue;
            }

            $attributes[$key] = $this->toString($value);
        }

        return $attributes;
    }

    private function toInt(mixed $value, int $default): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    private function toBool(mixed $value, bool $default): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value) || is_int($value)) {
            $result = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

            return $result ?? $default;
        }

        return $default;
    }

    private function toString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }

        if ($value instanceof Stringable) {
            return (string) $value;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }
}
