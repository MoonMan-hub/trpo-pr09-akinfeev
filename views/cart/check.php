<section class="box">
    <h1>Проверка сценариев корзины</h1>
    <p><a href="/check">Каталог</a> | <b>Корзина</b></p>
    <p class="muted">Сценарии берутся из data/cart_scenarios.json. Шаги выполняются настоящим CartService над временным массивом, сессия не меняется.</p>

    <?php if (count($results) === 0): ?>
        <p class="muted">Сценариев нет.</p>
    <?php else: ?>
        <table class="table">
            <tr>
                <th>№</th>
                <th>Шаги</th>
                <th>Ожидалось</th>
                <th>Получено</th>
                <th>Сумма</th>
                <th>Результат</th>
            </tr>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?= e($row['number']) ?></td>
                    <td><?= e($row['steps']) ?></td>
                    <td><?= e($row['expected']) ?></td>
                    <td><?= e($row['actual']) ?></td>
                    <td><?= e(money($row['total'])) ?></td>
                    <td>
                        <?php if ($row['ok']): ?>
                            <span class="status ok">совпало</span>
                        <?php else: ?>
                            <span class="status no">не совпало</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</section>
