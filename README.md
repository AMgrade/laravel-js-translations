## Laravel JS Translations

Easy way to export Laravel translations to JavaScript.

### Installation

```bash
composer require amgrade/laravel-js-translations
```

Then, if you don't use Laravel package autodiscovery feature, you need to add
`LaravelJsTranslationServiceProvider` to the `config/app.php`.

```php
/*
 * Package Service Providers...
 */
AMgrade\LaravelJsTranslations\LaravelJsTranslationServiceProvider::class,
```

### Configuration

You can declare as many bundles as you wish with different options. 
For example, when you want to split your translations for admin part and client side part.
Then just pass `php artisan js-translations:extract --bundle=admin` and `php artisan js-translations:extract --bundle=client`.
For detailed configurations, please review config file.

### Usage

Just run `php artisan js-translations:extract`.
