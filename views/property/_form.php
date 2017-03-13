<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\money\MaskMoney;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Property */
/* @var $form yii\widgets\ActiveForm */
$m2Tpl = "{label}\n<div class=\"input-group\">{input}<span class=\"input-group-addon\">m<sup>2</sup></span></div>\n{hint}\n{error}";
?>

<div class="property-form">

  <?php $form = ActiveForm::begin(); ?>
    <div class="row">

      <div class="col-md-6">
        <?= $form->field($model, 'reference')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>

        <?= $form->field($model, 'location')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>

        <?= $form->field($model, 'property_type')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>

        <?= $form->field($model, 'built_area_m2', [
            'template' => $m2Tpl
        ])->widget(MaskMoney::classname(), ['options' => [
                'class' => 'text-right input-sm mask-money',
                'form' => $form->id
        ]]); ?>

        <?= $form->field($model, 'n_bedrooms')->textInput(['class' => 'input-sm form-control']) ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'entry_date')->widget(DatePicker::classname(), [
            'options' => ['form' => $form->id],
            'size' => 'sm',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]); ?>

        <?= $form->field($model, 'geo_coordinates')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>

        <?= $form->field($model, 'building_complex')->textInput(['maxlength' => true, 'class' => 'input-sm form-control']) ?>


        <?= $form->field($model, 'plot_area_m2', [
            'template' => $m2Tpl
        ])->widget(MaskMoney::classname(), ['options' => [
                'class' => 'text-right input-sm mask-money',
                'form' => $form->id
        ]]); ?>

        <?= $form->field($model, 'active_date')->widget(DatePicker::classname(), [
            'options' => ['form' => $form->id],
            'size' => 'sm',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]); ?>

        <?= $form->field($model, 'inactive_date')->widget(DatePicker::classname(), [
            'options' => ['form' => $form->id],
            'size' => 'sm',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]); ?>

      </div>

      <div class="col-md-12">
        <small class="pull-right">
          <dl class="text-info dl-horizontal">
            <dt><?= Yii::t('app', 'Created At') ?></dt> <dd><?= Yii::$app->formatter->asDatetime($model->created_at, 'long') ?></dd>
            <dt><?= Yii::t('app', 'Last Updated At') ?></dt> <dd><?= Yii::$app->formatter->asDatetime($model->updated_at, 'long') ?></dd>
          </dl>
        </small>
        <div class="form-group">
          <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-lg btn-success' : 'btn btn-lg btn-primary']) ?>
        </div>
      </div>

    </div>
  <?php ActiveForm::end(); ?>

</div>
