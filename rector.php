<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPreparedSets(
        deadCode: false, // I don't want the RemoveUnreachableStatementRector::class rule
        typeDeclarations: true,
        earlyReturn: true,
        strictBooleans: true,

        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withRules([
        ReadOnlyPropertyRector::class,
    ])

//    ->withConfiguredRule(ReadOnlyPropertyRector::class, [
//        'App\SomeOldClass' => 'App\SomeNewClass',
//    ])
    ;