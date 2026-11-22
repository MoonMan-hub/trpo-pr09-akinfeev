<?php

namespace App\Core;

// Перенаправление браузера на другую страницу (после POST)
class Redirect
{
    public static function to(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}
