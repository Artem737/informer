<?php

namespace app\report\render;

use app\common\helpers\HtmlHelper;
use app\report\builders\TotalClientsReportBuilder;

/**
 * Class TotalClientsReport
 * @package app\models\report
 */
class TotalClientsReport implements ReportInterface
{

    public function getName()
    {
        return 'Итоговый отчёт за период';
    }

    public function getAlias()
    {
        return 'totalClients';
    }

    public function getHtmlParams()
    {
        return HtmlHelper::renderRow(
            HtmlHelper::renderCalendarFromTo('totalClients', 3) .
            HtmlHelper::renderSelect(
                TotalClientsReportBuilder::SELECT_FORM_NAME,
                'Группировка',
                4,
                TotalClientsReportBuilder::BY_CATEGORY,
                [
                    TotalClientsReportBuilder::BY_CATEGORY => 'Группировка по категориям посетителей',
                    TotalClientsReportBuilder::BY_DURATION => 'Группировка по продолжительности пребывания',
                ]
            ) .
            HtmlHelper::renderCheckBox('percents', 'Строка процентов', 2)
        )
            ;
    }
}