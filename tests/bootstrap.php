<?php

// Newer PHP versions than Kirby has been released for are fine in tests
const KIRBY_PHP_VERSION_CHECK = false;

require __DIR__ . '/../vendor/autoload.php';

\Kirby\Cms\App::$enableWhoops = false;

// Register the plugins once (Uniform and its guards are installed to site/plugins
// by Composer), the test cases create their own Kirby instances
foreach (glob(__DIR__ . '/../site/plugins/*/index.php') as $plugin) {
    require_once $plugin;
}
require_once __DIR__ . '/../index.php';
