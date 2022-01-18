# fabiang/assetic-google-closure-compiler

## Installation

New to Composer? Read the [introduction](https://getcomposer.org/doc/00-intro.md#introduction). Run the following Composer command:

    composer require --dev fabiang/assetic-google-closure-compiler

*Note:* Remove `--dev` if you don't compile the assets while building your application before deploying it and you need the filter on production.

## Usage in a Laminas application

Add this filter to you assetic configuration (e.g. `assetic.global.php`):

```php
<?php

return [
    'assetic_configuration' => [
        // [...]
        'modules'        => [
            'MyModule' => [
                'collections' => [
                    'my_collection' => [
                        'filters' => [
                            '?JSMinFilter' => [
                                'name'   => Assetic\Filter\GoogleClosure\CompilerJarFilter::class,
                                'option' => [
                                    realpath('node_modules/google-closure-compiler/compiler.jar'),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
```

The class `CompilerJarFilter` accepts two arguments:

1. the path to the composer.jar
2. the path to your Java binary (default is `/usr/bin/java`)

## Licence

BSD-2-Clause. See the [LICENSE.md](LICENSE.md).
