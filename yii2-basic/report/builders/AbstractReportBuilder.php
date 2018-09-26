<?php

namespace app\report\builders;
use app\common\helpers\PhpExcelHelper;
use app\report\exception\ReportException;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Class AbstractReportBuilder
 * @package app\report\builders
 */
abstract class AbstractReportBuilder implements ReportBuilderInterface
{
    protected $alias;
    protected $request;
    protected $excel;
    protected $activeSheet;
    protected $sheets;
    protected $sheetNames;
    protected $data;
    protected $deferredData = [];
    private $reportTimestamp;

    public abstract function getQuery();
    public abstract function setStyle();
    public abstract function prepareData();
    public abstract function writeData();

    public function __construct($alias, $request)
    {
        $this->alias = $alias;
        $this->request = $request;
        $this->excel = new PHPExcel();
    }

    /**
     * @param int $x
     * @param int $y
     * @param $value
     * @return \PHPExcel_Cell|\PHPExcel_Worksheet
     * @throws \PHPExcel_Exception
     */
    protected function writeToCell($x, $y, $value)
    {
        return PhpExcelHelper::writeToCell($this->excel, $x, $y, $value);
    }

    /**
     * @param $x
     * @param $y
     * @param array $style
     * @return \PHPExcel_Style
     * @throws \PHPExcel_Exception
     */
    protected function setCellStyle($x, $y, array $style)
    {
        return PhpExcelHelper::setCellStyle($this->excel, $x, $y, $style);
    }

    public function setCellRangeStyle($x1, $y1, $x2, $y2, array $style)
    {
        return PhpExcelHelper::setCellRangeStyle($this->excel, $x1, $y1, $x2, $y2, $style);
    }

    /**
     * @param $strIndex
     * @param array $style
     * @return \PHPExcel_Style
     * @throws \PHPExcel_Exception
     */
    protected function setCellStyleStrIndex($strIndex, array $style)
    {
        return PhpExcelHelper::setCellStyleStrIndex($this->excel, $strIndex, $style);
    }

    /**
     * @param string $strIndex
     * @param mixed $value
     * @return \PHPExcel_Cell|\PHPExcel_Worksheet
     * @throws \PHPExcel_Exception
     */
    protected function writeToCellStrIndex($strIndex, $value)
    {
        return PhpExcelHelper::writeToCellStrIndex($this->excel, $strIndex, $value);
    }

    /**
     * @param int $row
     * @param int $level
     * @param boolean $visible
     * @param boolean $collapsed
     * @return \PHPExcel_Worksheet_RowDimension
     * @throws \PHPExcel_Exception
     */
    public function setOutLineLevelForRow( $row, $level, $visible = false, $collapsed = true)
    {
        return PhpExcelHelper::setOutLineLevelForRow($this->excel, $row, $level, $visible, $collapsed);
    }

    /**
     * @param $x1
     * @param $y1
     * @param $x2
     * @param $y2
     * @return \PHPExcel_Worksheet
     * @throws \PHPExcel_Exception
     */
    public function mergeCells($x1, $y1, $x2, $y2)
    {
        return PhpExcelHelper::mergeCells($this->excel, $x1, $y1, $x2, $y2);
    }

    /**
     * Сгенерировать старицы 
     * @param array $names
     * @throws \PHPExcel_Exception
     */
    public function setSheets($names)
    {

        if(!$names) {
            $names = ['Отчёт'];
        }

        if(!is_array($names)) {
            $names = [$names];
        }

        $sheetIndex = 0;

        foreach ($names as $name) {

            $this->sheets[$sheetIndex] = $name;
            if($sheetIndex != 0) {
                $this->excel->createSheet($sheetIndex);
            }

            $this->excel->getSheet($sheetIndex++)->setTitle($name);

        }

        $this->excel->setActiveSheetIndex(0);
    }

    /**
     * Сделать отчёт
     * @throws ReportException
     * @throws \PHPExcel_Writer_Exception
     * @throws \yii\db\Exception
     * @throws \PHPExcel_Exception
     */
    public function build()
    {
        $query = $this->getQuery();

        if($query instanceof ActiveQuery) {

            $this->data = $query->asArray()->all();

        } else if (is_array($query) && isset($query['query']) || is_string($query)) {

            $params = ArrayHelper::getValue($query, 'params', []);
            $query = is_array($query) ? $query['query'] : $query;
            $this->data = \Yii::$app->db->createCommand($query, $params)->queryAll();

        } else {
            throw new ReportException('Неверный формат запроса.');
        }

        $this->data = $this->prepareData();
        $this->setSheets($this->createSheetNames());
        $this->setStyle();
        $this->writeData();
        $this->writeDeferredData();

        $objWriter = new PHPExcel_Writer_Excel2007($this->excel);
        $objWriter->save($this->getFile());
    }

    /**
     * Путь по которому хранится сгенерированный отчёт
     * @return string
     */
    public function getFile()
    {
        $this->reportTimestamp = $this->reportTimestamp ? $this->reportTimestamp : time();
        return 'C:\OSPanel\tmp\report_' . $this->alias . '_' .$this->reportTimestamp .'.xlsx';
    }

    /**
     * @return mixed
     */
    public function createSheetNames()
    {
        return $this->sheetNames;
    }

    /**
     * @param string $sheet
     * @param int $x
     * @param int $y
     * @param string $initValue
     * @param bool $strIndex
     */
    public function setDeferredData($sheet, $x, $y, $initValue = '', $strIndex = true)
    {
        if(!$strIndex) {
            $x = PhpExcelHelper::indexToString($x);
        }

        $this->deferredData[$sheet][$x][$y] = $initValue;
    }

    /**
     * @throws \PHPExcel_Exception
     */
    public function writeDeferredData()
    {
        foreach ($this->deferredData as $sheetName => $sheet) {
            $this->excel->setActiveSheetIndexByName($sheetName);
            foreach ($sheet as $x => $row) {
                foreach ($row as $y => $data) {
                    $this->writeToCellStrIndex($x . $y, $data);
                }
            }
        }
    }


}