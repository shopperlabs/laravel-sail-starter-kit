<?php

declare(strict_types=1);

namespace App;

if (! function_exists('App\updateEnvVariable')) {
    function updateEnvVariable(string $key, mixed $value): void
    {
        $envPath = base_path('.env');
        /** @var string $content */
        $content = file_get_contents($envPath);

        $keyPosition = mb_strpos($content, "{$key}=");

        /** @var string $formattedValue */
        $formattedValue = is_bool($value)
            ? ($value ? 'true' : 'false')
            : (is_string($value) && ! is_numeric($value) ? "\"$value\"" : $value);
        $newLine = "{$key}={$formattedValue}";

        if ($keyPosition !== false) {
            $endOfLinePosition = mb_strpos($content, PHP_EOL, $keyPosition);
            $oldLine = mb_substr($content, $keyPosition, $endOfLinePosition !== false ? $endOfLinePosition - $keyPosition : mb_strlen($content));

            $content = str_replace($oldLine, $newLine, $content);
        } else {
            $content .= PHP_EOL.$newLine;
        }

        file_put_contents($envPath, $content);
    }
}
