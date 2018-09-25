<?php

namespace app\common\helpers;

/**
 * Class DateHelper
 * @package app\common\helpers
 */
class DateHelper
{
    private static $lngDaysMapping = [
        'Monday' => 'пн.',
        'Tuesday' => 'вт.',
        'Wednesday' => 'ср.',
        'Thursday' => 'чт.',
        'Friday' => 'пт.',
        'Saturday' => 'субб.',
        'Sunday' => 'вс.',
    ];

    /**
     * @param string $str
     * @return string
     */
    public static function translateDayInStr($str)
    {
        return str_replace(array_keys(self::$lngDaysMapping), array_values(self::$lngDaysMapping), $str);
    }

}