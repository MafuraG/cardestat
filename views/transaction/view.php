<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\TransactionListItem */

$this->title = Yii::t('app', 'Transaction #{id}', ['id' => $model->id]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Transactions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transaction-list-item-view">

  <h1><?= Html::encode($this->title) ?></h1>
  <p>
    <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data' => [
            'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
            'method' => 'post',
        ],
    ]) ?>
  </p>
  <?php Pjax::begin(['id' => 'p1']) ?>
    <?= $this->render('_form', [
        'model' => $model,
        'invoice' => $invoice,
        'invoiceDataProvider' => $invoiceDataProvider,
        'attribution' => $attribution,
        'attributionDataProvider' => $attributionDataProvider,
        'attribution_types' => $attribution_types,
        'advisor_defaults' => $advisor_defaults,
        'total_invoiced_eu' => $total_invoiced_eu,
        'readonly' => true
    ]) ?>
  <?php Pjax::end() ?>
</div>
