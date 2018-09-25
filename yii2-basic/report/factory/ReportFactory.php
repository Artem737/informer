<?php

namespace app\report\factory;

use app\report\builders\ReportBuilderInterface;

/**
 * Class ReportFactory
 */
class ReportFactory
{
    /**
     * @param $alias
     * @param $request
     * @return ReportBuilderInterface
     * @throws \Exception
     */
    public static function makeReportBuilder($alias, $request)
    {
        $first = mb_substr($alias,0,1);
        $last = mb_substr($alias,1);
        $namespace = 'app\\report\\builders\\';
        $class = $namespace . strtoupper($first) . $last . 'ReportBuilder';

        if (!class_exists($class)) {
            throw new \Exception('Построитель отчёта с именем ' . $alias . ' не найден');
        }

        return new $class($alias, $request);
    }
}