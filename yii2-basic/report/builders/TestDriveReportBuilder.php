<?php

namespace app\report\builders;
use app\common\Controller;
use app\common\Event;
use app\report\exception\ReportException;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use yii\helpers\ArrayHelper;

/**
 * Class TestDriveReportBuilder
 * @package app\report\builders
 */
class TestDriveReportBuilder extends AbstractReportBuilder
{

    private $dateFrom;
    private $dateTo;
    private $hardwareEvents;


    public function __construct($alias, $request)
    {
        parent::__construct($alias, $request);

        $this->dateFrom = ArrayHelper::getValue($this->request, 'date_start_testDrive');
        $this->dateTo = ArrayHelper::getValue($this->request, 'date_end_testDrive');

        if(!$this->dateFrom || !$this->dateTo) {
            throw new ReportException('Не введены данные по диапазону выборки.');
        }
    }

    public function getQuery()
    {

        return [
            'query' => '
                SELECT CardCode, TransTime, Name  FROM dbo.CheckDetail
				LEFT JOIN dbo. MasterTransaction ON dbo. MasterTransaction.CheckDetailId=dbo.CheckDetail.Id
                WHERE 
                CAST(TransTime as date) >= :dateFrom AND 
                CAST(TransTime as date) <= :dateTo AND
                Name like \'%Тестдрайв%\' AND
                ReturnCheckDetailId IS NULL
                ORDER BY TransTime',
            'params' => [
                ':dateFrom' => $this->dateFrom,
                ':dateTo' => $this->dateTo,
            ]
        ];
    }

    public function setStyle()
    {

        $styleAlignment = [
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ]
        ];

        $styleBorder = [
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ]
            ]
        ];

        $styleColor = [
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '2E829B']
            ]
        ];

        $this->excel->getActiveSheet()->getDefaultStyle()->applyFromArray($styleAlignment);
        $this->excel->getActiveSheet()->getStyle('B2:F' . (2 + count($this->data)))->applyFromArray($styleBorder);


        $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
    }

    public function prepareData()
    {

        $result = [];
        $this->hardwareEvents = [];

        if($this->data) {

            $codesStr = implode(
                ',',
                array_map(
                    function($value) { return "'" . $value . "'";},
                    ArrayHelper::getColumn(
                        $this->data,
                        'CardCode'
                    )
                )
            );

            $this->hardwareEvents = ArrayHelper::index(
                \Yii::$app->db->createCommand('
                SELECT Card, Time, ControllerId, Comment FROM dbo.HardwareEvent
                WHERE
                CAST(Time as date) >= :dateFrom AND
                CAST(Time as date) <= :dateTo AND
                Card IN (' . $codesStr . ') AND
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
        }

        foreach ($this->data as $row) {
            $result[] =  [
                'code' => $row['CardCode'],
                'name' => $row['Name'],
                'from' => $this->findTimes($row['CardCode'], $row['TransTime'], $to, $comment),
                'to' => $to,
                'comment' => $comment,
            ];
        }
        
        return $result;
    }

    /**
     * @param string $cardCode
     * @param string $transTime
     * @param string $to
     * @param string $comment
     * @return string
     * @throws \Exception
     */
    private function findTimes($cardCode, $transTime, &$to, &$comment)
    {
        $today = date('Y-m-d', strtotime($transTime)) == date('Y-m-d');
        $notFoundMsg = $today ? 'В аквапарке' : 'Выход не зарегистрирован';
        $cardDispenceTime = strtotime($transTime);
        $to = '';
        $comment = '';
        $from = '';

        try {
            if (!isset($this->hardwareEvents[$cardCode])) {
                throw new \Exception('Не найден вход');
            }

            ArrayHelper::multisort($this->hardwareEvents[$cardCode], 'Time');

            foreach ($this->hardwareEvents[$cardCode] as  $cardHardwareEvents) {

                $isInput = in_array($cardHardwareEvents['ControllerId'], Controller::inputIds());
                $cardTime = strtotime($cardHardwareEvents['Time']);

                if (!$from && $cardDispenceTime > $cardTime) {
                    continue;
                }

                if (!$from && $isInput && strtotime($transTime) <= $cardTime) {
                    $from = $cardHardwareEvents['Time'];
                }




                if(!$to && !$isInput && $from) {

                    if(date('Y-m-d', strtotime($from)) < date('Y-m-d', $cardTime)) {
                        break;
                    }

                    $to = $cardHardwareEvents['Time'];
                    $comment = str_replace(['ПРОХОДИТЕ', 'Длит-ть'], '', $cardHardwareEvents['Comment']);
                    return $from;
                }

            }

            $comment = $from ? $notFoundMsg : 'Вход не зарегистрирован';

        }

        catch (\Exception $ex) {
            $comment = $ex->getMessage();
        }

        return $from;

    }

    public function writeData()
    {
        $this->writeToCellStrIndex('B2', 'Код браслета');
        $this->writeToCellStrIndex('C2', 'Имя услуги');
        $this->writeToCellStrIndex('D2', 'Время входа');
        $this->writeToCellStrIndex('E2', 'Время выхода');
        $this->writeToCellStrIndex('F2', 'Время нахождения');

        $rowIndex = 0;
        foreach ($this->data as $row) {

            $this->writeToCell(1,3 + $rowIndex, $row['code']);
            $this->writeToCell(2,3 + $rowIndex, $row['name']);

            $isStarted = strtotime($row['from']) !== false;
            if($isStarted) {
                $this->writeToCell(3, 3 + $rowIndex, date('d.m.Y H:i:s', strtotime($row['from'])));
            } else {
                $this->writeToCell(3, 3 + $rowIndex, $row['from']);
            }

            $inFinished = strtotime($row['to']) !== false;
            if($inFinished) {
                $this->writeToCell(4,3 + $rowIndex, date('d.m.Y H:i:s', strtotime($row['to'])));
            } else {
                $this->writeToCell(4,3 + $rowIndex, $row['to']);
            }

            $this->writeToCell(5,3 + $rowIndex, $row['comment']);

            $rowIndex++;
        }
    }
}