<?php

namespace app\controllers;

use app\common\sms\Sender;
use app\report\render\ClientsBuyReport;
use app\report\render\ClientsCountReport;
use app\report\render\TestDriveReport;
use app\report\render\TotalClientsReport;
use app\report\factory\ReportFactory;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionReport()
    {
        if(\Yii::$app->user->isGuest) {
            return $this->render('need_auth');
        }

        $post = \Yii::$app->request->post();
        $alias = ArrayHelper::getValue($post, 'reportAlias');

        if (\Yii::$app->user->identity->getId() == 105) {
            $reports = [
                new ClientsCountReport(),
            ];
        } else {
            $reports = [
                new ClientsCountReport(),
                new TotalClientsReport(),
                new TestDriveReport(),
                new ClientsBuyReport(),
            ];
        }



        if($alias) {
            $reportBuilder =  ReportFactory::makeReportBuilder($alias, $post);
            $reportBuilder->build();
            \Yii::$app->response->sendFile($reportBuilder->getFile());

        } else {
            return $this->render('reports', [
                'reports' => $reports
            ]);
        }
    }

    public function actionSms()
    {

        if(\Yii::$app->user->isGuest) {
            return $this->render('need_auth');
        }

        $result = '';
        if(ArrayHelper::getValue(\Yii::$app->request->post(), 'sendSms')) {
            $sender = new Sender(['79963761251'], 'Тест отправки.');
            $result = ArrayHelper::getValue($sender->send(), '79963761251');
            $result = $result ? $result : 'Отправка СМС отключена';
        }

        $balance = (new Sender())->getBalance();

        return $this->render('sms', [
            'sendStatus' => $result,
            'balance' => $balance,
        ]);
    }
}
