<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $template, array $data = []): void
    {
        extract($data);
        $file = __DIR__ . '/../Views/' . $template . '.php';
        include __DIR__ . '/../Views/layout/header.php';
        include $file;
        include __DIR__ . '/../Views/layout/footer.php';
    }

    public static function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }
}
