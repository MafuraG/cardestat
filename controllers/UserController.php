<?php

namespace app\controllers;

use yii\data\Pagination;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use app\models\User;
use app\models\UserForm;

class UserController extends Controller {
    public function behaviors() {
        return ArrayHelper::merge([
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [[
                    'allow' => true,
                    'roles' => ['admin']
                ]]
            ]
        ], parent::behaviors());
    }
    public function actionIndex() {
        $model = new UserForm();
        $query = User::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index.twig', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionValidate() {
        $model = new UserForm();
        if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
    }
    public function actionCreate() {
        $model = new UserForm();
        if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
            $val = ActiveForm::validate($model);
            if ($val) throw new HttpException(422, $val);
            $user = new User();
            $user->username = $model->username;
            $user->hash = \Yii::$app->getSecurity()->generatePasswordHash($model->password);
            if ($user->save(false)) return $this->actionIndex();
            else throw new HttpException(500, \Yii::t('app', 'Could not save the user'));
        }
    }
}
