<?php declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/config',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony:risky'  => true,

        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'final_class' => true,
        'void_return' => true,

        // TODO: put promoted properties always on their own lines
        // TODO: put argument list on multiple line when longer than 120 chars or 3 arguments
        // TODO: add trailing coma on argument list and arrays
        // TODO: add spaces before declare(strict_type=1);
        // TODO: make properties readonly when they can
    ])
    ->setParallelConfig( \PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
