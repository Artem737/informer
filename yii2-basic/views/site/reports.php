<?php

use app\report\builders\TotalClientsReportBuilder;
use app\report\builders\ClientsBuyReportBuilder;
use kartik\date\DatePicker;
use yii\bootstrap\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

$week = [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
];

function renderRow($innerContent)
{
    return '<div class="row">' . $innerContent . '</div>';
}

function renderCalendar($name, $header = '', $sm = 6, $dateFormat = 'd-m-Y', $messageChoose = 'Выберите дату ...', $more = '')
{
    return
        '<div class="col-sm-' .  $sm . '">
            <label>' . $header . '</label>' .
            DatePicker::widget([
                'name' => $name,
                'language' => 'ru',
                'value' => date($dateFormat, time()),
                'options' => ['placeholder' => $messageChoose],
                'pluginOptions' => [
                    'format' => 'dd-mm-yyyy',
                    'todayHighlight' => true
                ]
            ]) .
        '</div>' .
        $more;
}

function renderCalendarFromTo($name, $sm = 6, $dateFormat = 'd-m-Y', $messageChoose = 'Выберите дату ...', $more = '', $middle = '')
{
    return
        renderCalendar('date_start_' . $name, 'Дата начала промежутка', $sm, $dateFormat, $messageChoose, $middle) .
        renderCalendar('date_end_' . $name, 'Дата окончания промежутка', $sm, $dateFormat, $messageChoose, $more);
}

function renderSelect($name, $header, $sm, $value, $data, $message = '', $hideSearch = true)
{
    return
        '<div class="col-sm-' . $sm .'">
            <label>' . $header . '</label>' .
            Select2::widget([
                'name' => $name,
                'value' => $value,
                'hideSearch' => $hideSearch,
                'data' => $data,
                'options' => [
                    'placeholder' => $message,
                    'multiple' => false,
                ]
            ]) .
        '</div>';
}

$reportsConfig = [
    [
        'alias' => 'clientsCount',
        'header' => 'Отчёт по посещениям',
        'paramsHtml' => renderRow(renderCalendarFromTo('clientsCount'))
    ],
    [
        'alias' => 'totalClients',
        'header' => 'Итоговый отчёт за период',
        'paramsHtml' => renderRow(
            renderCalendarFromTo('totalClients', 4) .
            renderSelect(
                TotalClientsReportBuilder::SELECT_FORM_NAME,
                'Группировка',
                4,
                TotalClientsReportBuilder::BY_CATEGORY,
                [
                    TotalClientsReportBuilder::BY_CATEGORY => 'Группировка по категориям посетителей',
                    TotalClientsReportBuilder::BY_DURATION => 'Группировка по продолжительности пребывания',
                ]
            )
        )
    ],
    [
        'alias' => 'testDrive',
        'header' => 'Пересидки по "Test Drive"',
        'paramsHtml' => renderRow(renderCalendarFromTo('testDrive'))
    ],
    [
        'alias' => 'clientsBuy',
        'header' => 'Покупки внутри аквапарка',
        'paramsHtml' => renderRow(
            renderCalendar(ClientsBuyReportBuilder::FORM_DATE_FROM_NAME, 'Время начала', 4) .
            renderSelect(ClientsBuyReportBuilder::FORM_DAYS_NAME, 'Количество дней', 4, 1, $week) .
            renderSelect(
                ClientsBuyReportBuilder::SELECT_FORM_NAME,
                'Вид',
                4,
                ClientsBuyReportBuilder::BY_TIME,
                [
                    ClientsBuyReportBuilder::BY_TIME => 'Детализация по времени',
                    ClientsBuyReportBuilder::BY_TRANSACTIONS => 'Детализация по оплатам',
                ]
            )
        )
    ]
];

?>

<?foreach ($reportsConfig as $report): ?>
    <?ActiveForm::begin();?>
        <div class="row">
            <div class="col-sm">
                <h1><?=$report['header']?></h1>
            </div>
        </div>

        <?=Html::hiddenInput('reportAlias', $report['alias'])?>

        <?= $report['paramsHtml']?>

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

