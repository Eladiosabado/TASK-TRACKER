<?php
/**
 * config/env.php
 *
 * Minimal dependency-free .env file loader.
 * Reads key=value pairs from the .env file (if present) into
 * $_ENV / $_SERVER / getenv() so the rest of the app can use getenv().
 *
 * Falls back silently if .env does not exist (e.g. on a host where
 * environment variables are injected directly by the hosting panel).
 */

if (!function_exists('load_env')) {
    function load_env(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and blank lines
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name  = trim($name);
            $value = trim($value);

            // Strip surrounding quotes if present
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last  = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            if ($name === '') {
                continue;
            }

            // Do not overwrite variables that are already set
            if (getenv($name) === false) {
                putenv("{$name}={$value}");
                $_ENV[$name]    = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Load the .env file from the project root (one level up from /config)
load_env(__DIR__ . '/../.env');
