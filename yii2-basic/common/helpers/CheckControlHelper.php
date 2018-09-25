<?php

namespace app\common\helpers;

use app\common\Rate;

/**
 * Class CheckControlHelper
 * @package app\common\helpers
 */
class CheckControlHelper
{
    private $checkControl;
    private static $ratesCache;

    /**
     * CheckControlHelper constructor.
     * @param array $checkControl
     */
    public function __construct(array $checkControl)
    {
        foreach ($checkControl as $element) {
            $this->checkControl[$element['CardCode']][] = $element;
        }
    }

    /**
     * Получить покупки по номеру карты и диапазону времени времени
     * @param $card
     * @param $from
     * @param $to
     * @return array
     */
    public function getChecksByCardCodeAndTimes($card, $from, $to)
    {
        if(!isset($this->checkControl[$card])) {
            return [];
        }

        $result = [];

        foreach ($this->checkControl[$card] as $check) {

            if (strtotime($check['TransTime']) > strtotime($to)) {
                break;
            }

            if (strtotime($check['TransTime']) < strtotime($from)) {
                continue;
            }

            $result[] = $check;
        }

        return $result;
    }

    /**
     * @param $card
     * @param $inputTime
     * @return array|null
     */
    public function getRateByCode($card, $inputTime)
    {
        if(!self::$ratesCache) {
            self::$ratesCache = Rate::getRates();
        }

        if(!isset($this->checkControl[$card])) {
            return null;
        }

        $result = null;
        $inputTime = strtotime($inputTime);
        $inputTimeHourAgo = $inputTime - 3600;

        foreach ($this->checkControl[$card] as $check) {

            $checkTime = strtotime($check['TransTime']);

            if ($checkTime > $inputTimeHourAgo && $checkTime < $inputTime && in_array($check['Name'], self::$ratesCache)) {
                $result = $check['Name'];
            }
        }

        return $result;
    }
}