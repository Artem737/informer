<?php

namespace app\common;

use yii\helpers\ArrayHelper;

/**
 * Тарифы
 * Class Rate
 * @package app\common
 */
class Rate
{
    private static $rates = [
        'Льготные' =>
            [
                0 => 'День Аква Взросл. Льготн.',
                1 => 'Утренний Аква-Термо Льготн.',
                2 => 'День Аква-Термо Льготн.',
                3 => '4ч Аква-Термо Льготн.',
                4 => '3ч Аква-Термо Льготн.',
            ],
        'Взрослые' =>
            [
                0 => 'Вечерний 6ч Аква Взросл.',
                1 => 'День Аква Взросл.',
                2 => 'Купонный Полный день Единый Взр.',
                3 => 'День Аква-Термо Взросл.',
                4 => 'День Аква-Термо Взрослый -10%',
                5 => 'День Аква-Термо Взрослый -20%',
                6 => 'День Аква-Термо Взрослый -25%',
                7 => 'День Аква-Термо Взрослый -50%',
                8 => 'Утренний Аква-Термо Взросл.',
                9 => 'Утренний Аква-Термо Взрослый -10%',
                10 => 'Утрениий Аква-Термо Взрослый -20%',
                11 => 'Утрениий Аква-Термо Взрослый -25%',
                12 => 'Утрениий Аква-Термо Взрослый -50%',
                13 => 'День КупиКупон Взросл.',
                14 => 'День КассирРУ Взросл',
                15 => '4ч Аква-Термо Взросл.',
                16 => '4ч Аква-Термо Взрослый -10%',
                17 => '4ч Аква-Термо Взрослый -20%',
                18 => '4ч Аква-Термо Взрослый -25%',
                19 => '4ч Аква-Термо Взрослый -50%',
                20 => '3ч Аква-Термо Взросл.',
                21 => '3ч Аква-Термо Взрослый -20%',
                22 => '3ч Аква-Термо Взрослый -25%',
                23 => '3ч Аква-Термо Взрослый -50%',
                24 => 'Полный день единый',
            ],
        'Взрослые скидки' =>
            [
                0 => 'День Аква Взрослый -25%',
                1 => 'День Аква Взрослый -20%',
                2 => 'День Аква Взрослый -10%',
                3 => 'День Аква Взрослый -50%',
                4 => 'Вечерний 6ч Аква Взрослый -25%',
                5 => 'Вечерний 6ч Аква Взрослый -20%',
                6 => 'Вечерний 6ч Аква Взрослый -10%',
            ],
        'Детские' =>
            [
                0 => 'Вечерний 6ч Аква-Термо Дет.',
                1 => 'День Аква-Термо 4-17Дет.',
                2 => '17л. Утрениий Аква-Термо.',
                3 => '17л. День Аква-Термо.',
                4 => 'Хайп',
                5 => 'День Аква-Термо Дет.',
                6 => 'День Аква-Термо Детский -10%',
                7 => 'День Аква-Термо Детский -20%',
                8 => 'День Аква-Термо Детский -25%',
                9 => 'День Аква-Термо Детский -50%',
                10 => 'Утренний Аква-Термо Дет.',
                11 => 'Утрениий Аква-Термо Детский -10%',
                12 => 'Утрениий Аква-Термо Детский -20%',
                13 => 'Утрениий Аква-Термо Детский -25%',
                14 => 'Утрениий Аква-Термо Детский -50%',
                15 => '4ч Аква-Термо Дет.',
                16 => '4ч Аква-Термо Детский -10%',
                17 => '4ч Аква-Термо Детский -20%',
                18 => '4ч Аква-Термо Детский -25%',
                19 => '4ч Аква-Термо Детский -50%',
                20 => '3ч Аква-Термо Дет.',
                21 => '3ч Аква-Термо Детский -20%',
                22 => '3ч Аква-Термо Детский -25%',
                23 => '3ч Аква-Термо Детский -50%',
                24 => '09-15 Аква-Термо Детский тест',
                25 => 'День КупиКупон Дет.',
                26 => 'День КассирРУ Детск',
                27 => 'Полный единый день',
            ],
        'Инвалиды с сопровождением' =>
            [
                0 => 'День ОВ взр +1',
                1 => 'День ОВ дет +1',
                2 => 'Вечерний 6ч ОВ взр +1',
                3 => 'Утренний ОВ дет +1',
                4 => 'День ОВ дет +1',
                5 => 'День ОВ взр +1',
                6 => 'Утренний ОВ взр +1',
                7 => '4ч ОВ взр +1',
                8 => '3ч ОВ дет +1',
                9 => '3ч ОВ взр +1',
            ],
        'Акции' =>
            [
                0 => '3ч Вечерний Аква взрослый',
                1 => '3ч Вечерний Аква-Термо Дет.',
                2 => 'Тестдрайв Взрослый',
                3 => '1+1 Детский',
                4 => '3ч Вечерний Сотрудники',
                5 => '3ч Вечерний',
                6 => 'День Аква-Термо Пенсионер',
                7 => 'Студенческий Ноябрь',
            ],
        'Подарочные сертификаты' =>
            [
                0 => 'День Аква-Термо Взросл. ПС вых',
                1 => 'День Аква-Термо Детск. ПС вых',
                2 => 'День Аква-Термо Взросл. ПС будний',
            ],
        'Детские скидки' =>
            [
                0 => 'День Аква-Термо 4-17 Дет.-20%',
                1 => 'День Аква-Термо 4-17 Дет.-25%',
                2 => 'Вечерний 6ч Аква Детский -25%',
                3 => 'Вечерний 6ч Аква Детский -20%',
                4 => 'День Аква-Термо Детский -10%',
                5 => 'День Аква-Термо 4-17 Дет.-10%',
                6 => 'Вечерний 6ч Аква  Детский -10%',
            ],
        'Взрослые юр лица праздн' =>
            [
                0 => 'Полный день Взрослый ЮЛ',
            ],
        'ЮЛ' =>
            [
                0 => 'ЮЛ Полный день Единый Взр.',
                1 => 'ЮЛ Полный день Единый Дет.',
            ],
        'Двойные' =>
            [
                0 => 'День Взр+Взр',
                1 => 'День Взр+Дет',
                2 => 'День Детск+Детск',
                3 => '4ч Взр+Взр',
                4 => '4ч Взр+Дет',
                5 => '4ч Детск+Детск',
                6 => 'День Аква-Термо Взросл. OLD',
            ],
    ];

    /**
     * Получить список категорий тарифов
     * @return array
     */
    public static function getCategories()
    {
        return array_keys(self::$rates);
    }

    /**
     * Получить всю структуру категорий и тарифов
     * @return array
     */
    public static function getRatesStructure()
    {
        return self::$rates;
    }

    /**
     * Находит категорию услуги по её имени
     * @param $name
     * @return string|null
     */
    public static function getCategoryByRateName($name)
    {
        $categories = self::getCategories();

        foreach ($categories as $category) {
            if (array_search($name, self::$rates[$category]) !== false) {
                return $category;
            }
        }

        return null;
    }

    /**
     * Получить список тарифов
     * @param $inSelect
     * @return array|string
     */
    public static function getRates($inSelect = false)
    {
        $result = [];
        foreach (self::$rates as $rate) {
            $result = array_merge($result, $rate);
        }

        if($inSelect) {
            $result = array_map(function($val) { return "'" . $val . "'"; }, $result);
            return implode(',', $result);
        }

        return $result;
    }

    public static function regroupByDuration()
    {
        $result = [
            'День' => [
                'patterns' => ['День', 'Полный день', '1+1 Детский'],
                'names' => [],
            ],
            '1час' => [
                'patterns' => ['Тестдрайв'],
                'names' => [],
            ],
            '3часа' => [
                'patterns' => ['3ч'],
                'names' => [],
            ],
            '4часа' => [
                'patterns' => ['4ч'],
                'names' => [],
            ],
            'Вечерний 6ч' => [
                'patterns' => ['Вечерний 6ч'],
                'names' => [],
            ],
            'Утренний' => [
                'patterns' => ['Утренний'],
                'names' => [],
            ],
        ];

        $ratesAll = [];
        foreach (self::$rates as $rate) {
            foreach ($rate as $rateName) {
                $ratesAll[] = $rateName;
            }
        }
        
        foreach ($ratesAll as $rateName) {

            $found = false;

            foreach ($result as $type => $descriptor) {

                if ($found) {
                    break;
                }

                foreach ($descriptor['patterns'] as $pattern) {
                    if (strpos($rateName, $pattern) !== false) {

                        $result[$type]['names'][] = $rateName;
                        $found = true;
                        break;

                    }
                }
            }
        }

        foreach ($result as $name => &$description) {
            $description = ArrayHelper::getValue($description, 'names', []);
        }
            
        
        self::$rates = $result;
    }

}