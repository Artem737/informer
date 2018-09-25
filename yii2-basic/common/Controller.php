<?php

namespace app\common;

/**
 * Class Controller
 * @package app\common
 */
class Controller
{
    private static $controllerInput = [
        638, 622, 614, 598, 590, 606, 630, 646, 666
    ];

    private static $controllerOutput = [
        594, 602, 610, 618, 626, 634, 642, 650, 662
    ];

    /**
     * @param bool $inSelect
     * @return array
     */
    public static function inputIds($inSelect = false)
    {
        return $inSelect ? implode(',', self::$controllerInput) : self::$controllerInput;
    }

    /**
     * @param bool $inSelect
     * @return array
     */
    public static function outputIds($inSelect = false)
    {
        return $inSelect ? implode(',', self::$controllerOutput) : self::$controllerOutput;
    }

    public static function all($inSelect = false)
    {
        $data = array_merge(self::inputIds(), self::outputIds());

        return $inSelect ? implode(',', $data) : $data;
    }

}