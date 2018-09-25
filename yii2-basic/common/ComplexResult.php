<?php

namespace app\common;
use yii\helpers\ArrayHelper;

/**
 * Class ComplexResult
 * @package app\common
 */
class ComplexResult
{

    public $data = [];


    public function addValue($path, $data)
    {
        $currentData = ArrayHelper::getValue($this->data, $path, []);
        $currentData[] = $data;
        ArrayHelper::setValue($this->data, $path, $currentData);
    }

    /**
     * @param array $path
     * @param mixed $value
     */
    public function incrementValue(array $path, $value)
    {

        $currentValue = &$this->data;
        foreach ($path as $part) {

            $isset = isset($currentValue[$part]);
            $last = !next($path);

            if(!$isset) {
                $currentValue[$part] = $last ? $value : [];
            } else if($last) {
                $currentValue[$part] += $value;
            }

            $currentValue = &$currentValue[$part];
        }
    }

    public function getData()
    {
        return $this->data;
    }

}