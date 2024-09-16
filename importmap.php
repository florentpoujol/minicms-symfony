<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@symfony/ux-vue' => [
        'path' => './vendor/symfony/ux-vue/assets/dist/loader.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.6',
    ],
    'vue' => [
        'version' => '3.5.6',
        'package_specifier' => 'vue/dist/vue.esm-bundler.js',
    ],
    '@vue/runtime-dom' => [
        'version' => '3.5.6',
    ],
    '@vue/compiler-dom' => [
        'version' => '3.5.6',
    ],
    '@vue/shared' => [
        'version' => '3.5.6',
    ],
    '@vue/runtime-core' => [
        'version' => '3.5.6',
    ],
    '@vue/compiler-core' => [
        'version' => '3.5.6',
    ],
    '@vue/reactivity' => [
        'version' => '3.5.6',
    ],
];
