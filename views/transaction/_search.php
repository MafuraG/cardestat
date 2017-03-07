<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use app\models\Advisor;
use app\models\TransactionType;

/* @var $this yii\web\View */
/* @var $model app\models\TransactionListItemSearch */
/* @var $form yii\widgets\ActiveForm */
$yesno = [true => Yii::t('app', 'Yes'), false => Yii::t('app', 'No')]
?>

<div class="transaction-list-item-search">

    <?php $form = ActiveForm::begin([
        'enableClientScript' => false,
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="well well-sm">
      <div class="row">
        <div class="col-md-6">
          <div class="input-group">
            <div style="position: relative">
              <?= Html::activeTextInput($model, 'search_any', ['placeholder' => $model->getAttributeLabel('search_any'), 'class' => 'form-control']); ?>
              <a id="advanced-search-caret" href="#" style="padding: 6px; position: absolute; right: 2px; z-index: 3"><span class="caret" ></span></a>
            </div>
            <div class="input-group-btn">
              <button type="button" class="btn btn-default btn-reset"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>
              <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
            </div>
          </div>
          <div id="advanced-search-box" class="advanced-input hidden">
            <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <?= $form->field($model, 'transaction_type')
                      ->dropDownList(TransactionType::listAll(), ['prompt' => '', 'class' => 'form-control input-sm']) ?>
                </div>
              </div>
              <div class="clearfix"></div>
              <div class="col-md-6">
                <?= $form->field($model, 'option_signed_from')->widget(DatePicker::classname(), [
                    'size' => 'sm',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ]
                ]); ?>
              </div>
              <div class="col-md-6">
                <?= $form->field($model, 'option_signed_to')->widget(DatePicker::classname(), [
                    'size' => 'sm',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ]
                ]); ?>
              </div>
              <div class="col-md-6">
                <?= $form->field($model, 'first_invoiced_from')->widget(DatePicker::classname(), [
                    'size' => 'sm',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ]
                ]); ?>
              </div>
              <div class="col-md-6">
                <?= $form->field($model, 'first_invoiced_to')->widget(DatePicker::classname(), [
                    'size' => 'sm',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ]
                ]); ?>
              </div>
              <div class="col-md-6">
                <?php 
                    $advisors = Advisor::listAll();
                    $advisors = array_combine(array_values($advisors), array_values($advisors));
                    echo $form->field($model, 'advisors')
                        ->dropDownList($advisors, ['prompt' => '', 'class' => 'form-control input-sm']) ?>
              </div>
              <div class="clearfix"></div>
              <div class="col-md-3">
                <?= $form->field($model, 'approved')
                    ->dropDownList($yesno, ['prompt' => Yii::t('app', 'All'), 'class' => 'form-control input-sm']) ?>
              </div>
              <div class="col-md-3">
                <?= $form->field($model, 'payrolled')
                    ->dropDownList($yesno, ['prompt' => Yii::t('app', 'All'), 'class' => 'form-control input-sm']) ?>
              </div>
              <div class="col-md-3">
                <?= $form->field($model, 'invoiced')
                    ->dropDownList($yesno, ['prompt' => Yii::t('app', 'All'), 'class' => 'form-control input-sm']) ?>
              </div>
              <div class="col-md-3">
                <?= $form->field($model, 'with_collaborator')
                    ->dropDownList($yesno, ['prompt' => Yii::t('app', 'All'), 'class' => 'form-control input-sm']) ?>
              </div>
              <div class="clearfix"></div>
              <div class="col-md-9">
                <div class="form-group">
                  <label><?= Yii::t('app', 'Order by') ?></label><br>
                  <div class="input-group">
                    <span class="input-group-addon"></span>
                    <div class="input-group-btn">
                      <select name="sort" class="input-sm form-control">
                        <option data-value="option_signed_at" selected value="-option_signed_at"><?= $model->getAttributeLabel('option_signed_at') ?></option>
                        <option data-value="first_invoiced_at" value="-first_invoiced_at"><?= $model->getAttributeLabel('first_invoiced_at') ?></option>
                        <option data-value="sale_price_euc" value="-sale_price_euc"><?= $model->getAttributeLabel('sale_price_euc') ?></option>
                      </select>
                    </div>
                    <select name="direction" class="input-sm form-control">
                      <option value=""><?= Yii::t('app', 'Asc') ?></option>
                      <option selected value="-"><?= Yii::t('app', 'Desc') ?></option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <br>
            <div class="form-group">
              <?= Html::submitButton(Yii::t('app', 'Search'), [
                  'class' => 'btn btn-primary',
              ]) ?>
              <?= Html::button(Yii::t('app', 'Reset'), [
                  'class' => 'btn btn-default btn-reset',
              ]) ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
