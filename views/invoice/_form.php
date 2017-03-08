<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use app\models\RecipientCategory;
use yii\widgets\PjaxAsset;
PjaxAsset::register($this);

$euTpl = "{label}\n<div class=\"input-group\">{input}<span class=\"input-group-addon\">€</span></div>\n{hint}\n{error}";
?>

<div class="invoice-form">

  <?php $form = ActiveForm::begin([
      'action' => ['create'],
      'enableClientValidation' => true,
      'options' => [
          'data-pjax' => true
      ]
  ]); ?>
  <?= $form->field($model, 'transaction_id')->hiddenInput()->label(false) ?>

  <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#add-invoice">+ <?= Yii::t('app', 'Add invoice') ?></button>

  <div id="add-invoice" class="collapse">
    <div class="col-md-6">
      <?= $form->field($model, 'code')->textInput([
          'class' => 'form-control input-sm',
          'maxlength' => true,
      ]); ?>
      <?= $form->field($model, 'amount_euc', ['template' => $euTpl])->textInput([
          'class' => 'form-control input-sm text-right',
          'maxlength' => true,
      ]) ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'issued_at')->widget(DatePicker::classname(), [
          'size' => 'sm',
          'pluginOptions' => [
              'autoclose' => true,
              'format' => 'yyyy-mm-dd',
          ]
      ]); ?>
      <?= $form->field($model, 'recipient_category')->dropDownList(
          RecipientCategory::listAll(), ['prompt' => '', 'class' => 'form-control input-sm']) ?>
    </div>

    <div class="col-md-12">
      <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Add'), ['class' => 'btn btn-sm btn-primary']) ?>
      </div>
    </div>

  </div>

  <?php ActiveForm::end(); ?>

</div>
<?php
$script = <<< JS
  $('.invoice-form form').on('beforeSubmit.yii', function(e) {
      $.pjax.submit(e, '#p0', {push: false, scrollTo: false});
      return false;
  });
JS;
$this->registerJs($script);
?>
