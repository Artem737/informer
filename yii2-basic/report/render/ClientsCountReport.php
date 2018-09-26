<?php

namespace app\report\render;

use app\common\helpers\HtmlHelper;

/**
 * Class ClientsCountReport
 * @package app\models\report
 */
class ClientsCountReport implements ReportInterface
{

    public function getName()
    {
        return 'Отчёт по посещениям';
    }

    public function getAlias()
    {
        return 'clientsCount';
    }

    public function getHtmlParams()
    {
        return HtmlHelper::renderRow(HtmlHelper::renderCalendarFromTo('clientsCount'));
    }
}