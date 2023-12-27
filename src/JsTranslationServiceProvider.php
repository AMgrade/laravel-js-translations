<?php

declare(strict_types=1);

namespace AMgrade\JsTranslations;

use Illuminate\Support\ServiceProvider;

class JsTranslationServiceProvider extends ServiceProvider
{
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

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/js-translations.php',
            'js-translations',
        );
    }
}
