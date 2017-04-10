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
$pctTpl = "{label}\n<div class=\"input-group\">{input}<span class=\"input-group-addon\">%</span></div>\n{hint}\n{error}";
if (!isset($readonly)) $readonly = false;
$user = Yii::$app->user;
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
    <div class="col-md-12">
      <legend class="bg-primary"><?= Yii::t('app', 'Clasification') ?></legend>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'transaction_type')
          ->dropDownList(TransactionType::listAll(), ['prompt' => '', 'class' => 'form-control input-sm', 'form' => $form->id, 'disabled' => $readonly]) ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'custom_type')
          ->dropDownList(CustomType::listAll(), ['prompt' => '', 'class' => 'form-control input-sm', 'form' => $form->id, 'disabled' => $readonly]) ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'transfer_type')
          ->dropDownList(TransferType::listAll(), ['prompt' => '', 'class' => 'form-control input-sm', 'form' => $form->id, 'disabled' => $readonly]) ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'development_type')
          ->dropDownList(DevelopmentType::listAll(), ['prompt' => '', 'class' => 'form-control input-sm', 'form' => $form->id, 'disabled' => $readonly]) ?>
    </div>
    <div class="col-md-12">
      <legend class="bg-primary"><?= Yii::t('app', 'Evolution') ?></legend>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'first_published_at')->widget(DatePicker::classname(), [
          'options' => ['form' => $form->id, 'disabled' => $readonly],
          'size' => 'sm',
          'removeButton' => !$readonly ? [] : false,
          'pluginOptions' => [
              'autoclose' => true,
              'format' => 'yyyy-mm-dd',
          ]
      ]); ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'first_published_price_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
              'class' => 'text-right input-sm mask-money',
              'disabled' => $readonly,
              'form' => $form->id
          ]]); ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'last_published_at')->widget(DatePicker::classname(), [
          'options' => ['form' => $form->id, 'disabled' => $readonly],
          'size' => 'sm',
          'removeButton' => !$readonly ? [] : false,
          'pluginOptions' => [
              'autoclose' => true,
              'format' => 'yyyy-mm-dd',
          ]
      ]); ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'last_published_price_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
              'class' => 'text-right input-sm mask-money',
              'form' => $form->id,
              'disabled' => $readonly
          ]]); ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'option_signed_at')->widget(DatePicker::classname(), [
          'options' => ['form' => $form->id, 'disabled' => $readonly],
          'size' => 'sm',
          'removeButton' => !$readonly ? [] : false,
          'pluginOptions' => [
              'autoclose' => true,
              'format' => 'yyyy-mm-dd',
          ]
      ]); ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'sale_price_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
              'class' => 'text-right input-sm mask-money',
              'form' => $form->id,
              'disabled' => $readonly
          ]]); ?>
    </div>
    <div class="col-md-12">
      <legend class="bg-primary"><?= Yii::t('app', 'Participants') ?></legend>
    </div>
    <div class="col-md-6">
      <?php
        $partners = Partner::listAll();
        $initValue = null;
        if ($model->seller_id) {
            $seller = $model->seller;
            $initValue = "{$seller->last_name}, {$seller->first_name} (ref. {$seller->reference})";
        }
        echo $form->field($model, 'seller_id')->widget(Select2::classname(), [
            'initValueText' => $initValue,
            'size' => 'sm',
            'options' => [
                'placeholder' => Yii::t('app', 'Search for a contact...'),
                'form' => $form->id,
                'disabled' => $readonly
            ], 'language' => Yii::$app->language,
            'addon' => [
                'prepend' => [
                    'content' => Html::button('', [
                        'class' => 'btn btn-toggle btn-default collapsed',
                        'data-toggle' => 'collapse',
                        'href' => '#seller-collapse'
                    ]),
                    'asButton' => true
                ] 
            ], 'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 3,
                'ajax' => [
                    'url' => Url::to(['/contact/list']),
                    'dataType' => 'json'
                ],
      ]]); ?>
      <div id="seller-collapse" class="collapse well well-sm">
        <?php if (isset($seller)) echo $this->render('/contact/_view', ['model' => $seller]) ?>
      </div>
      <?= $form->field($model, 'is_new_seller')->checkbox(['form' => $form->id, 'disabled' => $readonly]) ?>
      <?= $form->field($model, 'seller_provider')->dropDownList(
          $partners, ['prompt' => Yii::$app->params['company'], 'class' => 'form-control input-sm', 'form' => $form->id, 'disabled' => $readonly]) ?>
      <?= $form->field($model, 'suggested_sale_price_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
            'class' => 'text-right input-sm mask-money',
            'disabled' => $readonly,
            'form' => $form->id
        ]]); ?>
    </div>
    <div class="col-md-6">
      <?php
        $initValue = null;
        if ($model->buyer_id) {
            $buyer = $model->buyer;
            $initValue = "{$buyer->last_name}, {$buyer->first_name} (ref. {$buyer->reference})";
        }
        echo $form->field($model, 'buyer_id')->widget(Select2::classname(), [
            'initValueText' => $initValue,
            'size' => 'sm',
            'options' => [
                'placeholder' => Yii::t('app', 'Search for a contact...'),
                'form' => $form->id,
                'disabled' => $readonly
            ],
            'addon' => [
                'prepend' => [
                    'content' => Html::button('', [
                        'class' => 'btn btn-toggle btn-default collapsed',
                        'data-toggle' => 'collapse',
                        'href' => '#buyer-collapse'
                    ]),
                    'asButton' => true
                ] 
            ], 'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 3,
                'ajax' => [
                    'url' => Url::to(['/contact/list']),
                    'dataType' => 'json'
                ],
      ]]); ?>
      <div id="buyer-collapse" class="collapse well well-sm">
        <?php if (isset($buyer)) echo $this->render('/contact/_view', ['model' => $buyer]) ?>
      </div>
      <?= $form->field($model, 'is_new_buyer')->checkbox(['form' => $form->id, 'disabled' => $readonly]) ?>
      <?= $form->field($model, 'buyer_provider')->dropDownList(
          $partners, ['prompt' => Yii::$app->params['company'], 'class' => 'form-control input-sm', 'form' => $form->id, 'disabled' => $readonly]) ?>
      <div class="row">
        <label class="col-md-12"><?= Yii::t('app', 'Initial Search Date And Class') ?></label>
        <?= $form->field($model, 'search_started_at', ['options' => ['class' => 'col-md-7']])->widget(DatePicker::classname(), [
            'options' => ['form' => $form->id, 'disabled' => $readonly],
            'size' => 'sm',
            'removeButton' => !$readonly ? [] : false,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ])->label(false); ?>
        <?= $form->field($model, 'lead_type', ['options' => [
            'class' => 'col-md-5',
        ]])->dropDownList(LeadType::listAll(), [
            'class' => 'form-control input-sm',
            'form' => $form->id,
            'disabled' => $readonly
        ])->label(false) ?>
      </div>
      <?= $form->field($model, 'passed_to_sales_by')->dropDownList(
            Advisor::listActiveHub(), ['prompt' => '', 'class' => 'form-control input-sm', 'form' => $form->id, 'disabled' => $readonly]) ?>
    </div>
    <div class="col-md-12">
      <legend class="bg-primary"><?= Yii::t('app', 'Property') ?></legend>
    </div>
    <div class="col-md-6">
      <?php
        $initValue = null;
        if ($model->property_id) {
            $property = $model->property;
            $initValue = "{$property->location}, {$property->building_complex} (ref. {$property->reference})";
        }
        echo $form->field($model, 'property_id')->widget(Select2::classname(), [
            'initValueText' => $initValue,
            'size' => 'sm',
            'options' => [
                'placeholder' => Yii::t('app', 'Search for a property...'),
                'form' => $form->id,
                'disabled' => $readonly
            ], 'addon' => [
                'prepend' => [
                    'content' => Html::button('', [
                        'class' => 'btn btn-toggle btn-default collapsed',
                        'data-toggle' => 'collapse',
                        'href' => '#property-collapse'
                    ]),
                    'asButton' => true
                ] 
            ], 'language' => Yii::$app->language,
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 3,
                'ajax' => [
                    'url' => Url::to(['/property/list']),
                    'dataType' => 'json'
                ],
      ]]); ?>
      <div id="property-collapse" class="collapse well well-sm">
        <?php if (isset($property)) echo $this->render('/property/_view', ['model' => $property]) ?>
      </div>
    </div>
    <div class="col-md-6">
      <label>&nbsp;</label>
      <?= $form->field($model, 'is_home_staged')->checkbox(['label' => $model->getAttributeLabel('is_home_staged'), 'form' => $form->id, 'disabled' => $readonly]) ?>
    </div>
    <div class="col-md-12">
      <legend class="bg-primary"><?= Yii::t('app', 'Fees') ?></legend>
    </div>
    <div class="col-md-6">
      <div class="row">
        <label class="col-md-12">
        <?= Yii::t('app', '{company}\'s fee', ['company' => Yii::$app->params['company']]) ?></label>
        <div class="col-md-5">
          <div class="input-group">
            <?= MaskMoney::widget([
                'name' => 'our_fee_pct',
                'options' => [
                    'class' => 'form-control text-right input-sm mask-money',
                    'maxlength' => 6,
                    'disabled' => $readonly
                ]
            ]) ?>
            <span class="input-group-addon">%</span>
          </div>
        </div>
        <div class="col-md-7">
          <?= $form->field($model, 'our_fee_eu', ['template' => $euTpl])->widget(MaskMoney::classname(), ['options' => [
                  'class' => 'text-right input-sm mask-money',
                  'disabled' => $readonly,
                  'form' => $form->id
              ]])->label(false); ?>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="row">
        <label class="col-md-12"><?= Yii::t('app', 'Our Partner\'s Fees') ?></label>
        <div class="col-md-5">
          <div class="input-group">
            <?= MaskMoney::widget([
                'name' => 'their_fee_pct',
                'options' => [
                    'class' => 'form-control text-right input-sm mask-money',
                    'maxlength' => 6,
                    'disabled' => $readonly
                ]
            ]) ?>
            <span class="input-group-addon">%</span>
          </div>
        </div>
        <div class="col-md-7">
          <?= $form->field($model, 'their_fee_eu', ['template' => $euTpl])
              ->widget(MaskMoney::classname(), ['options' => [
                  'class' => 'text-right input-sm mask-money',
                  'form' => $form->id,
                  'disabled' => $readonly
              ]])->label(false); ?>
        </div>
      </div>
    </div>
    <?php if ($user->can('accounting')): ?>
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title"><?= Yii::t('app', 'Invoices') ?></h4></div>
          <div class="panel-body">
            <?= $this->render('/invoice/index', [
                'dataProvider' => $invoiceDataProvider,
                'model' => $invoice,
                'readonly' => $readonly
            ]) ?>
          </div>
        </div>
      </div>
      <div class="col-md-12">
        <legend class="bg-primary"><?= Yii::t('app', 'Commissions') ?></legend>
      </div>
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title"><?= Yii::t('app', 'Attributions') ?></h4></div>
          <div class="panel-body">
            <?= $this->render('/attribution/index', [
                'dataProvider' => $attributionDataProvider,
                'model' => $attribution,
                'attribution_types' => $attribution_types,
                'advisor_defaults' => $advisor_defaults,
                'total_invoiced_eu' => $total_invoiced_eu,
                'readonly' => $readonly
            ]) ?>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'payroll_month')->widget(DatePicker::classname(), [
            'options' => ['form' => $form->id, 'disabled' => $readonly],
            'size' => 'sm',
            'removeButton' => !$readonly ? [] : false,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm',
                'startView' => 'months', 
                'minViewMode' => 'months'
            ]
        ]); ?>
      </div>
      <div class="col-md-12">
        <hr style="border-top: 1px solid #ddd">
        <?= $form->field($model, 'comments')
            ->textarea(['rows' => 6, 'form' => $form->id, 'disabled' => $readonly]) ?>
        <?= $form->field($model, 'approved_by_direction')
            ->checkbox(['form' => $form->id, 'disabled' => $readonly or !$user->can('admin')]) ?>
        <?= $form->field($model, 'approved_by_accounting')
            ->checkbox(['form' => $form->id, 'disabled' => $readonly]) ?>
      </div>
    <?php endif; ?>
  </div>

  <small>
    <dl class="text-info pull-right dl-horizontal">
      <dt><?= Yii::t('app', 'Created At') ?></dt> <dd><?= Yii::$app->formatter->asDatetime($model->created_at, 'long') ?></dd>
      <dt><?= Yii::t('app', 'Last Updated At') ?></dt> <dd><?= Yii::$app->formatter->asDatetime($model->updated_at, 'long') ?></dd>
    </dl>
  </small>

  <?php if (!$readonly): ?>
    <div class="form-group">
      <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['form' => $form->id, 'class' => $model->isNewRecord ? 'btn btn-lg btn-success' : 'btn btn-lg btn-primary']) ?>
    </div>
  <?php endif; ?>

</div>
<?php
$attrIndexUrl = Url::to(['/attribution/index', 'transaction_id' => $model->id]);
$script = <<< JS
  // workaround until new release of yii includes commit github.com/yiisoft/yii2/commit/f47b6c
  $('.transaction-form input[type="checkbox"]').each(function(i) {
      $('.transaction-form')
          .find('input[type="hidden"][name="{name}"]'.replace('{name}', $(this).attr('name')))
          .attr('form', '{$form->id}');
  });
  $('.mask-money').on('keydown', function(e) {
      if(e.keyCode == 13) {
          return false;
      }
  });
  var attrIndexUrl = '$attrIndexUrl';
  $('#invoice-index-p0').on('pjax:end', function() {
      $.pjax.reload('#attribution-index-p0', {url: attrIndexUrl, push: false, replace: false, timeout: 6000});
  });
  \$salePriceEuMM = $('input[name="Transaction[sale_price_eu]"]').siblings('.mask-money');
  \$ourFeeEuMM = $('input[name="Transaction[our_fee_eu]"]').siblings('.mask-money');
  \$ourFeePctMM = $('input[name="our_fee_pct"]').siblings('.mask-money');
  \$ourFeePctMM.on('blur', function() {
      \$ourFeeEuMM.maskMoney('mask',
          \$ourFeePctMM.maskMoney('unmasked')[0] / 100. * \$salePriceEuMM.maskMoney('unmasked')[0]);
      \$ourFeeEuMM.blur();
  });
  \$ourFeeEuMM.on('blur', function() {
      \$ourFeePctMM.maskMoney('mask',
           \$ourFeeEuMM.maskMoney('unmasked')[0] * 100. / \$salePriceEuMM.maskMoney('unmasked')[0]);
  });
  \$ourFeeEuMM.blur();
  \$theirFeeEuMM = $('input[name="Transaction[their_fee_eu]"]').siblings('.mask-money');
  \$theirFeePctMM = $('input[name="their_fee_pct"]').siblings('.mask-money');
  \$theirFeePctMM.on('blur', function() {
      \$theirFeeEuMM.maskMoney('mask',
          \$theirFeePctMM.maskMoney('unmasked')[0] / 100. * \$salePriceEuMM.maskMoney('unmasked')[0]);
      \$theirFeeEuMM.blur();
  });
  \$theirFeeEuMM.on('blur', function() {
      \$theirFeePctMM.maskMoney('mask',
           \$theirFeeEuMM.maskMoney('unmasked')[0] * 100. / \$salePriceEuMM.maskMoney('unmasked')[0]);
  });
  \$theirFeeEuMM.blur();
JS;
$this->registerJs($script);
?>
