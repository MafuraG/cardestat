<?php

namespace app\controllers;

use Yii;
use app\models\Property;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

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
