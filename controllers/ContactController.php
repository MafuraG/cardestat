<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use app\models\Contact;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ContactController extends \yii\web\Controller
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
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionList($q = null, $id = null, $page = null)
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
            $data = $query->limit(10)->offset(10 * $page)->all();
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
}
