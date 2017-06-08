<?php
use yii\helpers\Json;
use app\assets\ChartjsAsset;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;

ChartjsAsset::register($this);

$this->title = strip_tags($title);
$this->params['breadcrumbs'][] = Yii::t('app', 'Charts');
$this->params['breadcrumbs'][] = $title;
$color1 = ['bg' => 'rgba(255, 99, 132, 0.4)', 'border' => 'rgba(255,99,132,1)'];
$color2 = ['bg' => 'rgba(54, 162, 235, 0.4)', 'border' => 'rgba(54, 162, 235, 1)'];
if (!isset($comments)) $comments = '';
?>
<div class="groupings-comparison">
  <h1 class="page-header"><?= $title ?></h1>
  <div class="well well-sm">
    <?= Html::beginForm('', 'get', ['class' => 'form form-inline']) ?>
      <?= Html::hiddenInput('label1', $period1['label']) ?>
      <?= Html::hiddenInput('label2', $period2['label']) ?>
      <div class="form-group color1">
        <label><?= Yii::t('app', 'Period') ?> 1</label>
        <?php $presetRanges = [
            Yii::t('app', 'Current quarter') => ["moment().startOf('quarter')", "moment().endOf('quarter')"],
            Yii::t('app', 'Previous quarter') => ["moment().subtract(1, 'quarter').startOf('quarter')", "moment().subtract(1, 'quarter').endOf('quarter')"],
            Yii::t('app', 'Same quarter previous year') => ["moment().startOf('quarter').subtract(1, 'year')", "moment().endOf('quarter').subtract(1, 'year')"],
            Yii::t('app', 'Current year') => ["moment().startOf('year')", "moment().endOf('year')"],
            Yii::t('app', 'Previous year') => ["moment().subtract(1, 'year').startOf('year')", "moment().subtract(1, 'year').endOf('year')"],
            Yii::t('app', 'Trailing twelve months') => ["moment().subtract(1, 'year').add(1, 'month')", "moment()"],
            Yii::t('app', 'Trailing twenty-four months') => ["moment().subtract(2, 'year').add(1, 'month')", "moment()"],
            Yii::t('app', 'Previous three years') => ["moment().subtract(3, 'year').startOf('year')", "moment().subtract(1, 'year').endOf('year')"],
            Yii::t('app', 'Previous five years') => ["moment().subtract(5, 'year').startOf('year')", "moment().subtract(1, 'year').endOf('year')"],
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
                'ranges' => $presetRanges
            ]
        ]) ?>
      </div>
      <div class="form-group color2">
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
                'ranges' => $presetRanges
            ]
        ]) ?>
      </div>
    <?= Html::endForm() ?>
  </div>
  <p class="lead text-center"><?= $subtitle ?></p>
  <canvas height="100%"></canvas>
  <p class="text-center"><?= $comments ?></p>
</div>
<?php
$groupings_json = Json::encode($groupings);
$presetRanges_json = Json::encode($presetRanges);
$period1_json = Json::encode($period1);
$period2_json = Json::encode($period2);
$script = <<<JS
  var presetRanges = $presetRanges_json;
  var period1 = $period1_json;
  var period2 = $period2_json;
  $('#w0-container').find('.range-value').html(period1['label']);
  $('#w1-container').find('.range-value').html(period2['label']);
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
  function mkRangeLabel(period) {
     var label = period.label;
     if (presetRanges[label] == undefined) label = moment(period.from).format('DD MMM \'YY') + ' - ' + moment(period.to).format('DD MMM \'YY');
     return label;
  }
  var period1avg = grouping_labels.reduce(avg_reduce(1, groupings), 0);
  var period2avg = grouping_labels.reduce(avg_reduce(2, groupings), 0);
  var data = {
      labels: grouping_labels,
      datasets: [{
          label: mkRangeLabel(period1),
          fill: false,
          backgroundColor: '{$color1['bg']}',
          borderColor: '{$color1['border']}',
          borderWidth: 1,
          data: grouping_labels.map(data_map(1, groupings))
      }, {
          label: mkRangeLabel(period2),
          fill: false,
          backgroundColor: '{$color2['bg']}',
          borderColor: '{$color2['border']}',
          borderWidth: 1,
          data: grouping_labels.map(data_map(2, groupings))
      }]
  };
  function updateChart(data) {
     var grouping_labels = Object.keys(data.groupings);
     chart.config.data.labels = grouping_labels;
     chart.config.data.datasets[0].label = mkRangeLabel(data.period1);
     chart.config.data.datasets[1].label = mkRangeLabel(data.period2);
     chart.config.data.datasets[0].data = grouping_labels.map(data_map(1, data.groupings));
     chart.config.data.datasets[1].data = grouping_labels.map(data_map(2, data.groupings));
     var period1avg = grouping_labels.reduce(avg_reduce(1, data.groupings), 0);
     chart.config.options.horizontalLines[0].y = period1avg;
     var period2avg = grouping_labels.reduce(avg_reduce(2, data.groupings), 0);
     chart.config.options.horizontalLines[1].y = period2avg;
     //chart.config.options.scales.yAxes.ticks.max = Math.max.apply(null, chart.config.data.datasets[0].data.concat(chart.config.data.datasets[1].data))*1.04;
     chart.update();
  }
  function ticksCallback(value, index, values) {
      if (value >= 1000000) return (value/1000000).toFixed(2) + 'M'; 
      else if (value >= 1000) return (value/1000).toFixed(2) + 'k';
      else return value.toFixed(2);
  }
  var chart = new Chart(ctx, {
      type: 'bar',
      data: data,
      options: {
          responsive: true,
          animation: {
              onComplete: function(animation) {
                  ctx = this.chart.ctx;
                  chart = this;

                  ctx.fillStyle = 'rgb(70, 90, 119)'; 
                  ctx.textAlign = 'center';
                  ctx.textBaseline = 'bottom';
                  ctx.font = '11px Helvetica';

                  datasets = this.config.data.datasets;
                  datasets.forEach(function (dataset, i) {
                      chart.getDatasetMeta(i).data.forEach(function (p, j) {
                          var y = p._model.y;
                          if (p._model.y < 50) y = p._model.y - 45;
                          ctx.fillText(Math.round(datasets[i].data[j]), p._model.x, y, Math.ceil(p._model.width));
                      });
                  });
              }
          },
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
                  scaleLabel: {
                      display: true,
                      labelString: '$label',
                  },
                  ticks: {
                      userCallback: ticksCallback,
                  },
              }]
          }
      }
  });
  $('.color1 span.form-control').css('background-color', '{$color1['bg']}');
  $('.color2 span.form-control').css('background-color', '{$color2['bg']}');
JS;
$this->registerJs($script);
