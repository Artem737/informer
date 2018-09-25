<?php

namespace app\report\builders;
use app\common\ComplexResult;
use app\common\Controller;
use app\common\Event;
use app\common\helpers\CheckControlHelper;
use app\common\helpers\HardwareEventsHelper;
use app\common\Rate;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use yii\helpers\ArrayHelper;

/**
 * Class ClientsBuyReportBuilder
 * @package app\report\builders
 */
class ClientsBuyReportBuilder extends AbstractReportBuilder
{

    const FORM_DATE_FROM_NAME = 'clientsBuyDate';
    const FORM_SELECT_MODE_NAME = 'selectMode';
    const FORM_DAYS_NAME = 'daysCount';

    private $hardwareEvents;
    private $dateFrom;
    private $dateTo;
    private $mode;
    private $styles = [
        'border' => [
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ]
            ]
        ],
        'colorBlue' => [
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '2E829B']
            ]
        ],
        'colorRed' => [
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D58272']
            ]
        ],
        'colorGreen' => [
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '5F9C8A']
            ]
        ],
        'alignment' => [
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            ]
        ]
    ];

    const BY_TIME = 1;
    const BY_TRANSACTIONS = 2;
    const SELECT_FORM_NAME = 'selectMode';

    public function getQuery()
    {
        $this->dateFrom = ArrayHelper::getValue($this->request, self::FORM_DATE_FROM_NAME);
        $this->dateTo = date(
            'Y-m-d',
            strtotime(
                '+' . ArrayHelper::getValue($this->request, self::FORM_DAYS_NAME, 1) . ' days',
                strtotime($this->dateFrom)
            )
        );

        $this->mode = ArrayHelper::getValue($this->request, self::FORM_SELECT_MODE_NAME);

        return [
            'query' => '
                SELECT Id, TransTime, Name, Price, CardCode FROM dbo.CheckDetail
                LEFT JOIN dbo.MasterTransaction ON dbo.CheckDetail.Id = dbo.MasterTransaction.CheckDetailId
                WHERE
                CAST(TransTime as date) >= :dateFrom AND 
                CAST(TransTime as date) < :dateTo
                ORDER BY TransTime',
            'params' => [
                ':dateFrom' => $this->dateFrom,
                ':dateTo' => $this->dateTo
            ]
        ];
    }

    public function setStyle()
    {
        $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    }

    public function prepareData()
    {

        $result = new ComplexResult();

        $this->hardwareEvents = ArrayHelper::index(
            \Yii::$app->db->createCommand('
                SELECT Card, Time, ControllerId, Comment FROM dbo.HardwareEvent
                WHERE
                CAST(Time as date) >= :dateFrom AND
                CAST(Time as date) < :dateTo AND
                Comment like \'%ПРОХОДИТЕ%\' AND
                EventType=:event AND
                ControllerID IN (' . Controller::all(true) . ')
                ORDER BY Time', [
                ':dateFrom' => $this->dateFrom,
                ':dateTo' => $this->dateTo,
                ':event' => Event::CLIENT_PASS
            ])->queryAll(),
            null,
            'Card'
        );

        $codes = array_unique(ArrayHelper::getColumn($this->data, 'CardCode'));

        $checkControlHelper = new CheckControlHelper($this->data);
        $hardwareEventHelper = new HardwareEventsHelper($this->hardwareEvents);

        Rate::regroupByDuration();

        foreach ($codes as $code) {
            foreach ($hardwareEventHelper->getNextClientInfo($code) as $timeInfo) {
                if($timeInfo &&
                    isset($timeInfo[HardwareEventsHelper::EVENT_INPUT]) &&
                    isset($timeInfo[HardwareEventsHelper::EVENT_OUTPUT])
                ) {

                    $checkData = $checkControlHelper->getChecksByCardCodeAndTimes(
                        $code,
                        $timeInfo[HardwareEventsHelper::EVENT_INPUT]['Time'],
                        $timeInfo[HardwareEventsHelper::EVENT_OUTPUT]['Time']
                    );

                    $rate  = $checkControlHelper->getRateByCode(
                        $code,
                        $timeInfo[HardwareEventsHelper::EVENT_INPUT]['Time']
                    );
                    if ($rate) {
                        $rateGroup = Rate::getCategoryByRateName($rate);

                        foreach ($checkData as $data) {

                            $checkStartHour = date('H', strtotime($data['TransTime']));
                            $checkHourIndex = $checkStartHour . '-'. (intval($checkStartHour) + 1);
                            $currentSum = doubleval($data['Price']);

                            $result->data['data'][$rateGroup]['data'][$rate]['data'][] = $data;
                            $result->data['dataByTime'][$checkHourIndex]['data'][$rateGroup]['data'][$rate]['data'][] =
                                $data;


                            /*Общая сумма*/
                            $result->incrementValue(['count'], 1);
                            $result->incrementValue(['sum'], $currentSum);

                            /*Сумма по группе билетов*/
                            $result->incrementValue(['data', $rateGroup, 'count'], 1);
                            $result->incrementValue(['data', $rateGroup, 'sum'], $currentSum);

                            /*Сумма по времени*/
                            $result->incrementValue(['dataByTime', $checkHourIndex, 'count'], 1);
                            $result->incrementValue(['dataByTime', $checkHourIndex, 'sum'], $currentSum);

                            /*Сумма по группе билетов по времени*/
                            $result->incrementValue(['dataByTime', $checkHourIndex, 'data', $rateGroup, 'count'], 1);
                            $result->incrementValue(['dataByTime', $checkHourIndex, 'data', $rateGroup, 'sum'], $currentSum);

                            /*Сумма по типу билета по времени*/
                            $result->incrementValue(['dataByTime', $checkHourIndex, 'data', $rateGroup, 'data', $rate, 'count'], 1);
                            $result->incrementValue(['dataByTime', $checkHourIndex, 'data', $rateGroup, 'data', $rate, 'sum'], $currentSum);

                            /*Сумма по типу билета*/
                            $result->incrementValue(['data', $rateGroup, 'data', $rate, 'count'], 1);
                            $result->incrementValue(['data', $rateGroup, 'data', $rate, 'sum'], $currentSum);
                        }
                    }
                }
            }
        }

        if(!$result || !$result->data) {
            return [];
        }

        return $result->data;

    }

    public function writeData()
    {
        if ($this->mode == self::BY_TRANSACTIONS) {
            $this->writeDataByTransactions();
        } else if ($this->mode == self::BY_TIME) {
            $this->writeDataByTime();
        }
    }

    private function writeHeader($message)
    {

        $from = date('d.m.Y',  strtotime($this->dateFrom));
        $to = date('d.m.Y',  strtotime('-1 days', strtotime($this->dateTo)));
        $message = $message . ' c ' . $from . ' по ' . $to . '.';

        $this->mergeCells(0,1, 100, 1);
        $this->setCellStyle(0,1, $this->styles['alignment']);
        $this->writeToCell(0,1, $message);
        $this->setCellRangeStyle(1, 2, 2, 2, $this->styles['colorRed']);
        $this->writeToCell(1, 2, 'Общая сумма');
        $this->writeToCell(2, 2, $this->data['sum']);
    }

    private function writeDataByTransactions()
    {

        $this->writeHeader('Список покупок клиентов с разбивкой по продолжительности и типу билета');
        $currentRow = 3;
        foreach ($this->data['data'] as $groupName => $group) {

            $this->setCellRangeStyle(1, $currentRow, 2, $currentRow, $this->styles['colorBlue']);
            $this->writeToCell(1, $currentRow, $groupName);
            $this->writeToCell(2, $currentRow++, $group['sum']);

            foreach ($group['data'] as $rateName => $rate) {

                $this->setCellRangeStyle(1, $currentRow, 2, $currentRow, $this->styles['colorGreen']);
                $this->writeToCell(1, $currentRow, $rateName);
                $this->writeToCell(2, $currentRow++, $rate['sum']);

                foreach ($rate['data'] as $check) {

                    $this->setOutLineLevelForRow($currentRow, 1);
                    $this->writeToCell(1, $currentRow, $check['Name']);
                    $this->writeToCell(2, $currentRow++, $check['Price']);
                }
            }
        }
    }

    private function writeDataByTime()
    {
        $currentRow = 3;
        $this->writeHeader('Список покупок клиентов с разбивкой по часам и типам билетов ');

        if(isset($this->data['dataByTime']) && $this->data['dataByTime']) {
            ksort($this->data['dataByTime']);
        } else {
            $this->data['dataByTime'] = [];
        }

        foreach ($this->data['dataByTime'] as $timeIndex => $rateGroups) {

            $this->setCellRangeStyle(1, $currentRow, 2, $currentRow, $this->styles['colorBlue']);
            $this->writeToCell(1, $currentRow, $timeIndex);
            $this->writeToCell(2, $currentRow++, $rateGroups['sum']);

            foreach ($rateGroups['data'] as $groupName => $group) {

                $this->setCellRangeStyle(1, $currentRow, 2, $currentRow, $this->styles['colorGreen']);
                $this->writeToCell(1, $currentRow, $groupName);
                $this->writeToCell(2, $currentRow++, $group['sum']);

                foreach ($group['data'] as $rateName => $rate) {

                    $this->writeToCell(1, $currentRow, $rateName);
                    $this->writeToCell(2, $currentRow++, $rate['sum']);
                }
            }
        }

    }
}