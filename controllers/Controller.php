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
        if (\Yii::$app->session->get('lang') === 'es') {
            \Yii::$app->language = 'es';
            \Yii::$app->formatter->thousandSeparator = '.';
            \Yii::$app->formatter->decimalSeparator = ',';
        } else {
            \Yii::$app->language = 'en';
            \Yii::$app->formatter->thousandSeparator = ',';
            \Yii::$app->formatter->decimalSeparator = '.';
        }
        return parent::beforeAction($event);
    }
}
