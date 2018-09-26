<?php


namespace app\common\helpers;

/**
 * Class MathHelper
 * @package app\common\helpers
 */
class MathHelper
{

    public static function percent($part, $all, $asString = false)
    {
        $result = round(doubleval($part / $all * 100), 2);
        return $asString ? $result . '%' : $result;
    }
}