<?php

use yii\bootstrap\Html;
use yii\widgets\ActiveForm;
use app\report\render\ReportInterface;

/**
 * @var ReportInterface[] $reports
 */

?>

<?foreach ($reports as $report): ?>
    <?ActiveForm::begin();?>
        <div class="row">
            <div class="col-sm">
                <h1><?=$report->getName()?></h1>
            </div>
        </div>

        <?=Html::hiddenInput('reportAlias', $report->getAlias())?>

        <?= $report->getHtmlParams()?>

        <div class="row" style="margin-top:20px">
            <div class="col-sm-12">
                <?= Html::submitButton('Получить', [
                    'class' => 'btn btn-success'
                ])?>
            </div>
        </div>

    <? ActiveForm::end();?>
    <hr class="col-xs-12">
<? endforeach?>

