<?php

namespace app\config;
use yii\helpers\ArrayHelper;

/**
 * Class EnvHelper
 */
class EnvHelper
{
    const ENV_FILE_NAME = '.env';
    private static $params;

    /**
     * @return string
     */
    private static function  getPath()
    {
        return __DIR__ . '/../../' . self::ENV_FILE_NAME;
    }

    /**
     * @param $paramName
     * @return mixed
     * @throws \Exception
     */
    public static function getParam($paramName)
    {
        if(!self::$params) {
            self::getParams();
        }

        return ArrayHelper::getValue(self::$params, $paramName);

    }

    /**
     * @throws \Exception
     */
    private static function getParams()
    {
        $filePath = self::getPath();
        if(!file_exists($filePath)) {
            throw new \Exception('Файл окружения не найден по пути ' . $filePath);
        }

        $fileData = file($filePath);

        foreach ($fileData as $line) {
            if(ArrayHelper::getValue($line, 0) !== '#') {
                $explodedData = explode('=', $line);
                if(count($explodedData) > 1) {
                    self::$params[$explodedData[0]] = $explodedData[1];
                }
            }
        }
    }


}