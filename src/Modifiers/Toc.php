<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Modifiers;

use BitMx\StatamicToc\Toc\TocService;
use Statamic\Modifiers\Modifier;

final class Toc extends Modifier
{
    public function index($value, $params, $context)
    {
        /** @var TocService $service */
        $service = app(TocService::class);

        $attributes = $this->extractAttributes($params);
        $minLevel = isset($params[0]) ? (int) $params[0] : (int) config('statamic-toc.min_level', 1);
        $maxLevel = isset($params[1]) ? (int) $params[1] : (int) config('statamic-toc.max_level', 6);
        $preserve = isset($params[2]) ? filter_var((string) $params[2], FILTER_VALIDATE_BOOL) : (bool) config('statamic-toc.preserve_existing_ids', true);

        if ($attributes !== []) {
            $minLevel = (int) config('statamic-toc.min_level', 1);
            $maxLevel = (int) config('statamic-toc.max_level', 6);
            $preserve = (bool) config('statamic-toc.preserve_existing_ids', true);
        }

        return $service->injectIdsIntoHtml((string) $value, $minLevel, $maxLevel, $preserve, $attributes);
    }

    /**
     * @param array<int|string, mixed> $params
     * @return array<string, string>
     */
    private function extractAttributes(array $params): array
    {
        $attributes = [];

        foreach ($params as $key => $value) {
            if (! is_string($key) || is_array($value) || is_object($value) || $value === null) {
                continue;
            }

            $attributes[$key] = (string) $value;
        }

        return $attributes;
    }
}
