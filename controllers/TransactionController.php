<?php

namespace app\controllers;

use app\models\TransactionListItem;
use yii\data\ActiveDataProvider;

class TransactionController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => TransactionListItem::find(),
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,

        ]);
    }

}
