<?php
use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
/**@var string $sendStatus*/
/**@var string $balance*/
?>

<?ActiveForm::begin();?>
    <div class="row">
        <div class='col-sm-8'>
            <?= Html::submitButton('Тестировать', [
                'class' => 'btn btn-success',
                'name' => 'sendSms',
                'value' => 'smsTest'
            ])?>
        </div>
        <div class="col-sm-4">
            <p><?= $balance?></p>
        </div>
    </div>
<?ActiveForm::end();?>
<? if($sendStatus) : ?>
    <div class="row">
        <div class='col-sm-12'>
            <p><?= $sendStatus?></p>
        </div>
    </div>
<? endif;?>
