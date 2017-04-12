<?php

namespace app\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Invoice;

/**
 * InvoiceController implements the CRUD actions for Invoice model.
 */
class InvoiceController extends Controller
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
            'query' => Invoice::find()->where(['transaction_id' => $transaction_id])
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => new Invoice([
                'transaction_id' => $transaction_id
            ])
        ]);
    }

    /**
     * Creates a new Invoice model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Invoice();

        if ($model->load(Yii::$app->request->post())) {
            if (!Yii::$app->user->can('admin') and $model->transaction->approved_by_direction) throw new ForbiddenHttpException();
            else if ($model->save()) return $this->actionIndex($model->transaction_id);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => Invoice::find()->where(['transaction_id' => $model->transaction_id])
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
        if (!Yii::$app->user->can('admin') and $model->transaction->approved_by_direction) throw new ForbiddenHttpException();
        $transaction_id = $model->transaction_id;
        $model->delete();
        if (Yii::$app->request->isAjax)
            return $this->actionIndex($transaction_id);
        else $this->redirect(['index', 'transaction_id' => $transaction_id]);
    }

    protected function findModel($id)
    {
        if (($model = Invoice::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
