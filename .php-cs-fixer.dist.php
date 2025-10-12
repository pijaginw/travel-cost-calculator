<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_blank_line_at_eof' => true,
        'align_multiline_comment' => true,
        'array_syntax' => ['syntax' => 'short'],
        'yoda_style' => false,
        'void_return' => true,
    ])
    ->setFinder($finder);
