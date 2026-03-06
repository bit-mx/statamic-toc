<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Tags;

use BitMx\StatamicToc\Toc\Cache\TocCache;
use BitMx\StatamicToc\Toc\TocService;
use Illuminate\Support\Collection;
use Statamic\Tags\Concerns\OutputsItems;
use Statamic\Tags\Tags;

final class Toc extends Tags
{
    use OutputsItems;

    protected static $handle = 'toc';

    public function index()
    {
        /** @var TocService $service */
        $service = app(TocService::class);
        /** @var TocCache $cache */
        $cache = app(TocCache::class);

        $source = (string) $this->params->get('source', '');
        $isFlat = $this->params->bool('is_flat', false);

        $depth = (int) $this->params->get('depth', 3);
        $from = (string) $this->params->get('from', 'h1');
        $fromLevel = $this->resolveHeadingLevel($from);

        $minLevel = (int) $this->params->get('min_level', $fromLevel);
        $maxLevel = (int) $this->params->get('max_level', min(6, $minLevel + max(1, $depth) - 1));

        $tree = ! $isFlat;

        $content = $this->params->get('content');
        $field = (string) $this->params->get('field', 'article');

        if ($content === null) {
            $content = $this->context[$field] ?? $this->context['content'] ?? null;
        }

        if ($content === null) {
            return $this->output(collect());
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

        return $this->output(Collection::make($legacy));
    }

    public function count(): int
    {
        $result = $this->index();

        if (isset($result['total_results'])) {
            return (int) $result['total_results'];
        }

        return is_array($result) ? count($result) : 0;
    }

    public function items()
    {
        return $this->index();
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function mapToLegacyShape(array $items): array
    {
        return array_map(function (array $item): array {
            $children = array_map(fn (array $child): array => $this->mapChild($child), $item['children'] ?? []);

            return [
                'toc_title' => (string) ($item['text'] ?? ''),
                'toc_id' => (string) ($item['id'] ?? ''),
                'toc_level' => (int) ($item['level'] ?? 0),
                'url' => (string) ($item['url'] ?? '#'.($item['id'] ?? '')),
                'children' => $children,
                'has_children' => $children !== [],
                'total_children' => count($children),
            ];
        }, $items);
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function mapChild(array $item): array
    {
        $children = array_map(fn (array $child): array => $this->mapChild($child), $item['children'] ?? []);

        return [
            'toc_title' => (string) ($item['text'] ?? ''),
            'toc_id' => (string) ($item['id'] ?? ''),
            'toc_level' => (int) ($item['level'] ?? 0),
            'url' => (string) ($item['url'] ?? '#'.($item['id'] ?? '')),
            'children' => $children,
            'has_children' => $children !== [],
            'total_children' => count($children),
        ];
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

        $string = (string) $content;
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
}
