<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\TransactionListItem */

$this->title = Yii::t('app', 'Update transaction #{id}', ['id' => $model->id]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Transactions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => "#{$model->id}", 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="transaction-list-item-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'invoice' => $invoice,
        'invoiceDataProvider' => $invoiceDataProvider,
        'attribution' => $attribution,
        'attributionDataProvider' => $attributionDataProvider,
        'attribution_types' => $attribution_types,
        'advisor_defaults' => $advisor_defaults
    ]) ?>

</div>
