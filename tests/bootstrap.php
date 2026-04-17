<?php
/**
 * PHPUnit bootstrap for hypeNotifications plugin tests.
 * Plugin is mounted at {elgg_root}/mod/hypenotifications/
 */

// tests/ -> plugin/ -> mod/ -> elgg_root/
$elggRoot = dirname(__DIR__, 3);

require_once $elggRoot . '/vendor/autoload.php';

$testClassesDir = $elggRoot . '/vendor/elgg/elgg/engine/tests/classes';
spl_autoload_register(function ($class) use ($testClassesDir) {
    $file = $testClassesDir . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

\Elgg\Application::loadCore();
