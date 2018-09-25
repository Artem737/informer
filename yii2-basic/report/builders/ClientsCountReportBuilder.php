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
 * Class ClientsCountReportBuilder
 * @package app\report\builders
 */
class ClientsCountReportBuilder extends AbstractReportBuilder
{

    /**
     * @return array
     * @throws ReportException
     */
    public function getQuery()
    {
        $dateFrom = ArrayHelper::getValue($this->request, 'date_start_clientsCount');
        $dateTo = ArrayHelper::getValue($this->request, 'date_end_clientsCount');

        if(!$dateFrom || !$dateTo) {
            throw new ReportException('Не введены данные по диапазону выборки.');
        }

        return [
            'query' => 'SELECT * FROM dbo.HardwareEvent where 
            CAST(Time as date) >= :dateFrom and 
            CAST(TIme as date) <= :dateTo
            and ControllerID IN (' . Controller::inputIds(true) .')
            and EventType=:event',
            'params' => [
                ':dateFrom' => $dateFrom,
                ':dateTo' => $dateTo,
                ':event' => Event::CLIENT_PASS
            ]
        ];
    }

    /**
     * @throws \PHPExcel_Exception
     */
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
        $this->excel->getActiveSheet()->getStyle('B2:D' . (2 + count($this->data)))->applyFromArray($styleBorder);
        $this->excel->getActiveSheet()->getStyle('B2:D2')->applyFromArray($styleColor);

        unset($styleArray);
        unset($styleAlignment);
        unset($styleColor);

        $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        
    }

    /**
     * @return array
     */
    public function createSheetNames()
    {
        return ['Данные по посещаемости'];
    }


    /**
     * @return array
     */
    public function prepareData()
    {
        $result = [];

        foreach ($this->data as $row) {
            $dateIndex = date('d-m-Y', strtotime($row['Time']));

            if (!isset($result[$dateIndex]['count'])) {
                $result[$dateIndex]['count'] = 0;
                $result[$dateIndex]['countAll'] = 0;
            }

            $result[$dateIndex]['countAll']++;
            if($row['Comment'] == 'ПРОХОДИТЕ') {
                $result[$dateIndex]['count']++;
            }
        }
        
        return $result;
        
    }

    /**
     * @throws \PHPExcel_Exception
     */
    public function writeData()
    {

        $this->writeToCellStrIndex('B2', 'Дата');
        $this->writeToCellStrIndex('C2', 'Посетителей без сопровождающих');
        $this->writeToCellStrIndex('D2', 'Посетителей с сопровождающими');

        $docIndex = 3;

        foreach ($this->data as $date => $descriptor) {
            $this->writeToCellStrIndex('B' . $docIndex, $date);
            $this->writeToCellStrIndex('C' . $docIndex, $descriptor['count']);
            $this->writeToCellStrIndex('D' . $docIndex++, $descriptor['countAll']);
        }
    }
}