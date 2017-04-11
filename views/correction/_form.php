<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\money\MaskMoney;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Correction */
/* @var $form ActiveForm */

$euTpl = "{label}\n<div class=\"input-group\">{input}<span class=\"input-group-addon\">€</span></div>\n{hint}\n{error}";
?>
<div class="correction-form">

  <legend><?= Yii::t('app', 'New correction') ?></legend>
  <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'payroll_id')->hiddenInput()->label(false) ?>
    <div class="row">
      <div class="col-sm-4">
        <?= $form->field($model, 'corrected_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
            'class' => 'text-right input-sm mask-money',
        ], 'pluginOptions' => [
            'allowNegative' => true
        ]]); ?>
      </div>
      <div class="col-sm-4">
        <?= $form->field($model, 'compensation_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
            'class' => 'text-right input-sm mask-money',
        ], 'pluginOptions' => [
            'allowNegative' => true
        ]]); ?>
      </div>
      <div class="col-sm-4">
        <?= $form->field($model, 'compensation_on')->widget(DatePicker::classname(), [
            'size' => 'sm',
            'removeButton' => false,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm',
                'startView' => 'months', 
                'minViewMode' => 'months'
            ]
        ]); ?>
      </div>
      <div class="col-sm-12">
        <?= $form->field($model, 'reason')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>
      </div>
    
      <div class="col-sm-12">
        <div class="form-group">
          <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
      </div>
    </div>
  <?php ActiveForm::end(); ?>

</div><!-- correction-form -->
