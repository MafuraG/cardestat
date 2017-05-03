<?php
use yii\helpers\Json;
use app\assets\ChartjsAsset;
use app\models\TransactionType;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;

ChartjsAsset::register($this);
$this->title = $title;
$this->params['breadcrumbs'][] = Yii::t('app', 'Charts');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sums-comparison">
  <h1 class="page-header"><?= $this->title ?></h1>
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
      ] ?>
      <div class="form-group">
        <label><?= Yii::t('app', 'Period') ?> </label>
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
  <canvas height="100%"></canvas>
</div>
<?php
$sums_json = Json::encode($sums);
$script = <<<JS
  var last_data = {};
  /*
  var last_data = {
     from: '$from',
     to:, '$to',
     label: '$period',
     interval_months: '$interval_months',
     transaction_type: '$transaction_type'
  };
  */
  $('#w0-container').find('.range-value').html('{$period}');
  var sums = $sums_json;
  function intervalFormat(interval) {
      if (last_data.interval_months == 12) return moment(interval).format('YYYY');
      else if (last_data.interval_months == 3) return moment(interval).format('QTYY');
      else return moment(interval).format('MMM \'YY');
  }
  var intervals = Object.keys(sums);
  var sums_labels = intervals.map(intervalFormat);
  var ctx = $('canvas');
  $('select[name="transaction_type"]').on('change', function() {
      last_data.transaction_type = $(this).val();
      $.ajax({
          data: last_data,
          success: function(data) {
              updateChart(data);
          }
      });
  });
  $('select[name="interval_months"]').on('change', function() {
      last_data.interval_months = $(this).val();
      $.ajax({
          data: last_data,
          success: function(data) {
              updateChart(data);
          }
      });
  });
  function data_map(period, sums) {
      var sum_index = 'sum' + period + '_eu';
      return function(key, index) {
          if (sums[key][sum_index]) return parseFloat(sums[key][sum_index]).toFixed(2);
          else return 0;
      }
  }
  function updateChart(data) {
     var intervals = Object.keys(data.sums);
     var sum_labels = intervals.map(intervalFormat);
     chart.config.data.labels = sum_labels;
     chart.config.data.datasets[0].data = intervals.map(data_map(1, data.sums));
     chart.config.data.datasets[1].data = intervals.map(data_map(2, data.sums));
     chart.update();
  }
  function ticksCallback(value, index, values) {
      if (value >= 1000000) return (value/1000000).toFixed(2) + 'M'; 
      else if (value >= 1000) return (value/1000).toFixed(2) + 'k';
      else return value.toFixed(2);
  }
  var chart = new Chart(ctx, {
      type: 'line',
      data: {
          labels: sums_labels,
          datasets: [{
              label: '$label1',
              yAxisID: 'A',
              borderColor: 'rgba(255,99,132,1)',
              backgroundColor: 'rgba(255, 99, 132, 0.2)',
              data: intervals.map(data_map(1, sums))
          }, {
              label: '$label2',
              yAxisID: 'B',
              borderColor: 'rgba(54, 162, 235, 1)',
              backgroundColor: 'rgba(54, 162, 235, 0.2)',
              data: intervals.map(data_map(2, sums))
          }]
      },
      options: {
          scaleStartValue: 0,
          scales: {
              yAxes: [{
                  id: 'A',
                  type: 'linear',
                  position: 'left',
                  scaleLabel: {
                      display: true,
                      labelString: '$label1'
                  },
                  ticks: {
                      userCallback: ticksCallback,
                      min: 0,
                      beginAtZero: true,
                  }
              }, {
                  id: 'B',
                  type: 'linear',
                  position: 'right',
                  scaleLabel: {
                      display: true,
                      labelString: '$label2',
                  },
                  ticks: {
                      userCallback: ticksCallback,
                      min: 0,
                      beginAtZero: true,
                  }
              }]
          }
      }
  });
JS;
$this->registerJs($script);
