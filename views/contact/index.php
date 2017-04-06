<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Contacts');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contact-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Contact'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'reference',
            ['attribute' => 'last_name', 'label' => Yii::t('app', 'Name'), 'value' => function($model) {
                return "{$model->last_name}, {$model->first_name}";
            }], 'nationality',
            'type_of_data',
            'internet',
            'contact_source',
            'birth_date:date',
            ['class' => 'yii\grid\ActionColumn', 'contentOptions' => ['class' => 'nowrap']],
        ]
    ]) ?>
<?php Pjax::end(); ?></div>
