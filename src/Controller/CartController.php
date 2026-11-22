<?php

namespace App\Controller;

use App\Core\Flash;
use App\Core\Redirect;
use App\Core\View;
use App\Service\CartService;

// Корзина. GET /cart показывает её, POST-действия меняют состояние и заканчиваются redirect.
class CartController
{
    private View $view;
    private CartService $cart;

    public function __construct(View $view, CartService $cart)
    {
        $this->view = $view;
        $this->cart = $cart;
    }

    // GET /cart
    public function index(): void
    {
        $this->view->render('cart/index', [
            'title' => 'Корзина',
            'lines' => $this->cart->lines(),
            'total' => $this->cart->total(),
        ]);
    }

    // POST /cart/add (поле product_id)
    public function add(): void
    {
        $productId = $this->readInt('product_id');

        if ($productId !== null && $this->cart->add($productId)) {
            Flash::set('ok', 'Товар добавлен в корзину');
        } else {
            Flash::set('error', 'Не удалось добавить товар: такого товара нет или достигнут максимум ('
                . CartService::MAX_QUANTITY . ' шт.)');
        }

        Redirect::to('/cart');
    }

    // POST /cart/update (поля product_id и quantity)
    public function update(): void
    {
        $productId = $this->readInt('product_id');
        $quantity = $this->readInt('quantity');

        if ($quantity === null || $quantity < 1 || $quantity > CartService::MAX_QUANTITY) {
            Flash::set('error', 'Количество должно быть целым числом от 1 до ' . CartService::MAX_QUANTITY);
        } elseif ($productId !== null && $this->cart->setQuantity($productId, $quantity)) {
            Flash::set('ok', 'Количество обновлено');
        } else {
            Flash::set('error', 'Такого товара нет в корзине');
        }

        Redirect::to('/cart');
    }

    // POST /cart/remove (поле product_id)
    public function remove(): void
    {
        $productId = $this->readInt('product_id');

        if ($productId !== null && $this->cart->remove($productId)) {
            Flash::set('ok', 'Товар удалён из корзины');
        } else {
            Flash::set('error', 'Такого товара нет в корзине');
        }

        Redirect::to('/cart');
    }

    // Целое число из POST-поля или null, если поля нет или значение не целое
    private function readInt(string $field): ?int
    {
        if (!isset($_POST[$field]) || !is_string($_POST[$field])) {
            return null;
        }

        $value = filter_var(trim($_POST[$field]), FILTER_VALIDATE_INT);

        return $value === false ? null : $value;
    }
}
