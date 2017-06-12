<?php

namespace app\controllers;

use Yii;
use app\models\Property;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

class PropertyController extends Controller
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
     * Lists all Property models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Property::find(),
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_DESC, 'id' => SORT_DESC]
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
            $consoleController->runAction('properties');
        } catch (\Exception $e) {
            throw new ServerErrorHttpException(Yii::t('app', 'Field mapping broken. Please check the mapping for onOffice CSV'));
        }
    }

    /**
    * Creates a new Property model.
    * If creation is successful, the browser will be redirected to the 'view' page.
    * @return mixed
    */
   public function actionCreate()
   {
       $model = new Property();

       if ($model->load(Yii::$app->request->post()) && $model->save()) {
           return $this->redirect(['view', 'id' => $model->id]);
       } else {
           return $this->render('create', [
               'model' => $model,
           ]);
       }
   }

   /**
    * Updates an existing Property model.
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
    * Deletes an existing Property model.
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
    * Finds the Property model based on its primary key value.
    * If the model is not found, a 404 HTTP exception will be thrown.
    * @param integer $id
    * @return Property the loaded model
    * @throws NotFoundHttpException if the model cannot be found
    */
   protected function findModel($id)
   {
       if (($model = Property::findOne($id)) !== null) {
           return $model;
       } else {
           throw new NotFoundHttpException('The requested page does not exist.');
       }
   }
    /**
     * Displays a single Property model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionList($q = null, $id = null, $page = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = Property::find();
            $query->select(['id', 'concat(building_complex, \', \', location, \' (ref. \', reference, \')\') as text'])
                ->where(['ilike', 'building_complex', $q])
                ->orWhere(['ilike', 'location', $q])
                ->orWhere(['ilike', 'reference', $q])
                ->asArray();
            $ntotal = $query->count();
            $data = $query->limit(10)->offset(10 * $page)->all();
            $out['results'] = array_values($data);
            $out['pagination'] = ['more' => $ntotal > 10 * $page];
        } elseif ($id > 0) {
            $model = Property::findOne($id);
            $out['results'] = [
                'id' => $id,
                'text' => "{$model->building_complex}, {$model->location} (ref. {$model->reference})"
            ];
        }
        return $out;
    }

}
