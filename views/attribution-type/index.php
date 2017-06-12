<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Attribution types');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="attribution-type-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Attribution Type'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'active:boolean',
            'name',
            ['attribute' => 'attribution_bp', 'value' => function ($model, $key, $index, $column) { return $model->attribution_pct . ' %'; }, 'contentOptions' => ['class' => 'text-right']],
            'category',

            ['class' => 'yii\grid\ActionColumn', 'template' => '{view} {delete}'],
        ],
    ]); ?>
</div>
