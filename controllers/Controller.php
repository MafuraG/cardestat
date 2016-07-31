<?php

namespace app\controllers;

use yii\web\Controller as YiiController;

class Controller extends YiiController {
    public function behaviors() {
        return [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'index'  => ['get'],
                    'view'   => ['get'],
                    'create' => ['get', 'post'],
                    'update' => ['get', 'put', 'post'],
                    'delete' => ['post', 'delete'],
                ],
            ],
        ];
    }
    public function beforeAction($event) {
        if (\Yii::$app->session->get('lang') === 'es') \Yii::$app->language = 'es';
        else \Yii::$app->language = 'en';
        return parent::beforeAction($event);
    }
}
