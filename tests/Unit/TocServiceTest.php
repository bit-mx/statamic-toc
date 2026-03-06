<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Tests\Unit;

use BitMx\StatamicToc\Toc\AnchorGenerator;
use BitMx\StatamicToc\Toc\TocService;
use PHPUnit\Framework\TestCase;

final class TocServiceTest extends TestCase
{
    public function test_extracts_markdown_headings(): void
    {
        $service = new TocService(new AnchorGenerator);

        $items = $service->extractAsArray('markdown', "# Intro\n\n## Setup\n\n## Usage", 1, 6, false);

        self::assertCount(3, $items);
        self::assertSame('intro', $items[0]['id']);
        self::assertSame('setup', $items[1]['id']);
        self::assertSame(2, $items[1]['level']);
    }

    public function test_builds_tree_structure(): void
    {
        $service = new TocService(new AnchorGenerator);

        $items = $service->extractAsArray('markdown', "# Intro\n\n## Setup\n\n### Install\n\n## Usage", 1, 6, true);

        self::assertCount(1, $items);
        self::assertSame('Intro', $items[0]['text']);
        self::assertCount(2, $items[0]['children']);
        self::assertCount(1, $items[0]['children'][0]['children']);
    }

    public function test_injects_ids_into_html(): void
    {
        $service = new TocService(new AnchorGenerator);

        $html = '<h1>Intro</h1><h2>Setup</h2>';
        $result = $service->injectIdsIntoHtml($html);

        self::assertStringContainsString('id="intro"', $result);
        self::assertStringContainsString('id="setup"', $result);
    }
}
