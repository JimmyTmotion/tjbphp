<?php

function tjb_load_env(string $path): array
{
    if (!is_readable($path)) {
        return [];
    }

    $values = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines ?: [] as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($key === '') {
            continue;
        }

        if (
            (strpos($value, '"') === 0 && substr($value, -1) === '"')
            || (strpos($value, "'") === 0 && substr($value, -1) === "'")
        ) {
            $value = substr($value, 1, -1);
        }

        $values[$key] = $value;
        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }

    return $values;
}

function tjb_env(string $key, ?string $default = null): ?string
{
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false || $value === '') {
        return $default;
    }

    return (string) $value;
}
