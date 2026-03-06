<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Tests\Unit;

use BitMx\StatamicToc\Modifiers\Toc as TocModifier;
use BitMx\StatamicToc\Toc\AnchorGenerator;
use BitMx\StatamicToc\Toc\TocService;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Container\Container;
use Illuminate\Support\HtmlString;
use PHPUnit\Framework\TestCase;

final class TocModifierTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container;
        Container::setInstance($this->container);

        $this->container->instance('config', new ConfigRepository([
            'statamic-toc.min_level' => 1,
            'statamic-toc.max_level' => 6,
            'statamic-toc.preserve_existing_ids' => true,
        ]));
        $this->container->instance(TocService::class, new TocService(new AnchorGenerator));
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);
        parent::tearDown();
    }

    public function test_modifier_keeps_html_string_content_and_injects_ids(): void
    {
        $modifier = new TocModifier;

        $result = $modifier->index(new HtmlString('<h1>Intro</h1>'), [], []);

        self::assertStringContainsString('Intro', $result);
        self::assertStringContainsString('id="intro"', $result);
    }
}
