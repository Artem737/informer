<?php

namespace app\commands;

use app\common\Event;
use app\common\sms\Sender;
use app\common\SuperAccount;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class SmsController
 * Отправка смс учасникам рассылки
 * @package app\controllers
 */
class SmsController extends Controller
{
    public function actionSummary()
    {
        ob_start();
        $messageCategory = 'app\commands\\';

        try {

            $dateSms = date('m.d на H:i');
            $dateFrom = date('d.m.Y');
            $dateTo = date('d.m.Y', time() + 24 * 60 * 60);

            \Yii::info("SEND STARTED...", $messageCategory);


            $totalPeoples = \Yii::$app->db->createCommand('
            SELECT COUNT(*) FROM dbo.HardwareEvent WHERE 
            CAST(Time AS date) = :date 
            AND ControllerID IN (' . \app\common\Controller::inputIds(true) . ')
            AND Comment LIKE \'%ПРОХОДИТЕ%\'
            AND EventType=:event', [
                ':date' => date('Y-m-d'),
                ':event' => Event::CLIENT_PASS
            ])->queryScalar();

            $totalWeb = array_reduce(
                ArrayHelper::getColumn(
                    \Yii::$app->db->createCommand('
                    EXEC "dbo"."sp_reportOrganizationTotals"
                    @sa = :superAccount,
                    @from  = :dateFrom,
                    @to  = :dateTo,
                    @hideZeroes = 0, 
                    @hideInternal = 0',
                        [
                            ':dateFrom' => $dateFrom,
                            ':dateTo' => $dateTo,
                            ':superAccount' => SuperAccount::AKVAMIR,
                        ])->queryAll(),
                    'Amount'),
                function ($carry, $item) {
                    $carry += $item;
                    return $carry;
                }
            );

            $totalAquaPark = array_reduce(
                ArrayHelper::getColumn(
                    \Yii::$app->db->createCommand('
                    EXEC "dbo"."sp_reportOrganizationTotals"
                    @sa = :superAccount,
                    @from  = :dateFrom,
                    @to  = :dateTo,
                    @hideZeroes = 0, 
                    @hideInternal = 0',
                        [
                            ':dateFrom' => $dateFrom,
                            ':dateTo' => $dateTo,
                            ':superAccount' => SuperAccount::VDT,
                        ])->queryAll(),
                    'Amount'),
                function ($carry, $item) {
                    $carry += $item;
                    return $carry;
                }
            );

            $totalSum = $totalAquaPark + $totalWeb;
            $message = 'Статистика от ' . $dateSms . ':' . PHP_EOL . $totalPeoples . PHP_EOL . $totalSum;

            $sender = new Sender([
                Sender::TEST_NUMBER,
//                Sender::CONSTANTIN_NYMBER,
//                Sender::OWNER1_NUMBER,
//                Sender::OWNER2_NUMBER,
//            Sender::MIHAIL_NYMBER,
//            Sender::ALEXEY_NUMBER,
            ], $message);
            $result = $sender->send();
            $resultAsString = print_r($result, true);

            \Yii::info('SEND_RESULT:' . ($resultAsString ? $resultAsString :
                'Нет результата отправки, возможно отправка отключена в params.php'), $messageCategory);

        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), $messageCategory);
        }

        $stdout = ob_get_contents();
        ob_flush();

        \Yii::info('STDOUT = ' . $stdout, $messageCategory);
        \Yii::info('SEND FINISHED', $messageCategory);
    }
}