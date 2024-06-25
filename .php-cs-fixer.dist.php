<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/config',
        __DIR__.'/src',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony:risky'  => true,
    ])
    ->setParallelConfig( \PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
