<?php

namespace App\Core;

use JsonException;
use RuntimeException;

// Чтение JSON-файлов. Единое место для всех моделей и репозиториев.
class JsonFile
{
    // Возвращает данные файла в виде массива.
    // Если файла нет или JSON сломан, бросает RuntimeException с понятным текстом.
    public static function read(string $path): array
    {
        if (!file_exists($path)) {
            throw new RuntimeException('Файл не найден: ' . basename($path));
        }

        $raw = @file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException('Не удалось прочитать файл: ' . basename($path));
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Файл повреждён (неверный JSON): ' . basename($path));
        }

        if (!is_array($data)) {
            throw new RuntimeException('Неверный формат данных в файле: ' . basename($path));
        }

        return $data;
    }
}
