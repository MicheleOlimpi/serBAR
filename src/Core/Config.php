<?php

declare(strict_types=1);

namespace App\Core;

class Config
{
    public static function load(string $path): ?array
    {
        if (!file_exists($path)) {
            return null;
        }

        $config = include $path;
        return is_array($config) ? $config : null;
    }

    public static function save(string $path, array $config): bool
    {
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return false;
        }

        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        return (bool) file_put_contents($path, $content);
    }
}
