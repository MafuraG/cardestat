<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$dataProvider->sort = false;
if (!isset($formExpanded)) $formExpanded = false;
?>
<?php Pjax::begin([
    'id' => 'attribution-index-p0'
]) ?>
<div class="attribution-index">

  <?= GridView::widget([
      'dataProvider' => $dataProvider,
      'summary' => false,
      'tableOptions' => [
          'class' => 'table table-condensed table-striped'
      ], 'columns' => [
          //'id',
          //['attribute' => 'code', 'value' => '$model->advisor->name'],
          'advisor.name',
          'office',
          //['attribute' => 'attribution_type', 'value' => '$model->attribution_type->name'],
          'attributionType.name',
          ['attribute' => 'amount_eu', 'format' => ['currency', 'EUR']],
          'comments:text',
          ['class' => 'yii\grid\ActionColumn', 'buttons' => [
              'view' => function ($url, $model) {},
              'update' => function ($url, $model) {},
          ]]
      ]
  ]); ?>

  <?= $this->render('_form', [
      'model' => $model,
      'formExpanded' => $formExpanded
  ]) ?>
</div>

<?php Pjax::end() ?>

