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
use kartik\money\MaskMoney;

$euTpl = "{label}\n<div class=\"input-group\">{input}<span class=\"input-group-addon\">€</span></div>\n{hint}\n{error}";
/* @var $this yii\web\View */
/* @var $model app\models\Transaction */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="transaction-form">

  <?php $form = ActiveForm::begin([
      'id' => 'transaction-form',
      'action' => $model->isNewRecord ?
          ['transaction/create'] : ['transaction/update', 'id' => $model->id],
  ]); ?>
  <?php ActiveForm::end(); ?>
  <?= $form->field($model, 'id')->hiddenInput(['form' => $form->id])->label(false) ?>
  <div class="row">
    <fieldset>
      <div class="col-md-12">
        <legend><?= Yii::t('app', 'Clasification') ?></legend>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'transaction_type')
            ->dropDownList(TransactionType::listAll(), ['prompt' => '', 'class' => 'form-control input-sm', 'form' => $form->id]) ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'custom_type')
            ->dropDownList(CustomType::listAll(), ['prompt' => '', 'class' => 'form-control input-sm', 'form' => $form->id]) ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'transfer_type')
            ->dropDownList(TransferType::listAll(), ['prompt' => '', 'class' => 'form-control input-sm', 'form' => $form->id]) ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'development_type')
            ->dropDownList(DevelopmentType::listAll(), ['prompt' => '', 'class' => 'form-control input-sm', 'form' => $form->id]) ?>
      </div>
      <div class="col-md-12">
        <legend><?= Yii::t('app', 'Evolution') ?></legend>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'first_published_at')->widget(DatePicker::classname(), [
            'options' => ['form' => $form->id],
            'size' => 'sm',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]); ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'first_published_price_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
                'class' => 'text-right input-sm currency',
                'form' => $form->id
            ]]); ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'last_published_at')->widget(DatePicker::classname(), [
            'options' => ['form' => $form->id],
            'size' => 'sm',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]); ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'last_published_price_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
                'class' => 'text-right input-sm currency',
                'form' => $form->id
            ]]); ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'option_signed_at')->widget(DatePicker::classname(), [
            'options' => ['form' => $form->id],
            'size' => 'sm',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]); ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'sale_price_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
                'class' => 'text-right input-sm currency',
                'form' => $form->id
            ]]); ?>
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
              'options' => [
                  'placeholder' => Yii::t('app', 'Search for a contact...'),
                  'form' => $form->id
              ], 'language' => Yii::$app->language,
              'pluginOptions' => [
                  'allowClear' => true,
                  'minimumInputLength' => 3,
                  'ajax' => [
                      'url' => Url::to(['/contact/list']),
                      'dataType' => 'json'
                  ],
          ]]); ?>
          <?= $form->field($model, 'is_new_seller')->checkbox(['form' => $form->id]) ?>
          <?= $form->field($model, 'seller_provider')->dropDownList(
              $partners, ['prompt' => Yii::$app->params['company'], 'class' => 'form-control input-sm', 'form' => $form->id]) ?>
        <div class="row">
          <label class="col-md-12"><?= Yii::t('app', 'Initial Search Date And Class') ?></label>
          <?= $form->field($model, 'search_started_at', ['options' => ['class' => 'col-md-7']])->widget(DatePicker::classname(), [
              'options' => ['form' => $form->id],
              'size' => 'sm',
              'pluginOptions' => [
                  'autoclose' => true,
                  'format' => 'yyyy-mm-dd',
              ]
          ])->label(false); ?>
          <?= $form->field($model, 'lead_type', ['options' => [
              'class' => 'col-md-5',
          ]])->dropDownList(LeadType::listAll(), [
              'class' => 'form-control input-sm',
              'form' => $form->id
          ])->label(false) ?>
        </div>
        <?= $form->field($model, 'passed_to_sales_by')->dropDownList(
              Advisor::listAll(), ['prompt' => '', 'class' => 'form-control input-sm', 'form' => $form->id]) ?>
      </div>
      <div class="col-md-6">
        <?php
          $buyer = $model->buyer;
          $initValue = "{$buyer->last_name}, {$buyer->first_name} (ref. {$buyer->reference})";
          echo $form->field($model, 'buyer_id')->widget(Select2::classname(), [
              'initValueText' => $initValue,
              'size' => 'sm',
              'options' => [
                  'placeholder' => Yii::t('app', 'Search for a contact...'),
                  'form' => $form->id
              ], 'pluginOptions' => [
                  'allowClear' => true,
                  'minimumInputLength' => 3,
                  'ajax' => [
                      'url' => Url::to(['/contact/list']),
                      'dataType' => 'json'
                  ],
          ]]); ?>
          <?= $form->field($model, 'is_new_buyer')->checkbox(['form' => $form->id]) ?>
          <?= $form->field($model, 'buyer_provider')->dropDownList(
              $partners, ['prompt' => Yii::$app->params['company'], 'class' => 'form-control input-sm', 'form' => $form->id]) ?>
        <?= $form->field($model, 'suggested_sale_price_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
                'class' => 'text-right input-sm currency',
                'form' => $form->id
            ]]); ?>
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
              'options' => [
                  'placeholder' => Yii::t('app', 'Search for a property...'),
                  'form' => $form->id
              ], 'language' => Yii::$app->language,
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
        <?= $form->field($model, 'is_home_staged')->checkbox(['form' => $form->id]) ?>
      </div>
      <div class="col-md-12">
        <legend><?= Yii::t('app', 'Fees') ?></legend>
      </div>
      <div class="col-md-6">
        <div class="row">
          <label class="col-md-12"><?= Yii::t('app', 'Our fees') ?></label>
          <div class="col-md-5">
            <?= Html::dropDownList('our_fee_pct', null, range(0.1, 10, 0.1), [
                'class' => 'form-control input-sm',
                'form' => $form->id
            ]) ?>
          </div>
          <div class="col-md-7">
            <?= $form->field($model, 'our_fee_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
                    'class' => 'text-right input-sm currency',
                    'form' => $form->id
                ]])->label(false); ?>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="row">
          <label class="col-md-12"><?= Yii::t('app', 'Our partner\'s fees') ?></label>
          <div class="col-md-5">
            <?= Html::dropDownList('their_fee_pct', null, range(0.1, 10, 0.1), [
                'class' => 'form-control input-sm',
                'form' => $form->id
            ]) ?>
          </div>
          <div class="col-md-7">
            <?= $form->field($model, 'their_fee_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
                    'class' => 'text-right input-sm currency',
                    'form' => $form->id
                ]])->label(false); ?>
          </div>
        </div>
      </div>
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><h4 class="panel-title"><?= Yii::t('app', 'Invoices') ?></h4></div>
          <div class="panel-body">
            <?= $this->render('/invoice/index', [
                'dataProvider' => $invoiceDataProvider,
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
            'options' => ['form' => $form->id],
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
            <?= $this->render('/attribution/index', [
                'dataProvider' => $attributionDataProvider,
                'model' => $attribution
            ]) ?>
          </div>
        </div>
      </div>
      <div class="col-md-12">
        <hr style="border-top: 1px solid #ddd">
        <?= $form->field($model, 'comments')->textarea(['rows' => 6, 'form' => $form->id]) ?>
        <?= $form->field($model, 'approved')->checkbox(['form' => $form->id]) ?>
      </div>
    </fieldset>
  </div>

  <small>
    <dl class="text-info pull-right dl-horizontal">
      <dt><?= Yii::t('app', 'Created at') ?></dt> <dd><?= Yii::$app->formatter->asDatetime($model->created_at, 'long') ?></dd>
      <dt><?= Yii::t('app', 'Last updated at') ?></dt> <dd><?= Yii::$app->formatter->asDatetime($model->updated_at, 'long') ?></dd>
    </dl>
  </small>

  <div class="form-group">
      <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['form' => $form->id, 'class' => $model->isNewRecord ? 'btn btn-lg btn-success' : 'btn btn-lg btn-primary']) ?>
  </div>

</div>
<?php
$script = <<< JS
  // workaround until new release of yii includes commit github.com/yiisoft/yii2/commit/f47b6c
  $('.transaction-form input[type="checkbox"]').each(function(i) {
      $('.transaction-form')
          .find('input[type="hidden"][name="{name}"]'.replace('{name}', $(this).attr('name')))
          .attr('form', '{$form->id}');
  });
  $('.currency').on('keydown', function(e) {
      if(e.keyCode == 13) {
          return false;
      }
  });
JS;
$this->registerJs($script);
?>
