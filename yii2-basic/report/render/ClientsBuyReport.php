<?php
namespace app\report\render;
use app\common\helpers\HtmlHelper;
use app\report\builders\ClientsBuyReportBuilder;

/**
 * Class ClientsBuyReport
 * @package app\models\report
 */
class ClientsBuyReport implements ReportInterface
{

    public function getName()
    {
        return 'Покупки внутри аквапарка';
    }

    public function getAlias()
    {
        return 'clientsBuy';
    }

    public function getHtmlParams()
    {

        $week = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
        ];

        return HtmlHelper::renderRow(
            HtmlHelper::renderCalendar(ClientsBuyReportBuilder::FORM_DATE_FROM_NAME, 'Время начала', 4) .
            HtmlHelper::renderSelect(ClientsBuyReportBuilder::FORM_DAYS_NAME, 'Количество дней', 4, 1, $week) .
            HtmlHelper::renderSelect(
                ClientsBuyReportBuilder::SELECT_FORM_NAME,
                'Вид',
                4,
                ClientsBuyReportBuilder::BY_TIME,
                [
                    ClientsBuyReportBuilder::BY_TIME => 'Детализация по времени',
                    ClientsBuyReportBuilder::BY_TRANSACTIONS => 'Детализация по оплатам',
                ]
            )
        );

    }
}