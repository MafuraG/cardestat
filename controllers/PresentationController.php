<?php

namespace app\controllers;

use app\models\Configuration;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use Yii;

class PresentationController extends Controller {
    public function behaviors() {
        return ArrayHelper::merge([
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
                    'add-n-sales' => ['post'],
                ],
            ],
        ], parent::behaviors());
    }
    public function actionNSales() {
        $goals = Configuration::find()->where(['category' => 'NSALES_ACCU_GOAL'])->asArray()->all();
        $actuals = Configuration::find()->where(['category' => 'NSALES_ACCU_ACTUAL'])->asArray()->all();
        return $this->render('n_sales.twig', [
            'last_update' => date('Y-m-d'),
            'goals' => $goals,
            'actuals' => $actuals
        ]);
    }
    public function actionAddNSales() {
        $request = Yii::$app->request;
        $qty = $request->post('qty');
        $month = $request->post('month');
        $model = Configuration::find()->where(['category' => 'NSALES_ACCU_ACTUAL'])
            ->andWhere(['name' => $month])->one();
        $model->value = (string) ($model->value + $qty);
        $model->save();
        //return $this->redirect(['n-sales']);
        return $this->actionNSales();
    }
    public function actionUpdateNSales() {
        $request = Yii::$app->request;
        $category = $request->post('category');
        $models = Configuration::find()->where(['category' => $category])->all();
        $fields = $request->post('fields');
        //\yii\helpers\VarDumper::dump($fields, 5, true); die;
        Yii::warning('fields: ' . var_export($fields, 1));
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($models as $model) {
                if (isset($fields[$model->name])) {
                    $model->value = $fields[$model->name];
                    if (!$model->value) $model->value = '0';
                    $model->save();
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
        //return $this->redirect(['n-sales']);
        return $this->actionNSales();
    }
}
