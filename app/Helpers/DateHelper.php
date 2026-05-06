<?php

namespace App\Helper;

use IntlDateFormatter;
use Carbon\CarbonInterface;

class DateHelper
{
    public static function withWareki(?CarbonInterface $date): ?string
    {
        if (! $date) {
            return null;
        }

        $seireki = $date->format('Y-m-d');

        $formatter = new IntlDateFormatter(
            'ja_JP@calendar=japanese',
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE
        );

        $wareki = $formatter->format($date);

        return "{$seireki}（{$wareki}）";
    }
}
