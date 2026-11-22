<?php

namespace App\Controller;

use App\Core\View;
use App\Repository\CartScenarioRepository;
use App\Repository\ProductRepository;
use App\Service\CartService;

// Проверка корзины по сценариям из cart_scenarios.json.
// Шаги выполняются настоящим CartService, но над временным массивом, а не над сессией.
class CartCheckController
{
    private View $view;
    private CartScenarioRepository $scenarios;
    private ProductRepository $products;

    public function __construct(View $view, CartScenarioRepository $scenarios, ProductRepository $products)
    {
        $this->view = $view;
        $this->scenarios = $scenarios;
        $this->products = $products;
    }

    // GET /check/cart
    public function check(): void
    {
        $results = [];

        foreach ($this->scenarios->all() as $number => $scenario) {
            $state = [];                                   // временное "хранилище" вместо сессии
            $cart = new CartService($this->products, $state);

            foreach ($scenario['steps'] as $step) {
                $this->runStep($cart, $step);
            }

            $actual = $cart->items();
            $expected = $scenario['expected'];
            ksort($actual);
            ksort($expected);

            $results[] = [
                'number'   => $number + 1,
                'steps'    => implode(', ', $scenario['steps']),
                'expected' => $this->describe($expected),
                'actual'   => $this->describe($actual),
                'total'    => $cart->total(),
                'ok'       => $actual === $expected,
            ];
        }

        $this->view->render('cart/check', [
            'title'   => 'Проверка сценариев корзины',
            'results' => $results,
        ]);
    }

    // Шаги: add:ID, remove:ID, set:ID:КОЛИЧЕСТВО
    private function runStep(CartService $cart, string $step): void
    {
        $parts = explode(':', $step);
        $action = $parts[0];
        $productId = (int)($parts[1] ?? 0);

        if ($action === 'add') {
            $cart->add($productId);
        } elseif ($action === 'remove') {
            $cart->remove($productId);
        } elseif ($action === 'set') {
            $cart->setQuantity($productId, (int)($parts[2] ?? 0));
        }
    }

    // Текст для таблицы: "товар 1: 2 шт.; товар 2: 1 шт." или "пусто"
    private function describe(array $items): string
    {
        if (count($items) === 0) {
            return 'пусто';
        }

        $texts = [];
        foreach ($items as $productId => $quantity) {
            $texts[] = 'товар ' . $productId . ': ' . $quantity . ' шт.';
        }

        return implode('; ', $texts);
    }
}
