<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\TransactionType;
use app\models\CustomType;
use app\models\Advisor;
use app\models\Partner;
use app\models\LeadType;
use app\models\TransferType;
use app\models\DevelopmentType;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\date\DatePicker;
use kartik\select2\Select2;

$euTpl = "{label}\n<div class=\"input-group\">{input}<span class=\"input-group-addon\">€</span></div>\n{hint}\n{error}";
/* @var $this yii\web\View */
/* @var $model app\models\Transaction */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="transaction-form">

  <?php $form = ActiveForm::begin([
        'enableClientValidation' => true,
  ]); ?>
  <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
  <div class="row">
    <fieldset>
      <div class="col-md-12">
        <legend><?= Yii::t('app', 'Clasification') ?></legend>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'transaction_type')
            ->dropDownList(TransactionType::listAll(), ['prompt' => '', 'class' => 'form-control input-sm']) ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'custom_type')
            ->dropDownList(CustomType::listAll(), ['prompt' => '', 'class' => 'form-control input-sm']) ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'transfer_type')
            ->dropDownList(TransferType::listAll(), ['prompt' => '', 'class' => 'form-control input-sm']) ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'development_type')
            ->dropDownList(DevelopmentType::listAll(), ['prompt' => '', 'class' => 'form-control input-sm']) ?>
      </div>
      <div class="col-md-12">
        <legend><?= Yii::t('app', 'Evolution') ?></legend>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'first_published_at')->widget(DatePicker::classname(), [
            'size' => 'sm',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]); ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'first_published_price_euc', ['template' => $euTpl]) ->textInput([
            'class' => 'form-control input-sm text-right',
            'maxlength' => true,
        ]) ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'last_published_at')->widget(DatePicker::classname(), [
            'size' => 'sm',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]); ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'last_published_price_euc', ['template' => $euTpl]) ->textInput([
            'class' => 'form-control input-sm text-right',
            'maxlength' => true,
        ]) ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'option_signed_at')->widget(DatePicker::classname(), [
            'size' => 'sm',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]); ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'sale_price_eu', ['template' => $euTpl]) ->textInput([
            'class' => 'form-control input-sm text-right',
            'maxlength' => true,
        ]) ?>
      </div>
      <div class="col-md-12">
        <legend><?= Yii::t('app', 'Participants') ?></legend>
      </div>
      <div class="col-md-6">
        <?php
          $partners = Partner::listAll();
          $seller = $model->seller;
          $initValue = "{$seller->last_name}, {$seller->first_name} (ref. {$seller->reference})";
          echo $form->field($model, 'seller_id')->widget(Select2::classname(), [
              'initValueText' => $initValue,
              'size' => 'sm',
              'options' => ['placeholder' => Yii::t('app', 'Search for a contact...')],
              'language' => Yii::$app->language,
              'pluginOptions' => [
                  'allowClear' => true,
                  'minimumInputLength' => 3,
                  'ajax' => [
                      'url' => Url::to(['/contact/list']),
                      'dataType' => 'json'
                  ],
          ]]); ?>
          <?= $form->field($model, 'is_new_seller')->checkbox() ?>
          <?= $form->field($model, 'seller_provider')->dropDownList(
              $partners, ['prompt' => Yii::$app->params['company'], 'class' => 'form-control input-sm']) ?>
        <div class="row">
          <label class="col-md-12"><?= Yii::t('app', 'Initial search type and date') ?></label>
          <?= $form->field($model, 'lead_type', ['options' => [
              'class' => 'col-md-5'
          ]])->dropDownList(LeadType::listAll(), [
              'class' => 'form-control input-sm'
          ])->label(false) ?>
          <?= $form->field($model, 'search_started_at', ['options' => ['class' => 'col-md-7']])->widget(DatePicker::classname(), [
              'size' => 'sm',
              'pluginOptions' => [
                  'autoclose' => true,
                  'format' => 'yyyy-mm-dd',
              ]
          ])->label(false); ?>
        </div>
        <?= $form->field($model, 'passed_to_sales_by')->dropDownList(
              Advisor::listAll(), ['prompt' => '', 'class' => 'form-control input-sm']) ?>
      </div>
      <div class="col-md-6">
        <?php
          $buyer = $model->buyer;
          $initValue = "{$buyer->last_name}, {$buyer->first_name} (ref. {$buyer->reference})";
          echo $form->field($model, 'buyer_id')->widget(Select2::classname(), [
              'initValueText' => $initValue,
              'size' => 'sm',
              'options' => ['placeholder' => Yii::t('app', 'Search for a contact...')],
              'pluginOptions' => [
                  'allowClear' => true,
                  'minimumInputLength' => 3,
                  'ajax' => [
                      'url' => Url::to(['/contact/list']),
                      'dataType' => 'json'
                  ],
          ]]); ?>
          <?= $form->field($model, 'is_new_buyer')->checkbox() ?>
          <?= $form->field($model, 'buyer_provider')->dropDownList(
              $partners, ['prompt' => Yii::$app->params['company'], 'class' => 'form-control input-sm']) ?>
        <?= $form->field($model, 'suggested_sale_price_euc', ['template' => $euTpl]) ->textInput([
            'class' => 'form-control input-sm text-right',
            'maxlength' => true,
        ]) ?>
      </div>
      <div class="col-md-12">
        <legend><?= Yii::t('app', 'Property') ?></legend>
      </div>
      <div class="col-md-6">
        <?php
          $property = $model->property;
          $initValue = "{$property->location}, {$property->building_complex} (ref. {$property->reference})";
          echo $form->field($model, 'property_id')->widget(Select2::classname(), [
              'initValueText' => $initValue,
              'size' => 'sm',
              'options' => ['placeholder' => Yii::t('app', 'Search for a property...')],
              'language' => Yii::$app->language,
              'pluginOptions' => [
                  'allowClear' => true,
                  'minimumInputLength' => 3,
                  'ajax' => [
                      'url' => Url::to(['/property/list']),
                      'dataType' => 'json'
                  ],
          ]]); ?>
      </div>
      <div class="col-md-6">
        <label>&nbsp;</label>
        <?= $form->field($model, 'is_home_staged')->checkbox() ?>
      </div>
      <div class="col-md-12">
        <legend><?= Yii::t('app', 'Fees') ?></legend>
      </div>
      <div class="col-md-6">
        <div class="row">
          <label class="col-md-12"><?= Yii::t('app', 'Our fees') ?></label>
          <div class="col-md-5">
            <?= Html::dropDownList('our_fee_pct', null, range(0.1, 10, 0.1), [
                'class' => 'form-control input-sm'
            ]) ?>
          </div>
          <div class="col-md-7">
            <?= $form->field($model, 'our_fee_euc', ['template' => $euTpl]) ->textInput([
                'class' => 'form-control input-sm text-right',
                'maxlength' => true,
            ])->label(false) ?>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="row">
          <label class="col-md-12"><?= Yii::t('app', 'Our partner\'s fees') ?></label>
          <div class="col-md-5">
            <?= Html::dropDownList('their_fee_pct', null, range(0.1, 10, 0.1), [
                'class' => 'form-control input-sm'
            ]) ?>
          </div>
          <div class="col-md-7">
            <?= $form->field($model, 'their_fee_euc', ['template' => $euTpl]) ->textInput([
                'class' => 'form-control input-sm text-right',
                'maxlength' => true,
            ])->label(false) ?>
          </div>
        </div>
      </div>
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><h4 class="panel-title"><?= Yii::t('app', 'Invoices') ?></h4></div>
          <div class="panel-body">
            <?php if (count($model->invoices) > 0): ?>
            <table class="table table-condensed table-striped">
              <thead class="text-center"><tr>
                <th><?= Yii::t('app', 'Code') ?></th>
                <th><?= Yii::t('app', 'Date') ?></th>
                <th><?= Yii::t('app', 'Amount') ?></th>
                <th><?= Yii::t('app', 'Recipient') ?></th>
                <th></th>
              </tr></thead>
              <tbody>
                <?php foreach($model->invoices as $invoice): ?>
                  <tr>
                    <td><?= $invoice->code ?></td>
                    <td><?= Yii::$app->formatter->asDate($invoice->issued_at, 'medium') ?></td>
                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($invoice->amount_euc/100., 2) ?> €</td>
                    <td class="text-center"><?= $invoice->recipient_category ?></td>
                    <td class="text-center"><button class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span></td></tr> 
                <?php endforeach; ?>
              </tbody>
            </table>
            <?php else: ?>
              <p class="text-warning"><em><?= Yii::t('app', 'No invoices found') ?></em></p>
            <?php endif; ?>
            <?= $this->render('/invoice/_form', [
                'model' => $invoice
            ]) ?>
          </div>
        </div>
      </div>
      <div class="col-md-12">
        <legend><?= Yii::t('app', 'Commissions') ?></legend>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'payrolled_at')->widget(DatePicker::classname(), [
            'size' => 'sm',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]); ?>
      </div>
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><h4 class="panel-title"><?= Yii::t('app', 'Attributions') ?></h4></div>
          <div class="panel-body">
            <table class="table table-condensed">
              <thead class="text-center"><tr>
                <th><?= Yii::t('app', 'Advisor') ?></th>
                <th><?= Yii::t('app', 'Office') ?></th>
                <th><?= Yii::t('app', 'Attribution type') ?></th>
                <th><?= Yii::t('app', 'Attribution') ?></th>
                <th><?= Yii::t('app', 'Comments') ?></th>
                <th></th>
              </tr></thead>
              <tbody>
                <?php foreach($model->attributions as $attribution): ?>
                  <tr>
                    <td><?= $attribution->advisor->name ?></td>
                    <td><?= $attribution->office ?></td>
                    <?php $at = $attribution->attributionType ?>
                    <td class="text-center"><?= $at->name . ' ' . Yii::$app->formatter->asDecimal($at->attribution_bp / 100, 2) . '%'?></td>
                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($attribution->amount_euc / 100., 2)?> €</td>
                    <td class="text-center"><small><?= $attribution->comments ?></small></td>
                    <td><button class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span></td></tr> 
                <?php endforeach; ?>
              </body>
            </table>
            <?= $this->render('/attribution/_form') ?>
          </div>
        </div>
      </div>
      <div class="col-md-12">
        <hr style="border-top: 1px solid #ddd">
        <?= $form->field($model, 'comments')->textarea(['rows' => 6]) ?>
        <?= $form->field($model, 'approved')->checkbox() ?>
      </div>
    </fieldset>
  </div>

  <small class="text-info pull-right">
    <strong><?= Yii::t('app', 'Created at') ?></strong> <?= Yii::$app->formatter->asDate($model->created_at, 'full') ?>
    <br>
    <strong><?= Yii::t('app', 'Last updated at') ?></strong> <?= Yii::$app->formatter->asDate($model->updated_at, 'full') ?>
  </small>

  <div class="form-group">
      <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-lg btn-success' : 'btn btn-lg btn-primary']) ?>
  </div>

  <?php ActiveForm::end(); ?>


</div>
