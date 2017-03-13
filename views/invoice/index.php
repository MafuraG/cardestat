<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$dataProvider->sort = false;
if (!isset($formExpanded)) $formExpanded = false;
?>
<?php Pjax::begin([
    'id' => 'invoice-index-p0'
]) ?>
<div class="invoice-index">

  <?= GridView::widget([
      'dataProvider' => $dataProvider,
      'summary' => false,
      'tableOptions' => [
          'class' => 'table table-condensed table-striped'
      ], 'columns' => [
          //'id',
          'code',
          'issued_at:date',
          ['attribute' => 'amount_eu', 'format' => ['currency', 'EUR']],
          'recipient_category',
          ['class' => 'yii\grid\ActionColumn', 'buttons' => [
              'view' => function ($url, $model) {},
              'update' => function ($url, $model) {},
              'delete' => function ($url, $model, $key) {
                  return Html::a('<span class="text-danger glyphicon glyphicon-trash"></span>', $url, [
                      'title' => 'Delete',
                      'data-pjax' => true,
                      'data-method' => 'post',
                      'data-confirm' => Yii::t('app', 'Are you sure you want to delete this item?')
                  ]);
              }
          ], 'urlCreator' => function ($action, $model, $key, $index) {
              if ($action === 'delete') return Url::to(["/invoice/delete/{$model->id}"]);
          }]
      ]
  ]); ?>

  <?= $this->render('_form', [
      'model' => $model,
      'formExpanded' => $formExpanded
  ]) ?>
</div>

<?php Pjax::end() ?>
