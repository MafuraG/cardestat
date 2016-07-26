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
use app\models\UserExtended;
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
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'ajax-create-table'  => ['post'],
                ]
            ]
        ], parent::behaviors());
    }
    public function renderRoles($model, $key, $index, $column) {
        $res = '';
        foreach (explode(',', substr($model->roles, 1, strlen($model->roles) - 2)) as $role)
            if ($role !== 'NULL') $res .= "<span class=\"badge\">$role</span>";
        return $res;
    }
    public function actionIndex() {
        $model = new UserForm();
        $query = UserExtended::find();
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
    public function actionAjaxCreate() {
        $model = new UserForm();
        if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
            $val = ActiveForm::validate($model);
            if ($val) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                throw new HttpException(422, \yii\helpers\Json::encode($val));
            }
            $user = new User();
            $user->username = $model->username;
            $user->hash = \Yii::$app->getSecurity()->generatePasswordHash($model->password);
            if (!$user->save(false)) 
                throw new HttpException(500, \Yii::t('app', 'Could not save the user'));
            if ($model->is_admin) {
                $auth = \Yii::$app->authManager;
                $admin = $auth->getRole('admin');
                $auth->assign($admin, $user->id);
            }
            return $this->actionIndex();
        }
    }
}
