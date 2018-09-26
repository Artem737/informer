<?php

namespace app\report\builders;
use app\common\helpers\DateHelper;
use app\common\helpers\MathHelper;
use app\common\helpers\PhpExcelHelper;
use app\common\Rate;
use app\report\exception\ReportException;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use yii\helpers\ArrayHelper;

/**
 * Class TotalClientsReportBuilder
 * @package app\report\builders
 */
class TotalClientsReportBuilder extends AbstractReportBuilder
{

    const ROW_INITIAL = 4;
    const BY_DURATION = 2;
    const BY_CATEGORY = 1;
    const SELECT_FORM_NAME= 'selectGroup';
    private $datesBySheetCache = [];
    private $currentRow = self::ROW_INITIAL;
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
        ]
    ];

    public function getQuery()
    {

        $dateFrom = ArrayHelper::getValue($this->request, 'date_start_totalClients');
        $dateTo = ArrayHelper::getValue($this->request, 'date_end_totalClients');
        $mode = ArrayHelper::getValue($this->request, self::SELECT_FORM_NAME);


        if (!$dateFrom || !$dateTo || !$mode) {
            throw new ReportException('Не введены данные по диапазону выборки.');
        }

        if ($mode == self::BY_DURATION) {
            Rate::regroupByDuration();
        }

        return [
            'query' =>
                'SELECT Name, Data, Price FROM dbo.CheckDetail 
                LEFT JOIN "dbo"."Check" ON dbo.CheckDetail.CheckId = "dbo"."Check"."CheckId"
                WHERE 
                CAST(Data as date) >= :dateFrom AND 
                CAST(Data as date) <= :dateTo AND
                Name IN (' . Rate::getRates(true) .') AND
                ReturnCheckDetailId IS NULL',
            'params' => [
                ':dateFrom' => $dateFrom,
                ':dateTo' => $dateTo,
            ],
        ];
    }

    /**
     * @return array
     */
    public function prepareData()
    {
        $result = [];

        foreach ($this->data as $row) {
            $rate = $row['Name'];
            $category = Rate::getCategoryByRateName($rate);
            $month = date('Y-m', strtotime($row['Data']));
            $date = date('Y-m-d', strtotime($row['Data']));
            if(!isset($result[$month][$category][$rate][$date])) {
                $result[$month][$category][$rate][$date] = [
                    'count' => 0,
                    'sum' => 0,
                ];
            }

            $result[$month][$category][$rate][$date]['count']++;
            $result[$month][$category][$rate][$date]['sum']+= $row['Price'];

        }

        $this->sheetNames = array_keys($result);

        return $result;

    }

    /**
     * @param $sheetName
     * @return array
     */
    public function getDatesBySheet($sheetName)
    {
        if (isset($this->datesBySheetCache[$sheetName])) {
            return $this->datesBySheetCache[$sheetName];
        }

        $data= $this->data[$sheetName];
        $dates = [];
        foreach ($data as $category => $rate) {
            foreach ($rate as $rateName => $date) {
                foreach ($date as $dateValue => $transactions) {
                    $dates[$dateValue] = $transactions;
                }
            }
        }

        $result = $dates;
        $this->datesBySheetCache[$sheetName] = $result;
        return $result;
    }

    public function getSheetColumnsCount($sheetName)
    {
        return 3 + count($this->getDatesBySheet($sheetName));
    }

    public function getSheetRowsCount()
    {
        return 5 + count(Rate::getRates()) + count(Rate::getCategories());
    }

    /**
     * @param $sheetName
     * @throws \PHPExcel_Exception
     */
    private function setCurrentPageStyle($sheetName)
    {
        $styleAlignment = [
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ]
        ];

        $this->excel->getActiveSheet()->getDefaultStyle()->applyFromArray($styleAlignment);

        for($i = 0; $i < $this->getSheetColumnsCount($sheetName); $i++) {
            $this->excel->getActiveSheet()->getColumnDimension(PhpExcelHelper::getColumnIndexByInt($i))->setAutoSize(true);
        }

        $this->excel->getActiveSheet()->getStyle('A1:A256')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

    }

    public function setStyle()
    {
        foreach ($this->sheets as $index => $sheetName) {
            $this->excel->setActiveSheetIndex($index);
            $this->setCurrentPageStyle($sheetName);
        }
    }

    public function writeData()
    {

        foreach ($this->sheets as $index => $sheetName) {

            $this->excel->setActiveSheetIndex($index);
            $dates = array_keys($this->getDatesBySheet($sheetName));

            /*Header*/

            $this->writeToCell(0, 3, 'Всего по датам');
            $this->setCellRangeStyle(0, 3, 2 + count($dates), 3,$this->styles['colorRed']);

            $datesByIndex = [];
            foreach ($dates as $dateIndex => $date) {
                $this->writeToCell(1 + $dateIndex, 1, DateHelper::translateDayInStr(date('d.m(l)', strtotime($date))));
                $datesByIndex[$date] = PhpExcelHelper::getColumnIndexByInt(1 + $dateIndex);
            }

            $this->writeToCell(1 + count($dates), 1, 'Колличество');
            $this->writeToCell(2 + count($dates), 1, 'Сумма');


            /*Data*/
            $total = [];
            foreach ($this->data[$sheetName] as $categoryName => $category) {
                $total[] = $this->writeSheetCategory($categoryName, $category, $datesByIndex);
            }

            /*Total data by days in header*/
            $this->writeDataByDay($total, $datesByIndex, $sheetName);

            $this->currentRow = self::ROW_INITIAL;

        }
    }

    /**
     * @param string $categoryName
     * @param array $data
     * @param array $datesByIndex
     * @return array
     * @throws \PHPExcel_Exception
     */
    private function writeSheetCategory($categoryName, $data, array $datesByIndex)
    {
        $percents = ArrayHelper::getValue($this->request, 'percents', 0);
        $percents = $percents == 1 ? true : false;

        $initialRow = $this->currentRow++;
        if ($percents) {
            $this->currentRow++;
        }

        $countCategory = [];
        $sumCategory = [];

        foreach ($data as $rate => $transactions) {

            $count = 0;
            $sum = 0;

            /*Show rate name*/
            $this->writeToCell(0, $this->currentRow, $rate);

            /*Show rates count by date*/
            foreach ($transactions as $date => $transaction) {

                $count+= $transaction['count'];
                $sum+= $transaction['sum'];

                if(!isset($countCategory[$date])) {
                    $countCategory[$date] = 0;
                    $sumCategory[$date] = 0;
                }

                $countCategory[$date] += $transaction['count'];
                $sumCategory[$date] += $transaction['sum'];

                $this->writeToCellStrIndex($datesByIndex[$date] . $this->currentRow, $transaction['count']);
            }


            /*Show total count's by category*/
            $this->writeToCell(1 + count($datesByIndex), $this->currentRow, $count);
            $this->writeToCell(2 + count($datesByIndex), $this->currentRow++, $sum);

        }

        /*Show Category and total count's by dates*/
        $this->writeToCell(0, $initialRow, $categoryName);
        $this->setCellRangeStyle(0, $initialRow, 2 + count($datesByIndex), $initialRow, $this->styles['colorBlue']);

        if ($percents) {
            $this->setCellRangeStyle(0, $initialRow + 1, 2 + count($datesByIndex), $initialRow + 1, $this->styles['colorBlue']);
        }

        $count = 0;
        $sum = 0;

        foreach ($countCategory as $date => $amount) {
            $count+= $amount;
            $this->writeToCellStrIndex($datesByIndex[$date] . $initialRow, $amount);
        }

        if ($percents) {
            foreach ($countCategory as $date => $amount) {
                $this->setDeferredData(
                    $this->excel->getActiveSheet()->getTitle(),
                    $datesByIndex[$date],
                    $initialRow + 1,
                    $amount
                );
            }
        }

        foreach ($sumCategory as $date => $amount) {
            $sum += $amount;
        }

        $this->writeToCell(1 + count($datesByIndex),  $initialRow, $count);
        $this->writeToCell(2 + count($datesByIndex),  $initialRow, $sum);

        if ($percents) {
            $this->setDeferredData(
                $this->excel->getActiveSheet()->getTitle(),
                1 + count($datesByIndex),
                $initialRow + 1,
                $count,
                false
            );
        }

        return [
            'count' => $countCategory,
            'sum' => $sumCategory
        ];
    }

    /**
     * @param array $total
     * @param array $daysByIndex
     * @param $sheetName
     * @throws \PHPExcel_Exception
     */
    private function writeDataByDay(array $total, array $daysByIndex, $sheetName)
    {
        $countByDate = [];
        $sumByDate = [];
        $totalCount = 0;
        $totalSum = 0;

        foreach ($total as $categoryData) {

            $dataCount = ArrayHelper::getValue($categoryData, 'count', []);
            $dataSum = ArrayHelper::getValue($categoryData, 'sum', []);

            foreach ($dataCount as $date => $count) {
                if(!isset($countByDate[$date])) {
                    $countByDate[$date] = 0;
                }
                $countByDate[$date] += $count;
                $totalCount += $count;
            }

            foreach ($dataSum as $date => $sum) {
                if(!isset($sumByDate[$date])) {
                    $sumByDate[$date] = 0;
                }
                $sumByDate{$date} += $sum;
                $totalSum += $sum;
            }
        }

        foreach ($countByDate as $date => $count) {

            $colIndex = $daysByIndex[$date];

            $this->writeToCellStrIndex($colIndex . '3', $count);
            $this->setCellStyleStrIndex($colIndex . '3', $this->styles['colorRed']);

            if (isset($this->deferredData[$sheetName][$colIndex])) {
                foreach ($this->deferredData[$sheetName][$colIndex] as $rowIndex => &$value) {
                    $value = MathHelper::percent($value, $count, true);
                }
            }

        }

        $percentTotalCollIndex = PhpExcelHelper::indexToString(1 + count($daysByIndex));
        if (isset($this->deferredData[$sheetName][$percentTotalCollIndex])) {
            foreach ($this->deferredData[$sheetName][$percentTotalCollIndex] as $rowIndex => &$value) {
                $value = MathHelper::percent($value, $totalCount, true);
            }
        }

        $this->writeToCell(1 + count($daysByIndex), 3, $totalCount);
        $this->writeToCell(2 + count($daysByIndex), 3, $totalSum);

    }
}