<?php
use yii\helpers\Json;
use app\assets\ChartjsAsset;
ChartjsAsset::register($this);
echo $this->render('_double_period', [
    'title' => $title,
    'subtitle' => $subtitle,
    'comments' => $comments,
    'sums' => $sums,
    'period' => $period,
    'label1' => $label1,
    'label2' => $label2,
    'from' => $from,
    'to' => $to,
    'transaction_type' => $transaction_type,
    'interval_months' => $interval_months,
    'intervals' => $intervals,
]);
$sums_json = Json::encode($sums);
$script = <<<JS
  var last_data = {};
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
      if (value >= 1000000) return (value/1000000).toFixed(0) + 'M'; 
      else if (value >= 1000) return (value/1000).toFixed(0) + 'k';
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
                          var y = p._model.y -10;
                          if (y < 50) y = p._model.y - 15;
                          ctx.fillText(Math.round(datasets[i].data[j]), p._model.x + (i*2 -1)*30, y);
                      });
                  });
              }
          },
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
