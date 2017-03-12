<?php

namespace app\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Advisor;
use app\models\Attribution;
use app\models\AttributionType;
use yii\helpers\ArrayHelper;

/**
 * AttributionController implements the CRUD actions for Attribution model.
 */
class AttributionController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [[
                    'allow' => true,
                    'roles' => ['@']
                ]]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all TransactionListItem models.
     * @return mixed
     */
    public function actionIndex($transaction_id)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Attribution::find()->where(['transaction_id' => $transaction_id])
        ]);
        $advisor_defaults = ArrayHelper::index(Advisor::find()->with('defaultAttributionType')->asArray()->all(), 'id');
        $attribution_types = ArrayHelper::map(AttributionType::find()->all(), 'id', 'attribution_bp');

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => new Attribution([
                'transaction_id' => $transaction_id
            ]),
            'attribution_types' => $attribution_types,
            'advisor_defaults' => $advisor_defaults,
        ]);
    }

    /**
     * Creates a new Attribution model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Attribution();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->actionIndex($model->transaction_id);
        } else {
            $dataProvider = new ActiveDataProvider([
                'query' => Attribution::find()->where(['transaction_id' => $model->transaction_id])
            ]);
            return $this->render('index', [
                'model' => $model,
                'dataProvider' => $dataProvider,
                'formExpanded' => true
            ]);
        }
    }

    protected function findModel($id)
    {
        if (($model = Attribution::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
