<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\money\MaskMoney;

/* @var $this yii\web\View */
/* @var $model app\models\AttributionType */
/* @var $form yii\widgets\ActiveForm */
$pctTpl = "{label}\n<div class=\"input-group\">{input}<span class=\"input-group-addon\">%</span></div>\n{hint}\n{error}";
?>

<div class="attribution-type-form">
  <div class="row">
    <div class="col-md-6">
      <?php $form = ActiveForm::begin(); ?>
  
      <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
  
      <?= $form->field($model, 'attribution_pct', ['template' => $pctTpl])->widget(MaskMoney::classname(), ['options' => [
          'class' => 'text-right input-sm mask-money',
          'readonly' => !$model->isNewRecord
      ]]); ?>
  
      <?= $form->field($model, 'active')->checkbox() ?>
  
      <div class="form-group">
          <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
      </div>
  
      <?php ActiveForm::end(); ?>
    </div>
  </div>

</div>
