# Statamic TOC

A reusable Table of Contents package for Statamic that extracts headings from **HTML**, **Markdown**, and **Bard** content, generates stable anchors, and optionally injects heading IDs into rendered HTML.

## Requirements

- PHP 8.3+
- Laravel 12
- Statamic 6

## Installation

```bash
composer require bit-mx/statamic-toc
```

## Publish Configuration

```bash
php artisan vendor:publish --tag=statamic-toc-config
```

This publishes `config/statamic-toc.php`.

## Configuration

```php
return [
    'min_level' => 1,
    'max_level' => 6,
    'preserve_existing_ids' => true,
    'default_source' => 'html',
    'default_tree' => true,

    'cache' => [
        'enabled' => false,
        'ttl' => 600,
        'store' => null,
        'prefix' => 'statamic_toc',
    ],
];
```

### Key Options

- `min_level`, `max_level`: Heading level range included in TOC.
- `preserve_existing_ids`: Keep existing heading IDs when injecting anchors.
- `cache.enabled`: Enable caching for tag output.
- `cache.ttl`: Cache lifetime in seconds.

## Usage

### Blade Tag Compatibility (`<s:toc>`)

This package is compatible with Statamic Blade tag usage like:

```blade
<s:toc :content="$content" :is_flat="false" depth="3" from="h1">
    @isset($toc_id)
        <a href="#{{ $toc_id }}">{{ $toc_title }}</a>

        @if($has_children)
            @foreach($children as $child)
                <a href="#{{ $child['toc_id'] }}">{{ $child['toc_title'] }}</a>
            @endforeach
        @endif
    @endisset
</s:toc>
```

Supported legacy params:

- `content`
- `field` (default: `article`)
- `depth` (default: `3`)
- `from` (default: `h1`)
- `is_flat` (default: `false`)

Legacy output keys are provided for drop-in compatibility:

- `toc_title`
- `toc_id`
- `toc_level`
- `children`
- `has_children`
- `total_children`
- `total_results`
- `no_results`

## Tag Usage (Antlers / Blade context)

Use the `toc` tag to extract TOC items.

```antlers
{{ toc:items source="html" :content="content" min_level="2" max_level="4" }}
  <li><a href="#{{ id }}">{{ text }}</a></li>
{{ /toc:items }}
```

Parameters:

- `source`: `html`, `markdown`, or `bard`.
- `content`: The input payload to parse.
- `min_level` / `max_level`: Optional level limits.
- `tree`: `true`/`false` to return hierarchical or flat output.

## Modifier Usage

Inject heading IDs into rendered HTML:

```antlers
{{ content | toc }}
```

Optional params:

```antlers
{{ content | toc:2:4:true }}
```

Meaning:

1. min level
2. max level
3. preserve existing IDs

## Service / Facade Usage (PHP)

```php
use BitMx\StatamicToc\Facades\Toc;

$items = Toc::extractAsArray('markdown', $markdown, 1, 6, true);
```

Or via DI:

```php
use BitMx\StatamicToc\Toc\TocService;

public function show(TocService $tocService): array
{
    return $tocService->extractAsArray('html', $html, 2, 4, true);
}
```

## Output Shape

Each item includes:

- `text`
- `level`
- `id`
- `url` (for example, `#installation`)
- `children` (when `tree=true`)

## Bard Integration Notes

The Bard extractor walks nested node structures and set values, so headings inside nested/complex Bard content are included.

## Caching

When `cache.enabled=true`, tag results are cached using a key that includes source, content, level range, and tree mode.

## Migration from In-App TOC Logic

If you already have custom parser/tag/modifier classes in your project:

1. Install this package.
2. Replace custom TOC tag calls with package tag usage.
3. Replace custom heading-id injection with `| toc` modifier.
4. Keep your front-end template markup, only swap data source.
5. Remove legacy TOC classes after parity verification.

## Troubleshooting

### IDs are not added to headings

- Ensure content passed to modifier is rendered HTML.
- Confirm heading levels are within configured min/max.

### Duplicate anchors

- Duplicate heading text is expected to produce suffixes (`-2`, `-3`, ...).

### Missing Bard headings

- Verify the field content passed to source `bard` is raw Bard data (array structure).

### Cache not updating

- Disable cache during development.
- Check cache store and TTL settings.

## Testing

```bash
composer test
```

## License

MIT
