<?php

namespace app\common\helpers;

use PHPExcel;
use PHPExcel_Cell;

/**
 * Class PhpExcelHelper
 */
class PhpExcelHelper
{

    /**
     * @param int $index
     * @return string
     */
    public static function indexToString($index)
    {
        return PHPExcel_Cell::stringFromColumnIndex($index);
    }

    /**
     * @param int $x
     * @param int $y
     * @return string
     */
    public static function getCellCoordinateByInt($x, $y)
    {
        return self::indexToString($x) . $y;
    }

    /**
     * @param int $x
     * @return string
     */
    public static function getColumnIndexByInt($x)
    {
        return PHPExcel_Cell::stringFromColumnIndex($x);
    }

    /**
     * @param PHPExcel $excel
     * @param int $x
     * @param int $y
     * @param mixed $value
     * @return PHPExcel_Cell|\PHPExcel_Worksheet
     * @throws \PHPExcel_Exception
     */
    public static function writeToCell(PHPExcel $excel, $x, $y, $value)
    {
        return $excel->getActiveSheet()->setCellValue(self::getCellCoordinateByInt($x, $y), $value);
    }

    /**
     * @param PHPExcel $excel
     * @param string $strIndex
     * @param mixed $value
     * @return PHPExcel_Cell|\PHPExcel_Worksheet
     * @throws \PHPExcel_Exception
     */
    public static function writeToCellStrIndex(PHPExcel $excel, $strIndex, $value)
    {
        return $excel->getActiveSheet()->setCellValue($strIndex, $value);
    }

    /**
     * @param $number
     * @return string
     */
    public static function numberToCellIndex($number)
    {
        return PHPExcel_Cell::stringFromColumnIndex($number);
    }

    /**
     * @param PHPExcel $excel
     * @param int $x
     * @param int $y
     * @param array $style
     * @return \PHPExcel_Style
     * @throws \PHPExcel_Exception
     */
    public static function setCellStyle(PHPExcel $excel, $x, $y, array $style)
    {
        return $excel->getActiveSheet()->getStyle(self::getCellCoordinateByInt($x, $y))->applyFromArray($style);
    }

    /**
     * @param PHPExcel $excel
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @param array $style
     * @return \PHPExcel_Style
     * @throws \PHPExcel_Exception
     */
    public static function setCellRangeStyle(PHPExcel $excel, $x1, $y1, $x2, $y2, array $style)
    {
        return $excel->getActiveSheet()->getStyle(
            self::getCellCoordinateByInt($x1, $y1) . ':' . self::getCellCoordinateByInt($x2, $y2)
        )->applyFromArray($style);
    }

    /**
     * @param PHPExcel $excel
     * @param string $strIndex
     * @param array $style
     * @return \PHPExcel_Style
     * @throws \PHPExcel_Exception
     */
    public static function setCellStyleStrIndex(PHPExcel $excel, $strIndex, array $style)
    {
        return $excel->getActiveSheet()->getStyle($strIndex)->applyFromArray($style);
    }

    /**
     * @param PHPExcel $excel
     * @param int $row
     * @param int $level
     * @param boolean $visible
     * @param boolean $collapsed
     * @return \PHPExcel_Worksheet_RowDimension
     * @throws \PHPExcel_Exception
     */
    public static function setOutLineLevelForRow(PHPExcel $excel, $row, $level, $visible = false, $collapsed = true)
    {
        return
            $excel->getActiveSheet()
                ->getRowDimension($row)
                ->setOutlineLevel($level)
                ->setVisible($visible)
                ->setCollapsed($collapsed);
    }

    /**
     * @param PHPExcel $excel
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @return \PHPExcel_Worksheet
     * @throws \PHPExcel_Exception
     */
    public static function mergeCells(PHPExcel $excel, $x1, $y1, $x2, $y2)
    {
        return
            $excel->getActiveSheet()
                ->mergeCells(
                    self::getCellCoordinateByInt($x1, $y1)  . ':' . self::getCellCoordinateByInt($x2, $y2)
                );
    }

}