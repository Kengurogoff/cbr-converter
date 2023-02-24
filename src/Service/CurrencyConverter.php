<?php

namespace App\Service;

use App\Entity\Currency;

class CurrencyConverter
{
    public static function convert(Currency $from, Currency $to, float $amount): float
    {
        $rateFrom = $from->getValue() / $from->getRatio();
        $rateTo = $to->getValue() / $to->getRatio();
        $result = $amount * ($rateFrom / $rateTo);

        return round($result, 3);
    }

}
