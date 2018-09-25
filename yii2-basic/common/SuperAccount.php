<?php

namespace app\common;

use ReflectionClass;

/**
 * Class SuperAccount
 * @package app\common
 */
class SuperAccount
{
    const VDT = 7;
    const AKVAMIR = 1854;
    const AKVABAR = 1855;
    const MISHENEV = 2420;
    const FABRICA_KUHNI = 2433;
    const PIRAT = 2434;
    const AKAVMIR_MASSAGE = 783129;
    const TEST_SEUIC = 847596;
    const KAPITAL = 855979;
    const KAMBUZ = 1190697;
    const AKVAMIR_SPA = 1976033;
    const AKVAMED = 3922528;
    const SWIM_TIME = 4663127;
    const VDT_OSN = 5261188;
    const ALIEV = 6422691;
    const OP = 7092772;
    const TERASSA = 7833753;
    const PEPSI_VENDING = 8109015;
    const VIGRA = 8883073;
    const SAVKOVA = 8937187;
    const KONKVEST_FIDING = 9717508;
    const DAINER = 9717509;
    const KONKVEST_202 = 9717510;
    const KONKVEST_DAIN = 9717511;
    const GUREVICH = 10389862;

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function all()
    {
        $selfClass = new ReflectionClass(__CLASS__);
        return $selfClass->getConstants();
    }

    /**
     * @return array
     */
    public static function notUsed()
    {
        return [
            self::AKVABAR, self::FABRICA_KUHNI, self::PIRAT, self::TEST_SEUIC, self::AKAVMIR_MASSAGE, self::KAPITAL,
            self::KAMBUZ, self::AKVAMIR_SPA, self::VDT_OSN, self::ALIEV, self::OP, self::TERASSA, self::KONKVEST_DAIN
        ];
    }
}