<?php
use yii\helpers\Html;

$formatter = Yii::$app->formatter;
?>
<dl class="dl-horizontal">
  <?= Html::a('<small class="glyphicon glyphicon-edit"></small>', [
      '/property/update',
      'id' => $model->id
  ], [
      'class' => 'close',
      'target' => '_blank',
      'data-pjax' => 0
  ]); ?>
  <dt><?= $model->getAttributeLabel('property_type') ?></dt>
  <dd><?= $model->property_type ?></dd>
  <dt><?= $model->getAttributeLabel('location') ?></dt>
  <dd><?= $model->location ?></dd>
  <dt><?= $model->getAttributeLabel('building_complex') ?></dt>
  <dd><?= $model->building_complex ?></dd>
  <dt><?= $model->getAttributeLabel('geo_coordinates') ?></dt>
  <dd><?= $model->geo_coordinates ?></dd>
  <dt><?= $model->getAttributeLabel('n_bedrooms') ?></dt>
  <dd><?= $model->n_bedrooms ?></dd>
  <dt><?= $model->getAttributeLabel('plot_area_m2') ?></dt>
  <dd><?= $formatter->asDecimal($model->plot_area_m2, 2) ?> m<sup>2</sup></dd>
  <dt><?= $model->getAttributeLabel('built_area_m2') ?></dt>
  <dd><?= $formatter->asDecimal($model->built_area_m2, 2) ?> m<sup>2</sup></dd>
</dl>

