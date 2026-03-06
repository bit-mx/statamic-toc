<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Tags;

use BitMx\StatamicToc\Toc\Cache\TocCache;
use BitMx\StatamicToc\Toc\TocService;
use Statamic\Tags\Concerns\OutputsItems;
use Statamic\Tags\Tags;

final class Toc extends Tags
{
    use OutputsItems;

    /** @var string */
    protected static $handle = 'toc';

    public function index(): mixed
    {
        /** @var TocService $service */
        $service = app(TocService::class);
        /** @var TocCache $cache */
        $cache = app(TocCache::class);

        $source = $this->toString($this->params->get('source', ''));
        $isFlat = $this->params->bool('is_flat', false);

        $depth = $this->toInt($this->params->get('depth', 3), 3);
        $from = $this->toString($this->params->get('from', 'h1'));
        $fromLevel = $this->resolveHeadingLevel($from);

        $minLevel = $this->toInt($this->params->get('min_level', $fromLevel), $fromLevel);
        $maxLevel = $this->toInt($this->params->get('max_level', min(6, $minLevel + max(1, $depth) - 1)), min(6, $minLevel + max(1, $depth) - 1));

        $tree = ! $isFlat;

        $content = $this->params->get('content');
        $field = $this->toString($this->params->get('field', 'article'));

        if ($content === null) {
            $content = $this->context[$field] ?? $this->context['content'] ?? null;
        }

        if ($content === null) {
            return $this->output([]);
        }

        $resolvedContent = $this->resolveContentValue($content);

        if ($source === '') {
            $source = $this->detectSource($resolvedContent);
        }

        $compute = function () use ($service, $source, $resolvedContent, $minLevel, $maxLevel, $tree): array {
            return $service->extractAsArray($source, $resolvedContent, $minLevel, $maxLevel, $tree);
        };

        $items = $cache->isEnabled()
            ? $cache->remember($cache->buildKey($source, $resolvedContent, $minLevel, $maxLevel, $tree), $cache->ttl(), $compute)
            : $compute();

        $legacy = $this->mapToLegacyShape($items);

        return $this->output($legacy);
    }

    public function count(): int
    {
        $result = $this->index();

        if (is_array($result) && array_key_exists('total_results', $result) && is_numeric($result['total_results'])) {
            return (int) $result['total_results'];
        }

        return is_array($result) ? count($result) : 0;
    }

    public function items(): mixed
    {
        return $this->index();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function mapToLegacyShape(array $items): array
    {
        return array_map(function (array $item): array {
            $children = $this->mapChildren($item['children'] ?? []);
            $id = $this->toString($item['id'] ?? '');

            return [
                'toc_title' => $this->toString($item['text'] ?? ''),
                'toc_id' => $id,
                'toc_level' => $this->toInt($item['level'] ?? 0, 0),
                'url' => $this->toString($item['url'] ?? '#'.$id),
                'children' => $children,
                'has_children' => $children !== [],
                'total_children' => count($children),
            ];
        }, $items);
    }

    /**
     * @param  array<mixed, mixed>  $item
     * @return array<string, mixed>
     */
    private function mapChild(array $item): array
    {
        $children = $this->mapChildren($item['children'] ?? []);
        $id = $this->toString($item['id'] ?? '');

        return [
            'toc_title' => $this->toString($item['text'] ?? ''),
            'toc_id' => $id,
            'toc_level' => $this->toInt($item['level'] ?? 0, 0),
            'url' => $this->toString($item['url'] ?? '#'.$id),
            'children' => $children,
            'has_children' => $children !== [],
            'total_children' => count($children),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapChildren(mixed $children): array
    {
        if (! is_array($children)) {
            return [];
        }

        $mapped = [];
        foreach ($children as $child) {
            if (! is_array($child)) {
                continue;
            }

            $mapped[] = $this->mapChild($child);
        }

        return $mapped;
    }

    private function resolveHeadingLevel(string $value): int
    {
        if (preg_match('/h([1-6])/i', $value, $matches) === 1) {
            return (int) $matches[1];
        }

        return max(1, min(6, (int) $value));
    }

    private function detectSource(mixed $content): string
    {
        if (is_array($content)) {
            return 'bard';
        }

        $string = $this->toString($content);
        if ($string !== '' && str_contains($string, '<h')) {
            return 'html';
        }

        return 'markdown';
    }

    private function resolveContentValue(mixed $content): mixed
    {
        if (is_object($content) && method_exists($content, 'raw')) {
            return $content->raw();
        }

        return $content;
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

    private function toString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }
}
