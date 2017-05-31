<?php

namespace app\controllers;

use yii\data\Pagination;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use app\models\AuthItem;
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
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'ajax-save'  => ['post'],
                    'validate' => ['post', 'get'],
                    'ajax-delete-user'  => ['post']
                ]
            ]
        ], parent::behaviors());
    }
    public function renderRoles($model, $key, $index, $column) {
        $res = '';
        foreach (explode(',', substr($model->roles, 1, strlen($model->roles) - 2)) as $role)
            if ($role !== 'NULL') $res .= "<span data-role class=\"badge\">$role</span>";
        return $res;
    }
    public function actionIndex() {
        $model = new UserForm();
        $query = User::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->sort->route = 'user/index';
        $roles = AuthItem::listRoles();
        return $this->render('index.twig', [
            'model' => $model,
            'roles' => $roles,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionAjaxDeleteUser() {
        if (!\Yii::$app->user->can('admin')) throw new HttpException(403);
        $request = \Yii::$app->request;
        if (!$request->post('id'))  throw new HttpException(400);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $user = User::findOne($request->post('id'));
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            if (count($user->createdGroups) > 0 or count($user->updatedGroups))
                throw new HttpException(422, \Yii::t('app', 'The user cannot be deleted because is still referenced from rows he/she has created or updated'));
            $user->delete();
            $transaction->commit();
            $this->layout = false;
            return $this->actionIndex();
        } catch (Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }
    public function actionValidate() {
        $model = new UserForm();
        $request = \Yii::$app->request;
        if ($request->isAjax && $model->load($request->post())) {
            if ($model->id) $model->scenario = 'update';
            else $model->scenario = 'create';
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
    }
    public function actionAjaxSave() {
        $model = new UserForm();
        if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
            if ($model->id) $model->scenario = 'update';
            else $model->scenario = 'create';
            $val = ActiveForm::validate($model);
            if ($val) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                throw new HttpException(422, \yii\helpers\Json::encode($val));
            }
            if (!$model->id) {
                $user = new User();
                $user->username = $model->username;
            } else $user = User::findOne($model->id);
            if ($model->password)
                $user->hash = \Yii::$app->getSecurity()->generatePasswordHash($model->password);
            if (!$user->save(false)) 
                throw new HttpException(500, \Yii::t('app', 'Could not save the user'));

            $auth = \Yii::$app->authManager;
            $auth->revokeAll($user->id);
            $role = $auth->getRole($model->role);
            if ($role) $auth->assign($role, $user->id);

            $this->layout = false;
            return $this->actionIndex();
        }
    }
}
