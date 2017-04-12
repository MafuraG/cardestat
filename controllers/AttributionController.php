<?php

namespace app\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Advisor;
use app\models\Invoice;
use app\models\Attribution;
use app\models\TransactionAttribution;
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
                    'roles' => ['accounting']
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
            'query' => TransactionAttribution::find()->where(['transaction_id' => $transaction_id])
        ]);
        $total_invoiced_eu = Invoice::find()
            ->where(['transaction_id' => $transaction_id])
            ->sum('amount_euc') / 100.;
        $advisor_defaults = ArrayHelper::index(Advisor::find()->with('defaultAttributionType')->asArray()->all(), 'id');
        $attribution_types = ArrayHelper::map(AttributionType::find()->all(), 'id', 'attribution_bp');

        $attribution = new Attribution(['transaction_id' => $transaction_id]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $attribution,
            'attribution_types' => $attribution_types,
            'advisor_defaults' => $advisor_defaults,
            'total_invoiced_eu' => $total_invoiced_eu
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

        if ($model->load(Yii::$app->request->post())) {
            if (!Yii::$app->user->can('admin') and $model->transaction->approved_by_direction) throw new ForbiddenHttpException();
            else if ($model->save()) return $this->actionIndex($model->transaction_id);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => TransactionAttribution::find()
                ->where(['transaction_id' => $model->transaction_id])
        ]);
        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'formExpanded' => true
        ]);
    }

    /**
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $transaction_id = $model->transaction_id;
        $model->delete();
        if (Yii::$app->request->isAjax)
            return $this->actionIndex($transaction_id);
        else $this->redirect(['index', 'transaction_id' => $transaction_id]);
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
