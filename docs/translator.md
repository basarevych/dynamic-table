ZF2 Translator
==============

Configuration
-------------
Translator configuration belong to module's config (**module/ModuleName/config/module.config.php**).

Standard Translator configuration parameters are used with the addition of the following two keys:

* 'locales' - array of locales this module provides (it cannot be autodetected so set it by hand)
* 'default' - the default (fallback) locale

Configuration Example
---------------------
Consider the following example:

```php
    'translator' => [
        'locales' => [ 'en', 'fr', 'ru' ],
        'default' => 'en',
        'translation_file_patterns' => [
            [
                'type'     => 'phpArray',
                'base_dir' => __DIR__ . '/../l10n',
                'pattern'  => '%s.php',
            ],
        ],
    ],
```

In this example module provides translation into 'en', 'fr' and 'ru' locales using phpArray adapter. Files are in **module/ModuleName/l10n** directory (en.php, fr.php and ru.php).

Out of the box the translator is configured to use phpArray translation:

```php
<?php

return [
    'English string' => 'Translation',
    // ...
];
```

There is en.php only which have all the strings used by this skeleton. You can use that file as starting point.

Usage
-----
**NOTE**: The framework (all the validator messages) uses English text as parameter to $translate. If no translation found the parameter is printed as is.

In your view script:
```php
<h1><?= $this->translate('English text') ?><h1>
```

If you need to use translator in your controller:
```php
$sl = $this->getServiceLocator();
$translate = $sl->get('viewhelpermanager')->get('translate');

$result = $translate('English text');
```

Locale autodetection
--------------------
**Application\Module::onBootstrap** has code for choosing best locale based on HTTP Accept-Language header.

You don't have to copy this code to your other module's **onBootstrap** since **Application** module is always bootstrapped even when running another module.

Caching
-------
If you enabled our **memcached.local.php** then the translator is set up using Memcached as cache.

When you modify a translation you might also want to restart Memcached daemon to clear the cache and see the changes immediately.
