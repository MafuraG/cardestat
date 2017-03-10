<?php

namespace app\controllers;

use Yii;
use app\models\Invoice;
use app\models\Attribution;
use app\models\Transaction;
use app\models\TransactionListItem;
use app\models\TransactionListItemSearch;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * TransactionController implements the CRUD actions for TransactionListItem model.
 */
class TransactionController extends Controller
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
    public function actionIndex()
    {
        $searchModel = new TransactionListItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single TransactionListItem model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findListModel($id),
        ]);
    }

    /**
     * Creates a new TransactionListItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Transaction();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing TransactionListItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id, true);
        $invoice = new Invoice(['transaction_id' => $id]);
        $invoiceDataProvider = new ActiveDataProvider([
            'query' => $invoice->find()->where(['transaction_id' => $id])
        ]);
        $attribution = new Attribution(['transaction_id' => $id]);
        $attributionDataProvider = new ActiveDataProvider([
            'query' => $attribution->find()->where(['transaction_id' => $id])
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->isAjax)
                return $this->actionView($model->id);
            else return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $data = [
                'model' => $model,
                'invoice' => $invoice,
                'attribution' => $attribution,
                'invoiceDataProvider' => $invoiceDataProvider,
                'attributionDataProvider' => $attributionDataProvider
            ];
            if (Yii::$app->request->isAjax) return $this->renderAjax('_form', $data);
            else return $this->render('update', $data);
        }
    }

    /**
     * Deletes an existing TransactionListItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TransactionListItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TransactionListItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findListModel($id)
    {
        if (($model = TransactionListItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function findModel($id, $with_related = false)
    {
        if ($with_related) {
            if (($model = Transaction::find()
                ->with(['invoices', 'attributions.advisor', 'attributions.attributionType'])
                ->where(['id' => $id]) 
                ->one()) !== null) return $model;
            else throw new NotFoundHttpException('The requested page does not exist.');
        } else if (($model = Transaction::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
