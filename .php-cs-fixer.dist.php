<?php declare(strict_types=1);

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new PhpCsFixer\Finder())
    ->in([
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
        'numeric_literal_separator' => true,

        'trailing_comma_in_multiline' => [ // symfony seems to set it to false
            'after_heredoc' => true,
            'elements' => ['arguments', 'arrays', 'match', 'parameters'],
        ],

        'phpdoc_line_span' => [
            'const' => 'multi',
            'method' => 'multi',
            'property' => 'multi',
        ],

        'fully_qualified_strict_types' => [
            'import_symbols' => true,
            'leading_backslash_in_global_namespace' => true,
        ],

        'blank_line_before_statement' => [
            'statements' => ['declare', 'return'], // Symfony only has 'return'
            // Note that this actually doesn't work for the declare(strict_types=1)k lines when they are at the top of the file (which is the only pertinent place for them...)
        ],

        'concat_space' => ['spacing' => 'one'],

        // only works for built-in PHPUnit assertion, not for Symfony assertions.
        // see also the ThisCallOnStaticMethodToStaticCallRector rector rules that supposedly do what I want but doesn't seem to work
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],

        // no rules with PHP CS Fixer:
        // - put promoted properties always on their own lines > no rules
        // - put argument list on multiple line when longer than 120 chars or 3 arguments > no rule
    ])
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
