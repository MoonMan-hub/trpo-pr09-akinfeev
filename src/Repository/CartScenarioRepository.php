<?php

namespace App\Repository;

use App\Core\JsonFile;

// Сценарии проверки корзины из cart_scenarios.json
class CartScenarioRepository
{
    private string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    // Список сценариев: ['steps' => ['add:1', ...], 'expected' => [id => количество]]
    public function all(): array
    {
        $scenarios = [];

        foreach (JsonFile::read($this->file) as $row) {
            if (!is_array($row) || !isset($row['steps']) || !is_array($row['steps'])) {
                continue;
            }

            $expected = [];
            if (isset($row['expected']) && is_array($row['expected'])) {
                foreach ($row['expected'] as $productId => $quantity) {
                    $expected[(int)$productId] = (int)$quantity;
                }
            }

            $scenarios[] = [
                'steps'    => array_map('strval', $row['steps']),
                'expected' => $expected,
            ];
        }

        return $scenarios;
    }
}
