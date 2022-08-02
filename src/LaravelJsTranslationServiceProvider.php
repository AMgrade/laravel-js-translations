<?php

declare(strict_types=1);

namespace AMgrade\LaravelJsTranslations;

use Illuminate\Support\ServiceProvider;

/**
 * Class LaravelJsTranslationServiceProvider
 *
 * @package AMgrade\LaravelJsTranslations
 */
class LaravelJsTranslationServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/js-translations.php' => $this->app->configPath('js-translations.php'),
            ], 'config');

            $this->commands([
                Console\Commands\ExtractCommand::class,
            ]);
        }
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/js-translations.php',
            'js-translations',
        );
    }
}
