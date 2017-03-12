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
          ['attribute' => 'advisor.name', 'label' => Yii::t('app', 'Advisor')],
          'office',
          //['attribute' => 'attribution_type', 'value' => '$model->attribution_type->name'],
          ['attribute' => 'attribution_type_id', 'value' => 'attributionType.name'],
          ['attribute' => 'attributionType.attribution_bp', 'value' => function($model) {
              $attrPct = Yii::$app->formatter->asDecimal($model->attributionType->attribution_bp / 100., 2);
              return "$attrPct%";
          }], ['attribute' => 'amount_eu', 'format' => ['currency', 'EUR']],
          'comments:ntext',
          ['class' => 'yii\grid\ActionColumn', 'buttons' => [
              'view' => function ($url, $model) {},
              'update' => function ($url, $model) {},
          ]]
      ]
  ]); ?>

  <?= $this->render('_form', [
      'model' => $model,
      'formExpanded' => $formExpanded,
      'attribution_types' => $attribution_types,
      'advisor_defaults' => $advisor_defaults
  ]) ?>
</div>

<?php Pjax::end() ?>

