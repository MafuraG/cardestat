<?php
use yii\helpers\Json;
use app\assets\ChartjsAsset;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;

ChartjsAsset::register($this);

$this->title = $title;
$this->params['breadcrumbs'][] = Yii::t('app', 'Charts');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="groupings-comparison">
  <h1 class="page-header"><?= Html::encode($this->title) ?></h1>
  <div class="well well-sm">
    <?= Html::beginForm('', 'get', ['class' => 'form form-inline']) ?>
      <?= Html::hiddenInput('label1', $period1['label']) ?>
      <?= Html::hiddenInput('label2', $period2['label']) ?>
      <label><?= Yii::t('app', 'Period') ?> 1</label>
      <?php $presetRanges = [
          Yii::t('app', 'Current month') => ["moment().startOf('month')", "moment().endOf('month')"],
          Yii::t('app', 'Previous month') => ["moment().subtract(1, 'month').startOf('month')", "moment().subtract(1, 'month').endOf('month')"],
          Yii::t('app', 'Same month previous year') => ["moment().startOf('month').subtract(1, 'year')", "moment().endOf('month').subtract(1, 'year')"],
          Yii::t('app', 'Current year') => ["moment().startOf('year')", "moment().endOf('year')"],
          Yii::t('app', 'Previous year') => ["moment().subtract(1, 'year').startOf('year')", "moment().subtract(1, 'year').endOf('year')"],
          Yii::t('app', 'Trailing twelve months') => ["moment().subtract(1, 'year').add(1, 'month')", "moment()"],
      ] ?>
      <?= DateRangePicker::widget([
          'name' => 'daterange1',
          'hideInput' => true,
          'convertFormat' => true,
          'startAttribute' => 'from1',
          'callback' => 'function(startDate, endDate, label) {
              var picker2 = $(\'[name="daterange2"]\')
                  .closest(\'.drp-container\')
                  .data(\'daterangepicker\');
              $(\'[name="label1"]\').val(label);
              var label2 = $(\'[name="label2"]\').val();
              $(\'#w0-container\').find(\'.range-value\').html(label);
              $.ajax({
                  data: {
                      from1: startDate.format(\'YYYY-MM-DD\'),
                      to1: endDate.format(\'YYYY-MM-DD\'),
                      label1: label,
                      from2: picker2.startDate.format(\'YYYY-MM-DD\'),
                      to2: picker2.endDate.format(\'YYYY-MM-DD\'),
                      label2: label2
                  }, success: function(data) {
                      updateChart(data);
                  }
              });
          }',
          'endAttribute' => 'to1',
          'startInputOptions' => ['value' => $period1['from']],
          'endInputOptions' => ['value' => $period1['to']],
          'pluginOptions' => [
              'locale' => ['format' => 'Y-m-d', 'separator' => ' → '],
              'autoApply' => true,
              'ranges' => $presetRanges
          ]
      ]) ?>
      <label><?= Yii::t('app', 'Period') ?> 2</label>
      <?= DateRangePicker::widget([
          'name' => 'daterange2',
          'hideInput' => true,
          'convertFormat' => true,
          'startAttribute' => 'from2',
          'callback' => 'function(startDate, endDate, label) {
              var picker1 = $(\'[name="daterange1"]\')
                  .closest(\'.drp-container\')
                  .data(\'daterangepicker\');
              $(\'[name="label2"]\').val(label);
              var label1 = $(\'[name="label1"]\').val();
              $(\'#w1-container\').find(\'.range-value\').html(label);
              $.ajax({
                  data: {
                      from1: picker1.startDate.format(\'YYYY-MM-DD\'),
                      to1: picker1.endDate.format(\'YYYY-MM-DD\'),
                      label1: label1,
                      from2: startDate.format(\'YYYY-MM-DD\'),
                      to2: endDate.format(\'YYYY-MM-DD\'),
                      label2: label
                  }, success: function(data) {
                      updateChart(data);
                  }
              });
          }',
          'endAttribute' => 'to2',
          'startInputOptions' => ['value' => $period2['from']],
          'endInputOptions' => ['value' => $period2['to']],
          'pluginOptions' => [
              'locale' => ['format' => 'Y-m-d', 'separator' => ' → '],
              'autoApply' => true,
              'ranges' => $presetRanges
          ]
      ]) ?>
    <?= Html::endForm() ?>
  </div>
  <canvas height="100%"></canvas>
</div>
<?php
$groupings_json = Json::encode($groupings);
$script = <<<JS
  $('#w0-container').find('.range-value').html('{$period1['label']}');
  $('#w1-container').find('.range-value').html('{$period2['label']}');
  var horizonalLinePlugin = {
      beforeDraw: function(chartInstance) {
          var yValue;
          var yScale = chartInstance.scales["y-axis-0"];
          var canvas = chartInstance.chart;
          var ctx = canvas.ctx;
          var index;
          var line;
          var style;
          if (chartInstance.options.horizontalLines) {
              for (index = 0; index < chartInstance.options.horizontalLines.length; index++) {
                  line = chartInstance.options.horizontalLines[index];
                  if (!line.style) {
                      style = 'rgba(169,169,169, .6)';
                  } else {
                      style = line.style;
                  }
                  if (line.y) {
                      yValue = yScale.getPixelForValue(line.y);
                  } else {
                      yValue = 0;
                  }
                  ctx.lineWidth = 1;
                  if (yValue) {
                      ctx.beginPath();
                      //ctx.moveTo(0, yValue);
                      ctx.moveTo(chartInstance.chartArea.left, yValue);
                      //ctx.lineTo(canvas.width, yValue);
                      ctx.lineTo(chartInstance.chartArea.right, yValue);
                      ctx.strokeStyle = style;
                      ctx.stroke();
                  }
                  if (line.text) {
                      ctx.fillStyle = style;
                      ctx.fillText(line.text, 0, yValue - 16);
                  }
              }
          }
      }
  };
  Chart.pluginService.register(horizonalLinePlugin);
  var groupings = $groupings_json;
  var grouping_labels = Object.keys(groupings);
  var ctx = $('canvas');
  function avg_reduce(period, groupings) {
      var sum_index = 'sum' + period + '_eu';
      var count_index = 'count' + period;
      var len = Object.keys(groupings).length;
      return function(total, key) {
          if (groupings[key][sum_index]) {
              return parseFloat(groupings[key][sum_index] / len) + total;
          }
          else return total;
      }
  }
  function data_map(period, groupings) {
      var sum_index = 'sum' + period + '_eu';
      return function(key, index) {
          if (groupings[key][sum_index]) return parseFloat(groupings[key][sum_index]).toFixed(2);
          else return 0;
      }
  }
  var period1avg = grouping_labels.reduce(avg_reduce(1, groupings), 0);
  var period2avg = grouping_labels.reduce(avg_reduce(2, groupings), 0);
  var data = {
      labels: grouping_labels,
      datasets: [{
          label: '{$period1['label']}',
          fill: false,
          backgroundColor: 'rgba(255, 99, 132, 0.4)',
          borderColor: 'rgba(255,99,132,1)',
          borderWidth: 1,
          data: grouping_labels.map(data_map(1, groupings))
      }, {
          label: '{$period2['label']}',
          fill: false,
          backgroundColor: 'rgba(54, 162, 235, 0.4)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1,
          data: grouping_labels.map(data_map(2, groupings))
      }]
  };
  function updateChart(data) {
     var grouping_labels = Object.keys(data.groupings);
     chart.config.data.labels = grouping_labels;
     chart.config.data.datasets[0].label = data.period1.label;
     chart.config.data.datasets[0].data = grouping_labels.map(data_map(1, data.groupings));
     chart.config.data.datasets[1].label = data.period2.label;
     chart.config.data.datasets[1].data = grouping_labels.map(data_map(2, data.groupings));
     var period1avg = grouping_labels.reduce(avg_reduce(1, data.groupings), 0);
     chart.config.options.horizontalLines[0].y = period1avg;
     var period2avg = grouping_labels.reduce(avg_reduce(2, data.groupings), 0);
     chart.config.options.horizontalLines[1].y = period2avg;
     chart.update();
  }
  var chart = new Chart(ctx, {
      type: 'bar',
      data: data,
      options: {
          responsive: true,
          horizontalLines: [{
              y: period1avg,
              style: 'rgba(255,99,132,1)',
          }, {
              y: period2avg,
              style: 'rgba(54, 162, 235, 1)',
          }], scales: {
              xAxes: [{
                  type: 'category',
                  position: 'bottom',
              }],
              yAxes: [{
                  ticks: {
                      min: 0,
                      //max: 100,
                  },
              }]
          }
      }
  });
JS;
$this->registerJs($script);
