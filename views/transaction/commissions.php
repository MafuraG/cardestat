<?php 
use yii\helpers\Json;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Advisor;
use yii\widgets\PjaxAsset;

PjaxAsset::register($this);
$formatter = Yii::$app->formatter;
?>
<h1 class="page-header"><?= Yii::t('app', 'Commission sheets') ?></h1>
<div class="well well-sm">
  <div class="row">
    <form class="form">
      <div class="col-xs-5 col-md-3"> 
        <div class="input-group">
          <span class="input-group-addon"><?= Yii::t('app', 'Year') ?></span>
          <?= Html::dropDownList('year', $year, $years, ['class' => 'form-control input-sm', 'prompt' => '-- ' . Yii::t('app', 'All') . ' --']) ?>
        </div>
      </div>
      <div class="col-xs-5 col-md-3"> 
        <div class="input-group">
          <span class="input-group-addon">Asesor</span>
          <?= Html::dropDownList('advisor_id', $advisor_id, Advisor::listAll(), ['class' => 'form-control input-sm', 'prompt' => '-- ' . Yii::t('app', 'All') . ' --']) ?>
        </div>
      </div>
      <div class="col-md-3"> 
        <!-- PDF: Transacción, Fecha factura, Vendedor, Comprador, Propiedad, Precio venta, Honorarios Cárdenas, Honorarios Colaborador y resto columnas desde Oficina hasta Comisión y los Comentarios.-->
        <a class="btn btn-sm btn-primary btn-print" href="<?= Url::to(['print-commissions', 'advisor_id' => '_advisor_id_', 'year' => '_year_']) ?>">
          <span class="glyphicon glyphicon-export"></span> PDF
        </a>
      </div>
    </form>
  </div>
</div>
<div class="row">
  <div class="col-xs-6 col-sm-3">
    <div class="thumbnail text-center">
      <h5><?= Yii::t('app', 'Invoiced') ?></h5>
      <h4><small><span class="text-info"><?= $formatter->asDecimal($positive_invoiced_euc / 100. , 2) ?></span>
          <span class="text-danger">- <?= $formatter->asDecimal($negative_invoiced_euc / 100. , 2) ?></span></small> = <?= $formatter->asDecimal(($positive_invoiced_euc - $negative_invoiced_euc) / 100. , 2) ?> €</h4>
    </div>
  </div>
  <div class="col-xs-6 col-sm-3">
    <div class="thumbnail text-center">
      <h5><?= Yii::t('app', 'Our Fees') ?></h5>
      <h4><?= $formatter->asDecimal($our_fees_euc / 100. , 2) ?> €</h4>
    </div>
  </div>
  <div class="col-xs-6 col-sm-3">
    <div class="thumbnail text-center">
      <h5><?= Yii::t('app', 'Our Partner\'s Fees') ?></h5>
      <h4><?= $formatter->asDecimal($their_fees_euc / 100. , 2) ?> €</h4>
    </div>
  </div>
  <div class="col-xs-6 col-sm-3">
    <div class="thumbnail text-center">
      <h5><?= Yii::t('app', 'Potential') ?></h5>
      <h4><?= $formatter->asDecimal(($our_fees_euc + $their_fees_euc - $positive_invoiced_euc + $negative_invoiced_euc) / 100., 2) ?> €</h4>
    </div>
  </div>
</div>
<div class="transaction-commissions">
  <?= $this->render('_commission_tables', ['data' => $data, 'year' => $year]) ?>
</div>
<?php
$script = <<< JS
  \$form = $('form.form');
  \$form.find('select').on('change', function() {
      $.pjax({container: '.transaction-commissions', fragment: '.transaction-commissions', data: \$form.serialize(), scrollTo: false});
  });
  $('.btn-print').on('click', function() {
      var href = $(this).attr('href');
      var advisor_id = $('select[name="advisor_id"]').val();
      var year = $('select[name="year"]').val();
      $(this).attr('href', href.replace('_advisor_id_', advisor_id).replace('_year_', year));
  });
  $.pjax.defaults.timeout = 6000;
JS;
$this->registerJs($script);
