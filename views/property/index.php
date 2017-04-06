<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Properties');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="property-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Property'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'reference',
            'entry_date:date',
            'property_type',
            'location',
            'n_bedrooms',
            ['attribute' => 'plot_area_dm2', 'value' => function($model) { return $model->plot_area_m2; }],
            ['attribute' => 'built_area_dm2', 'value' => function($model) { return $model->built_area_m2; }],
            ['class' => 'yii\grid\ActionColumn'],
        ]
    ]) ?>
<?php Pjax::end(); ?></div>
