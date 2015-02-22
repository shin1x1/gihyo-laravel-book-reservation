<?php

return [
    'debug' => true,
    'providers' => append_config([
        'Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider',
        'Barryvdh\Debugbar\ServiceProvider',
        'Way\Generators\GeneratorsServiceProvider',
    ]),
];
