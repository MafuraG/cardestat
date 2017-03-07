<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\TransactionListItem */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="transaction-list-item-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput() ?>

    <?= $form->field($model, 'transaction_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'custom_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'transfer_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'development_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'first_published_at')->textInput() ?>

    <?= $form->field($model, 'first_published_price_euc')->textInput() ?>

    <?= $form->field($model, 'last_published_at')->textInput() ?>

    <?= $form->field($model, 'last_published_price_euc')->textInput() ?>

    <?= $form->field($model, 'option_signed_at')->textInput() ?>

    <?= $form->field($model, 'sale_price_euc')->textInput() ?>

    <?= $form->field($model, 'buyer_id')->textInput() ?>

    <?= $form->field($model, 'is_new_buyer')->checkbox() ?>

    <?= $form->field($model, 'buyer_provider')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'seller_id')->textInput() ?>

    <?= $form->field($model, 'is_new_seller')->checkbox() ?>

    <?= $form->field($model, 'seller_provider')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'lead_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'search_started_at')->textInput() ?>

    <?= $form->field($model, 'suggested_sale_price_euc')->textInput() ?>

    <?= $form->field($model, 'passed_to_sales_by')->textInput() ?>

    <?= $form->field($model, 'property_id')->textInput() ?>

    <?= $form->field($model, 'is_home_staged')->checkbox() ?>

    <?= $form->field($model, 'our_fee_euc')->textInput() ?>

    <?= $form->field($model, 'their_fee_euc')->textInput() ?>

    <?= $form->field($model, 'payrolled_at')->textInput() ?>

    <?= $form->field($model, 'comments')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'approved')->checkbox() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'sale_duration')->textInput() ?>

    <?= $form->field($model, 'property_location')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'seller_name')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'buyer_name')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'cardenas100')->checkbox() ?>

    <?= $form->field($model, 'advisors')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'n_invoices')->textInput() ?>

    <?= $form->field($model, 'first_invoice_issued_at')->textInput() ?>

    <?= $form->field($model, 'our_fee_bp')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
