<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\common\Rate;
use app\common\SuperAccount;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     * @throws \yii\db\Exception
     */
    public function actionIndex($message = 'hello world')
    {


//        Rate::regroupByDuration();

        $accounts = SuperAccount::all();

        foreach ($accounts as $accountConstName => $accountId) {
            $ratesNew = \Yii::$app->db->createCommand('EXEC "dbo"."sp_reportOrganizationTotals"
                    @sa = '  . $accountId . ',
                    @from  = \'10.09.2018 0:00\',
                    @to  = \'11.09.2018 0:00\',
                    @hideZeroes = 0,
                    @hideInternal = 0
         ')->queryAll();


            echo $accountConstName . ': ' . PHP_EOL;
            print_r($ratesNew);

        }



//         $ratesNewNew = [];
//         foreach ($ratesNew as $rate) {
//             if($rate['SuperName'] == 'Услуги' && $rate['SuperParentId'] == 3) {
//                 $ratesNewNew[$rate['ViewString']][] = $rate['Name'];
//             }
//         }
//
//        file_put_contents('C:\OSPanel\tmp\fail.txt', var_export(array_merge_recursive($ratesNewNew, $rates), true));

        return ExitCode::OK;
    }
}
