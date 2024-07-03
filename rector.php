<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPreparedSets(
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withRules([
//        ReadOnlyPropertyRector::class,
    ])

//    ->withConfiguredRule(ReadOnlyPropertyRector::class, [
//        'App\SomeOldClass' => 'App\SomeNewClass',
//    ])
    ;