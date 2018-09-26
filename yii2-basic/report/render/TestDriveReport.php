<?php


namespace app\report\render;

use app\common\helpers\HtmlHelper;

/**
 * Class TestDriveReport
 * @package app\models\report
 */
class TestDriveReport implements ReportInterface
{

    public function getName()
    {
        return 'Пересидки по "Test Drive"';
    }

    public function getAlias()
    {
        return'testDrive' ;
    }

    public function getHtmlParams()
    {
        return HtmlHelper::renderRow(HtmlHelper::renderCalendarFromTo('testDrive'));
    }
}