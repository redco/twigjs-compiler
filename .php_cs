<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->name('*.php')
    ->in([
        __DIR__ . '/src/RedCode'
    ])
;

if (file_exists(__DIR__ . '/.gitignore')) {
    foreach (file(__DIR__ . '/.gitignore') as $ignore) {
        $ignore = trim($ignore);
        if (is_dir(__DIR__ . '/' . trim($ignore, '/'))) {
            $finder->exclude(trim($ignore, '/'));
        } else {
            $finder->notName(trim($ignore, '/'));
        }
    }
}

return Symfony\CS\Config\Config::create()
    ->finder($finder)
    ->fixers(['header_comment', 'short_array_syntax'])
    ->level(\Symfony\CS\FixerInterface::SYMFONY_LEVEL)
;