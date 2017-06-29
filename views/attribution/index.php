<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$dataProvider->sort = false;
if (!isset($formExpanded)) $formExpanded = false;
if (!isset($readonly)) $readonly = false;
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
          ['attribute' => 'attributionType.attribution_pct', 'value' => function($model) {
              $attrPct = Yii::$app->formatter->asDecimal($model->attributionType->attribution_bp / 100., 2);
              return "$attrPct%";
          }], ['attribute' => 'amount_eu', 'format' => ['currency', 'EUR']],
          'comments:ntext',
          ['class' => 'yii\grid\ActionColumn', 'buttons' => [
              'view' => function ($url, $model) {},
              'update' => function ($url, $model) {},
              'delete' => function ($url, $model, $key) use ($readonly) {
                  if (!$readonly) return Html::a('<span class="text-danger glyphicon glyphicon-trash"></span>', $url, [
                      'title' => 'Delete',
                      'data-pjax' => true,
                      'data-pjax-scrollto' => 'false',
                      'data-method' => 'post',
                      'data-confirm' => Yii::t('app', 'Are you sure you want to delete this item?')
                  ]);
              }
          ], 'urlCreator' => function ($action, $model, $key, $index) {
              if ($action === 'delete') return Url::to(["/attribution/delete/{$model->id}"]);
          }]
      ]
  ]); ?>

  <?php if (!$readonly) echo $this->render('_form', [
      'model' => $model,
      'formExpanded' => $formExpanded,
      'attribution_types' => $attribution_types,
      'advisor_defaults' => $advisor_defaults,
      'total_invoiced_eu' => $total_invoiced_eu
  ]) ?>
</div>

<?php Pjax::end() ?>

