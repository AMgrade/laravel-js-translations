<?php

declare(strict_types=1);

return [
    'bundles' => [
        // You can declare as many bundles as you wish with different options.
        // For example, when you want to split your translations for admin part
        // and client side part. Then just pass `--bundle=your_bundle_name` to
        // the extract command
        'default' => [
            // Destination where your translations will be stored. You can pass
            // filename with "js" or "json" extension, and it will extract in the
            // preferred format.
            'destination' => public_path('/js/translations.js'),

            // Path where your translations are located. By default, in Laravel 9
            // it is "root project folder" + "/lang".
            'path' => base_path('lang'),

            // If you need to extract translations for "react-i18next", then you
            // need to wrap each locale with "translation" key, so you can just
            // put here "translation".
            'namespace' => null,

            'exclude' => [
                // You can exclude any locale from extraction.
                'locales' => [
                    // 'uk',
                ],

                // Also you can exclude any file from extraction.
                'files' => [
                    // 'validation',
                ],

                // Or you can exclude some extension. For now, Laravel and this
                // package supports only "php" and "json" extensions.
                'extensions' => [
                    // 'json',
                    // 'php',
                ],
            ],
        ],
    ],
];
