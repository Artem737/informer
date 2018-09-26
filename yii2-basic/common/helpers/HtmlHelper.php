<?php

namespace app\common\helpers;

use kartik\checkbox\CheckboxX;
use kartik\date\DatePicker;
use kartik\select2\Select2;

/**
 * Генерация html блоков
 * Class HtmlHelper
 * @package app\common\helpers
 */
class HtmlHelper
{
    /**
     * @param string $innerContent
     * @return string
     */
    public static function renderRow($innerContent)
    {
        return '<div class="row flex-center">' . $innerContent . '</div>';
    }

    /**
     * @param string $name
     * @param string $header
     * @param int $sm
     * @param string $dateFormat
     * @param string $messageChoose
     * @param string $more
     * @return string
     * @throws \Exception
     */
    public static function renderCalendar($name, $header = '', $sm = 6, $dateFormat = 'd-m-Y', $messageChoose = 'Выберите дату ...', $more = '')
    {
        return
            '<div class="col-sm-' .  $sm . '">
            <label>' . $header . '</label>' .
            DatePicker::widget([
                'name' => $name,
                'language' => 'ru',
                'value' => date($dateFormat, time()),
                'options' => ['placeholder' => $messageChoose],
                'pluginOptions' => [
                    'format' => 'dd-mm-yyyy',
                    'todayHighlight' => true
                ]
            ]) .
            '</div>' .
            $more;
    }

    /**
     * @param string $name
     * @param int $sm
     * @param string $dateFormat
     * @param string $messageChoose
     * @param string $more
     * @param string $middle
     * @return string
     * @throws \Exception
     */
    public static function renderCalendarFromTo($name, $sm = 6, $dateFormat = 'd-m-Y', $messageChoose = 'Выберите дату ...', $more = '', $middle = '')
    {
        return
            self::renderCalendar('date_start_' . $name, 'Дата начала промежутка', $sm, $dateFormat, $messageChoose, $middle) .
            self::renderCalendar('date_end_' . $name, 'Дата окончания промежутка', $sm, $dateFormat, $messageChoose, $more);
    }

    /**
     * @param string $name
     * @param string $header
     * @param int $sm
     * @param string $value
     * @param array $data
     * @param string $message
     * @param bool $hideSearch
     * @return string
     * @throws \Exception
     */
    public static function renderSelect($name, $header, $sm, $value, $data, $message = '', $hideSearch = true)
    {
        return
            '<div class="col-sm-' . $sm .'">
            <label>' . $header . '</label>' .
            Select2::widget([
                'name' => $name,
                'value' => $value,
                'hideSearch' => $hideSearch,
                'data' => $data,
                'options' => [
                    'placeholder' => $message,
                    'multiple' => false,
                ]
            ]) .
            '</div>';
    }

    /**
     * @param string $name
     * @param string $header
     * @param int $sm
     * @param bool $selected
     * @return string
     * @throws \Exception
     */
    public static function renderCheckBox($name, $header, $sm, $selected = true)
    {

        $selected = $selected === true ? 1 : 0;

        return '<div class="col-sm-' . $sm . '"><label class="cbx-label" for="' . $name . '">' . $header . '</label>' .
            CheckboxX::widget([
                'name'=> $name,
                'value' => $selected,
                'options'=> ['id' => $name],
                'pluginOptions'=> ['threeState'=>false]
            ]) . '</div>';

    }
}