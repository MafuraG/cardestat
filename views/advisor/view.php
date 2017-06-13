<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use app\assets\MaskmoneyAsset;

/* @var $this yii\web\View */
/* @var $model app\models\Advisor */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Advisors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$euTpl = "{label}\n<div class=\"input-group\">{input}<span class=\"input-group-addon\">€</span></div>\n{hint}\n{error}";
$pctTpl = "{label}\n<div class=\"input-group\">{input}<span class=\"input-group-addon\">%</span></div>\n{hint}\n{error}";
MaskmoneyAsset::register($this);
?>
<div class="advisor-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'active:boolean',
            'is_hub_agent:boolean',
            'default_office', [
                'attribute' => 'default_attribution_type_id',
                'value' => isset($model->defaultAttributionType) ? ($model->defaultAttributionType->name . ' ' . round($model->defaultAttributionType->attribution_bp / 100., 2) . '%') : ''
            ],
        ],
    ]) ?>

    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><?= Yii::t('app', 'Commission tranches') ?></h3>
      </div>
      <?php Pjax::begin(['enablePushState' => false]); $form = ActiveForm::begin([
        'enableClientValidation' => false,
        'options' => ['data-pjax' => true, 'enctype' => 'application/x-www-form-urlencoded'],
        'action' => ['advisor/save-tranches', 'advisor_id' => $model->id]
      ]); ?>
        <table class="table table-condensed table-striped">
          <thead><tr>
            <th></th>
            <th><?= Yii::t('app', 'From') ?></th>
            <th><?= Yii::t('app', 'To') ?></th>
            <th><?= Yii::t('app', 'Rate') ?></th>
            <th></th>
          </tr></thead>
          <tbody>
            <tr id="tranche-template">
              <td class="text-center">
                <button class="btn btn-xs btn-danger btn-remove-tranche">
                  <span class="glyphicon glyphicon-trash"></span></button>
              </td>
              <td class="form-inline"><div class="input-group">
                <?= Html::textInput('AdvisorTranche[_i_][from_eu]', 0, [
                    'class' => 'form-control text-right input-sm mask-money',
                    'readonly' => true
                ]) ?>
                <span class="input-group-addon">€</span>
              </div></td>
              <td class="form-inline"><div class="input-group">
                <?= Html::textInput('AdvisorTranche[_i_][to_eu]', 0.01, [
                    'class' => 'form-control text-right input-sm mask-money',
                ]) ?>
                <span class="input-group-addon">€</span>
              </div></td>
              <td class="form-inline"><div class="input-group">
                <?= Html::textInput('AdvisorTranche[_i_][commission_pct]', 0, [
                    'class' => 'form-control text-right input-sm mask-money',
                ]) ?>
                <span class="input-group-addon">%</span>
              </div></td>
              <td>
                <button class="btn btn-xs btn-primary btn-add-tranche">
                <span class="glyphicon glyphicon-plus-sign"></span></button>
              </td>
            </tr>
            <?php foreach ($model->tranches as $i => $tranche): ?><tr>
              <td class="text-center">
                <?php if ($i+1 === count($model->tranches)): ?>
                  <span class="glyphicon glyphicon-pushpin"></span>
                <?php else: ?>
                  <button class="btn btn-xs btn-danger btn-remove-tranche">
                    <span class="glyphicon glyphicon-trash"></span></button>
                <?php endif; ?>
              </td>
              <td class="form-inline">
                <?= $form->field($tranche, "[$i]from_eu", ['template' => $euTpl])->textInput([
                    'class' => 'text-right input-sm mask-money form-control',
                    'readonly' => true,
                ])->label(false); ?>
              </td>
              <td class="form-inline"><div class="input-group">
                <?php if (isset($model->tranches[$i+1])): ?>
                  <?= Html::textInput("AdvisorTranche[$i][to_eu]", $model->tranches[$i+1]->from_eu - 0.01, [
                          'class' => 'form-control text-right input-sm mask-money',
                  ]) ?>
                <?php else: ?>
                  <input class="text-right form-control input-sm" value="&infin;" readonly>
                <?php endif; ?>
                <span class="input-group-addon">€</span>
              </div></td>
              <td class="form-inline">
                <?= $form->field($tranche, "[$i]commission_pct", ['template' => $pctTpl])->textInput([
                    'class' => 'text-right input-sm mask-money form-control',
                ])->label(false); ?>
              </td>
              <td>
                <button class="btn btn-xs btn-primary btn-add-tranche">
                <span class="glyphicon glyphicon-plus-sign"></span></button>
              </td>
            </tr><?php endforeach; ?>
          </tbody>
        </table>
        <div class="panel-body"><div class="form-group">
          <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
        </div></div>
      <?php $form = ActiveForm::end(); Pjax::end(); ?>
    </div>
</div>
<?php
if (!isset($i)) $i = -1;
$i++;
$thousands = \Yii::$app->formatter->thousandSeparator;
$decimal = \Yii::$app->formatter->decimalSeparator;
$script = <<< JS
  var \$trancheTpl;
  $('#p0').on('pjax:end', function() {
      init();
  });
  function init() {
      \$trancheTpl = $('#tranche-template').detach();
      \$trancheTpl.attr('id', null);
      $('.mask-money').maskMoney({
          allowZero: true,
          thousands: '$thousands',
          decimal: '$decimal'
      });
      $('.mask-money').each(function(k, v) {
          var \$this = $(this);
          var val = \$this.val();
          $(this).maskMoney('mask', parseFloat(val));
      });
  }
  init();
  var seq = $i;
  $('.advisor-view').on('beforeSubmit', 'form', function() {
      $('input.mask-money').each(function(i, v) {
          var \$this = $(this);
          \$this.val(\$this.maskMoney('unmasked')[0]);
      });
  });
  $('.advisor-view').on('blur', 'input[name$="[to_eu]"]', function() {
      var max_next_from_eu = $(this).closest('tr').next().find('input[name$="[to_eu]"]').maskMoney('unmasked')[0];
      var min_next_from_eu = $(this).closest('tr').find('input[name$="[from_eu]"]').maskMoney('unmasked')[0];
      var next_from_eu = $(this).maskMoney('unmasked')[0] + 0.01;
      if (next_from_eu >= max_next_from_eu) {
          $(this).maskMoney('mask', max_next_from_eu - 0.02);
          $(this).closest('tr').next().find('.mask-money[name$="[from_eu]"]').maskMoney('mask', max_next_from_eu - 0.01);
      } else if (next_from_eu < min_next_from_eu) {
          $(this).maskMoney('mask', min_next_from_eu + 0.01);
          $(this).closest('tr').next().find('.mask-money[name$="[from_eu]"]').maskMoney('mask', min_next_from_eu + 0.02);
      } else
          $(this).closest('tr').next().find('.mask-money[name$="[from_eu]"]').maskMoney('mask', next_from_eu);
      return false;
  });
  $('.advisor-view').on('click', '.btn-add-tranche', function() {
      var \$current = $(this).closest('tr');
      var \$currentFromEu = \$current.find('input[name$="[from_eu]"]');
      var \$currentToEu = \$current.find('input[name$="[to_eu]"]');
      var \$currentComPct = \$current.find('input[name$="[commission_pct]"]');
      var cur_com_pct = \$currentComPct.maskMoney('unmasked')[0];
      var cur_from_eu = \$currentFromEu.maskMoney('unmasked')[0];
      var cur_to_eu = \$currentToEu.maskMoney('unmasked')[0];
      if (cur_to_eu < 0.02 || cur_from_eu + 0.02 >= cur_to_eu) return false;
      \$currentFromEu.maskMoney('mask', cur_from_eu + 0.02);
      $(this).closest('tr').before(\$trancheTpl.clone());
      var \$new = $(this).closest('tr').prev();
      \$newFromEu = \$new.find('input[name$="[from_eu]"]');
      \$newFromEu.attr('name', \$newFromEu.attr('name').replace('_i_', seq));
      \$newFromEu.maskMoney();
      \$newFromEu.maskMoney('mask', cur_from_eu);
      \$newToEu = \$new.find('input[name$="[to_eu]"]');
      \$newToEu.attr('name', \$newToEu.attr('name').replace('_i_', seq));
      \$newToEu.maskMoney();
      \$newToEu.maskMoney('mask', cur_from_eu + 0.01);
      \$newComPct = \$new.find('input[name$="[commission_pct]"]');
      \$newComPct.attr('name', \$newComPct.attr('name').replace('_i_', seq));
      \$newComPct.maskMoney();
      \$newComPct.maskMoney('mask', cur_com_pct);
      seq++;
      return false;
  });
  $('.advisor-view').on('click', '.btn-remove-tranche', function() {
      var old_to_eu = $(this).closest('tr').find('input[name$="[to_eu]"]')
          .siblings('.mask-money').maskMoney('unmasked')[0];
      var \$prev = $(this).closest('tr').prev();
      if (\$prev.length) \$prev.find('input[name$="[to_eu]"]')
          .siblings('.mask-money').maskMoney('mask', old_to_eu);
      else {
          $(this).closest('tr').next().find('input[name$="[from_eu]"]')
          .siblings('.mask-money').maskMoney('mask', 0);
      }
      $(this).closest('tr').remove();
      return false;
  });
JS;
$this->registerJs($script);
?>
