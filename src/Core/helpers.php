<?php

// Экранирование текста перед выводом в HTML
function e($text): string
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

// Деньги для вывода: 900 ₽ или 900,50 ₽
function money(float $amount): string
{
    $decimals = ($amount == floor($amount)) ? 0 : 2;
    return number_format($amount, $decimals, ',', ' ') . ' ₽';
}
