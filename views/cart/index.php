<section class="box">
    <h1>Корзина</h1>

    <?php if (count($lines) === 0): ?>
        <p class="muted">Корзина пуста.</p>
        <p><a href="/catalog" class="btn">Перейти в каталог</a></p>
    <?php else: ?>
        <table class="table">
            <tr>
                <th>Товар</th>
                <th>Цена</th>
                <th>Количество</th>
                <th>Сумма</th>
                <th></th>
            </tr>
            <?php foreach ($lines as $line): ?>
                <tr>
                    <td><a href="/product?id=<?= e($line['product']->getId()) ?>"><?= e($line['product']->getName()) ?></a></td>
                    <td><?= e($line['product']->getFormattedPrice()) ?></td>
                    <td>
                        <form method="post" action="/cart/update" class="inline-form">
                            <input type="hidden" name="product_id" value="<?= e($line['product']->getId()) ?>">
                            <input type="number" name="quantity" value="<?= e($line['qty']) ?>" min="1" max="99" class="qty">
                            <button type="submit" class="btn small">Изменить</button>
                        </form>
                    </td>
                    <td><?= e(money($line['sum'])) ?></td>
                    <td>
                        <form method="post" action="/cart/remove" class="inline-form">
                            <input type="hidden" name="product_id" value="<?= e($line['product']->getId()) ?>">
                            <button type="submit" class="btn small danger">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <p class="price">Итого: <?= e(money($total)) ?></p>
        <p><a href="/catalog" class="reset">Продолжить покупки</a></p>
    <?php endif; ?>
</section>
