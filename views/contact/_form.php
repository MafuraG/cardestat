<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Contact */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="contact-form">
  <div class="row">
    <?php $form = ActiveForm::begin(); ?>
      <div class="col-md-6">

        <?= $form->field($model, 'reference')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>

        <?= $form->field($model, 'first_name')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>

        <?= $form->field($model, 'nationality')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>

        <?= $form->field($model, 'contact_source')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>

        <?= $form->field($model, 'birth_date')->widget(DatePicker::classname(), [
            'options' => ['form' => $form->id],
            'size' => 'sm',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]); ?>

      </div>

      <div class="col-md-6">
        <?= $form->field($model, 'type_of_data')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>

        <?= $form->field($model, 'last_name')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>

        <?= $form->field($model, 'country_of_residence')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>

        <?= $form->field($model, 'internet')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>

      </div>

      <div class="col-md-12">
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
      </div>

    <?php ActiveForm::end(); ?>
  </div>
</div>
