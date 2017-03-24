<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\TransactionListItem */

$this->title = Yii::t('app', 'Update transaction #{id}', ['id' => $model->id]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Transactions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => "#{$model->id}", 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="transaction-list-item-update">

  <h1><?= Html::encode($this->title) ?></h1>

  <?php Pjax::begin(['id' => 'p1']) ?>
    <?= $this->render('_form', [
        'model' => $model,
        'invoice' => $invoice,
        'invoiceDataProvider' => $invoiceDataProvider,
        'attribution' => $attribution,
        'attributionDataProvider' => $attributionDataProvider,
        'attribution_types' => $attribution_types,
        'advisor_defaults' => $advisor_defaults,
        'total_invoiced_eu' => $total_invoiced_eu
    ]) ?>
  <?php Pjax::end() ?>

</div>
