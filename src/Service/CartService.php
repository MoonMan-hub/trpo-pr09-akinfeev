<?php

namespace App\Service;

use App\Repository\ProductRepository;

// Корзина покупателя (выбран подход CartService: сервис над данными сессии).
//
// В сессии хранится только состояние: [id товара => количество].
// Название и цену товара сервис берёт из ProductRepository.
// Стоимость позиции и общая стоимость считаются только здесь (lines() и total()).
class CartService
{
    // Максимальное количество одного товара в корзине
    public const MAX_QUANTITY = 99;

    private ProductRepository $products;
    private array $session; // ссылка на массив, где лежит корзина ($_SESSION или временный массив)

    // $session передаётся по ссылке (&): в приложении это $_SESSION, при проверке сценариев
    // это временный массив, поэтому настоящая сессия при проверке не меняется.
    public function __construct(ProductRepository $products, array &$session)
    {
        $this->products = $products;
        $this->session = &$session;
    }

    // Добавляет одну штуку товара. false: товара нет или достигнут максимум.
    public function add(int $productId): bool
    {
        if ($this->products->find($productId) === null) {
            return false;
        }

        $items = $this->items();
        $quantity = ($items[$productId] ?? 0) + 1;

        if ($quantity > self::MAX_QUANTITY) {
            return false;
        }

        $items[$productId] = $quantity;
        $this->save($items);

        return true;
    }

    // Задаёт количество товара, который уже в корзине. false: товара нет в корзине
    // или количество не целое число от 1 до MAX_QUANTITY.
    public function setQuantity(int $productId, int $quantity): bool
    {
        $items = $this->items();

        if (!isset($items[$productId]) || $quantity < 1 || $quantity > self::MAX_QUANTITY) {
            return false;
        }

        $items[$productId] = $quantity;
        $this->save($items);

        return true;
    }

    // Удаляет позицию. false: такой позиции нет.
    public function remove(int $productId): bool
    {
        $items = $this->items();

        if (!isset($items[$productId])) {
            return false;
        }

        unset($items[$productId]);
        $this->save($items);

        return true;
    }

    // Состояние корзины [id товара => количество]. Если ключа в сессии нет, корзина пуста.
    public function items(): array
    {
        $stored = $this->session['cart'] ?? [];
        $items = [];

        if (!is_array($stored)) {
            return [];
        }

        foreach ($stored as $productId => $quantity) {
            if (is_int($quantity) && $quantity > 0) {
                $items[(int)$productId] = $quantity;
            }
        }

        return $items;
    }

    // Позиции корзины: ['product' => Product, 'qty' => int, 'sum' => float]
    // Стоимость позиции = цена товара * количество (единственное место расчёта).
    public function lines(): array
    {
        $lines = [];

        foreach ($this->items() as $productId => $quantity) {
            $product = $this->products->find($productId);
            if ($product === null) {
                continue; // товара больше нет в каталоге
            }

            $lines[] = [
                'product' => $product,
                'qty'     => $quantity,
                'sum'     => $product->getPrice() * $quantity,
            ];
        }

        return $lines;
    }

    // Общая стоимость корзины (единственное место расчёта итога)
    public function total(): float
    {
        $total = 0;

        foreach ($this->lines() as $line) {
            $total += $line['sum'];
        }

        return $total;
    }

    // Сколько штук товаров в корзине (для счётчика в меню)
    public function count(): int
    {
        $count = 0;

        foreach ($this->items() as $quantity) {
            $count += $quantity;
        }

        return $count;
    }

    public function isEmpty(): bool
    {
        return count($this->items()) === 0;
    }

    private function save(array $items): void
    {
        if (count($items) === 0) {
            unset($this->session['cart']);
        } else {
            $this->session['cart'] = $items;
        }
    }
}
