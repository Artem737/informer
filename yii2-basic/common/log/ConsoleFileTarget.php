<?php

namespace app\common\log;

use yii\log\FileTarget;

/**
 * Class SmsFileTarget
 * @package app\common\log
 */
class ConsoleFileTarget extends FileTarget
{

    CONST FILE_NAME = 'send.log';

    public function init()
    {
        parent::init();

        $this->maxFileSize = 5120;
        $this->maxLogFiles = 20;

        $time = time();
        $m = date('m', $time);
        $d = date('d', $time);
        $dir = \Yii::$app->getRuntimePath() . "/logs/sms/{$m}/{$d}/";
        $this->logFile = $dir . self::FILE_NAME;

        if (!@file_exists($dir)) {
           @mkdir($dir);
        }

        if (!@file_exists($this->logFile)) {
            @touch($this->logFile);
        }
    }
}