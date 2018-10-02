<?php

namespace app\common\sms;

use stdClass;
use yii\helpers\ArrayHelper;

class Sender
{
    const API_KEY           = 'D379B08D-007F-079C-9204-11F883484B1D';
    const SENDER            = 'Аквамир';

    private $sender;
    private $numbers;
    private $text;

    /**
     * Sender constructor.
     * @param array $numbers
     * @param $text
     */
    public function __construct(array $numbers = [], $text = '')
    {
        $this->numbers = $numbers;
        $this->text = $text;
        $this->sender = new SMSRU(self::API_KEY);
    }

    /**
     * @return array
     */
    public function send()
    {
        $result = [];

        if (ArrayHelper::getValue(\Yii::$app->params, 'enableSms') === true) {

            try {

                foreach ($this->numbers as $number) {

                    $data = new stdClass();
                    $data->to = $number;
                    $data->text = $this->text;
                    $data->from = self::SENDER;
                    $sms = $this->sender->send_one($data);

                    if ($sms->status == "OK") { // Запрос выполнен успешно
                        $result[$number] .= "Сообщение отправлено успешно. " . PHP_EOL;
                        $result[$number] .= "ID сообщения: $sms->sms_id. " . PHP_EOL;
                        $result[$number] .= "Ваш новый баланс: $sms->balance" . PHP_EOL;
                    } else {
                        $result[$number] .= "Сообщение не отправлено. " . PHP_EOL;
                        $result[$number] .= "Код ошибки: $sms->status_code. " . PHP_EOL;
                        $result[$number] .= "Текст ошибки: $sms->status_text." . PHP_EOL;
                    }
                }
            } catch (\Exception $exception) {
                $result[$number] = 'Отправка провалилась: ' . $exception->getMessage() . PHP_EOL;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getBalance()
    {

        $result = '';
        $request = $this->sender->getBalance();

        if ($request->status == "OK") { // Запрос выполнен успешно
            $result .= "Баланс: $request->balance " . PHP_EOL;
        } else {
            $result .= "Ошибка при выполнении запроса. " . PHP_EOL;
            $result .= "Код ошибки: $request->status_code. " . PHP_EOL;
            $result .= "Текст ошибки: $request->status_text. " . PHP_EOL;
        }

        return $result;
    }
}