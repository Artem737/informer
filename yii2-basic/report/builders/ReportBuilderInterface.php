<?php

namespace app\report\builders;

/**
 * Interface ReportBuilderInterface
 * @package app\report\builders
 */
interface ReportBuilderInterface
{
    public function getQuery();
    public function setSheets($names);
    public function createSheetNames();
    public function setStyle();
    public function build();
    public function prepareData();
    public function writeData();
    public function getFile();
}