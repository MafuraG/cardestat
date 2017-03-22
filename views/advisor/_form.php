<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Office;
use app\models\AttributionType;

/* @var $this yii\web\View */
/* @var $model app\models\Advisor */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="advisor-form">
 <div class="row">
  <div class="col-md-6">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['class' => 'form-control input-sm', 'maxlength' => true]) ?>

    <?= $form->field($model, 'default_office')->dropDownList(
        Office::listAll(), [
            'prompt' => '',
            'class' => 'form-control input-sm',
            'form' => $form->id
    ]) ?>
    <?= $form->field($model, 'default_attribution_type_id')->dropDownList(
        AttributionType::listActive(), [
            'prompt' => '',
            'class' => 'form-control input-sm',
            'form' => $form->id
    ]) ?>
    <?= $form->field($model, 'is_hub_agent')->checkbox() ?>
    <?= $form->field($model, 'active')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-lg btn-success' : 'btn btn-lg btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
  </div>
 </div>
</div>
