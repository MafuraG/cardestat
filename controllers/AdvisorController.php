<?php

namespace app\controllers;

use Yii;
use app\models\Advisor;
use app\models\AdvisorTranche;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * AdvisorController implements the CRUD actions for Advisor model.
 */
class AdvisorController extends Controller
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
                    'roles' => ['admin']
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

    public function actionSaveTranches($advisor_id) {
        $tranches = [];
        $count = count(Yii::$app->request->post('AdvisorTranche', []));
        for ($i = 0; $i < $count; $i++)
            $tranches[] = new AdvisorTranche(['advisor_id' => $advisor_id]);
        if (AdvisorTranche::loadMultiple($tranches, Yii::$app->request->post()) &&
            AdvisorTranche::validateMultiple($tranches)) {
            AdvisorTranche::deleteAll(['advisor_id' => $advisor_id]);
            foreach ($tranches as $tranche) {
                $tranche->advisor_id = $advisor_id;
                $tranche->save(false);
            }
        } else foreach ($tranches as $tranche) Yii::warning(var_export($tranche->errors, 1));
        return $this->actionView($advisor_id);
    }
    /**
     * Lists all Advisor models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Advisor::find(),
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC]
            ]
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Advisor model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $tranche = new AdvisorTranche();
        return $this->render('view', [
            'model' => $this->findModel($id),
            'tranche' => $tranche
        ]);
    }

    /**
     * Creates a new Advisor model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Advisor();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Advisor model.
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
     * Deletes an existing Advisor model.
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
     * Finds the Advisor model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Advisor the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Advisor::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
