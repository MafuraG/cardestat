<?php
use app\models\TransactionType;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;

$this->title = strip_tags($title);
$this->params['breadcrumbs'][] = Yii::t('app', 'Charts');
$this->params['breadcrumbs'][] = $title;
?>
<div class="sums-comparison">
  <h1 class="page-header"><?= $title ?></h1>
  <div class="well well-sm">
    <?= Html::beginForm('', 'get', ['class' => 'form form-inline']) ?>
      <?php $presetRanges = [
          Yii::t('app', 'Current year') => ["moment().startOf('year')", "moment().endOf('year')"],
          Yii::t('app', 'Previous year') => ["moment().subtract(1, 'year').startOf('year')", "moment().subtract(1, 'year').endOf('year')"],
          Yii::t('app', 'Trailing twelve months') => ["moment().subtract(1, 'year').add(1, 'month')", "moment()"],
          Yii::t('app', 'Trailing twenty-four months') => ["moment().subtract(2, 'year').add(1, 'month')", "moment()"],
          Yii::t('app', 'Previous two years') => ["moment().subtract(2, 'year').startOf('year')", "moment().subtract(1, 'year').endOf('year')"],
          Yii::t('app', 'Previous three years') => ["moment().subtract(3, 'year').startOf('year')", "moment().subtract(1, 'year').endOf('year')"],
          Yii::t('app', 'Previous five years') => ["moment().subtract(5, 'year').startOf('year')", "moment().subtract(1, 'year').endOf('year')"],
          Yii::t('app', 'Previous ten years') => ["moment().subtract(10, 'year').startOf('year')", "moment().subtract(1, 'year').endOf('year')"],
      ] ?>
      <div class="form-group">
        <label><?= Yii::t('app', 'Period') ?></label>
        <?= DateRangePicker::widget([
            'name' => 'daterange',
            'hideInput' => true,
            'convertFormat' => true,
            'startAttribute' => 'from',
            'callback' => 'function(startDate, endDate, label) {
                $(\'[name="label1"]\').val(label);
                $(\'#w0-container\').find(\'.range-value\').html(label);
                last_data.from = startDate.format(\'YYYY-MM-DD\');
                last_data.to = endDate.format(\'YYYY-MM-DD\');
                last_data.label = label;
                $.ajax({
                    data: last_data,
                    success: function(data) {
                        updateChart(data);
                    }
                });
            }',
            'endAttribute' => 'to',
            'startInputOptions' => ['value' => $from],
            'endInputOptions' => ['value' => $to],
            'pluginOptions' => [
                'locale' => ['format' => 'Y-m-d', 'separator' => ' → '],
                'ranges' => $presetRanges
            ]
        ]) ?>
      </div>
      <div class="form-group">
        <label><?= Yii::t('app', 'Transaction Type') ?></label>
        <?= Html::dropDownList('transaction_type', $transaction_type, TransactionType::listAll(), [
            'class' => 'form-control input-sm', 'prompt' => '-- ' . Yii::t('app', 'All') . ' --']) ?>
      </div>
      <div class="form-group">
        <label><?= Yii::t('app', 'Interval') ?></label>
        <?= Html::dropDownList('interval_months', $interval_months, $intervals, ['class' => 'form-control input-sm']) ?>
      </div>
  </div>
  <p class="lead text-center"><?= $subtitle ?></p>
  <canvas height="100%"></canvas>
  <p class="text-center"><?= $comments ?></p>
</div>
