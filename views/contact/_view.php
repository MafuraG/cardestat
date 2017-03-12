<?php
use yii\helpers\Html;
?>
<dl class="dl-horizontal">
  <?= Html::a('<small class="glyphicon glyphicon-edit"></small>', [
      '/contact/update',
      'id' => $model->id
  ], [
      'class' => 'close',
      'target' => '_blank'
  ]); ?>
  <dt><?= $model->getAttributeLabel('reference') ?></dt>
  <dd><?= $model->reference ?></dd>
  <dt><?= $model->getAttributeLabel('nationality') ?></dt>
  <dd><?= $model->nationality ?></dd>
  <dt><?= $model->getAttributeLabel('contact_source') ?></dt>
  <dd><?= $model->contact_source ?></dd>
  <dt><?= $model->getAttributeLabel('internet') ?></dt>
  <dd><?= $model->internet ?></dd>
</dl>
