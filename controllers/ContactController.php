<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use app\models\Contact;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class ContactController extends Controller
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
                    'on-office' => ['POST']
                ],
            ],
        ];
    }

    /**
     * Lists all Contact models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Contact::find(),
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_DESC]
            ]
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionSyncOnoffice() {
        $consoleController = new \app\commands\CsvImportController('csv-import', Yii::$app); 
        ini_set('memory_limit', '1G');
        try {
            $consoleController->runAction('contacts');
        } catch (\Exception $e) {
            throw new ServerErrorHttpException(Yii::t('app', 'Field mapping broken. Please check the mapping for onOffice CSV'));
        }
    }

    public function actionList($q = null, $id = null, $page = 1)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = Contact::find();
            $query->select(['id', 'concat(last_name, \', \', first_name, \' (ref. \', reference, \')\') as text'])
                ->where(['ilike', 'first_name', $q])
                ->orWhere(['ilike', 'last_name', $q])
                ->orWhere(['ilike', 'reference', $q])
                ->asArray();
            $ntotal = $query->count();
            $data = $query->limit(10)->offset(10 * ($page - 1))->all();
            $out['results'] = array_values($data);
            $out['pagination'] = ['more' => $ntotal > 10 * $page];
        } elseif ($id > 0) {
            $model = Contact::findOne($id);
            $out['results'] = [
                'id' => $id,
                'text' => "{$model->last_name}, {$model->first_name} (ref. {$model->reference})"
            ];
        }
        return $out;
    }
    /**
     * Displays a single Contact model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    /**
     * Creates a new Contact model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Contact();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Contact model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    /**
     * Deletes an existing Contact model.
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
     * Finds the Contact model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Contact the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Contact::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
