<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use app\models\Advisor;
use app\models\AttributionType;
use app\models\Office;
use yii\widgets\PjaxAsset;
use kartik\money\MaskMoney;
use yii\helpers\Json;
PjaxAsset::register($this);

$euTpl = "{label}\n<div class=\"input-group\">{input}<span class=\"input-group-addon\">€</span></div>\n{hint}\n{error}";
?>

<div class="attribution-form">

  <?php $form = ActiveForm::begin([
      'id' => 'attribution-form',
      'action' => ['/attribution/create'],
      'enableClientValidation' => true,
      'options' => [
          'data-pjax' => true
      ]
  ]); ?>
  <?php ActiveForm::end(); ?>
  <?= $form->field($model, 'transaction_id')->hiddenInput(['form' => $form->id])->label(false) ?>

  <div class="edit-mode">
    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#add-attribution">+ <?= Yii::t('app', 'Add attribution') ?></button>
  
    <div id="add-attribution" class="collapse <?= (isset($formExpanded) and $formExpanded) ? 'in' : ''?>">
      <div class="col-md-6">
        <?= $form->field($model, 'advisor_id')->dropDownList(
            Advisor::listActive(), [
                'prompt' => '',
                'class' => 'form-control input-sm',
                'form' => $form->id
            ]) ?>
        <?= $form->field($model, 'attribution_type_id')->dropDownList(
            AttributionType::listActive(), [
                'prompt' => '',
                'class' => 'form-control input-sm',
                'form' => $form->id
            ]) ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'office')->dropDownList(
            Office::listAll(), [
                'prompt' => '',
                'class' => 'form-control input-sm',
                'form' => $form->id
            ]) ?>
        <?= $form->field($model, 'amount_eu', ['template' => $euTpl])->textInput([
            'readonly' => true,
            'class' => 'text-right form-control input-sm',
        ]); ?>
      </div>
      <div class="col-md-12">
        <?= $form->field($model, 'comments')->textArea([
            'class' => 'form-control input-sm',
            'maxlength' => true,
            'form' => $form->id
        ]); ?>
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
$json_advisor_defaults = Json::encode($advisor_defaults);
$json_attribution_types = Json::encode($attribution_types);
$script = <<< JS
  var advisor_defaults = $json_advisor_defaults;
  var attribution_types = $json_attribution_types;
  $('.attribution-form form').on('beforeSubmit.yii', function(e) {
      $.pjax.submit(e, '#attribution-index-p0', {push: false, scrollTo: false});
      return false;
  });
  var \$advisorId = $('select[name="Attribution[advisor_id]"]');
  var \$office = $('select[name="Attribution[office]"]');
  var \$attributionTypeId = $('select[name="Attribution[attribution_type_id]"]');
  var \$amountEu = $('input[name="Attribution[amount_eu]"]');
  \$advisorId.on('change', function() {
      var advisorId = $(this).val();
      \$office.val(advisor_defaults[advisorId].default_office);
      \$attributionTypeId.val(advisor_defaults[advisorId].default_attribution_type_id);
      \$attributionTypeId.change();
      
  });
  var totalInvoiced = $total_invoiced_eu;
  \$attributionTypeId.on('change', function() {
      var attribution_type_id = $(this).val();
      var advisorId = \$advisorId.val();
      var amount_eu = (totalInvoiced * attribution_types[attribution_type_id] / 10000.).toFixed(2);
      \$amountEu.val(amount_eu);
  });
JS;
$this->registerJs($script);
?>
