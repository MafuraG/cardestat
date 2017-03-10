<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use app\models\Advisor;
use app\models\AttributionType;
use app\models\Office;
use yii\widgets\PjaxAsset;
use kartik\money\MaskMoney;
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
  
    <div id="add-attribution" class="collapse <?= isset($formExpanded) and $formExpanded ? 'in' : ''?>">
      <div class="col-md-6">
        <?= $form->field($model, 'advisor_id')->dropDownList(
            Advisor::listAll(), [
                'prompt' => '',
                'class' => 'form-control input-sm',
                'form' => $form->id
            ]) ?>
        <?= $form->field($model, 'amount_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
                'class' => 'text-right input-sm currency',
                'form' => $form->id
            ]]); ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'attribution_type_id')->dropDownList(
            AttributionType::listAll(), [
                'prompt' => '',
                'class' => 'form-control input-sm',
                'form' => $form->id
            ]) ?>
        <?= $form->field($model, 'office')->dropDownList(
            Office::listAll(), [
                'prompt' => '',
                'class' => 'form-control input-sm',
                'form' => $form->id
            ]) ?>
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
  $('.attribution-form form').on('beforeSubmit.yii', function(e) {
      $.pjax.submit(e, '#attribution-index-p0', {push: false, scrollTo: false});
      return false;
  });
JS;
$this->registerJs($script);
?>
<!--
<div class="edit-mode">
  <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#add-advisor">+ Añadir atribución</button>
  <div id="add-advisor" class="collapse">
    <div class="form-group col-md-6">
      <label>Asesor</label>
      <select class="input-sm form-control">
        <option selected>TOMAS</option>
      </select>
    </div>
    <div class="form-group col-md-6">
      <label>Oficina</label>
      <select class="input-sm form-control">
        <option>-- SIN ESTABLECER --</option>
        <option selected>ARGUINEGUÍN</option>
      </select>
    </div>
    <div class="form-group col-md-6">
      <label>Tipo de atribución</label>
      <select class="input-sm form-control">
        <option selected>CAPTACIÓN 30%</option>
      </select>
    </div>
    <div class="form-group col-md-6">
      <label>Atribución</label>
      <div class="input-group">
        <span class="input-group-addon"></span>
        <div class="input-group-btn">
          <select class="input-sm form-control">
            <option selected>AUTOMÁTICA</option>
          </select>
        </div>
        <input class="input-sm form-control text-right" disabled>
        <span class="input-group-addon">€</span>
      </div>
    </div>
    <div class="form-group col-md-12">
      <label>Comentarios para el asesor</label>
      <textarea class="form-control" rows="3"></textarea>
    </div>
    <div class="form-group col-md-12">
      <button class="btn btn-sm btn-primary" type="button">Añadir</button>
    </div>
  </div>
</div>
-->
