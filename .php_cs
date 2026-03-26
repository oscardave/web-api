<?php

/*
 * PHP-CS-Fixer v2 configuration.
 *
 * Rename to .php-cs-fixer.php and replace Config::create()
 * with (new Config()) when upgrading to php-cs-fixer v3.
 */

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
    ])
    ->exclude([
        'Common/GoogleAuthenticator',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_quote' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline_array' => true,
        'no_whitespace_in_blank_line' => true,
        'blank_line_before_statement' => ['statements' => ['return']],
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(true);
