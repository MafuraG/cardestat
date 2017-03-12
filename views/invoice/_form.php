<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use app\models\RecipientCategory;
use yii\widgets\PjaxAsset;
use kartik\money\MaskMoney;
PjaxAsset::register($this);

$euTpl = "{label}\n<div class=\"input-group\">{input}<span class=\"input-group-addon\">€</span></div>\n{hint}\n{error}";
?>

<div class="invoice-form">

  <?php $form = ActiveForm::begin([
      'id' => 'invoice-form',
      'action' => ['/invoice/create'],
      'enableClientValidation' => true,
      'options' => [
          'data-pjax' => true
      ]
  ]); ?>
  <?php ActiveForm::end(); ?>
  <?= $form->field($model, 'transaction_id')->hiddenInput(['form' => $form->id])->label(false) ?>

  <div class="edit-mode">
    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#add-invoice">+ <?= Yii::t('app', 'Add invoice') ?></button>
  
    <div id="add-invoice" class="collapse <?= (isset($formExpanded) and $formExpanded) ? 'in' : ''?>">
      <div class="col-md-6">
        <?= $form->field($model, 'code')->textInput([
            'class' => 'form-control input-sm',
            'maxlength' => true,
            'form' => $form->id
        ]); ?>
        <?= $form->field($model, 'recipient_category')->dropDownList(
            RecipientCategory::listAll(), [
                'prompt' => '',
                'class' => 'form-control input-sm',
                'form' => $form->id
            ]) ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'issued_at')->widget(DatePicker::classname(), [
            'options' => ['form' => $form->id],
            'size' => 'sm',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]); ?>
        <?= $form->field($model, 'amount_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
                'class' => 'text-right input-sm currency',
                'form' => $form->id
            ]]); ?>
      </div>
  
      <div class="col-md-12">
        <div class="form-group">
          <?= Html::submitButton(Yii::t('app', 'Add'), ['class' => 'btn btn-sm btn-primary', 'form' => $form->id]) ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$script = <<< JS
  $('.invoice-form form').on('beforeSubmit.yii', function(e) {
      $.pjax.submit(e, '#invoice-index-p0', {push: false, scrollTo: false});
      return false;
  });
JS;
$this->registerJs($script);
?>
