<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\TransactionListItem */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Transaction List Items'), 'url' => ['index']];
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

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'transaction_type',
            'custom_type',
            'transfer_type',
            'development_type',
            'first_published_at',
            'first_published_price_euc',
            'last_published_at',
            'last_published_price_euc',
            'option_signed_at',
            'sale_price_euc',
            'buyer_id',
            'is_new_buyer:boolean',
            'buyer_provider',
            'seller_id',
            'is_new_seller:boolean',
            'seller_provider',
            'lead_type',
            'search_started_at',
            'suggested_sale_price_euc',
            'passed_to_sales_by',
            'property_id',
            'is_home_staged:boolean',
            'our_fee_euc',
            'their_fee_euc',
            'payrolled_at',
            'comments:ntext',
            'approved:boolean',
            'created_at',
            'updated_at',
            'sale_duration',
            'property_location',
            'property_building_complex',
            'property_reference',
            'seller_reference',
            'seller_name:ntext',
            'buyer_reference',
            'buyer_name:ntext',
            'cardenas100:boolean',
            'advisors:ntext',
            'n_invoices',
            'first_invoice_issued_at',
            'our_fee_bp',
        ],
    ]) ?>

</div>
