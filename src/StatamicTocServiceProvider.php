<?php

declare(strict_types=1);

namespace BitMx\StatamicToc;

use BitMx\StatamicToc\Modifiers\Toc as TocModifier;
use BitMx\StatamicToc\Tags\Toc as TocTag;
use BitMx\StatamicToc\Toc\AnchorGenerator;
use BitMx\StatamicToc\Toc\Cache\TocCache;
use BitMx\StatamicToc\Toc\TocService;
use Illuminate\Support\Facades\Blade;
use Statamic\Providers\AddonServiceProvider;

final class StatamicTocServiceProvider extends AddonServiceProvider
{
    /**
     * @var array<int, class-string>
     */
    protected $tags = [
        TocTag::class,
    ];

    /**
     * @var array<int, class-string>
     */
    protected $modifiers = [
        TocModifier::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statamic-toc.php', 'statamic-toc');

        $this->app->singleton(AnchorGenerator::class, static fn (): AnchorGenerator => new AnchorGenerator());
        $this->app->singleton(TocCache::class, static fn (): TocCache => new TocCache());

        $this->app->singleton('statamic-toc', function ($app): TocService {
            return new TocService($app->make(AnchorGenerator::class));
        });

        $this->app->alias('statamic-toc', TocService::class);
    }

    public function bootAddon(): void
    {
        $this->publishes([
            __DIR__.'/../config/statamic-toc.php' => config_path('statamic-toc.php'),
        ], 'statamic-toc-config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'statamic-toc');

        Blade::directive('toc', function (string $expression): string {
            return "<?php echo view('statamic-toc::toc', ['items' => {$expression}])->render(); ?>";
        });
    }
}
